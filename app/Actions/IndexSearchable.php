<?php

declare(strict_types=1);

namespace App\Actions;

use App\Contracts\Searchable;
use App\Models\SearchToken;
use App\Services\BlindIndex;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Rebuild the search hashes of one record from its own text and from the few
 * things around it a user would search it by. This is bookkeeping the
 * application does for itself, so it takes no user and checks no role.
 */
class IndexSearchable
{
    /**
     * A word in the name of the record is the strongest thing a query can match.
     */
    public const int WEIGHT_TITLE = 100;

    /**
     * An identifier or a file name: precise, but not what the record is called.
     */
    public const int WEIGHT_IDENTIFIER = 80;

    /**
     * The names of the things around the record: its collection, its category,
     * its tags, where it is kept.
     */
    public const int WEIGHT_RELATED = 60;

    /**
     * Free text: descriptions, notes, the values of custom fields.
     */
    public const int WEIGHT_TEXT = 30;

    public function __construct(
        private readonly Model&Searchable $searchable,
    ) {}

    public function execute(): void
    {
        $accountId = $this->searchable->searchableAccountId();

        if ($accountId === null) {
            return;
        }

        $rows = $this->rows($accountId);

        DB::transaction(function () use ($rows): void {
            $this->searchable->searchTokens()->delete();

            if ($rows === []) {
                return;
            }

            SearchToken::query()->insert($rows);
        });
    }

    /**
     * One row per hash. A word that appears in both the name and the description
     * is worth the name, so the heaviest weight wins: the table holds one row
     * per token and per record.
     *
     * @return list<array{account_id: int, searchable_type: string, searchable_id: int, token: string, weight: int, created_at: string, updated_at: string}>
     */
    private function rows(int $accountId): array
    {
        $weights = [];

        foreach ($this->searchable->searchableText() as $weight => $values) {
            $values = array_values(array_filter($values, fn (?string $value): bool => $value !== null && $value !== ''));

            if ($values === []) {
                continue;
            }

            foreach (BlindIndex::hashesFor(...$values) as $hash) {
                $weights[$hash] = max($weights[$hash] ?? 0, $weight);
            }
        }

        $now = now()->toDateTimeString();

        return array_map(fn (string $hash): array => [
            'account_id' => $accountId,
            'searchable_type' => $this->searchable->getMorphClass(),
            'searchable_id' => $this->searchable->getKey(),
            'token' => $hash,
            'weight' => $weights[$hash],
            'created_at' => $now,
            'updated_at' => $now,
        ], array_keys($weights));
    }
}
