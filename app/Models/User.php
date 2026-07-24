<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
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
            'last_seen_at'      => 'datetime',
            'password'          => 'hashed',
        ];
    }

    /**
     * A user is considered online if they were active within the last 5 minutes.
     * The TrackLastSeen middleware writes last_seen_at at most every 2 minutes,
     * so a 5-minute window comfortably covers normal browsing activity.
     */
    public function isOnline(): bool
    {
        return $this->last_seen_at !== null
            && $this->last_seen_at->gt(now()->subMinutes(5));
    }

    // ─── Notifications ───────────────────────────────────────────────────────

    /**
     * Send the password reset notification using our custom branded template.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
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
