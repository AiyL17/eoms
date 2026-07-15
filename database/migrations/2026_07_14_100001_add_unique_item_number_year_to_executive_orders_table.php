<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('executive_orders', function (Blueprint $table) {
            // Enforce uniqueness at the database level — item_number must be unique per year
            $table->unique(['item_number', 'year'], 'eo_item_number_year_unique');
        });
    }

    public function down(): void
    {
        Schema::table('executive_orders', function (Blueprint $table) {
            $table->dropUnique('eo_item_number_year_unique');
        });
    }
};
