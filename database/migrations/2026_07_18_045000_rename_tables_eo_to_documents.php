<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename the main table
        if (Schema::hasTable('executive_orders') && ! Schema::hasTable('documents')) {
            Schema::rename('executive_orders', 'documents');
        }

        // Rename the activity logs table
        if (Schema::hasTable('eo_activity_logs') && ! Schema::hasTable('doc_activity_logs')) {
            Schema::rename('eo_activity_logs', 'doc_activity_logs');
        }

        // Rename the FTS search index table
        if (Schema::hasTable('eo_search_index') && ! Schema::hasTable('doc_search_index')) {
            Schema::rename('eo_search_index', 'doc_search_index');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('documents') && ! Schema::hasTable('executive_orders')) {
            Schema::rename('documents', 'executive_orders');
        }

        if (Schema::hasTable('doc_activity_logs') && ! Schema::hasTable('eo_activity_logs')) {
            Schema::rename('doc_activity_logs', 'eo_activity_logs');
        }

        if (Schema::hasTable('doc_search_index') && ! Schema::hasTable('eo_search_index')) {
            Schema::rename('doc_search_index', 'eo_search_index');
        }
    }
};
