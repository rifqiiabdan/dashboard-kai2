<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DiklatEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'program_id',
        'status',
        'enrolled_at',
        'completed_at',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
        'completed_at' => 'date',
    ];

    /**
     * Get the status (now fully manual, no automatic computation)
     */
    public function getComputedStatusAttribute(): string
    {
        // Return the stored status directly (all statuses are now manual)
        return $this->status ?? 'registered';
    }

    /**
     * Check if the current status is manually overridden
     * Since all statuses are now manual, this always returns true
     */
    public function getIsManualOverrideAttribute(): bool
    {
        // All statuses are now manual
        return true;
    }

    /**
     * Get the effective status (computed or manual override)
     */
    public function getEffectiveStatusAttribute(): string
    {
        return $this->computed_status;
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(DiklatParticipant::class, 'participant_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(DiklatProgram::class, 'program_id');
    }

    public function assessment(): HasOne
    {
        return $this->hasOne(DiklatAssessment::class, 'enrollment_id');
    }
}



