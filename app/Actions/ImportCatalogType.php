<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\FieldTypeEnum;
use App\Models\Account;
use App\Models\CatalogType;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use JsonException;

/**
 * Create a collection type from a JSON document produced by ExportCatalogType.
 * Only owners and editors of the account may do so.
 */
class ImportCatalogType
{
    /** The largest document we are willing to parse, in bytes. */
    public const int MAX_LENGTH = 100_000;

    /**
     * The exported shape nests four levels deep (root, type, groups, fields), so
     * anything deeper is not a schema we know how to read.
     */
    private const int MAX_DEPTH = 16;

    private const int MAX_GROUPS = 50;

    private const int MAX_FIELDS = 300;

    private const int MAX_OPTIONS = 200;

    private const int MAX_STRING_LENGTH = 255;

    private const string DEFAULT_COLOR = '#6B7280';

    /** @var list<string> */
    private array $errors = [];

    private string $name;

    private string $color;

    /** @var list<array{name: string, fields: list<array{name: string, type: string, options: ?list<string>}>}> */
    private array $groups = [];

    /** @var list<array{name: string, type: string, options: ?list<string>}> */
    private array $standaloneFields = [];

    private int $fieldCount = 0;

    private CatalogType $catalogType;

    public function __construct(
        private readonly User $user,
        private readonly Account $account,
        private readonly string $json,
    ) {}

    public function execute(): CatalogType
    {
        $this->validate();
        $this->parse();
        $this->create();

        return $this->catalogType;
    }

    private function validate(): void
    {
        if (! $this->account->allowsManagementBy($this->user)) {
            throw new ModelNotFoundException('Account not found');
        }
    }

