<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\EoSearchService;

class ExecutiveOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'eo_number',
        'item_number',
        'year',
        'title',
        'subject',
        'content_summary',
        'date_issued',
        'date_effective',
        'signed_by',
        'signature_path',
        'pdf_path',
        'original_filename',
        'file_size',
        'status',
        'status_notes',
        'amended_by_id',
        'amends_id',
        'tags',
        'uploaded_by',
        'updated_by',
    ];

    protected $casts = [
        'date_issued'    => 'date',
        'date_effective' => 'date',
        'tags'           => 'array',
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

    /** The newer EO that amended this one */
    public function amendedBy()
    {
        return $this->belongsTo(ExecutiveOrder::class, 'amended_by_id');
    }

    /** The older EO that this one amends */
    public function amends()
    {
        return $this->belongsTo(ExecutiveOrder::class, 'amends_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(EoActivityLog::class)->latest();
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeSearch($query, string $term)
    {
        // Try FTS5 first; fall back to LIKE search
        $ftsQuery = EoSearchService::applyToQuery($query, $term);
        if ($ftsQuery !== null) {
            return $ftsQuery;
        }

        // Fallback: LIKE search
        return $query->where(function ($q) use ($term) {
            $q->where('eo_number', 'like', "%{$term}%")
              ->orWhere('title', 'like', "%{$term}%")
              ->orWhere('subject', 'like', "%{$term}%")
              ->orWhere('signed_by', 'like', "%{$term}%")
              ->orWhereJsonContains('tags', $term);
        });
    }

    // ─── FTS Index Sync ───────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saved(fn (self $eo) => EoSearchService::index($eo));
        static::deleted(fn (self $eo) => EoSearchService::remove($eo->id));
        static::restored(fn (self $eo) => EoSearchService::index($eo));
    }

    // ─── Computed Attributes ─────────────────────────────────────────────────

    /**
     * Return the signature as a base64 data URI for inline use in views.
     * Reads from local disk so the file never needs to be in the public folder.
     */
    public function getSignatureDataAttribute(): ?string
    {
        if (! $this->signature_path) {
            return null;
        }

        if (! \Illuminate\Support\Facades\Storage::disk('local')->exists($this->signature_path)) {
            return null;
        }

        $bytes = \Illuminate\Support\Facades\Storage::disk('local')->get($this->signature_path);

        return 'data:image/png;base64,' . base64_encode($bytes);
    }

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

    /** Human-readable status label */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'active'       => 'Active',
            'amended'      => 'Amended',
            'repealed'     => 'Repealed',
            'suspended'    => 'Suspended',
            'superseded'   => 'Superseded',
            'under_review' => 'Under Review',
            default        => ucfirst($this->status),
        };
    }

    /** Tailwind color group for status badge */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'active'       => 'green',
            'amended'      => 'amber',
            'repealed'     => 'red',
            'suspended'    => 'orange',
            'superseded'   => 'purple',
            'under_review' => 'blue',
            default        => 'gray',
        };
    }

    // ─── Static Helpers ───────────────────────────────────────────────────────

    /** Build the formatted EO number string */
    public static function buildEoNumber(int $itemNumber, int $year): string
    {
        $yearShort = substr((string) $year, -2);
        return "E.O. No. {$itemNumber}-{$yearShort}";
    }

    /** Suggest the next item number for a given year */
    public static function nextItemNumber(int $year): int
    {
        return self::withTrashed()->where('year', $year)->count() + 1;
    }

    /** All valid statuses */
    public static function statuses(): array
    {
        return [
            'active'       => 'Active',
            'amended'      => 'Amended',
            'repealed'     => 'Repealed',
            'suspended'    => 'Suspended',
            'superseded'   => 'Superseded',
            'under_review' => 'Under Review',
        ];
    }
}
