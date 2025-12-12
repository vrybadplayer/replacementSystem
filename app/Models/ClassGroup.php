<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassGroup extends Model
{
    protected $table = 'classGroup';
    protected $primaryKey = 'classGroupId';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'classGroupId',
        'maxCapacity',
        'currentEnrollment'
    ];

    // Relationships
    public function timetables()
    {
        return $this->hasMany(Timetable::class, 'classGroupId', 'classGroupId');
    }

    public function replacements()
    {
        return $this->hasMany(Replacement::class, 'classGroupId', 'classGroupId');
    }
}
