<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Replacement extends Model
{
    protected $table = 'replacement';
    protected $primaryKey = 'replacementId';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'replacementId',
        'appealId',
        'userId',
        'requestedClassGroupId',
        'originalTimetableId',
        'originalDate',
        'requestedDate',
        'originalStartTime',
        'startTime',
        'endTime'
    ];

    protected $casts = [
        'originalDate' => 'date',
        'requestedDate' => 'date',
        'originalStartTime' => 'datetime:H:i',
        'startTime' => 'datetime:H:i',
        'endTime' => 'datetime:H:i',
    ];

    // Relationships
    public function appeal()
    {
        return $this->belongsTo(ReplacementAppeal::class, 'appealId', 'appealId');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }

    public function requestedClassGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'requestedClassGroupId', 'classGroupId');
    }

    public function originalTimetable()
    {
        return $this->belongsTo(Timetable::class, 'originalTimetableId', 'timetableId');
    }
}
