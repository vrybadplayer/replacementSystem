<?php

namespace App\Http\Controllers;

use App\Facades\SubjectFacade;

class SubjectController extends Controller
{
    public function index()
    {
        // Get subjects from database using Facade
        $subjects = SubjectFacade::getSubjectsForView();

        return view('subject.listSubject', compact('subjects'));
    }
}