    private function parse(): void
    {
        $document = $this->decode();

        $this->readSchemaVersion($document);

        $type = $document['type'] ?? null;

        if (! is_array($type) || array_is_list($type)) {
            $this->fail(__('The document must contain a "type" object.'));
        }

        $this->name = $this->readName($type['name'] ?? null, __('The type'));
        $this->color = $this->readColor($type['color'] ?? null);
        $this->groups = $this->readGroups($type['groups'] ?? null);
        $this->standaloneFields = $this->readFields($type['standaloneFields'] ?? [], __('Standalone fields'));

        if ($this->errors !== []) {
            $this->fail(...$this->errors);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(): array
    {
        $trimmed = mb_trim($this->json);

        if ($trimmed === '') {
            $this->fail(__('Paste the JSON of a type to import it.'));
        }

        // Measured in bytes rather than characters: the limit is about how much
        // work we are willing to do, not about how much the user typed.
        if (strlen($trimmed) > self::MAX_LENGTH) {
            $this->fail(__('The document is too large to import (:max KB maximum).', ['max' => (int) (self::MAX_LENGTH / 1024)]));
        }

        try {
            $document = json_decode($trimmed, associative: true, depth: self::MAX_DEPTH, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->fail(__('This is not valid JSON: :message', ['message' => $exception->getMessage()]));
        }

        if (! is_array($document) || array_is_list($document)) {
            $this->fail(__('The root of the document must be a JSON object.'));
        }

        return $document;
    }

    /**
     * @param  array<string, mixed>  $document
     */
    private function readSchemaVersion(array $document): void
    {
        $version = $document['schemaVersion'] ?? null;

        if (! is_int($version)) {
            $this->fail(__('The document is missing its "schemaVersion" number. Export a type from KolleK to get a valid document.'));
        }

        if ($version !== ExportCatalogType::SCHEMA_VERSION) {
            $this->fail(__('This document uses schema version :given, and only version :supported can be imported.', [
                'given' => $version,
                'supported' => ExportCatalogType::SCHEMA_VERSION,
            ]));
        }
    }

    /**
     * @return list<array{name: string, fields: list<array{name: string, type: string, options: ?list<string>}>}>
     */
    private function readGroups(mixed $groups): array
    {
        if ($groups === null) {
            $groups = [];
        }

        if (! is_array($groups) || ! array_is_list($groups)) {
            $this->errors[] = __('"groups" must be an array.');

            return [];
        }

        if (count($groups) > self::MAX_GROUPS) {
            $this->errors[] = __('A type cannot have more than :max groups.', ['max' => self::MAX_GROUPS]);

            return [];
        }

        $parsed = [];

        foreach ($groups as $index => $group) {
            $label = __('Group :position', ['position' => $index + 1]);

            if (! is_array($group) || array_is_list($group)) {
                $this->errors[] = __(':label must be an object.', ['label' => $label]);

                continue;
            }

            $name = $this->readName($group['name'] ?? null, $label);

            $parsed[] = [
                'name' => $name,
                'fields' => $this->readFields($group['fields'] ?? [], $name !== '' ? $name : $label),
            ];
        }

        return $parsed;
    }

    /**
     * @return list<array{name: string, type: string, options: ?list<string>}>
     */
    private function readFields(mixed $fields, string $label): array
    {
        if ($fields === null) {
            $fields = [];
        }

        if (! is_array($fields) || ! array_is_list($fields)) {
            $this->errors[] = __(':label: "fields" must be an array.', ['label' => $label]);

            return [];
        }

        $parsed = [];

        foreach ($fields as $index => $field) {
            $fieldLabel = __(':label, field :position', ['label' => $label, 'position' => $index + 1]);

            $this->fieldCount++;

            if ($this->fieldCount > self::MAX_FIELDS) {
                $this->errors[] = __('A type cannot have more than :max fields.', ['max' => self::MAX_FIELDS]);

                return $parsed;
            }

            if (! is_array($field) || array_is_list($field)) {
                $this->errors[] = __(':label must be an object.', ['label' => $fieldLabel]);

                continue;
            }

            $name = $this->readName($field['name'] ?? null, $fieldLabel);
            $type = $this->readFieldType($field['type'] ?? null, $name !== '' ? $name : $fieldLabel);
            $options = $this->readOptions($field['options'] ?? null, $type, $name !== '' ? $name : $fieldLabel);

            $parsed[] = [
                'name' => $name,
                'type' => $type,
                'options' => $options,
            ];
        }

        return $parsed;
    }

    private function readName(mixed $name, string $label): string
    {
        if (! is_string($name) || mb_trim($name) === '') {
            $this->errors[] = __(':label needs a name.', ['label' => $label]);

            return '';
        }

        if (mb_strlen($name) > self::MAX_STRING_LENGTH) {
            $this->errors[] = __(':label has a name longer than :max characters.', ['label' => $label, 'max' => self::MAX_STRING_LENGTH]);

            return '';
        }

        return $name;
    }

    private function readColor(mixed $color): string
    {
        if ($color === null) {
            return self::DEFAULT_COLOR;
        }

        if (! is_string($color) || preg_match('/^#[0-9A-Fa-f]{6}$/', $color) !== 1) {
            $this->errors[] = __('"color" must be a hexadecimal color such as #6B7280.');

            return self::DEFAULT_COLOR;
        }

        return $color;
    }

    private function readFieldType(mixed $type, string $label): string
    {
        if (! is_string($type) || FieldTypeEnum::tryFrom($type) === null) {
            $this->errors[] = __(':label has an unknown type. It must be one of :types.', [
                'label' => $label,
                'types' => implode(', ', array_column(FieldTypeEnum::cases(), 'value')),
            ]);

            return FieldTypeEnum::Text->value;
        }

        return $type;
    }

    /**
     * @return ?list<string>
     */
    private function readOptions(mixed $options, string $type, string $label): ?array
    {
        // Only a select field carries options, and anything hung off another
        // kind of field is dropped rather than persisted.
        if ($type !== FieldTypeEnum::Select->value) {
            return null;
        }

        if (! is_array($options) || ! array_is_list($options) || $options === []) {
            $this->errors[] = __(':label is a select field, so it needs a non-empty "options" array.', ['label' => $label]);

            return null;
        }

        if (count($options) > self::MAX_OPTIONS) {
            $this->errors[] = __(':label cannot have more than :max options.', ['label' => $label, 'max' => self::MAX_OPTIONS]);

            return null;
        }

        $parsed = [];

        foreach ($options as $option) {
            if (! is_string($option) || mb_trim($option) === '' || mb_strlen($option) > self::MAX_STRING_LENGTH) {
                $this->errors[] = __(':label has an option that is not a non-empty string of :max characters or less.', ['label' => $label, 'max' => self::MAX_STRING_LENGTH]);

                return null;
            }

            $parsed[] = $option;
        }

        return $parsed;
    }

    private function create(): void
    {
        DB::transaction(function (): void {
            $this->catalogType = new CreateCatalogType(
                user: $this->user,
                account: $this->account,
                name: $this->name,
                color: $this->color,
            )->execute();

            foreach ($this->groups as $group) {
                $customFieldGroup = new CreateCustomFieldGroup(
                    user: $this->user,
                    catalogType: $this->catalogType,
                    name: $group['name'],
                )->execute();

                foreach ($group['fields'] as $field) {
                    new CreateCustomField(
                        user: $this->user,
                        catalogType: $this->catalogType,
                        name: $field['name'],
                        fieldType: $field['type'],
                        options: $field['options'],
                        group: $customFieldGroup,
                    )->execute();
                }
            }

            foreach ($this->standaloneFields as $field) {
                new CreateCustomField(
                    user: $this->user,
                    catalogType: $this->catalogType,
                    name: $field['name'],
                    fieldType: $field['type'],
                    options: $field['options'],
                )->execute();
            }
        });
    }

    private function fail(string ...$messages): never
    {
        throw ValidationException::withMessages(['json' => array_values($messages)]);
    }
}
