<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'position',
        'signature_path',
        'avatar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ─── Role Helpers ────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function getRoleLabelAttribute(): string
    {
        return ucfirst($this->role);
    }

    /**
     * Return the user's profile signature as a base64 data URI.
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

    // ─── Relationships ────────────────────────────────────────────────────────

    public function uploadedDocuments()
    {
        return $this->hasMany(Document::class, 'uploaded_by');
    }

    public function activityLogs()
    {
        return $this->hasMany(DocActivityLog::class);
    }
}
