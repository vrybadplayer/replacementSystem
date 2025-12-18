<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class CourseSubjectManagementFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'courseSubjectManagement.service';
    }
}

