<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    protected $table = 'timetable';
    protected $primaryKey = 'timetableId';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'timetableId',
        'classGroupId',
        'courseSubjectId',
        'classType',
        'userId',
        'venueId',
        'dayOfWeek',
        'startTime',
        'endTime',
        'classDate',
        'classMode'
    ];

    protected $casts = [
        'date' => 'date',
        'startTime' => 'datetime:H:i',
        'endTime' => 'datetime:H:i',
    ];

    // Relationships
    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'classGroupId', 'classGroupId');
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venueId', 'venueId');
    }

    public function courseSubject()
    {
        return $this->belongsTo(CourseSubject::class, 'courseSubjectId', 'courseSubjectId');
    }

    // Access subject through courseSubject
    public function subject()
    {
        return $this->hasOneThrough(
            Subject::class,
            CourseSubject::class,
            'courseSubjectId',
            'subjectId',
            'courseSubjectId',
            'subjectId'
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }
}
