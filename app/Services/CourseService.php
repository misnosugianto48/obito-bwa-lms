<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseSection;
use App\Repositories\CourseRepository;
use Illuminate\Support\Facades\Auth;

class CourseService
{
  protected $courseRepo;

  public function __construct(CourseRepository $courseRepository)
  {
    $this->courseRepo = $courseRepository;
  }

  // cek apakah user sudah terdaftar di course atau belum
  public function enrollUser(Course $course)
  {

    $user = Auth::user();

    if (!$course->courseStudents()->where('user_id', $user->user_id)->exists()) {
      $course->courseStudents()->create([
        'user_id' => $user->user_id,
        'is_active' => true,
      ]);
    }

    return $user->name;
  }

  // mendapatkan section dan content pertama dari course saat pertama kali user terdaftar di course
  public function getFirstSectionAndContent(Course $course): array
  {
    $firstSectionId = $course->courseSections()->orderBy('course_section_id')->value('course_section_id');
    $firstContentId = $firstSectionId ? $course->courseSections()->find($firstSectionId)->sectionContents()->orderBy('section_content_id')->value('section_content_id') : null;

    return [
      'firstSectionId' => $firstSectionId,
      'firstContentId' => $firstContentId,
    ];
  }

  // mendapatkan data untuk halaman mana user belajar di course, supaya bisa lanjut ke content selanjutnya
  public function getLearningData(Course $course, $courseSectionId, $sectionContentId): array
  {
    $course->load(['courseSections.sectionContents']);

    $currentSection = $course->courseSections()->find($courseSectionId);
    $currentContent = $currentSection ? $currentSection->sectionContents()->find($sectionContentId) : null;

    $nextContent = null;

    if ($currentContent) {

      $nextContent = $currentSection->sectionContents
        ->where('section_content_id', '>', $currentContent->section_content_id)
        ->sortBy('section_content_id')
        ->first();
    }

    if (!$nextContent && $currentSection) {

      $nextSection = $course->courseSections
        ->where('course_section_id', '>', $currentSection->course_section_id)
        ->sortBy('course_section_id')
        ->first();

      if ($nextSection) {
        $nextContent = $nextSection->sectionContents->sortBy('id')->first();
      }
    }

    return [
      'course' => $course,
      'currentSection' => $currentSection,
      'currentContent' => $currentContent,
      'nextContent' => $nextContent,
      'isFinished' => !$nextContent,
    ];
  }

  public function searchCourses(string $keyword)
  {
    return $this->courseRepo->searchByKeyword($keyword);
  }

  public function getCoursesGroupedByCategory()
  {
    $courses = $this->courseRepo->getAllWithCategory();

    return $courses->groupBy(function ($course) {
      return $course->category->name ?? 'Uncategorized';
    });
  }
}
