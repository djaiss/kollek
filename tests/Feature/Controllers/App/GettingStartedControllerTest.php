<?php

declare(strict_types=1);
use App\Enums\PermissionEnum;
use App\Models\Catalog;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('shows the getting started screen', function () {
    $user = $this->createUser(['first_name' => 'Ross']);

    $response = $this->actingAs($user)->get('/getting-started');

    $response->assertOk()
        ->assertSee('Hi Ross, so glad you are here.', false)
        ->assertSee('Configure collection types')
        ->assertSee('Configure tags')
        ->assertSee('Add other members')
        ->assertSee('Add locations')
        ->assertSee('Add your first collection')
        ->assertSee('0 of 5 done');
});

it('reports progress as the account fills up', function () {
    $user = $this->createUser();
    Tag::factory()->create(['account_id' => $user->account_id]);
    Catalog::factory()->create(['account_id' => $user->account_id]);

    $this->actingAs($user)->get('/getting-started')
        ->assertOk()
        ->assertSee('2 of 5 done');
});

// The dashboard is where every route out of authentication lands, so the redirect is what
// makes this the screen an empty account actually sees.
it('sends an empty account from the dashboard to the getting started screen', function () {
    $user = $this->createUser();

    $this->actingAs($user)->get('/dashboard')->assertRedirect('/getting-started');
});

it('shows the dashboard once the account has a collection', function () {
    $user = $this->createUser();
    Catalog::factory()->create(['account_id' => $user->account_id]);

    $this->actingAs($user)->get('/dashboard')->assertOk();
});

it('shows the dashboard once the screen has been dismissed', function () {
    $user = $this->createUser();
    $user->account->update(['show_getting_started' => false]);

    $this->actingAs($user)->get('/dashboard')->assertOk();
});

it('puts a getting started link in the sidebar while the screen is on', function () {
    $user = $this->createUser();
    Catalog::factory()->create(['account_id' => $user->account_id]);

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertSee('Getting started')
        ->assertSee(route('gettingStarted.index'));
});

it('takes the sidebar link away once the screen has been dismissed', function () {
    $user = $this->createUser();
    $user->account->update(['show_getting_started' => false]);
    Catalog::factory()->create(['account_id' => $user->account_id]);

    $this->actingAs($user)->get('/dashboard')
        ->assertOk()
        ->assertDontSee(route('gettingStarted.index'));
});

it('lets an owner dismiss the screen', function () {
    Queue::fake();

    $account = $this->createAccount();
    $owner = $this->createUser();
    $this->assignUserToAccount(user: $owner, account: $account, role: PermissionEnum::Owner->value);

    $response = $this->actingAs($owner)->delete('/getting-started');

    $response->assertRedirect('/dashboard')
        ->assertSessionHas('status', 'Getting started dismissed');

    expect($account->refresh()->show_getting_started)->toBeFalse();
});

it('offers the dismiss button to an owner', function () {
    $account = $this->createAccount();
    $owner = $this->createUser();
    $this->assignUserToAccount(user: $owner, account: $account, role: PermissionEnum::Owner->value);

    $this->actingAs($owner)->get('/getting-started')
        ->assertOk()
        ->assertSee('Skip for now, take me to my dashboard');
});

// Dismissing hides the screen for the whole account, so it is not an editor's call.
it('does not offer the dismiss button to an editor', function () {
    $account = $this->createAccount();
    $editor = $this->createUser();
    $this->assignUserToAccount(user: $editor, account: $account, role: PermissionEnum::Editor->value);

    $this->actingAs($editor)->get('/getting-started')
        ->assertOk()
        ->assertDontSee('Skip for now, take me to my dashboard')
        ->assertSee('Only an owner can hide this screen for the account.');
});

it('lets a viewer read the screen', function () {
    $account = $this->createAccount();
    $viewer = $this->createUser();
    $this->assignUserToAccount(user: $viewer, account: $account, role: PermissionEnum::Viewer->value);

    $this->actingAs($viewer)->get('/getting-started')->assertOk();
});

it('does not let an editor dismiss the screen', function () {
    Queue::fake();

    $account = $this->createAccount();
    $editor = $this->createUser();
    $this->assignUserToAccount(user: $editor, account: $account, role: PermissionEnum::Editor->value);

    $this->actingAs($editor)->delete('/getting-started')->assertForbidden();

    expect($account->refresh()->show_getting_started)->toBeTrue();
});

it('does not let a viewer dismiss the screen', function () {
    Queue::fake();

    $account = $this->createAccount();
    $viewer = $this->createUser();
    $this->assignUserToAccount(user: $viewer, account: $account, role: PermissionEnum::Viewer->value);

    $this->actingAs($viewer)->delete('/getting-started')->assertForbidden();

    expect($account->refresh()->show_getting_started)->toBeTrue();
});

it('requires a logged in user', function () {
    $this->get('/getting-started')->assertRedirect('/login');
});
