<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doc_activity_logs', function (Blueprint $table) {
            $table->renameColumn('executive_order_id', 'document_id');
        });
    }

    public function down(): void
    {
        Schema::table('doc_activity_logs', function (Blueprint $table) {
            $table->renameColumn('document_id', 'executive_order_id');
        });
    }
};
