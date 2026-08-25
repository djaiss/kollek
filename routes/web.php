<?php

declare(strict_types=1);

use App\Http\Controllers\App\Account\AccountController;
use App\Http\Controllers\App\Account\InvitationController;
use App\Http\Controllers\App\Account\MemberController;
use App\Http\Controllers\App\CatalogController;
use App\Http\Controllers\App\CatalogExportController;
use App\Http\Controllers\App\CatalogItemViewController;
use App\Http\Controllers\App\CatalogTypeController;
use App\Http\Controllers\App\CatalogTypeExportController;
use App\Http\Controllers\App\CatalogTypeImportController;
use App\Http\Controllers\App\CategoryController;
use App\Http\Controllers\App\CustomFieldController;
use App\Http\Controllers\App\CustomFieldGroupController;
use App\Http\Controllers\App\CustomFieldGroupFieldController;
use App\Http\Controllers\App\CustomFieldGroupOrderController;
use App\Http\Controllers\App\CustomFieldOrderController;
use App\Http\Controllers\App\DashboardController;
use App\Http\Controllers\App\DashboardSectionController;
use App\Http\Controllers\App\DocumentController;
use App\Http\Controllers\App\DocumentDownloadController;
use App\Http\Controllers\App\GettingStartedController;
use App\Http\Controllers\App\Instance\AccountController as InstanceAccountController;
use App\Http\Controllers\App\Instance\BlogPostController as InstanceBlogPostController;
use App\Http\Controllers\App\Instance\BlogPostOgImageController as InstanceBlogPostOgImageController;
use App\Http\Controllers\App\Instance\BlogPostTranslationController as InstanceBlogPostTranslationController;
use App\Http\Controllers\App\Instance\CloudflareCacheController as InstanceCloudflareCacheController;
use App\Http\Controllers\App\Instance\DeletionReasonController as InstanceDeletionReasonController;
use App\Http\Controllers\App\Instance\OverviewController as InstanceOverviewController;
use App\Http\Controllers\App\Instance\SiteOptionController as InstanceSiteOptionController;
use App\Http\Controllers\App\Instance\SupportController as InstanceSupportController;
use App\Http\Controllers\App\Instance\SupportMessageController as InstanceSupportMessageController;
use App\Http\Controllers\App\Instance\TestimonialController as InstanceTestimonialController;
use App\Http\Controllers\App\Instance\UserController as InstanceUserController;
use App\Http\Controllers\App\InsuranceRecordController;
use App\Http\Controllers\App\ItemActivitiesController;
use App\Http\Controllers\App\ItemConditionController;
use App\Http\Controllers\App\ItemController;
use App\Http\Controllers\App\ItemCopiesController;
use App\Http\Controllers\App\ItemHistoryController;
use App\Http\Controllers\App\ItemPhotoController;
use App\Http\Controllers\App\ItemTagController;
use App\Http\Controllers\App\LoanController;
use App\Http\Controllers\App\LoanExportController;
use App\Http\Controllers\App\LoanReturnController;
use App\Http\Controllers\App\LoansController;
use App\Http\Controllers\App\LocationController;
use App\Http\Controllers\App\LocationHistoryController;
use App\Http\Controllers\App\MaintenanceRecordController;
use App\Http\Controllers\App\ProvenanceEventController;
use App\Http\Controllers\App\PurchaseConfirmationController;
use App\Http\Controllers\App\SearchController;
use App\Http\Controllers\App\SeriesController;
use App\Http\Controllers\App\SetController;
use App\Http\Controllers\App\Settings\ApiKeyController;
use App\Http\Controllers\App\Settings\AutoDeleteUserController;
use App\Http\Controllers\App\Settings\AvatarController;
use App\Http\Controllers\App\Settings\EmailSentController;
use App\Http\Controllers\App\Settings\GettingStartedController as SettingsGettingStartedController;
use App\Http\Controllers\App\Settings\LogController;
use App\Http\Controllers\App\Settings\PasswordController;
use App\Http\Controllers\App\Settings\PhotoController;
use App\Http\Controllers\App\Settings\PhotoCoverController;
use App\Http\Controllers\App\Settings\PhotoSelectionController;
use App\Http\Controllers\App\Settings\PhotoViewController;
use App\Http\Controllers\App\Settings\RecoveryCodeController;
use App\Http\Controllers\App\Settings\SecurityController;
use App\Http\Controllers\App\Settings\SettingsController;
use App\Http\Controllers\App\Settings\TestimonialController as SettingsTestimonialController;
use App\Http\Controllers\App\Settings\TwoFAController;
use App\Http\Controllers\App\Settings\UserController;
use App\Http\Controllers\App\Settings\WebhookController;
use App\Http\Controllers\App\StatisticsController;
use App\Http\Controllers\App\Support\MessageController as SupportMessageController;
use App\Http\Controllers\App\Support\TicketController as SupportTicketController;
use App\Http\Controllers\App\TagController;
use App\Http\Controllers\App\TransactionController;
use App\Http\Controllers\App\TrashController;
use App\Http\Controllers\App\UpgradeController;
use App\Http\Controllers\App\ValuationController;
use App\Http\Controllers\LocaleController;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

