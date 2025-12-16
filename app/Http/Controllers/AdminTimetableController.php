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

    // =========================================================================================
    // ============================= FOR TIMETABLE ASSIGNMENT PAGE =============================
    // =========================================================================================
    public function viewTimetableAssignment()
    {

        $courses = $this->mediator->getAllCourses();
        $allSubjects = $this->mediator->getAllSubjects();
        $classGroups = $this->mediator->getAllClassGroups();
        $venues = $this->mediator->getAllVenues();
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

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

    public function storeTimetable(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'courseId' => 'required|string',
            'subjectId' => 'required|string',
            'classType' => 'required|in:Lecture',
            'classGroupIds' => 'required|array|min:1',
            'day' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'venueId' => 'required|string',
            'selectedTimeslot' => 'required|date_format:H:i',
        ]);

        // If empty
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error', 'Invalid Submission: Please fill out all required fields.');
        }

        $data = $request->all();
        $data['lecturerId'] = Auth::id() ?? 'U001';

        $result = $this->mediator->assignClass($data);

        if ($result === 'clash') {
            return back()->withInput()->with('error', 'The selected Venue/Timeslot is already occupied by another class.');
        }

        if ($result === true) {
            return redirect()->route('admin.dashboard')->with('success', 'New Class Assignment created.');
        }

        return back()->withInput()->with('error', 'Assignment Failed: An unknown system error occurred.');
    }



    // ===================================================================================================
    // =================================== Timeslot Management ===========================================
    // ===================================================================================================

    public function viewTimeslotManagement(Request $request) {
       
        $data = $this->getInitialData();
        
        // Populate Dropdown
        $data['courses'] = $this->mediator->getAllCourseCodes();
        $data['groups'] = $this->mediator->getAllClassGroupCodes();

        // Session to record values
        $data['filteredSlots'] = session('filtered_slots', null);
        $data['selectedCourse'] = session('selected_course', null);
        $data['selectedGroup'] = session('selected_group', null);

        return view('admin.timetableModule.timeslotManagement', $data);
    }

    public function filterTimeslots(Request $request) {
        
        $validated = $request->validate([
            'courseId' => 'required|string',
            'classGroupId' => 'required|string',
        ]);
        
        $slots = $this->mediator->getFilteredTimetableSlots($validated['courseId'], $validated['classGroupId']);

        // Refresh page, assign data to session array
        return redirect()->route('admin.timeslotManagement')->with([
            'filtered_slots' => $slots,
            'selected_course' => $validated['courseId'],
            'selected_group' => $validated['classGroupId'],
        ])->withInput();

    }
    
    public function updateTimeslot(Request $request) {

        $validated = $request->validate([
            'originalTimetableId' => 'required|string|exists:timetable,timetableId',
            'newDay' => 'required|string|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'newVenueId' => 'required|string|exists:venue,venueId',
            'newStartTime' => 'required|date_format:H:i',
        ]);
        
        $result = $this->mediator->updateTimeslot($validated['originalTimetableId'], $validated);

        if ($result === 'clash') {
            return back()->withInput()->with('error', 'Update Failed: The new Venue/Timeslot is already occupied.');
        }

        if ($result === true) {
            return redirect()->route('admin.timeslotManagement')->with('success', 'SUCCESS: Timeslot updated.');
        }

        return back()->withInput()->with('error', 'Update Failed: An unknown error occurred.');
    }

    // ===================================================================================================
    // ====================================== helper functions ===========================================
    // ===================================================================================================

    protected function getInitialData() {
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $timeSlots = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];
        $venues = Venue::select('venueId', 'venueType')->get()->toArray();
        return compact('daysOfWeek', 'timeSlots', 'venues');
    }
}
