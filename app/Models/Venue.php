<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $table = 'venue';
    protected $primaryKey = 'venueId';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'venueId',
        'venueType',
        'pax',
        'equipment'
    ];

    // Relationships
    public function timetables()
    {
        return $this->hasMany(Timetable::class, 'venueId', 'venueId');
    }
}