// The public site is not required here. It runs outside the web middleware
// group so its responses carry no session cookie and can be cached by a CDN,
// which means it is registered from bootstrap/app.php instead.

Route::put('/locale', [LocaleController::class, 'update'])->name('locale.update');

Route::middleware(['auth', 'verified', 'throttle:60,1', 'set.locale'])->group(function (): void {
    // dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    // which blocks of the dashboard a member keeps on screen is a private preference, so any role may set it
    Route::put('dashboard/sections', [DashboardSectionController::class, 'update'])->name('dashboard.sections.update');

    // getting started — the screen a new account lands on until it has something in it.
    // Reading it is open to any role; dismissing it changes the whole account, so it is not.
    Route::get('getting-started', [GettingStartedController::class, 'index'])->name('gettingStarted.index');

    // placeholder sections for the future collection domain
    Route::get('collections', [CatalogController::class, 'index'])->name('collections.index');

    // Everything below a collection resolves it first. The `collection`
    // middleware answers 404 for anything outside the account, and hands the
    // model to the controllers and the views, so no screen looks it up again.
    // The `item` and `copy` middleware do the same one and two levels down,
    // each through the one above it.
    //
    // Laravel's own binding is switched off here on purpose: it would see the
    // models these controllers type hint and resolve them itself, by id alone
    // and across every account, before our middleware ever ran.
    Route::prefix('collections/{collection}')->whereNumber('collection')->withoutMiddleware(SubstituteBindings::class)->middleware(['catalog'])->group(function (): void {
        Route::get('', [CatalogController::class, 'show'])->name('collections.show');
        // remembering which items view a member last opened is a private preference, so any role may set it
        Route::put('item-view', [CatalogItemViewController::class, 'update'])->name('collections.item-view.update');
        // browsing the categories of a collection is read only, so any role may do it
        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/{category}', [CategoryController::class, 'show'])->whereNumber('category')->name('categories.show');
        // browsing the sets of a collection is read only, so any role may do it
        Route::get('sets', [SetController::class, 'index'])->name('sets.index');
        // the statistics of a collection are read only, so any role may see them
        Route::get('statistics', [StatisticsController::class, 'index'])->name('statistics.index');

        // exporting a collection reads it and writes nothing, so any role may do
        // it. The selection runs to over a hundred field keys, which is why the
        // file is asked for with a post rather than through the query string.
        Route::get('export', [CatalogExportController::class, 'show'])->name('collections.export.show');
        Route::post('export', [CatalogExportController::class, 'create'])->name('collections.export.create');

        // viewing an item is read only, so any role may do it
        // each tab of an item is its own page, with overview living on the item's own url
        Route::prefix('items/{item}')->whereNumber('item')->middleware(['item'])->group(function (): void {
            Route::get('', [ItemController::class, 'show'])->name('items.show');
            Route::get('copies', [ItemCopiesController::class, 'index'])->name('items.copies.index');
            // The history is read one copy at a time, so a copy lives in the url. The
            // bare url lands on the first copy; each copy pill links to its own, and each
            // section is its own url rather than a query parameter. The copy is picked out
            // of the ones already loaded on the item rather than resolved on its own.
            Route::get('history', [ItemHistoryController::class, 'index'])->name('items.history.index');
            Route::get('history/{copy}/{section?}', [ItemHistoryController::class, 'show'])->whereNumber('copy')->where('section', '[a-z]+')->name('items.history.show');
            Route::get('activities', [ItemActivitiesController::class, 'index'])->name('items.activities.index');
        });
    });

    Route::get('items/photos/{itemPhoto}', [ItemPhotoController::class, 'show'])->where('itemPhoto', '[1-9][0-9]*')->name('items.photos.show');
    // documents live on the private disk, so their files are streamed through here rather than served directly, and only to the account they belong to
    Route::get('documents/{document}', [DocumentDownloadController::class, 'show'])->where('document', '[1-9][0-9]*')->name('documents.show');
    // series are account-wide rather than per collection, so they hang off the dashboard
    // instead of a collection. Browsing them is read only, so any role may do it.
    Route::get('series', [SeriesController::class, 'index'])->name('series.index');
    Route::get('series/{series}', [SeriesController::class, 'show'])->where('series', '[1-9][0-9]*')->name('series.show');
    Route::get('locations', [LocationController::class, 'index'])->name('locations.index');

    // loans — the account-wide custody dashboard. Reading it is open to any role;
    // the create, return, edit and delete forms post to the copy-scoped routes
    // below, which gate on the owner or editor role. Direction, the open tab and
    // the selected loan all live in the path so every view has its own url; only
    // the filter bar and search use the query string.
    Route::get('loans', [LoansController::class, 'index'])->name('loans.index');
    Route::get('loans/{direction}/new', [LoansController::class, 'new'])->where('direction', 'lent-out|borrowed-in')->name('loans.new');
    Route::get('loans/{direction}/export', [LoanExportController::class, 'show'])->where('direction', 'lent-out|borrowed-in')->name('loans.export.show');
    Route::get('loans/{direction}/{tab?}/{loan?}', [LoansController::class, 'show'])->where(['direction' => 'lent-out|borrowed-in', 'tab' => 'all|due|risk|by-party|deposits|timeline', 'loan' => '[1-9][0-9]*'])->name('loans.show');

    // account wide search. The words typed are a free-form refinement, so they
    // live in the query string; narrowing the results to one kind of record is a
    // click, so it is a segment of the path and has its own url.
    Route::get('search/{type?}', [SearchController::class, 'index'])
        ->where('type', 'items|collections|copies|photos|loans|locations|sets|series|categories|tags|documents')
        ->name('search.index');

    // collections — owners and editors may create new collections
    Route::middleware(['editor'])->group(function (): void {
        Route::get('collections/new', [CatalogController::class, 'new'])->name('collections.new');
        Route::post('collections', [CatalogController::class, 'create'])->name('collections.create');
    });

    // everything owners and editors may change inside a collection, under the
    // same resolution as the read only routes above
    Route::prefix('collections/{collection}')->whereNumber('collection')->withoutMiddleware(SubstituteBindings::class)->middleware(['catalog', 'editor'])->group(function (): void {
        Route::get('edit', [CatalogController::class, 'edit'])->name('collections.edit');
        Route::put('', [CatalogController::class, 'update'])->name('collections.update');
        Route::delete('', [CatalogController::class, 'destroy'])->name('collections.destroy');

        // categories — owners and editors may create, update and delete the categories of a collection
        Route::post('categories', [CategoryController::class, 'create'])->name('categories.create');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->whereNumber('category')->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->whereNumber('category')->name('categories.destroy');

        // sets — owners and editors may create, update and delete the sets of a collection
        Route::post('sets', [SetController::class, 'create'])->name('sets.create');
        Route::put('sets/{set}', [SetController::class, 'update'])->whereNumber('set')->name('sets.update');
        Route::delete('sets/{set}', [SetController::class, 'destroy'])->whereNumber('set')->name('sets.destroy');

        // items — owners and editors may add items to a collection and edit them
        Route::get('items/new', [ItemController::class, 'new'])->name('items.new');
        Route::post('items', [ItemController::class, 'create'])->name('items.create');

        Route::prefix('items/{item}')->whereNumber('item')->middleware(['item'])->group(function (): void {
            Route::get('edit', [ItemController::class, 'edit'])->name('items.edit');
            Route::put('', [ItemController::class, 'update'])->name('items.update');
            Route::delete('', [ItemController::class, 'destroy'])->name('items.destroy');

            // tags are put on and taken off an item from the item screen itself, one at a time
            Route::post('tags', [ItemTagController::class, 'create'])->name('items.tags.create');
            Route::delete('tags/{tag}', [ItemTagController::class, 'destroy'])->whereNumber('tag')->name('items.tags.destroy');

            // everything that is recorded against a single copy rather than the item
            Route::prefix('copies/{copy}')->whereNumber('copy')->middleware(['copy'])->group(function (): void {
                // transactions — owners and editors record what a copy cost, sold for or was traded against
                Route::post('transactions', [TransactionController::class, 'create'])->name('transactions.create');
                Route::put('transactions/{transaction}', [TransactionController::class, 'update'])->whereNumber('transaction')->name('transactions.update');
                Route::delete('transactions/{transaction}', [TransactionController::class, 'destroy'])->whereNumber('transaction')->name('transactions.destroy');

                // provenance events — owners and editors record the story of a copy: who owned it, where it was shown, how it was authenticated
                Route::post('provenance-events', [ProvenanceEventController::class, 'create'])->name('provenanceEvents.create');
                Route::put('provenance-events/{provenanceEvent}', [ProvenanceEventController::class, 'update'])->whereNumber('provenanceEvent')->name('provenanceEvents.update');
                Route::delete('provenance-events/{provenanceEvent}', [ProvenanceEventController::class, 'destroy'])->whereNumber('provenanceEvent')->name('provenanceEvents.destroy');

                // valuations — owners and editors record what a copy is reckoned to be worth over time
                Route::post('valuations', [ValuationController::class, 'create'])->name('valuations.create');
                Route::put('valuations/{valuation}', [ValuationController::class, 'update'])->whereNumber('valuation')->name('valuations.update');
                Route::delete('valuations/{valuation}', [ValuationController::class, 'destroy'])->whereNumber('valuation')->name('valuations.destroy');

                // insurance records — owners and editors record what a copy is insured for as policies and values change
                Route::post('insurance-records', [InsuranceRecordController::class, 'create'])->name('insuranceRecords.create');
                Route::put('insurance-records/{insuranceRecord}', [InsuranceRecordController::class, 'update'])->whereNumber('insuranceRecord')->name('insuranceRecords.update');
                Route::delete('insurance-records/{insuranceRecord}', [InsuranceRecordController::class, 'destroy'])->whereNumber('insuranceRecord')->name('insuranceRecords.destroy');

                // maintenance records — owners and editors log the work done on a copy, its condition before and after, and when it is next due
                Route::post('maintenance-records', [MaintenanceRecordController::class, 'create'])->name('maintenanceRecords.create');
                Route::put('maintenance-records/{maintenanceRecord}', [MaintenanceRecordController::class, 'update'])->whereNumber('maintenanceRecord')->name('maintenanceRecords.update');
                Route::delete('maintenance-records/{maintenanceRecord}', [MaintenanceRecordController::class, 'destroy'])->whereNumber('maintenanceRecord')->name('maintenanceRecords.destroy');

                // loans — owners and editors record custody moving out or in, mark a loan as returned, and correct or remove one
                Route::post('loans', [LoanController::class, 'create'])->name('loans.create');
                Route::put('loans/{loan}', [LoanController::class, 'update'])->whereNumber('loan')->name('loans.update');
                Route::put('loans/{loan}/return', [LoanReturnController::class, 'update'])->whereNumber('loan')->name('loans.return.update');
                Route::delete('loans/{loan}', [LoanController::class, 'destroy'])->whereNumber('loan')->name('loans.destroy');

                // documents — owners and editors attach a file or an external link to a copy or one of its records, correct its details, and remove it
                Route::post('documents', [DocumentController::class, 'create'])->name('documents.create');
                Route::put('documents/{document}', [DocumentController::class, 'update'])->whereNumber('document')->name('documents.update');
                Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->whereNumber('document')->name('documents.destroy');

                // location history — owners and editors move a copy between locations; creating a record is a move, update and destroy correct a past one
                Route::post('location-history', [LocationHistoryController::class, 'create'])->name('locationHistory.create');
                Route::put('location-history/{locationHistory}', [LocationHistoryController::class, 'update'])->whereNumber('locationHistory')->name('locationHistory.update');
                Route::delete('location-history/{locationHistory}', [LocationHistoryController::class, 'destroy'])->whereNumber('locationHistory')->name('locationHistory.destroy');
            });
        });
    });

    // series — owners and editors may create, update and delete the series of the account
    Route::middleware(['editor'])->group(function (): void {
        Route::post('series', [SeriesController::class, 'create'])->name('series.create');
        Route::put('series/{series}', [SeriesController::class, 'update'])->where('series', '[1-9][0-9]*')->name('series.update');
        Route::delete('series/{series}', [SeriesController::class, 'destroy'])->where('series', '[1-9][0-9]*')->name('series.destroy');
    });

    // locations — owners and editors may create, update and delete locations
    Route::middleware(['editor'])->group(function (): void {
        Route::post('locations', [LocationController::class, 'create'])->name('locations.create');
        Route::put('locations/{location}', [LocationController::class, 'update'])->where('location', '[1-9][0-9]*')->name('locations.update');
        Route::delete('locations/{location}', [LocationController::class, 'destroy'])->where('location', '[1-9][0-9]*')->name('locations.destroy');
    });

    // personal profile — each user manages their own (any authenticated user)
    Route::get('profile', [SettingsController::class, 'index'])->name('profile.index');
    Route::put('profile', [SettingsController::class, 'update'])->name('profile.update');
    Route::post('profile/avatar', [AvatarController::class, 'update'])->name('profile.avatar.update');
    Route::delete('profile/avatar', [AvatarController::class, 'destroy'])->name('profile.avatar.destroy');
    Route::get('profile/avatar/{user}/{size}', [AvatarController::class, 'show'])->where('user', '[1-9][0-9]*')->where('size', '[1-9][0-9]*')->name('profile.avatar.show');
    Route::get('profile/logs', [LogController::class, 'index'])->name('profile.logs.index');
    Route::get('profile/emails', [EmailSentController::class, 'index'])->name('profile.emails.index');

    // profile: security
    Route::get('profile/security', [SecurityController::class, 'index'])->name('profile.security.index');
    Route::put('profile/security/password', [PasswordController::class, 'update'])->name('profile.security.password.update');
    Route::get('profile/security/2fa/new', [TwoFAController::class, 'new'])->name('profile.security.2fa.new');
    Route::post('profile/security/2fa', [TwoFAController::class, 'create'])->name('profile.security.2fa.create');
    Route::delete('profile/security/2fa', [TwoFAController::class, 'destroy'])->name('profile.security.2fa.destroy');
    Route::get('profile/security/recovery-codes', [RecoveryCodeController::class, 'show'])->name('profile.security.recoverycodes.show');
    Route::put('profile/security/auto-delete-account', [AutoDeleteUserController::class, 'update'])->name('profile.security.auto-delete.update');

    // profile: api keys
    Route::get('profile/api-keys/new', [ApiKeyController::class, 'new'])->name('profile.api-keys.new');
    Route::post('profile/api-keys', [ApiKeyController::class, 'create'])->name('profile.api-keys.create');
    Route::delete('profile/api-keys/{apiKey}', [ApiKeyController::class, 'destroy'])->name('profile.api-keys.destroy');

    // profile: webhooks
    Route::get('profile/webhooks', [WebhookController::class, 'index'])->name('profile.webhooks.index');
    Route::get('profile/webhooks/new', [WebhookController::class, 'new'])->name('profile.webhooks.new');
    Route::post('profile/webhooks', [WebhookController::class, 'create'])->name('profile.webhooks.create');
    Route::delete('profile/webhooks/{webhookEndpoint}', [WebhookController::class, 'destroy'])->where('webhookEndpoint', '[1-9][0-9]*')->name('profile.webhooks.destroy');

    // profile: danger zone (delete your own user)
    Route::get('profile/user', [UserController::class, 'index'])->name('profile.user.index');
    Route::delete('profile/user', [UserController::class, 'destroy'])->name('profile.user.destroy');

    // support — a signed in user's own conversations with the instance team. The
    // whole section only exists when SUPPORT_ENABLED is on: the middleware answers
    // 404 otherwise, matching the sidebar that hides the link.
    Route::middleware(['support.enabled'])->prefix('support')->name('support.')->group(function (): void {
        Route::get('/', [SupportTicketController::class, 'index'])->name('tickets.index');
        Route::get('new', [SupportTicketController::class, 'new'])->name('tickets.new');
        Route::post('/', [SupportTicketController::class, 'create'])->name('tickets.create');
        Route::get('{supportTicket}', [SupportTicketController::class, 'show'])->where('supportTicket', '[1-9][0-9]*')->name('tickets.show');
        Route::put('{supportTicket}', [SupportTicketController::class, 'update'])->where('supportTicket', '[1-9][0-9]*')->name('tickets.update');
        Route::delete('{supportTicket}', [SupportTicketController::class, 'destroy'])->where('supportTicket', '[1-9][0-9]*')->name('tickets.destroy');
        Route::post('{supportTicket}/messages', [SupportMessageController::class, 'create'])->where('supportTicket', '[1-9][0-9]*')->name('tickets.messages.create');
    });

    // the free allowance, and the two screens that explain outgrowing it. There is
    // nothing to buy on a self hosted instance, so the middleware answers 404
    // there. Any member may read the first screen, because anyone can run into the
    // limit; only an owner reaches the second, because only they can commit the
    // account to a payment.
    Route::middleware(['hosted'])->prefix('upgrade')->name('upgrade.')->group(function (): void {
        Route::get('/', [UpgradeController::class, 'index'])->name('index');

        Route::middleware(['owner'])->group(function (): void {
            Route::get('confirm', [PurchaseConfirmationController::class, 'new'])->name('confirm.new');
            Route::post('confirm', [PurchaseConfirmationController::class, 'create'])->name('confirm.create');
        });
    });

    // account settings — the account and its members (owners only)
    Route::middleware(['owner'])->group(function (): void {
        Route::delete('getting-started', [GettingStartedController::class, 'destroy'])->name('gettingStarted.destroy');

        Route::get('settings', [AccountController::class, 'index'])->name('settings.index');
        Route::put('settings/getting-started', [SettingsGettingStartedController::class, 'update'])->name('settings.gettingStarted.update');
        Route::put('settings', [AccountController::class, 'update'])->name('settings.update');
        Route::delete('settings', [AccountController::class, 'destroy'])->name('settings.destroy');

        Route::get('settings/members', [MemberController::class, 'index'])->name('settings.members.index');
        Route::post('settings/members', [MemberController::class, 'create'])->name('settings.members.create');
        Route::put('settings/members/{userId}', [MemberController::class, 'update'])->where('userId', '[1-9][0-9]*')->name('settings.members.update');
        Route::delete('settings/members/{userId}', [MemberController::class, 'destroy'])->where('userId', '[1-9][0-9]*')->name('settings.members.destroy');
    });

    // account settings: collection types — owners and editors define the custom fields available on items
    Route::middleware(['editor'])->group(function (): void {
        Route::get('settings/types', [CatalogTypeController::class, 'index'])->name('settings.types.index');
        Route::post('settings/types', [CatalogTypeController::class, 'create'])->name('settings.types.create');
        Route::get('settings/types/{collectionType}/edit', [CatalogTypeController::class, 'edit'])->where('collectionType', '[1-9][0-9]*')->name('settings.types.edit');
        Route::put('settings/types/{collectionType}', [CatalogTypeController::class, 'update'])->where('collectionType', '[1-9][0-9]*')->name('settings.types.update');
        Route::delete('settings/types/{collectionType}', [CatalogTypeController::class, 'destroy'])->where('collectionType', '[1-9][0-9]*')->name('settings.types.destroy');
        Route::get('settings/types/{collectionType}/export', [CatalogTypeExportController::class, 'show'])->where('collectionType', '[1-9][0-9]*')->name('settings.types.export.show');
        Route::get('settings/types/import', [CatalogTypeImportController::class, 'new'])->name('settings.types.import.new');
        Route::post('settings/types/import', [CatalogTypeImportController::class, 'create'])->name('settings.types.import.create');

        // a type's custom fields and the collections that may use it (edited inline, saved as you go)
        Route::post('settings/types/{collectionType}/fields', [CustomFieldController::class, 'create'])->where('collectionType', '[1-9][0-9]*')->name('settings.types.fields.create');
        Route::put('settings/types/{collectionType}/fields/{customField}', [CustomFieldController::class, 'update'])->where(['collectionType' => '[1-9][0-9]*', 'customField' => '[1-9][0-9]*'])->name('settings.types.fields.update');
        Route::delete('settings/types/{collectionType}/fields/{customField}', [CustomFieldController::class, 'destroy'])->where(['collectionType' => '[1-9][0-9]*', 'customField' => '[1-9][0-9]*'])->name('settings.types.fields.destroy');
        Route::put('settings/types/{collectionType}/fields/{customField}/order', [CustomFieldOrderController::class, 'update'])->where(['collectionType' => '[1-9][0-9]*', 'customField' => '[1-9][0-9]*'])->name('settings.types.fields.order.update');
        // the groups a type's fields can be organised into
        Route::post('settings/types/{collectionType}/groups', [CustomFieldGroupController::class, 'create'])->where('collectionType', '[1-9][0-9]*')->name('settings.types.groups.create');
        Route::put('settings/types/{collectionType}/groups/{group}', [CustomFieldGroupController::class, 'update'])->where(['collectionType' => '[1-9][0-9]*', 'group' => '[1-9][0-9]*'])->name('settings.types.groups.update');
        Route::delete('settings/types/{collectionType}/groups/{group}', [CustomFieldGroupController::class, 'destroy'])->where(['collectionType' => '[1-9][0-9]*', 'group' => '[1-9][0-9]*'])->name('settings.types.groups.destroy');
        Route::put('settings/types/{collectionType}/groups/{group}/order', [CustomFieldGroupOrderController::class, 'update'])->where(['collectionType' => '[1-9][0-9]*', 'group' => '[1-9][0-9]*'])->name('settings.types.groups.order.update');
        Route::post('settings/types/{collectionType}/groups/{group}/fields', [CustomFieldGroupFieldController::class, 'create'])->where(['collectionType' => '[1-9][0-9]*', 'group' => '[1-9][0-9]*'])->name('settings.types.groups.fields.create');

    });

    // account settings: tags — owners and editors define the labels items can carry
    Route::middleware(['editor'])->group(function (): void {
        Route::get('settings/tags', [TagController::class, 'index'])->name('settings.tags.index');
        Route::post('settings/tags', [TagController::class, 'create'])->name('settings.tags.create');
        Route::put('settings/tags/{tag}', [TagController::class, 'update'])->where('tag', '[1-9][0-9]*')->name('settings.tags.update');
        Route::delete('settings/tags/{tag}', [TagController::class, 'destroy'])->where('tag', '[1-9][0-9]*')->name('settings.tags.destroy');
    });

    // account settings: item conditions — owners and editors define the condition levels items can carry
    Route::middleware(['editor'])->group(function (): void {
        Route::get('settings/item-conditions', [ItemConditionController::class, 'index'])->name('settings.itemConditions.index');
        Route::post('settings/item-conditions', [ItemConditionController::class, 'create'])->name('settings.itemConditions.create');
        Route::put('settings/item-conditions/{itemCondition}', [ItemConditionController::class, 'update'])->where('itemCondition', '[1-9][0-9]*')->name('settings.itemConditions.update');
        Route::delete('settings/item-conditions/{itemCondition}', [ItemConditionController::class, 'destroy'])->where('itemCondition', '[1-9][0-9]*')->name('settings.itemConditions.destroy');
    });

    // account settings: photos — owners and editors manage every image in the account
    Route::middleware(['editor'])->group(function (): void {
        Route::get('settings/photos', [PhotoController::class, 'index'])->name('settings.photos.index');
        // remembering the layout is a private preference, so it is saved on its own
        // rather than carried in the query string
        Route::put('settings/photos/view', [PhotoViewController::class, 'update'])->name('settings.photos.view.update');
        Route::put('settings/photos/{itemPhoto}/cover', [PhotoCoverController::class, 'update'])->where('itemPhoto', '[1-9][0-9]*')->name('settings.photos.cover.update');
        Route::delete('settings/photos/{itemPhoto}', [PhotoController::class, 'destroy'])->where('itemPhoto', '[1-9][0-9]*')->name('settings.photos.destroy');
        Route::delete('settings/photos/selection', [PhotoSelectionController::class, 'destroy'])->name('settings.photos.selection.destroy');
    });

    // account settings: trash — owners and editors restore what has been deleted
    Route::middleware(['editor'])->group(function (): void {
        Route::get('settings/trash', [TrashController::class, 'index'])->name('settings.trash.index');
        Route::put('settings/trash', [TrashController::class, 'update'])->name('settings.trash.update');
        Route::delete('settings/trash', [TrashController::class, 'destroy'])->name('settings.trash.destroy');
    });

    // account settings: testimonials — any member may submit one for the marketing
    // site, so these are not role gated. The controller answers 404 when the
    // marketing site is not served, matching the sidebar section that surfaces them.
    Route::get('settings/testimonials', [SettingsTestimonialController::class, 'index'])->name('settings.testimonials.index');
    Route::post('settings/testimonials', [SettingsTestimonialController::class, 'create'])->name('settings.testimonials.create');
    Route::delete('settings/testimonials', [SettingsTestimonialController::class, 'destroy'])->name('settings.testimonials.destroy');

    // instance administration — spans every account on the instance, so it is
    // gated on the per user flag rather than on any role within an account
    Route::middleware(['instance.admin'])->prefix('instance-admin')->name('instanceAdmin.')->group(function (): void {
        Route::get('/', [InstanceOverviewController::class, 'index'])->name('index');

        Route::get('accounts', [InstanceAccountController::class, 'index'])->name('accounts.index');
        Route::get('accounts/{account}', [InstanceAccountController::class, 'show'])->where('account', '[1-9][0-9]*')->name('accounts.show');
        Route::delete('accounts/{account}', [InstanceAccountController::class, 'destroy'])->where('account', '[1-9][0-9]*')->name('accounts.destroy');

        Route::delete('users/{user}', [InstanceUserController::class, 'destroy'])->where('user', '[1-9][0-9]*')->name('users.destroy');
        Route::put('users/{user}/administrator', [InstanceUserController::class, 'update'])->where('user', '[1-9][0-9]*')->name('users.administrator.update');

        // what people wrote on their way out. The rows outlive the person who
        // wrote them and belong to nobody, so the page only lists them.
        Route::get('deletion-reasons', [InstanceDeletionReasonController::class, 'index'])->name('deletionReasons.index');

        // the support inbox, spanning every account. Both the tab and the open
        // conversation live in the path (no query string), so each bucket and each
        // selected ticket is its own URL, and a bare /support lands on the open one.
        Route::get('support/{status?}/{ticket?}', [InstanceSupportController::class, 'index'])->where('status', 'open|all|closed')->where('ticket', '[1-9][0-9]*')->name('support.index');
        Route::post('support/{supportTicket}/messages', [InstanceSupportMessageController::class, 'create'])->where('supportTicket', '[1-9][0-9]*')->name('support.messages.create');
        Route::put('support/{supportTicket}', [InstanceSupportController::class, 'update'])->where('supportTicket', '[1-9][0-9]*')->name('support.update');

        // marketing — moderating the testimonials members submit for the public
        // site. The filter bucket lives in the path, so each bucket is its own URL.
        // Publishing and rejecting are one update, told apart by the intent field.
        Route::get('marketing/testimonials/{status?}', [InstanceTestimonialController::class, 'index'])->where('status', 'in_review|published|rejected|draft|all')->name('marketing.testimonials.index');
        Route::put('marketing/testimonials/{testimonial}', [InstanceTestimonialController::class, 'update'])->where('testimonial', '[1-9][0-9]*')->name('marketing.testimonials.update');

        // marketing — the blog. The status bucket lives in the path, so each
        // bucket is its own URL; the search box is the one thing that reaches
        // for a query string, the same as the accounts list. Writing is done one
        // language at a time, so the locale is a path segment rather than a tab
        // the server cannot see.
        Route::get('marketing/blog-posts/new', [InstanceBlogPostController::class, 'new'])->name('marketing.blogPosts.new');
        Route::post('marketing/blog-posts', [InstanceBlogPostController::class, 'create'])->name('marketing.blogPosts.create');
        Route::get('marketing/blog-posts/{status?}', [InstanceBlogPostController::class, 'index'])->where('status', 'all|published|draft|archived')->name('marketing.blogPosts.index');
        Route::put('marketing/blog-posts/{blogPost}', [InstanceBlogPostController::class, 'update'])->where('blogPost', '[1-9][0-9]*')->name('marketing.blogPosts.update');
        Route::delete('marketing/blog-posts/{blogPost}', [InstanceBlogPostController::class, 'destroy'])->where('blogPost', '[1-9][0-9]*')->name('marketing.blogPosts.destroy');

        Route::get('marketing/blog-posts/{blogPost}/translations/{locale}', [InstanceBlogPostTranslationController::class, 'edit'])->where('blogPost', '[1-9][0-9]*')->name('marketing.blogPosts.translations.edit');
        Route::put('marketing/blog-posts/{blogPost}/translations/{locale}', [InstanceBlogPostTranslationController::class, 'update'])->where('blogPost', '[1-9][0-9]*')->name('marketing.blogPosts.translations.update');
        Route::post('marketing/blog-posts/{blogPost}/translations/{locale}/og-image', [InstanceBlogPostOgImageController::class, 'create'])->where('blogPost', '[1-9][0-9]*')->name('marketing.blogPosts.translations.ogImage.create');
        Route::delete('marketing/blog-posts/{blogPost}/translations/{locale}/og-image', [InstanceBlogPostOgImageController::class, 'destroy'])->where('blogPost', '[1-9][0-9]*')->name('marketing.blogPosts.translations.ogImage.destroy');

        // site options — the announcement banner on the marketing site, and the
        // Cloudflare cache that holds its rendered pages.
        Route::get('site-options', [InstanceSiteOptionController::class, 'index'])->name('siteOptions.index');
        Route::put('site-options', [InstanceSiteOptionController::class, 'update'])->name('siteOptions.update');
        Route::delete('site-options/cloudflare-cache', [InstanceCloudflareCacheController::class, 'destroy'])->name('siteOptions.cloudflareCache.destroy');
    });
});

// invitations can be viewed and accepted by guests as well as logged-in users
Route::middleware(['set.locale'])->group(function (): void {
    Route::get('invitations/{token}', [InvitationController::class, 'show'])->name('invitations.show');
    Route::post('invitations/{token}/accept', [InvitationController::class, 'create'])->name('invitations.create');
});

require __DIR__.'/auth.php';
