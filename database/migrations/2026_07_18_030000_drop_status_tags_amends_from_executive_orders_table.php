<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Drop self-referential FK constraints before dropping the columns
            // (Laravel SQLite driver rewrites the table, so this is safe on all drivers)
            $table->dropForeign(['amended_by_id']);
            $table->dropForeign(['amends_id']);

            $table->dropColumn([
                'status',
                'status_notes',
                'tags',
                'amends_id',
                'amended_by_id',
            ]);
        });

        // Rebuild the FTS index without the dropped columns
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('DROP TABLE IF EXISTS doc_search_index');
            DB::statement("
                CREATE VIRTUAL TABLE doc_search_index USING fts5(
                    doc_id UNINDEXED,
                    doc_number,
                    title,
                    received_from,
                    tokenize = \"unicode61\"
                )
            ");
        }
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('status', [
                'active', 'amended', 'repealed', 'suspended', 'superseded', 'under_review',
            ])->default('active')->after('recipient');
            $table->text('status_notes')->nullable()->after('status');
            $table->json('tags')->nullable()->after('status_notes');
            $table->unsignedBigInteger('amended_by_id')->nullable();
            $table->unsignedBigInteger('amends_id')->nullable();

            $table->foreign('amended_by_id')->references('id')->on('documents')->nullOnDelete();
            $table->foreign('amends_id')->references('id')->on('documents')->nullOnDelete();
        });
    }
};
