<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiklatParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_number',
        'name',
        'unit',
        'position',
        'email',
        'phone',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(DiklatEnrollment::class, 'participant_id');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($participant) {
            // Delete all enrollments and their assessments
            foreach ($participant->enrollments as $enrollment) {
                // Delete assessment if exists
                if ($enrollment->assessment) {
                    $enrollment->assessment->delete();
                }
                // Delete enrollment
                $enrollment->delete();
            }
        });
    }
}





