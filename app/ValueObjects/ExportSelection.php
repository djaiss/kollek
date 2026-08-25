<?php

declare(strict_types=1);

namespace App\ValueObjects;

use App\Enums\ExportFormat;
use App\Enums\ExportSection;
use App\Services\ExportBlueprint;

/**
 * What the user asked an export to contain.
 *
 * Everything downstream reads its choices through here rather than through the
 * request, so the payload builders and the writers never learn what the form
 * looked like. That is also what lets the same writers serve a single item
 * later: they are handed a selection and a payload, and neither says which
 * screen it came from.
 *
 * Unticked is the answer to anything not in the lists. A section being on is not
 * enough for a field inside it to be printed, and a field being on is not enough
 * either: both have to be, which is what `wants` answers in one call.
 */
class ExportSelection
{
    /**
     * @param  list<string>  $sections
     * @param  list<string>  $fields
     * @param  array<string, bool>  $options
     */
    public function __construct(
        public readonly ExportFormat $format,
        private readonly array $sections,
        private readonly array $fields,
        private readonly array $options = [],
    ) {}

    /**
     * The selection a validated form payload describes.
     *
     * Anything the blueprint does not know about is dropped rather than trusted,
     * so a stale form posting a field that has since been removed narrows the
     * export instead of breaking it.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function fromRequest(array $validated, ExportBlueprint $blueprint): self
    {
        /** @var list<string> $sections */
        $sections = array_values(array_intersect(
            array_map(strval(...), (array) ($validated['sections'] ?? [])),
            $blueprint->sectionKeys(),
        ));

        /** @var list<string> $fields */
        $fields = array_values(array_intersect(
            array_map(strval(...), (array) ($validated['fields'] ?? [])),
            $blueprint->fieldKeys(),
        ));

        $options = [];

        foreach ((array) ($validated['options'] ?? []) as $name => $value) {
            $options[(string) $name] = (bool) $value;
        }

        return new self(
            format: ExportFormat::from((string) $validated['format']),
            sections: $sections,
            fields: $fields,
            options: $options,
        );
    }

    /**
     * Everything the blueprint offers, which is what the screen starts on and
     * what a test asking for a whole export uses.
     */
    public static function everything(ExportBlueprint $blueprint, ExportFormat $format = ExportFormat::Pdf): self
    {
        return new self(
            format: $format,
            sections: $blueprint->sectionKeys(),
            fields: $blueprint->fieldKeys(),
            options: ['thumbnails' => true, 'embed_documents' => false, 'page_per_item' => true],
        );
    }

    public function hasSection(ExportSection $section): bool
    {
        return in_array($section->value, $this->sections, true);
    }

    public function has(string $field): bool
    {
        return in_array($field, $this->fields, true);
    }

    /**
     * Whether a field should be printed: its section has to be on as well as the
     * field itself.
     */
    public function wants(ExportSection $section, string $field): bool
    {
        return $this->hasSection($section) && $this->has($field);
    }

    /**
     * Whether any of the given fields survived, which is how a block of a
     * section decides whether it has anything left to draw.
     */
    public function wantsAny(ExportSection $section, string ...$fields): bool
    {
        if (! $this->hasSection($section)) {
            return false;
        }

        return array_any($fields, fn (string $field): bool => $this->has($field));
    }

    public function option(string $name): bool
    {
        return $this->options[$name] ?? false;
    }

    /**
     * @return list<string>
     */
    public function sections(): array
    {
        return $this->sections;
    }

    /**
     * @return list<string>
     */
    public function fields(): array
    {
        return $this->fields;
    }
}
