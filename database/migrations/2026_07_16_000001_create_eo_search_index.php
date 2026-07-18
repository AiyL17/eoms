<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Creates a SQLite FTS5 virtual table to power full-text search with ranking.
 * The table mirrors searchable columns from documents and is kept in
 * sync via application-level upserts (no DB triggers — SQLite in XAMPP may
 * not support triggers reliably).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Only create the FTS table if the database driver is SQLite
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('
            CREATE VIRTUAL TABLE IF NOT EXISTS doc_search_index
            USING fts5(
                doc_id UNINDEXED,
                doc_number,
                title,
                subject,
                signed_by,
                content_summary,
                tags,
                tokenize = "unicode61"
            )
        ');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }
        DB::statement('DROP TABLE IF EXISTS doc_search_index');
    }
};
