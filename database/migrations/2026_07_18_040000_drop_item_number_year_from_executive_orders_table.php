<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Drop the composite unique index before removing the columns
            $table->dropUnique('doc_item_number_year_unique');

            $table->dropColumn(['item_number', 'year']);
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->integer('item_number')->nullable()->after('doc_number');
            $table->integer('year')->nullable()->after('item_number');

            $table->unique(['item_number', 'year'], 'doc_item_number_year_unique');
        });
    }
};
