<?php

declare(strict_types=1);

namespace App\Contracts;

use App\ValueObjects\ExportSelection;
use Carbon\Carbon;

/**
 * Everything a writer needs to produce an export, already narrowed to what the
 * selection asked for.
 *
 * This is the seam that keeps the writers reusable. A payload knows what it is
 * describing, a whole collection today and a single item tomorrow, and answers
 * the same six questions either way. A writer takes one and never asks which
 * kind it is holding.
 *
 * A section that was not ticked answers null rather than an empty array, so a
 * writer can tell "left out" apart from "nothing to show".
 */
interface ExportPayload
{
    /**
     * What the export is called and where it came from, for the cover and the
     * file name.
     *
     * @return array{title: string, subtitle: ?string, currency: ?string, generatedAt: Carbon, fileSlug: string}
     */
    public function meta(): array;

    public function selection(): ExportSelection;

    /**
     * @return ?array<string, mixed>
     */
    public function cover(): ?array;

    /**
     * @return ?array<string, mixed>
     */
    public function summary(): ?array;

    /**
     * One row per copy, with the columns the selection kept.
     *
     * @return ?array{columns: list<array{key: string, label: string}>, rows: list<array<string, mixed>>}
     */
    public function inventory(): ?array;

    /**
     * One entry per item, each carrying its copies and their histories.
     *
     * @return ?iterable<int, array<string, mixed>>
     */
    public function itemSheets(): ?iterable;

    /**
     * @return ?array{columns: list<array{key: string, label: string}>, rows: list<array<string, mixed>>}
     */
    public function documents(): ?array;

    /**
     * @return ?array<string, mixed>
     */
    public function appendices(): ?array;
}
