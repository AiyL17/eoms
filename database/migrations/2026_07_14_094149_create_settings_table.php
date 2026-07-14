<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default values
        $defaults = [
            // System
            'app_name'                  => 'EOMS',
            'org_name'                  => 'City Government',

            // EO Management
            'archive_retention_days'    => '30',
            'default_upload_status'     => 'active',
            'max_pdf_size_mb'           => '20',

            // Notifications
            'notify_on_upload'          => '1',
            'notify_on_status_change'   => '1',
            'notify_on_archive'         => '1',
            'notify_on_restore'         => '1',

            // Access
            'staff_can_upload'          => '1',
        ];

        foreach ($defaults as $key => $value) {
            DB::table('settings')->insert([
                'key'        => $key,
                'value'      => $value,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
