<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourseSection extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'course_section_id';

    protected $fillable = [
        'name',
        'course_id',
        'position'
    ];

    /**
     * Get the course that owns the CourseSection
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    /**
     * Get all of the sectionContents for the CourseSection
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sectionContents(): HasMany
    {
        return $this->hasMany(SectionContent::class, 'course_section_id', 'course_section_id');
    }
}
