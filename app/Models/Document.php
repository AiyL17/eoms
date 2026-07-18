<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\DocSearchService;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'documents';

    protected $fillable = [
        'doc_number',
        'document_type',
        'title',
        'date_issued',
        'expiration_date',
        'received_from',
        'recipient',
        'pdf_path',
        'original_filename',
        'file_size',
        'uploaded_by',
        'updated_by',
    ];

    protected $casts = [
        'date_issued'     => 'date',
        'expiration_date' => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(DocActivityLog::class, 'document_id')->latest();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeSearch($query, string $term)
    {
        // Try FTS5 first; fall back to LIKE search
        $ftsQuery = DocSearchService::applyToQuery($query, $term);
        if ($ftsQuery !== null) {
            return $ftsQuery;
        }

        // Fallback: LIKE search
        return $query->where(function ($q) use ($term) {
            $q->where('doc_number', 'like', "%{$term}%")
              ->orWhere('title', 'like', "%{$term}%")
              ->orWhere('received_from', 'like', "%{$term}%");
        });
    }

    // ─── FTS Index Sync ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saved(function (self $doc) {
            DocSearchService::index($doc);
            \Illuminate\Support\Facades\Cache::forget('public_portal_meta');
        });
        static::deleted(function (self $doc) {
            DocSearchService::remove($doc->id);
            \Illuminate\Support\Facades\Cache::forget('public_portal_meta');
        });
        static::restored(function (self $doc) {
            DocSearchService::index($doc);
            \Illuminate\Support\Facades\Cache::forget('public_portal_meta');
        });
    }

    // ─── Computed Attributes ─────────────────────────────────────────────────

    /** Formatted file size (KB or MB) */
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1_048_576) {
            return round($bytes / 1_048_576, 2) . ' MB';
        }
        if ($bytes >= 1_024) {
            return round($bytes / 1_024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    /** Human-readable document type label */
    public function getDocumentTypeLabelAttribute(): string
    {
        return match ($this->document_type) {
            'incoming' => 'Incoming',
            'outgoing' => 'Outgoing',
            default    => ucfirst($this->document_type),
        };
    }

    /** All valid document types */
    public static function documentTypes(): array
    {
        return [
            'incoming' => 'Incoming',
            'outgoing' => 'Outgoing',
        ];
    }
}
