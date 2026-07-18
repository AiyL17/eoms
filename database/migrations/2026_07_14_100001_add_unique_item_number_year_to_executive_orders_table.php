<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Enforce uniqueness at the database level — item_number must be unique per year
            $table->unique(['item_number', 'year'], 'doc_item_number_year_unique');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropUnique('doc_item_number_year_unique');
        });
    }
};
