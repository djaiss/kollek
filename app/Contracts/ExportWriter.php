<?php

declare(strict_types=1);

namespace App\Contracts;

/**
 * Turns a payload into a file on disk.
 *
 * A writer owns one format and nothing else: it is handed a payload that has
 * already decided what belongs in the export, and its only judgement is how to
 * lay that out. Which means a writer never needs changing when a new kind of
 * export is added, only when a new format is.
 *
 * The file is written to a temporary path rather than returned as a string,
 * because a workbook is streamed as it is built and a large PDF has no business
 * sitting in memory twice.
 */
interface ExportWriter
{
    /**
     * The absolute path of the file that was written. The caller owns it, and is
     * the one that deletes it once it has been sent.
     */
    public function write(ExportPayload $payload): string;
}
