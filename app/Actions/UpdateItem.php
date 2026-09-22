<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\CopyStatus;
use App\Enums\FieldTypeEnum;
use App\Enums\ItemActionEnum;
use App\Enums\UserActionEnum;
use App\Enums\ValuationConfidence;
use App\Enums\ValuationType;
use App\Helpers\TextSanitizer;
use App\Jobs\LogItemAction;
use App\Jobs\LogUserAction;
use App\Jobs\ReindexItemPhotos;
use App\Models\CatalogType;
use App\Models\Category;
use App\Models\Copy;
use App\Models\CustomField;
use App\Models\CustomFieldValue;
use App\Models\Item;
use App\Models\ItemPhoto;
use App\Models\Series;
use App\Models\Set;
use App\Models\Tag;
use App\Models\User;
use App\Models\Valuation;
use App\Traits\RecordsCopyMoves;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Update an item, together with its tags, custom field values and copies. Only
 * owners and editors of its account may do so.
 */
class UpdateItem
{
    use RecordsCopyMoves;

    /**
     * The values that moved, captured before the item is written so the
     * activity tab can show what they moved from.
     *
     * @var list<array{label: string, from?: string|null, to?: string|null}>
     */
    private array $changes = [];

    /**
     * @param  list<int>|null  $tagIds  ids of existing account tags to apply
     * @param  list<string>|null  $newTagNames  names of new tags to create and apply
     * @param  array<int, string|null>|null  $customFieldValues  custom field id to raw value
     * @param  list<array{id?: int|null, identifier?: string|null, item_condition_id?: int|null, current_location_id?: int|null, status?: CopyStatus|null, quantity?: int|null, disposed_at?: string|null, note?: string|null, estimated_value?: int|null}>|null  $copies
     * @param  list<UploadedFile>  $photos  new photos, appended after the ones the item already has
     * @param  list<int>  $deletedPhotoIds  ids of photos of this item to remove
     * @param  int|null  $mainPhotoId  id of the photo to make the cover, among those the item keeps
     */
    public function __construct(
        private readonly User $user,
        private readonly Item $item,
        private string $name,
        private ?string $description = null,
        private readonly ?CatalogType $catalogType = null,
        private readonly ?Category $category = null,
        private readonly ?Set $set = null,
        private readonly ?Series $series = null,
        private readonly ?array $tagIds = null,
        private readonly ?array $newTagNames = null,
        private readonly ?array $customFieldValues = null,
        private readonly ?array $copies = null,
        private readonly array $photos = [],
        private readonly array $deletedPhotoIds = [],
        private readonly ?int $mainPhotoId = null,
    ) {}

    public function execute(): Item
    {
        $this->validate();
        $this->sanitize();
        $this->captureChanges();

        DB::transaction(function (): void {
            $this->update();
            $this->syncTags();
            $this->syncCustomFieldValues();
            $this->syncCopies();
        });

        $this->syncPhotos();
        $this->reindexPhotos();
        $this->reindexSearch();
        $this->log();

        return $this->item;
    }

    private function validate(): void
    {
        $catalog = $this->item->catalog;

        if (! $catalog->account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }

        if ($this->catalogType instanceof CatalogType && ! $catalog->catalogTypes()->whereKey($this->catalogType->id)->exists()) {
            throw new ModelNotFoundException('Type not found');
        }

        if ($this->category instanceof Category && $this->category->catalog_id !== $catalog->id) {
            throw new ModelNotFoundException('Category not found');
        }

        if ($this->set instanceof Set && $this->set->catalog_id !== $catalog->id) {
            throw new ModelNotFoundException('Set not found');
        }

        // A series is account-wide, so it only has to share the account, not the collection.
        if ($this->series instanceof Series && $this->series->account_id !== $catalog->account_id) {
            throw new ModelNotFoundException('Series not found');
        }

        $this->validateTags();
        $this->validateCopies();
    }

    private function validateTags(): void
    {
        if ($this->tagIds === null || $this->tagIds === []) {
            return;
        }

        $ownedCount = $this->item->catalog->account->tags()->whereKey($this->tagIds)->count();

        if ($ownedCount !== count(array_unique($this->tagIds))) {
            throw new ModelNotFoundException('Tag not found');
        }
    }

