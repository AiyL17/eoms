<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // Document Identification
            $table->string('eo_number')->unique(); // e.g. "E.O. No. 24-26"
            $table->integer('item_number');        // e.g. 24
            $table->integer('year');               // e.g. 2026 (full 4-digit year)

            // Document Content
            $table->string('title');
            $table->string('subject');
            $table->text('content_summary')->nullable();

            // Dates
            $table->date('date_issued');
            $table->date('date_effective')->nullable();

            // Signatory
            $table->string('signed_by');

            // PDF File
            $table->string('pdf_path');            // Relative path inside local storage
            $table->string('original_filename');   // Original uploaded filename
            $table->unsignedBigInteger('file_size'); // In bytes

            // Status
            $table->enum('status', [
                'active',
                'amended',
                'repealed',
                'suspended',
                'superseded',
                'under_review',
            ])->default('active');
            $table->text('status_notes')->nullable();

            // Amendment tracking (self-referential)
            $table->unsignedBigInteger('amended_by_id')->nullable(); // Points to the NEW document that amended this one
            $table->unsignedBigInteger('amends_id')->nullable();     // Points to the OLD document this one amends

            // Tags / categories
            $table->json('tags')->nullable();

            // Audit: who uploaded / who last edited
            $table->foreignId('uploaded_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });

        // Add self-referential foreign keys after table creation
        Schema::table('documents', function (Blueprint $table) {
            $table->foreign('amended_by_id')
                  ->references('id')
                  ->on('documents')
                  ->nullOnDelete();

            $table->foreign('amends_id')
                  ->references('id')
                  ->on('documents')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
