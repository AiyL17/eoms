<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // content_summary was added in the original scaffold migration but was never
            // exposed in any form, view, or fillable array. Drop it to keep the schema clean.
            $table->dropColumn('content_summary');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->text('content_summary')->nullable()->after('title');
        });
    }
};
