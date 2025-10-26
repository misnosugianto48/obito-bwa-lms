<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    protected $course;
    public function __construct(CourseService $courseService)
    {
        $this->course = $courseService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $courseByCategory = $this->course->getCoursesGroupedByCategory();

        return view('courses.index', compact('courseByCategory'));
    }

    public function detail(Course $course)
    {
        $course->load(
            [
                'category',
                'benefits',
                'courseSections.sectionContents'
            ]
        );
        return view('courses.details', compact('course'));
    }


    public function join(Course $course)
    {
        $studentName = $this->course->enrollUser($course);
        $firstSectionAndContent = $this->course->getFirstSectionAndContent($course);

        return view('courses.successJoined', array_merge(
            compact('course', 'studentName'),
            $firstSectionAndContent
        ));
    }

    public function learning(Course $course, $contentSectionId, $sectionContentId)
    {
        $learningData = $this->course->getLearningData($course, $contentSectionId, $sectionContentId);

        return view('courses.learning', $learningData);
    }

    public function learningFinished(Course $course)
    {
        return view('courses.learningFinished', compact('course'));
    }

    public function searchCourses(Request $request)
    {
        $request->validate([
            'search' => 'required|string'
        ]);
        $keyword = $request->search;

        $courses = $this->course->searchCourses($keyword);

        return view('courses.search', compact('courses', 'keyword'));
    }
}
