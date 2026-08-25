<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Where a blog entry sits on its way to the public site.
 *
 * A draft is being written and is nowhere to be seen. Publishing puts it in the
 * catalogue. Archiving takes it out of the index and the feed while leaving its
 * URL working, because the point of archiving rather than deleting is that the
 * links pointing at it keep resolving.
 */
enum BlogPostStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Published => __('Published'),
            self::Archived => __('Archived'),
        };
    }

    /**
     * The badge colour the status shows as in the instance administration.
     */
    public function color(): ?string
    {
        return match ($this) {
            self::Published => 'emerald',
            self::Archived => 'orange',
            self::Draft => null,
        };
    }

    /**
     * Whether an entry in this state has a public URL at all. An archived entry
     * still answers on its own URL, so only a draft is truly invisible.
     */
    public function isReadable(): bool
    {
        return $this !== self::Draft;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
