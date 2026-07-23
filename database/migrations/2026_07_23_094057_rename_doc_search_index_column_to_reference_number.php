<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Recreates the FTS5 virtual table with the column renamed from
 * doc_number → reference_number, then rebuilds the index from the documents table.
 *
 * FTS5 does not support ALTER TABLE, so drop-and-recreate is the only option.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        // Drop the old FTS table
        DB::statement('DROP TABLE IF EXISTS doc_search_index');

        // Recreate with the new column name
        DB::statement('
            CREATE VIRTUAL TABLE IF NOT EXISTS doc_search_index
            USING fts5(
                doc_id UNINDEXED,
                reference_number,
                title,
                received_from,
                tokenize = "unicode61"
            )
        ');

        // Repopulate from the documents table
        DB::statement('
            INSERT INTO doc_search_index (doc_id, reference_number, title, received_from)
            SELECT id, reference_number, title, COALESCE(received_from, \'\')
            FROM documents
            WHERE deleted_at IS NULL
        ');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('DROP TABLE IF EXISTS doc_search_index');

        DB::statement('
            CREATE VIRTUAL TABLE IF NOT EXISTS doc_search_index
            USING fts5(
                doc_id UNINDEXED,
                doc_number,
                title,
                received_from,
                tokenize = "unicode61"
            )
        ');

        // Repopulate with old column name from now-renamed column
        DB::statement('
            INSERT INTO doc_search_index (doc_id, doc_number, title, received_from)
            SELECT id, reference_number, title, COALESCE(received_from, \'\')
            FROM documents
            WHERE deleted_at IS NULL
        ');
    }
};
