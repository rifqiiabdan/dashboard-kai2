<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiklatAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'exam_date',
        'score',
        'passed',
        'certificate_number',
        'certificate_date',
    ];

    protected $casts = [
        'exam_date' => 'date',
        'certificate_date' => 'date',
        'passed' => 'boolean',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(DiklatEnrollment::class, 'enrollment_id');
    }
}










