<?php

declare(strict_types=1);

use App\Enums\PermissionEnum;
use App\Models\Catalog;
use App\Models\Copy;
use App\Models\Item;
use App\Services\ExportBlueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * A collection with one item and one copy in it, which is the least an export
 * needs to have something to write.
 */
function dunderMifflinCollection(int $accountId): Catalog
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

/**
 * The payload a fully ticked form posts.
 */
function everySection(string $format): array
{
    $blueprint = ExportBlueprint::forCollection();

    return [
        'format' => $format,
        'sections' => $blueprint->sectionKeys(),
        'fields' => $blueprint->fieldKeys(),
        'options' => ['thumbnails' => '1', 'page_per_item' => '1'],
    ];
}

it('shows the export screen of a collection', function (): void {
    $user = $this->createUser();
    $catalog = dunderMifflinCollection($user->account_id);

    $response = $this->actingAs($user)->get('/collections/'.$catalog->id.'/export');

    $response->assertStatus(200)
        ->assertSee('Export the collection')
        ->assertSee('Cover page')
        ->assertSee('Detailed sheet per item')
        ->assertSee('PDF document')
        ->assertSee('Excel workbook');
});

it('shows a breadcrumb back to the collection', function (): void {
    $user = $this->createUser();
    $catalog = dunderMifflinCollection($user->account_id);

    $this->actingAs($user)->get('/collections/'.$catalog->id.'/export')
        ->assertSeeInOrder(['Collections', 'Dunder Mifflin Memorabilia', 'Export'])
        ->assertSee(route('collections.show', $catalog->id), false);
});

it('lets a viewer reach the export screen', function (): void {
    $account = $this->createAccount('Dunder Mifflin');
    $user = $this->assignUserToAccount($this->createUser(), $account, PermissionEnum::Viewer->value);
    $catalog = dunderMifflinCollection($account->id);

    $this->actingAs($user)->get('/collections/'.$catalog->id.'/export')->assertStatus(200);
});

it('cannot see the export screen of another account', function (): void {
    $user = $this->createUser();
    $catalog = dunderMifflinCollection($this->createAccount('Vance Refrigeration')->id);

    $this->actingAs($user)->get('/collections/'.$catalog->id.'/export')->assertStatus(404);
});

it('generates a pdf', function (): void {
    $user = $this->createUser();
    $catalog = dunderMifflinCollection($user->account_id);

    $response = $this->actingAs($user)
        ->post('/collections/'.$catalog->id.'/export', everySection('pdf'));

    $response->assertStatus(200)
        ->assertHeader('content-type', 'application/pdf');

    expect($response->streamedContent())->toStartWith('%PDF-');
});

it('generates a workbook', function (): void {
    $user = $this->createUser();
    $catalog = dunderMifflinCollection($user->account_id);

    $response = $this->actingAs($user)
        ->post('/collections/'.$catalog->id.'/export', everySection('xlsx'));

    $response->assertStatus(200)
        ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    // A xlsx file is a zip archive, so its first bytes say so.
    expect($response->streamedContent())->toStartWith('PK');
});

it('names the file after the collection and the day', function (): void {
    $user = $this->createUser();
    $catalog = dunderMifflinCollection($user->account_id);

    $this->actingAs($user)
        ->post('/collections/'.$catalog->id.'/export', everySection('pdf'))
        ->assertDownload('dunder-mifflin-memorabilia-'.now()->format('Y-m-d').'.pdf');
});

it('cannot export a collection of another account', function (): void {
    $user = $this->createUser();
    $catalog = dunderMifflinCollection($this->createAccount('Vance Refrigeration')->id);

    $this->actingAs($user)
        ->post('/collections/'.$catalog->id.'/export', everySection('pdf'))
        ->assertStatus(404);
});

it('refuses an unknown format', function (): void {
    $user = $this->createUser();
    $catalog = dunderMifflinCollection($user->account_id);

    $this->actingAs($user)
        ->post('/collections/'.$catalog->id.'/export', [...everySection('pdf'), 'format' => 'docx'])
        ->assertSessionHasErrors('format');
});

it('refuses a field the blueprint does not know', function (): void {
    $user = $this->createUser();
    $catalog = dunderMifflinCollection($user->account_id);

    $this->actingAs($user)
        ->post('/collections/'.$catalog->id.'/export', [...everySection('pdf'), 'fields' => ['cover.name', 'cover.salary']])
        ->assertSessionHasErrors('fields.1');
});

it('generates a pdf with nothing ticked', function (): void {
    $user = $this->createUser();
    $catalog = dunderMifflinCollection($user->account_id);

    $response = $this->actingAs($user)
        ->post('/collections/'.$catalog->id.'/export', ['format' => 'pdf']);

    $response->assertStatus(200);
    expect($response->streamedContent())->toStartWith('%PDF-');
});

it('leaves out a section that was not ticked', function (): void {
    $user = $this->createUser();
    $catalog = dunderMifflinCollection($user->account_id);
    $blueprint = ExportBlueprint::forCollection();

    $withCover = $this->actingAs($user)->post('/collections/'.$catalog->id.'/export', [
        'format' => 'xlsx',
        'sections' => ['cover', 'inventory'],
        'fields' => $blueprint->fieldKeys(),
    ])->streamedContent();

    $withoutCover = $this->actingAs($user)->post('/collections/'.$catalog->id.'/export', [
        'format' => 'xlsx',
        'sections' => ['inventory'],
        'fields' => $blueprint->fieldKeys(),
    ])->streamedContent();

    expect(strlen((string) $withoutCover))->toBeLessThan(strlen((string) $withCover));
});
