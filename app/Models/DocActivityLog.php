<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocActivityLog extends Model
{
    public $timestamps = true;

    protected $table = 'doc_activity_logs';

    // updated_at is not needed for an immutable log — only created_at matters
    const UPDATED_AT = null;

    protected $fillable = [
        'document_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id')->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Static Helper ────────────────────────────────────────────────────────

    public static function record(
        Document $doc,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $notes = null
    ): self {
        return self::create([
            'document_id' => $doc->id,
            'user_id'            => auth()->id(),
            'action'             => $action,
            'old_values'         => $oldValues,
            'new_values'         => $newValues,
            'ip_address'         => request()->ip(),
            'notes'              => $notes,
        ]);
    }

    // ─── Action Label ─────────────────────────────────────────────────────────

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created'        => 'Uploaded',
            'updated'        => 'Updated',
            'status_changed' => 'Status Changed',
            'deleted'        => 'Archived',
            'restored'       => 'Restored',
            'force_deleted'  => 'Permanently Deleted',
            'downloaded'     => 'Downloaded PDF',
            'pdf_viewed'     => 'Viewed PDF',
            default          => ucfirst(str_replace('_', ' ', $this->action)),
        };
    }

    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'created'        => 'green',
            'updated'        => 'blue',
            'status_changed' => 'amber',
            'deleted'        => 'violet',
            'restored'       => 'teal',
            'force_deleted'  => 'red',
            'downloaded'     => 'purple',
            'pdf_viewed'     => 'gray',
            default          => 'gray',
        };
    }
}
