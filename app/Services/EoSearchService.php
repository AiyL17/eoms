<?php

namespace App\Services;

use App\Models\ExecutiveOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EoSearchService
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
            $rows = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name='eo_search_index'");
            return ! empty($rows);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Upsert a single EO into the FTS index.
     * Called after create/update/restore.
     */
    public static function index(ExecutiveOrder $eo): void
    {
        if (! static::ftsAvailable()) return;

        try {
            // Remove existing row then insert fresh (FTS5 doesn't support ON CONFLICT)
            DB::statement('DELETE FROM eo_search_index WHERE eo_id = ?', [$eo->id]);
            DB::statement(
                'INSERT INTO eo_search_index (eo_id, eo_number, title, subject, signed_by, content_summary, tags)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [
                    $eo->id,
                    $eo->eo_number,
                    $eo->title,
                    $eo->subject,
                    $eo->signed_by,
                    $eo->content_summary ?? '',
                    $eo->tags ? implode(' ', $eo->tags) : '',
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('FTS index update failed for EO ' . $eo->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Remove an EO from the FTS index.
     * Called on soft-delete or force-delete.
     */
    public static function remove(int $eoId): void
    {
        if (! static::ftsAvailable()) return;
        try {
            DB::statement('DELETE FROM eo_search_index WHERE eo_id = ?', [$eoId]);
        } catch (\Throwable $e) {
            Log::warning('FTS index removal failed for EO ' . $eoId . ': ' . $e->getMessage());
        }
    }

    /**
     * Rebuild the entire FTS index from the executive_orders table.
     */
    public static function rebuild(): int
    {
        if (! static::ftsAvailable()) return 0;

        DB::statement('DELETE FROM eo_search_index');

        $count = 0;
        ExecutiveOrder::withoutTrashed()->chunk(200, function ($eos) use (&$count) {
            foreach ($eos as $eo) {
                static::index($eo);
                $count++;
            }
        });

        return $count;
    }

    /**
     * Perform an FTS5 search and return EO IDs ordered by relevance.
     * Falls back to an empty collection if FTS is unavailable.
     *
     * @return Collection<int> EO IDs in relevance order (most relevant first)
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
                'SELECT eo_id, rank FROM eo_search_index WHERE eo_search_index MATCH ? ORDER BY rank',
                [$sanitised]
            );

            return collect($rows)->pluck('eo_id');
        } catch (\Throwable $e) {
            Log::warning('FTS search failed: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Apply FTS search to an EO query builder, preserving relevance order.
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
        $caseWhen   = 'CASE executive_orders.id ';
        foreach ($orderedIds as $position => $id) {
            $caseWhen .= "WHEN {$id} THEN {$position} ";
        }
        $caseWhen .= 'ELSE 9999 END';

        return $query->whereIn('id', $orderedIds)
                     ->reorder()
                     ->orderByRaw($caseWhen);
    }

    /**
     * Sanitise user input to safe FTS5 query syntax.
     * Converts "hello world" → `hello* world*` (prefix match on each token).
     */
    private static function sanitiseFtsQuery(string $term): string
    {
        // Remove FTS5 special characters except spaces
        $clean = preg_replace('/["\'\(\)\*\:\^]/', '', $term);
        $clean = trim($clean);

        if (empty($clean)) return '';

        // Split into tokens and append * for prefix matching
        $tokens = preg_split('/\s+/', $clean);
        $tokens = array_filter($tokens);
        $tokens = array_map(fn ($t) => '"' . str_replace('"', '', $t) . '"*', $tokens);

        return implode(' ', $tokens);
    }
}
