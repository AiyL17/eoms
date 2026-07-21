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
            $table->string('doc_number')->unique();  // e.g. "DOC-2026-001"
            $table->string('document_type')->default('incoming'); // incoming | outgoing

            // Document Content
            $table->string('title');

            // Dates
            $table->date('date_issued');
            $table->date('expiration_date')->nullable();

            // Origin / destination
            $table->string('received_from')->nullable(); // Office/origin of the document
            $table->string('recipient')->nullable();     // Intended recipient

            // PDF File
            $table->string('pdf_path');              // Relative path inside local storage
            $table->string('original_filename');     // Original uploaded filename
            $table->unsignedBigInteger('file_size'); // In bytes

            // Audit: who uploaded / who last edited
            $table->foreignId('uploaded_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
