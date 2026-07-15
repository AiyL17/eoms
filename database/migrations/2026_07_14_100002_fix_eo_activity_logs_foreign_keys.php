<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eo_activity_logs', function (Blueprint $table) {
            // Drop the old cascading FK on user_id
            $table->dropForeign(['user_id']);

            // Make user_id nullable so logs survive user deletion
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Re-add the FK with nullOnDelete — audit trail is preserved even if the user is removed
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('eo_activity_logs', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
