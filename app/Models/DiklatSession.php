<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiklatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'week_number',
        'week_start_date',
        'week_end_date',
        'planned_hours',
        'realized_hours',
        'status',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(DiklatProgram::class, 'program_id');
    }
}










