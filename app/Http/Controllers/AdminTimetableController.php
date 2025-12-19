<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Mediator\TimetableMediatorInterface;
use App\Models\Venue;

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

        $classType = 'Lecture';
        $timeSlots = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];


        // Generate token
        $actionToken = hash('sha256', session()->getId() . random_bytes(32) . microtime(true) . 'timetable_assignment');

        // Generate Id
        $confirmationId = 'ASSGN_' . strtoupper(bin2hex(random_bytes(6)));

        session([
            'assignment_action_token' => $actionToken,
            'assignment_token_expires' => now()->addMinutes(15),
            'assignment_confirmation_id' => $confirmationId
        ]);

        return view('admin.timetableModule.timetableAssignment', compact(
            'courses',
            'allSubjects',
            'classGroups',
            'classType',
            'daysOfWeek',
            'venues',
            'timeSlots',
            'actionToken',
            'confirmationId'
        ));
    }

    public function storeTimetable(Request $request)
    {
        $storedToken = session('assignment_action_token');
        $tokenExpires = session('assignment_token_expires');

        if (!$storedToken || !$tokenExpires || now()->gt($tokenExpires)) {
            return back()->withInput()->with('error', 'Security token expired. Please refresh the page and try again.');
        }

        // Validate Token
        $submittedToken = $request->input('action_token');
        if (!$submittedToken || !hash_equals($storedToken, $submittedToken)) {
            return back()->withInput()->with('error', 'Invalid security token. Please refresh the page.');
        }

        // Validate Id
        $storedConfirmationId = session('assignment_confirmation_id');
        $submittedConfirmationId = $request->input('confirmation_id');

        if (
            $storedConfirmationId && $submittedConfirmationId &&
            !hash_equals($storedConfirmationId, $submittedConfirmationId)
        ) {
            return back()->withInput()->with('error', 'Invalid confirmation ID.');
        }

        // Double Check
        if (!$request->has('user_confirmation') || $request->input('user_confirmation') !== 'yes') {
            return back()->withInput()->with('error', 'Please confirm the action by checking the confirmation box.');
        }

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

        // Clear tokens
        if ($result === true) {
            session()->forget([
                'assignment_action_token',
                'assignment_token_expires',
                'assignment_confirmation_id'
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'New Class Assignment created successfully.');
        }

        if ($result === 'clash') {
            return back()->withInput()->with('error', 'The selected Venue/Timeslot is already occupied by another class.');
        }

        return back()->withInput()->with('error', 'Assignment Failed: An unknown system error occurred.');
    }



    // ===================================================================================================
    // =================================== Timeslot Management ===========================================
    // ===================================================================================================

    public function viewTimeslotManagement(Request $request)
    {
        $data = $this->getInitialData();

        // Populate Dropdown
        $data['courses'] = $this->mediator->getAllCourseCodes();
        $data['groups'] = $this->mediator->getAllClassGroupCodes();

        // Session to record values
        $data['filteredSlots'] = session('filtered_slots', null);
        $data['selectedCourse'] = session('selected_course', null);
        $data['selectedGroup'] = session('selected_group', null);

        // Generate token
        $actionToken = hash('sha256', session()->getId() . random_bytes(32) . microtime(true));

        // Generate unique confirmation ID for double verification
        $confirmationId = 'TSL_' . strtoupper(bin2hex(random_bytes(8)));

        session([
            'timeslot_action_token' => $actionToken,
            'timeslot_token_expires' => now()->addMinutes(15),
            'timeslot_confirmation_id' => $confirmationId
        ]);

        $data['actionToken'] = $actionToken;
        $data['confirmationId'] = $confirmationId;

        return view('admin.timetableModule.timeslotManagement', $data);
    }

    public function filterTimeslots(Request $request)
    {

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

    public function updateTimeslot(Request $request)
    {
        // Get Token
        $storedToken = session('timeslot_action_token');
        $tokenExpires = session('timeslot_token_expires');

        if (!$storedToken || !$tokenExpires || now()->gt($tokenExpires)) {
            return back()->with('error', 'Security token expired. Please refresh the page.');
        }

        // Validate Token
        $submittedToken = $request->input('action_token');
        if (!$submittedToken || !hash_equals($storedToken, $submittedToken)) {
            return back()->with('error', 'Invalid security token.');
        }

        // Validate Id
        $storedConfirmationId = session('timeslot_confirmation_id');
        $submittedConfirmationId = $request->input('confirmation_id');

        if (
            $storedConfirmationId && $submittedConfirmationId &&
            !hash_equals($storedConfirmationId, $submittedConfirmationId)
        ) {
            return back()->with('error', 'Invalid confirmation ID.');
        }

        // Double Confirm
        if (!$request->has('user_confirmation') || $request->input('user_confirmation') !== 'yes') {
            return back()->with('error', 'Please confirm the action by checking the confirmation box.');
        }

        $originalTimetableId = $request->input('originalTimetableId');
        $newData = [
            'newDay' => $request->input('newDay'),
            'newVenueId' => $request->input('newVenueId'),
            'newStartTime' => $request->input('newStartTime'),
        ];

        $result = $this->mediator->updateTimeslot($originalTimetableId, $newData);

        // Clear Token
        if ($result === true) {
            session()->forget([
                'timeslot_action_token',
                'timeslot_token_expires',
                'timeslot_confirmation_id'
            ]);

            return redirect()->route('admin.timeslotManagement')
                ->with('success', 'Timeslot updated successfully.');
        }

        $errorMessages = [
            'not_found' => 'Timeslot not found.',
            'clash' => 'Timeslot clashes with existing schedule.',
            'error' => 'An error occurred. Please try again.'
        ];

        return back()->with('error', $errorMessages[$result] ?? 'Unknown error.');
    }

    // ===================================================================================================
    // ====================================== helper functions ===========================================
    // ===================================================================================================

    protected function getInitialData()
    {
        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $timeSlots = ['08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];
        $venues = Venue::select('venueId', 'venueType')->get()->toArray();
        return compact('daysOfWeek', 'timeSlots', 'venues');
    }
}
