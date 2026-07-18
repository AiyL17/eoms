<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('received_from')->nullable()->after('signed_by'); // Office/origin of the document
            $table->string('recipient')->nullable()->after('received_from');  // Intended recipient
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['received_from', 'recipient']);
        });
    }
};
