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
 * Produce the export file for one whole collection. Any member of the account
 * may do so.
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
