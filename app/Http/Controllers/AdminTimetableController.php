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
        $classGroups = \App\Models\ClassGroup::select('classGroupId')->get()->toArray();
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        $venues = \App\Models\Venue::select('venueId', 'venueType')->get()->toArray();
        // fixed 2hr interal (to simplify the module)
        $classType = 'Lecture';
        $timeSlots = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];

        return view('admin.timetableModule.timetableAssignment', compact(
            'courses', 
            'allSubjects',
            'classGroups',
            'classType', // Pass the locked type
            'daysOfWeek',
            'venues',
            'timeSlots' // Pass the fixed slots
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
        $data['lecturerId'] = Auth::id() ?? 'U001'; // Get the logged-in Admin/User ID



        $result = $this->mediator->assignClass($data);

        // 3. HANDLE MEDIATOR RESPONSE
        if ($result === 'clash') {
            // Mediator explicitly returned a clash error
            return back()->withInput()->with('error', 'Assignment Failed: The selected Venue/Timeslot is already occupied by another class.');
        } 
        
        if ($result === true) {
            // Success
            return redirect()->route('admin.dashboard')->with('success', 'SUCCESS: New Class Assignment created.');
        } 
        
        // Catch-all for other errors (e.g., DB write failed)
        return back()->withInput()->with('error', 'Assignment Failed: An unknown system error occurred.');
    }



    // ===================================================================================================

    public function viewTimeslotManagement()
    {
        // Returns the view in your specific folder structure
        return view('admin.timetableModule.timeslotManagement');
    }

}
