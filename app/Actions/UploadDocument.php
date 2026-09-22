<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\DocumentType;
use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Models\Account;
use App\Models\Copy;
use App\Models\Document;
use App\Models\User;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Attach a document to a copy or one of the records hanging off it, either by
 * storing an uploaded file or by pointing at an external URL. Only owners and
 * editors of the copy's account may do so.
 */
class UploadDocument
{
    private Document $document;

    private ?string $path = null;

    public function __construct(
        private readonly User $user,
        private readonly Copy $copy,
        private readonly Model $documentable,
        private readonly DocumentType $type,
        private readonly string $name,
        private readonly ?UploadedFile $file = null,
        private readonly ?string $externalUrl = null,
        private readonly ?string $description = null,
        private readonly ?string $issuedAt = null,
        private readonly ?string $referenceNumber = null,
    ) {}

    public function execute(): Document
    {
        $this->validate();
        $this->store();
        $this->create();
        $this->stampAuthor();
        $this->log();

        return $this->document;
    }

    private function validate(): void
    {
        if (! $this->account()->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        if ($this->file === null && ($this->externalUrl === null || $this->externalUrl === '')) {
            throw new InvalidArgumentException('A document needs either a file or an external URL');
        }

        if ($this->file === null) {
            return;
        }

        if (! in_array($this->file->getMimeType(), config('documents.allowed_mime_types'), true)) {
            throw new InvalidArgumentException('The file type is not allowed');
        }

        if ($this->file->getSize() > (int) config('documents.max_size_in_kilobytes') * 1024) {
            throw new InvalidArgumentException('The file is larger than the allowed size');
        }
    }

    private function store(): void
    {
        if ($this->file === null) {
            return;
        }

        $name = Str::uuid()->toString().'.'.$this->file->extension();

        $this->path = (string) $this->disk()->putFileAs('documents/'.$this->account()->id, $this->file, $name);
    }

    private function create(): void
    {
        $this->document = Document::query()->create([
            'account_id' => $this->account()->id,
            'documentable_type' => $this->documentable->getMorphClass(),
            'documentable_id' => $this->documentable->getKey(),
            'type' => $this->type,
            'name' => $this->name,
            'path' => $this->path,
            // A stored file wins, so its URL is dropped rather than kept alongside.
            'external_url' => $this->path === null ? $this->externalUrl : null,
            'mime_type' => $this->file?->getMimeType(),
            'size' => $this->file?->getSize(),
            'description' => $this->description,
            'issued_at' => $this->issuedAt,
            'reference_number' => $this->referenceNumber,
        ]);
    }

    private function stampAuthor(): void
    {
        $this->document->created_by_id = $this->user->id;
        $this->document->created_by_name = $this->user->getFullName();
        $this->document->updated_by_id = $this->user->id;
        $this->document->updated_by_name = $this->user->getFullName();
        $this->document->save();
    }

    private function account(): Account
    {
        return $this->copy->item->catalog->account;
    }

    private function disk(): Filesystem
    {
        return Storage::disk((string) config('filesystems.default'));
    }

    private function log(): void
    {
        $item = $this->copy->item;

        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::DocumentUpload,
            parameters: ['name' => $item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $item,
            user: $this->user,
            action: ItemActionEnum::DocumentUpload,
            parameters: ['label' => $this->name],
        )->onQueue('low');
    }
}
