<?php

declare(strict_types=1);

use App\Actions\ExportCatalog;
use App\Enums\ExportFormat;
use App\Enums\PermissionEnum;
use App\Models\Catalog;
use App\Models\Copy;
use App\Models\Item;
use App\Services\ExportBlueprint;
use App\ValueObjects\ExportSelection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function stamfordBranch(int $accountId): Catalog
{
    $catalog = Catalog::factory()->create([
        'account_id' => $accountId,
        'name' => 'Dunder Mifflin Memorabilia',
        'currency' => 'USD',
    ]);

    $item = Item::factory()->create(['catalog_id' => $catalog->id, 'name' => 'Dundie Award']);
    Copy::factory()->create(['item_id' => $item->id, 'identifier' => 'DUNDIE-1']);

    return $catalog;
}

it('writes a pdf for a collection of the account', function (): void {
    $user = $this->createUser();
    $catalog = stamfordBranch($user->account_id);

    $path = new ExportCatalog(
        user: $user,
        catalog: $catalog,
        selection: ExportSelection::everything(ExportBlueprint::forCollection(), ExportFormat::Pdf),
    )->execute();

    expect(file_get_contents($path))->toStartWith('%PDF-');
});

it('writes a workbook for a collection of the account', function (): void {
    $user = $this->createUser();
    $catalog = stamfordBranch($user->account_id);

    $path = new ExportCatalog(
        user: $user,
        catalog: $catalog,
        selection: ExportSelection::everything(ExportBlueprint::forCollection(), ExportFormat::Excel),
    )->execute();

    expect(file_get_contents($path))->toStartWith('PK');
});

it('cannot export a collection of another account', function (): void {
    $user = $this->createUser();
    $catalog = stamfordBranch($this->createAccount('Vance Refrigeration')->id);

    expect(fn (): string => new ExportCatalog(
        user: $user,
        catalog: $catalog,
        selection: ExportSelection::everything(ExportBlueprint::forCollection()),
    )->execute())->toThrow(ModelNotFoundException::class);
});

it('lets a viewer of the account export', function (): void {
    $account = $this->createAccount('Dunder Mifflin');
    $user = $this->assignUserToAccount($this->createUser(), $account, PermissionEnum::Viewer->value);
    $catalog = stamfordBranch($account->id);

    $path = new ExportCatalog(
        user: $user,
        catalog: $catalog,
        selection: ExportSelection::everything(ExportBlueprint::forCollection()),
    )->execute();

    expect(file_exists($path))->toBeTrue();
});
