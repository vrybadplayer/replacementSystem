<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Mediator\TimetableMediatorInterface;
use App\Models\Venue;
use App\Models\ClassGroup;

class AdminTimetableController extends Controller
{

protected $mediator;

    public function __construct(TimetableMediatorInterface $mediator)
    {
        $this->mediator = $mediator;
    }

    // ============================= FOR TIMETABLE ASSIGNMENT PAGE =============================
    public function viewTimetableAssignment() {

        $courses = $this->mediator->getAllCourses();
        $allSubjects = $this->mediator->getAllSubjects();
        $classGroups = \App\Models\ClassGroup::select('classGroupId')->get()->toArray(); // Call API
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $venues = \App\Models\Venue::select('venueId', 'venueType')->get()->toArray(); // Call API

        // fixed 2hr interal (to simplify the module)
        $classType = 'Lecture';
        $timeSlots = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];

        return view('admin.timetableModule.timetableAssignment', compact(
            'courses', 
            'allSubjects',
            'classGroups',
            'classType',
            'daysOfWeek',
            'venues',
            'timeSlots'
        ));
    }

    public function storeTimetable(Request $request) {
        $validator = Validator::make($request->all(), [
            'courseId' => 'required|string',
            'subjectId' => 'required|string',
            'classType' => 'required|in:Lecture',
            'classGroupIds' => 'required|array|min:1',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'venueId' => 'required|string',
            'selectedTimeslot' => 'required|date_format:H:i',
        ]);


        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Invalid Submission: Please fill out all required fields.');
        }
        
        $data = $request->all();
        $data['lecturerId'] = Auth::id() ?? 'U001';



        $result = $this->mediator->assignClass($data);

        if ($result === 'clash') {
            return back()->withInput()->with('error', 'Assignment Failed: The selected Venue/Timeslot is already occupied by another class.');
        } 
        
        if ($result === true) {
            return redirect()->route('admin.dashboard')->with('success', 'SUCCESS: New Class Assignment created.');
        } 
        
        return back()->withInput()->with('error', 'Assignment Failed: An unknown system error occurred.');
    }



    // ===================================================================================================

    public function viewTimeslotManagement()
    {
        // Returns the view in your specific folder structure
        return view('admin.timetableModule.timeslotManagement');
    }

}
