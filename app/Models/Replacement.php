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
        'classGroupId',
        'date',
        'timeSlot'
    ];

    protected $casts = [
        'date' => 'date',
        'timeSlot' => 'datetime:H:i',
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

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'classGroupId', 'classGroupId');
    }
}
