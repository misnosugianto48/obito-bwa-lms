<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionContent extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'section_content_id';

    protected $fillable = [
        'name',
        'course_section_id',
        'content'
    ];

    /**
     * Get the courseSection that owns the SectionContent
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function courseSection(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id', 'course_section_id');
    }
}
