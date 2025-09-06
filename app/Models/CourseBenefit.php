<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseBenefit extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'course_benefit_id';

    protected $fillable = [
        'name',
        'course_id'
    ];

    /**
     * Get the course that owns the CourseBenefit
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }
}
