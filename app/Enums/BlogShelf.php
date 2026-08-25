<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The shelf a blog entry sits on.
 *
 * The blog is presented as a catalogue rather than a stream, so an entry is
 * filed rather than tagged: it belongs to exactly one shelf, chosen when it is
 * written, and the public index lets a reader browse one shelf at a time.
 */
enum BlogShelf: string
{
    case Collecting = 'collecting';
    case Engineering = 'engineering';
    case SelfHosting = 'self_hosting';
    case Releases = 'releases';

    public function label(): string
    {
        return match ($this) {
            self::Collecting => __('Collecting'),
            self::Engineering => __('Engineering'),
            self::SelfHosting => __('Self-hosting'),
            self::Releases => __('Releases'),
        };
    }

    /**
     * The sentence printed under the shelf name on the public index, saying what
     * a reader will find there.
     */
    public function description(): string
    {
        return match ($this) {
            self::Collecting => __('Grading, provenance, valuation, insurance and the habits of people who keep things.'),
            self::Engineering => __('How the app is built: data modelling, search, and the decisions that were harder than they look.'),
            self::SelfHosting => __('Running your own instance on your own hardware, without a weekend disappearing.'),
            self::Releases => __('What shipped, what broke, what got fixed. Short by design.'),
        };
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
