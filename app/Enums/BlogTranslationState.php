<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * How far along one language of a blog entry is.
 *
 * English is the source every other language is written from, so it is the only
 * one that carries Source. A translation is only served to readers once it is
 * Live: while it is being proofread, or once the English source has moved on
 * underneath it, the locale falls back to English rather than showing writing
 * nobody has checked.
 *
 * There is no case for "not translated yet": that is the absence of a row.
 */
enum BlogTranslationState: string
{
    case Source = 'source';
    case Live = 'live';
    case InReview = 'in_review';
    case Outdated = 'outdated';

    public function label(): string
    {
        return match ($this) {
            self::Source => __('Source'),
            self::Live => __('Live'),
            self::InReview => __('In review'),
            self::Outdated => __('Outdated'),
        };
    }

    /**
     * The sentence the instance administration prints next to the language, so
     * the reason a locale is not live is visible without opening it.
     */
    public function note(): string
    {
        return match ($this) {
            self::Source => 'Master copy, every translation derives from this',
            self::Live => 'Published and up to date',
            self::InReview => 'Translated, awaiting proofread',
            self::Outdated => 'The source changed since this was translated',
        };
    }

    public function color(): ?string
    {
        return match ($this) {
            self::Source => 'violet',
            self::Live => 'emerald',
            self::InReview => 'orange',
            self::Outdated => 'error',
        };
    }

    /**
     * Whether a translation in this state is served to readers. Anything else
     * falls back to the English source.
     */
    public function isPublic(): bool
    {
        return in_array($this, [self::Source, self::Live], true);
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
