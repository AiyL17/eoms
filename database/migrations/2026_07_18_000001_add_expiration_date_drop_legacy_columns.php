<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add expiration_date
        Schema::table('documents', function (Blueprint $table) {
            $table->date('expiration_date')->nullable()->after('date_issued');
        });

        // 2. Drop legacy columns that were only scaffolding from the original document design.
        //    signed_by / subject / date_effective were mirrored from received_from / title and never
        //    exposed in the UI. signature_path was used for e-signature but that feature is not in use.
        Schema::table('documents', function (Blueprint $table) {
            // Drop FKs first (SQLite ignores them, but MySQL/PG need this)
            // Note: SQLite doesn't support dropping specific columns via alter in some versions,
            // but Laravel's SQLite driver rewrites the table internally.
            $table->dropColumn(['subject', 'signed_by', 'date_effective', 'signature_path']);
        });

        // 3. Rebuild FTS index without the dropped columns
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP TABLE IF EXISTS doc_search_index');
            DB::statement("
                CREATE VIRTUAL TABLE doc_search_index USING fts5(
                    doc_id UNINDEXED,
                    doc_number,
                    title,
                    received_from,
                    tags,
                    tokenize = \"unicode61\"
                )
            ");
        }
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('expiration_date');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('title');
            $table->string('signed_by')->nullable()->after('recipient');
            $table->date('date_effective')->nullable()->after('date_issued');
            $table->string('signature_path')->nullable();
        });
    }
};
