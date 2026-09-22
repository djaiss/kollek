<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ItemPhoto;
use App\Models\ItemPhotoSearchToken;
use App\Services\BlindIndex;
use Illuminate\Support\Facades\DB;

/**
 * Rebuild the search hashes of a photo from its file name and the name of the
 * item it belongs to. This is bookkeeping the application does for itself, so it
 * takes no user and checks no role.
 */
class IndexItemPhotoSearchTokens
{
    public function __construct(
        private readonly ItemPhoto $itemPhoto,
    ) {}

    public function execute(): void
    {
        $rows = $this->rows();

        DB::transaction(function () use ($rows): void {
            $this->itemPhoto->photoSearchTokens()->delete();

            if ($rows === []) {
                return;
            }

            ItemPhotoSearchToken::query()->insert($rows);
        });
    }

    /**
     * @return list<array{item_photo_id: int, token: string, created_at: string, updated_at: string}>
     */
    private function rows(): array
    {
        $now = now()->toDateTimeString();

        $hashes = BlindIndex::hashesFor(
            $this->itemPhoto->filename,
            $this->itemPhoto->item->name,
        );

        return array_map(fn (string $hash): array => [
            'item_photo_id' => $this->itemPhoto->id,
            'token' => $hash,
            'created_at' => $now,
            'updated_at' => $now,
        ], $hashes);
    }
}
