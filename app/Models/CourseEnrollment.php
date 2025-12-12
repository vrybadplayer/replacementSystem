<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    protected $table = 'course_enrollment';
    protected $primaryKey = 'enrollmentID';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'enrollmentID',
        'userID',
        'courseID',
        'year',
        'semester'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'userID', 'userId');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'courseID', 'courseId');
    }
}
