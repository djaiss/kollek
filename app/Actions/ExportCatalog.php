<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\ExportWriter;
use App\Enums\ExportFormat;
use App\Models\Catalog;
use App\Models\User;
use App\Services\CollectionExportPayload;
use App\Services\ExportBlueprint;
use App\Services\ExportExcelWriter;
use App\Services\ExportPdfWriter;
use App\ValueObjects\ExportSelection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Produce the export file for one whole collection.
 *
 * Four lines of actual work: check the caller may read the collection, build the
 * payload, hand it to the writer for the chosen format, and return where the file
 * landed. Everything that decides what goes in the file lives in the payload, and
 * everything that decides how it looks lives in the writer, which is what will
 * let the single item export be this same shape with a different payload.
 *
 * Reading is open to any member of the account, the same as the statistics screen
 * and the collection itself: an export shows nothing a member cannot already
 * browse.
 */
class ExportCatalog
{
    public function __construct(
        private readonly User $user,
        private readonly Catalog $catalog,
        private readonly ExportSelection $selection,
    ) {}

    /**
     * The absolute path of the file that was written. The caller owns it, and is
     * the one that deletes it once it has been sent.
     */
    public function execute(): string
    {
        $this->validate();

        $blueprint = ExportBlueprint::forCollection();

        return $this->writer($blueprint)->write(new CollectionExportPayload(
            catalog: $this->catalog,
            selection: $this->selection,
            blueprint: $blueprint,
        ));
    }

    private function validate(): void
    {
        if ($this->catalog->account_id !== $this->user->account_id) {
            throw new ModelNotFoundException('Collection not found');
        }
    }

    private function writer(ExportBlueprint $blueprint): ExportWriter
    {
        return match ($this->selection->format) {
            ExportFormat::Pdf => new ExportPdfWriter(blueprint: $blueprint),
            ExportFormat::Excel => new ExportExcelWriter(blueprint: $blueprint),
        };
    }
}