    private function validateCopies(): void
    {
        if ($this->copies === null) {
            return;
        }

        $account = $this->item->catalog->account;
        $conditionIds = $account->itemConditions()->pluck('id')->all();
        $locationIds = $account->locations()->pluck('id')->all();
        $copyIds = $this->item->copies()->pluck('id')->all();

        foreach ($this->copies as $copy) {
            $id = $copy['id'] ?? null;
            $conditionId = $copy['item_condition_id'] ?? null;
            $locationId = $copy['current_location_id'] ?? null;

            if ($id !== null && ! in_array($id, $copyIds, true)) {
                throw new ModelNotFoundException('Copy not found');
            }

            if ($conditionId !== null && ! in_array($conditionId, $conditionIds, true)) {
                throw new ModelNotFoundException('Condition not found');
            }

            if ($locationId !== null && ! in_array($locationId, $locationIds, true)) {
                throw new ModelNotFoundException('Location not found');
            }
        }
    }

    private function sanitize(): void
    {
        $this->name = TextSanitizer::plainText($this->name);
        $this->description = TextSanitizer::nullablePlainText($this->description);
    }

    private function captureChanges(): void
    {
        $this->changes = array_values(array_filter([
            $this->change('Name', $this->item->name, $this->name),
            $this->describedChange(),
            $this->change('Type', $this->item->catalogType?->name, $this->catalogType?->name),
            $this->change('Category', $this->item->category?->name, $this->category?->name),
            $this->change('Set', $this->item->set?->name, $this->set?->name),
            $this->change('Series', $this->item->series?->name, $this->series?->name),
        ]));
    }

    /**
     * @return array{label: string, from: string|null, to: string|null}|null
     */
    private function change(string $label, ?string $from, ?string $to): ?array
    {
        if ($from === $to) {
            return null;
        }

        return ['label' => $label, 'from' => $from, 'to' => $to];
    }

    /**
     * A description is far too long to sit in a chip, so the activity tab only
     * reports that it moved.
     *
     * @return array{label: string}|null
     */
    private function describedChange(): ?array
    {
        if ($this->item->description === $this->description) {
            return null;
        }

        return ['label' => 'Description'];
    }

    private function update(): void
    {
        $this->item->name = $this->name;
        $this->item->description = $this->description;
        $this->item->type_id = $this->catalogType?->id;
        $this->item->category_id = $this->category?->id;
        $this->item->set_id = $this->set?->id;
        $this->item->series_id = $this->series?->id;
        $this->item->updated_by_id = $this->user->id;
        $this->item->updated_by_name = $this->user->getFullName();
        $this->item->save();
    }

    private function syncTags(): void
    {
        if ($this->tagIds === null) {
            return;
        }

        $tagIds = $this->tagIds;

        foreach ($this->newTagNames ?? [] as $name) {
            $name = TextSanitizer::plainText($name);

            if ($name === '') {
                continue;
            }

            $tag = Tag::query()->create([
                'account_id' => $this->item->catalog->account_id,
                'name' => $name,
            ]);
            $this->stampAuthorOn($tag);

            $tagIds[] = $tag->id;
        }

        $this->item->tags()->sync($tagIds);
    }

    private function syncCustomFieldValues(): void
    {
        if ($this->customFieldValues === null) {
            return;
        }

        $fields = $this->catalogType?->customFields()->get()->keyBy('id') ?? collect();
        $existing = $this->item->customFieldValues()->get()->keyBy('custom_field_id');

        foreach ($this->customFieldValues as $fieldId => $value) {
            $field = $fields->get($fieldId);

            if (! $field instanceof CustomField) {
                continue;
            }

            $value = $this->cleanValue($field, $value);

            if ($value === null) {
                $existing->get($fieldId)?->delete();

                continue;
            }

            CustomFieldValue::query()->updateOrCreate(
                ['item_id' => $this->item->id, 'custom_field_id' => $fieldId],
                ['value' => $value],
            );
        }

        $this->item->customFieldValues()
            ->whereNotIn('custom_field_id', $fields->keys()->all())
            ->delete();
    }

    private function cleanValue(CustomField $field, string|int|null $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($field->field_type === FieldTypeEnum::Rating) {
            return $this->rating($value);
        }

        return TextSanitizer::plainText((string) $value);
    }

    private function rating(string|int $value): ?string
    {
        $stars = filter_var($value, FILTER_VALIDATE_INT);

        if ($stars === false || $stars < 1 || $stars > FieldTypeEnum::MAX_RATING) {
            return null;
        }

        return (string) $stars;
    }

