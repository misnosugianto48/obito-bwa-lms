<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseSection;
use Illuminate\Support\Facades\Auth;

class CourseService
{

  // cek apakah user sudah terdaftar di course atau belum
  public function enrollUser(Course $course)
  {

    $user = Auth::user();

    if (!$course->courseStudents()->where('user_id', $user->id)->exists()) {
      $course->courseStudents()->create([
        'user_id' => $user->id,
        'is_active' => true,
      ]);
    }

    return $user->name;
  }

  // mendapatkan section dan content pertama dari course saat pertama kali user terdaftar di course
  public function getFirstSectionAndContent(Course $course): array
  {
    $firstSectionId = $course->courseSections()->orderBy('id')->value('id');
    $firstContentId = $firstSectionId ? $course->courseSections()->find($firstSectionId)->sectionContents()->orderBy('id')->value('id') : null;

    return [
      'firstSectionId' => $firstSectionId,
      'firstContentId' => $firstContentId,
    ];
  }

  // mendapatkan data untuk halaman mana user belajar di course, supaya bisa lanjut ke content selanjutnya
  public function getLearningData(Course $course, $contentSectionId, $sectionContentId): array
  {
    $course->load(['courseSection.sectionContents']);

    $currentSection = $course->courseSections()->find($contentSectionId);
    $currentContent = $currentSection ? $currentSection->sectionContents()->find($sectionContentId) : null;

    $nextContent = null;

    if ($currentContent) {

      $nextContent = $currentSection->sectionContents()
        ->where('id', '>', $currentContent->id)
        ->sortBy('id')
        ->first();
    }

    if (!$nextContent && $currentSection) {

      $nextSection = $course->courseSections()
        ->where('id', '>', $currentSection->id)
        ->sortBy('id')
        ->first();

      if ($nextSection) {
        $nextContent = $nextSection->sectionContents()->sortBy('id')->first();
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
}
