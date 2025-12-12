<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Database\Eloquent\Collection getAllSubjects()
 * @method static \App\Models\Subject|null getSubjectById(string $subjectId)
 * @method static \App\Models\Subject createOrUpdateSubject(array $data, string $subjectId = null)
 * @method static \App\Models\Subject createSubject(array $data)
 * @method static \App\Models\Subject updateSubject(string $subjectId, array $data)
 * @method static bool deleteSubject(string $subjectId)
 * @method static array getSubjectsForView()
 * 
 * @see \App\Services\SubjectService
 */
class SubjectFacade extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'subject.service';
    }
}