    private function syncCopies(): void
    {
        if ($this->copies === null) {
            return;
        }

        $keptIds = [];

        foreach ($this->copies as $copy) {
            $attributes = [
                'identifier' => $copy['identifier'] ?? null,
                'item_condition_id' => $copy['item_condition_id'] ?? null,
                'status' => $copy['status'] ?? CopyStatus::Owned,
                'quantity' => $copy['quantity'] ?? 1,
                'disposed_at' => $copy['disposed_at'] ?? null,
                'note' => $copy['note'] ?? null,
            ];

            $id = $copy['id'] ?? null;

            if ($id !== null) {
                $existing = $this->item->copies()->findOrFail($id);
                $existing->fill($attributes);
                $this->stampAuthorOn($existing);

                // The web form drops the location field for an existing copy, so a
                // move only happens when the key is actually present (the api may
                // still send it). Without the key the copy stays where it is.
                if (array_key_exists('current_location_id', $copy)) {
                    $this->recordCopyMove($existing, $copy['current_location_id'], $this->user);
                }

                $this->valueCopy($existing, $copy['estimated_value'] ?? null);

                $keptIds[] = $id;

                continue;
            }

            $created = Copy::query()->create(['item_id' => $this->item->id, ...$attributes]);
            $created->created_by_id = $this->user->id;
            $created->created_by_name = $this->user->getFullName();
            $this->stampAuthorOn($created);

            // A new copy created somewhere opens its first location record.
            $this->recordCopyMove($created, $copy['current_location_id'] ?? null, $this->user);

            $this->valueCopy($created, $copy['estimated_value'] ?? null);

            $keptIds[] = $created->id;
        }

        $this->deleteMissingCopies($keptIds);
    }

    private function valueCopy(Copy $copy, ?int $estimatedValue): void
    {
        if ($estimatedValue === null || $estimatedValue === $copy->estimatedValue()) {
            return;
        }

        $valuation = new Valuation([
            'copy_id' => $copy->id,
            'type' => ValuationType::UserEstimate,
            'amount' => $estimatedValue,
            'currency_code' => $this->item->catalog->currency,
            'valued_at' => now()->toDateString(),
            'confidence' => ValuationConfidence::Unknown,
        ]);

        $valuation->created_by_id = $this->user->id;
        $valuation->created_by_name = $this->user->getFullName();
        $this->stampAuthorOn($valuation);

        $copy->unsetRelation('latestValuation')->unsetRelation('valuations');
    }

    /**
     * @param  list<int>  $keptIds
     */
    private function deleteMissingCopies(array $keptIds): void
    {
        // A soft delete only writes deleted_at, so who did it is stamped first.
        $this->item->copies()->whereNotIn('id', $keptIds)->get()->each(function (Copy $copy): void {
            $copy->deleted_by_id = $this->user->id;
            $copy->deleted_by_name = $this->user->getFullName();
            $copy->saveQuietly();
            $copy->delete();
        });
    }

    private function reindexPhotos(): void
    {
        ReindexItemPhotos::dispatch($this->item)->onQueue('low');
    }

    private function reindexSearch(): void
    {
        $this->item->load(['catalog', 'category', 'set', 'series', 'catalogType', 'tags', 'customFieldValues']);

        new IndexSearchable(searchable: $this->item)->execute();
    }

    private function syncPhotos(): void
    {
        $this->deletePhotos();
        $this->addPhotos();
        $this->setMainPhoto();
    }

    private function deletePhotos(): void
    {
        foreach ($this->deletedPhotoIds as $photoId) {
            $photo = $this->item->photos()->find($photoId);

            if (! $photo instanceof ItemPhoto) {
                continue;
            }

            new DestroyItemPhoto(
                user: $this->user,
                itemPhoto: $photo,
            )->execute();
        }
    }

    private function addPhotos(): void
    {
        foreach ($this->photos as $photo) {
            if (! $photo instanceof UploadedFile) {
                continue;
            }

            new AddItemPhoto(
                user: $this->user,
                item: $this->item,
                file: $photo,
            )->execute();
        }
    }

    private function setMainPhoto(): void
    {
        if ($this->mainPhotoId === null) {
            return;
        }

        $photo = $this->item->photos()->find($this->mainPhotoId);

        if (! $photo instanceof ItemPhoto || $photo->is_main) {
            return;
        }

        new SetMainItemPhoto(
            user: $this->user,
            itemPhoto: $photo,
        )->execute();
    }

    private function stampAuthorOn(Model $model): void
    {
        $model->setAttribute('updated_by_id', $this->user->id);
        $model->setAttribute('updated_by_name', $this->user->getFullName());
        $model->save();
    }

    private function log(): void
    {
        LogUserAction::dispatch(
            user: $this->user,
            action: UserActionEnum::ItemUpdate,
            parameters: ['name' => $this->item->name],
        )->onQueue('low');

        LogItemAction::dispatch(
            item: $this->item,
            user: $this->user,
            action: ItemActionEnum::ItemUpdate,
            parameters: $this->changes === [] ? null : ['changes' => $this->changes],
        )->onQueue('low');
    }
}
