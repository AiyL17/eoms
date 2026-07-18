<?php

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Add the new path columns ───────────────────────────────────────

        Schema::table('users', function (Blueprint $table) {
            $table->string('signature_path')->nullable()->after('signature_data');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->string('signature_path')->nullable()->after('signature_data');
        });

        // ── 2. Migrate existing base64 blobs → files ──────────────────────────

        // Users
        User::whereNotNull('signature_data')->each(function (User $user) {
            $path = $this->saveSignatureBlob($user->signature_data, "signatures/users/{$user->id}.png");
            if ($path) {
                $user->updateQuietly(['signature_path' => $path]);
            }
        });

        // Documents
        Document::withTrashed()->whereNotNull('signature_data')->each(function (Document $doc) {
            $path = $this->saveSignatureBlob($doc->signature_data, "signatures/documents/{$doc->id}.png");
            if ($path) {
                $doc->updateQuietly(['signature_path' => $path]);
            }
        });

        // ── 3. Drop the old blob columns ──────────────────────────────────────

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('signature_data');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('signature_data');
        });
    }

    public function down(): void
    {
        // Re-add the blob columns
        Schema::table('users', function (Blueprint $table) {
            $table->longText('signature_data')->nullable()->after('signature_path');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->longText('signature_data')->nullable()->after('signature_path');
        });

        // Restore blob data from files
        User::whereNotNull('signature_path')->each(function (User $user) {
            if (Storage::disk('local')->exists($user->signature_path)) {
                $blob = 'data:image/png;base64,' . base64_encode(Storage::disk('local')->get($user->signature_path));
                $user->updateQuietly(['signature_data' => $blob]);
            }
        });

        Document::withTrashed()->whereNotNull('signature_path')->each(function (Document $doc) {
            if (Storage::disk('local')->exists($doc->signature_path)) {
                $blob = 'data:image/png;base64,' . base64_encode(Storage::disk('local')->get($doc->signature_path));
                $doc->updateQuietly(['signature_data' => $blob]);
            }
        });

        // Drop the path columns
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('signature_path');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('signature_path');
        });
    }

    private function saveSignatureBlob(?string $blob, string $path): ?string
    {
        if (! $blob) {
            return null;
        }

        // Strip the data URI prefix
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $blob);
        $data   = base64_decode($base64);

        if (! $data) {
            return null;
        }

        Storage::disk('local')->put($path, $data);
        return $path;
    }
};
