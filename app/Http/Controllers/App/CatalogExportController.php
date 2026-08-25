<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Actions\ExportCatalog;
use App\Enums\ExportFormat;
use App\Http\Controllers\Controller;
use App\Models\Catalog;
use App\Services\CatalogStatistics;
use App\Services\CollectionExportPayload;
use App\Services\ExportBlueprint;
use App\ValueObjects\ExportSelection;
use App\ViewModels\CollectionExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * The export screen of a collection, and the file it produces.
 *
 * It is its own controller because an export is not part of the collection
 * resource: the screen picks a format and a set of fields, and the second action
 * answers with a file rather than a page. Reading it is open to any role, the
 * same as the collection it comes from.
 *
 * The selection posts rather than being carried in the query string: it is over a
 * hundred keys, which no url should have to hold.
 */
class CatalogExportController extends Controller
{
    public function show(Request $request): View
    {
        $catalog = $request->attributes->get('catalog');

        return view('app.catalogs.export', [
            'export' => $this->viewModel($catalog),
            'defaultFormat' => ExportFormat::Pdf,
        ]);
    }

    public function create(Request $request): BinaryFileResponse
    {
        $catalog = $request->attributes->get('catalog');
        $blueprint = ExportBlueprint::forCollection();

        $validated = $request->validate([
            'format' => ['required', Rule::enum(ExportFormat::class)],
            'sections' => ['array'],
            'sections.*' => ['string', Rule::in($blueprint->sectionKeys())],
            'fields' => ['array'],
            'fields.*' => ['string', Rule::in($blueprint->fieldKeys())],
            'options' => ['array'],
            'options.*' => ['boolean'],
        ]);

        $selection = ExportSelection::fromRequest($validated, $blueprint);

        $path = new ExportCatalog(
            user: $request->user(),
            catalog: $catalog,
            selection: $selection,
        )->execute();

        // The file was written to a temporary path, so it is sent and deleted
        // rather than left behind for a sweep to find later.
        return Response::download($path, $this->viewModel($catalog)->fileName($selection->format), [
            'Content-Type' => $selection->format->mimeType(),
        ])->deleteFileAfterSend(true);
    }

    private function viewModel(Catalog $catalog): CollectionExport
    {
        $blueprint = ExportBlueprint::forCollection();
        $totals = new CatalogStatistics(catalog: $catalog)->totals();

        // The document count is the payload's to work out: a document hangs off a
        // copy polymorphically, so counting them is the same query the register
        // is built from.
        $documents = new CollectionExportPayload(
            catalog: $catalog,
            selection: ExportSelection::everything($blueprint),
            blueprint: $blueprint,
        )->documentCount();

        return new CollectionExport(
            catalog: $catalog,
            blueprint: $blueprint,
            itemCount: $totals['items'],
            copyCount: $totals['copies'],
            documentCount: $documents,
        );
    }
}
