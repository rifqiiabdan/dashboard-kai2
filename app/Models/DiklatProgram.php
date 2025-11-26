<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiklatProgram extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'bidang',
        'start_date',
        'end_date',
        'target_participants',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function sessions(): HasMany
    {
        return $this->hasMany(DiklatSession::class, 'program_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(DiklatEnrollment::class, 'program_id');
    }
}










