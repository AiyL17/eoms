<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocSearchService
{
    /**
     * Returns whether the FTS5 index is available in this environment.
     */
    public static function ftsAvailable(): bool
    {
        if (DB::getDriverName() !== 'sqlite') {
            return false;
        }
        try {
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='doc_search_index'");
            return ! empty($rows);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Upsert a single document into the FTS index.
     * Called after create/update/restore.
     */
    public static function index(Document $doc): void
    {
        if (! static::ftsAvailable()) return;

        try {
            // Remove existing row then insert fresh (FTS5 doesn't support ON CONFLICT)
            DB::statement('DELETE FROM doc_search_index WHERE doc_id = ?', [$doc->id]);

            // Store both the original reference number AND a normalised (separator-stripped)
            // copy so that searches with or without dashes/slashes both hit this document.
            $refNormal = static::normalise($doc->reference_number);
            $refIndexed = $doc->reference_number !== $refNormal
                ? $doc->reference_number . ' ' . $refNormal
                : $doc->reference_number;

            DB::statement(
                'INSERT INTO doc_search_index (doc_id, reference_number, title, received_from)
                 VALUES (?, ?, ?, ?)',
                [
                    $doc->id,
                    $refIndexed,
                    $doc->title,
                    $doc->received_from ?? '',
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('FTS index update failed for document ' . $doc->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove a document from the FTS index.
     * Called on soft-delete or force-delete.
     */
    public static function remove(int $docId): void
    {
        if (! static::ftsAvailable()) return;
        try {
            DB::statement('DELETE FROM doc_search_index WHERE doc_id = ?', [$docId]);
        } catch (\Throwable $e) {
            Log::warning('FTS index removal failed for document ' . $docId . ': ' . $e->getMessage());
        }
    }

    /**
     * Rebuild the entire FTS index from the documents table.
     */
    public static function rebuild(): int
    {
        if (! static::ftsAvailable()) return 0;

        DB::statement('DELETE FROM doc_search_index');

        $count = 0;
        Document::withoutTrashed()->chunk(200, function ($docs) use (&$count) {
            foreach ($docs as $doc) {
                static::index($doc);
                $count++;
            }
        });

        return $count;
    }

    /**
     * Perform an FTS5 search and return document IDs ordered by relevance.
     * Falls back to an empty collection if FTS is unavailable.
     *
     * @return Collection<int> Document IDs in relevance order (most relevant first)
     */
    public static function search(string $term): Collection
    {
        if (! static::ftsAvailable()) {
            return collect();
        }

        try {
            // Sanitise and wrap as an FTS5 prefix query
            $sanitised = static::sanitiseFtsQuery($term);
            if (empty($sanitised)) return collect();

            $rows = DB::select(
                'SELECT doc_id, rank FROM doc_search_index WHERE doc_search_index MATCH ? ORDER BY rank',
                [$sanitised]
            );

            return collect($rows)->pluck('doc_id');
        } catch (\Throwable $e) {
            Log::warning('FTS search failed: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Apply FTS search to a document query builder, preserving relevance order.
     * Returns null ONLY if FTS is unavailable (caller should fall back to LIKE).
     * When FTS is available but finds no matches, returns a zero-result query.
     */
    public static function applyToQuery(Builder $query, string $term): ?Builder
    {
        if (! static::ftsAvailable()) {
            return null; // Signal caller to use LIKE fallback
        }

        $ids = static::search($term);

        if ($ids->isEmpty()) {
            // FTS is available but found nothing — return a definitive empty result
            return $query->whereRaw('1 = 0');
        }

        // Preserve relevance order using CASE WHEN
        $orderedIds = $ids->values()->toArray();
        $caseWhen   = 'CASE documents.id ';
        foreach ($orderedIds as $position => $id) {
            $caseWhen .= "WHEN {$id} THEN {$position} ";
        }
        $caseWhen .= 'ELSE 9999 END';

        return $query->whereIn('id', $orderedIds)
                     ->reorder()
                     ->orderByRaw($caseWhen);
    }

    /**
     * Normalise a reference number / search term by stripping common separators
     * (dashes, slashes, dots, spaces) so that "21-32103-123" and "2132103123"
     * both resolve to the same token and match each other.
     */
    public static function normalise(string $value): string
    {
        return preg_replace('/[\-\/\.\s]+/', '', $value);
    }

    /**
     * Sanitise user input to safe FTS5 query syntax.
     * Converts "hello world" → `hello* world*` (prefix match on each token).
     * Also emits a normalised (separator-stripped) token so that queries like
     * "2132103123" still match a stored reference number of "21-32103-123".
     */
    private static function sanitiseFtsQuery(string $term): string
    {
        // Remove FTS5 special characters except spaces and dashes
        $clean = preg_replace('/["\'\(\)\*\:\^]/', '', $term);
        $clean = trim($clean);

        if (empty($clean)) return '';

        // Split into whitespace-delimited tokens
        $tokens = array_filter(preg_split('/\s+/', $clean));

        $parts = [];
        foreach ($tokens as $t) {
            $t = str_replace('"', '', $t);
            // Original token (handles exact / partial matches with separators intact)
            $parts[] = '"' . $t . '"*';

            // Normalised token (strips separators so "2132103123" matches "21-32103-123")
            $normalised = static::normalise($t);
            if ($normalised !== $t && $normalised !== '') {
                $parts[] = '"' . $normalised . '"*';
            }
        }

        // Join with OR so either the original or the normalised form matches
        return implode(' OR ', $parts);
    }
}
