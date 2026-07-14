<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('executive_orders', function (Blueprint $table) {
            // Stores the drawn e-signature as a base64-encoded PNG data URL
            $table->longText('signature_data')->nullable()->after('signed_by');
        });
    }

    public function down(): void
    {
        Schema::table('executive_orders', function (Blueprint $table) {
            $table->dropColumn('signature_data');
        });
    }
};
