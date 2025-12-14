<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminTimetableController extends Controller
{

    public function viewSubjectAssignment()
    {
        // Returns the Admin View located at resources/views/admin/subject_assignment.blade.php
        return view('admin.timetableModule.subjectAssignment');
    }

    public function viewTimeslotManagement()
    {
        // Returns the view in your specific folder structure
        return view('admin.timetableModule.timeslotManagement');
    }

}
