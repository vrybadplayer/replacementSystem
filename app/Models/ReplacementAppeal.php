<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReplacementAppeal extends Model
{
    protected $table = 'replacementAppeal';
    protected $primaryKey = 'appealId';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'appealId',
        'userId',
        'originalTimetableId',
        'requestedTimetableId',
        'originalClassGroupId',
        'requestedClassGroupId',
        'originalTimeslot',
        'requestedTimeslot',
        'reason',
        'proof',
        'status',
        'submissionDate',
        'decisionDate',
        'adminId',
        'adminRemarks'
    ];

    protected $casts = [
        'submissionDate' => 'datetime',
        'decisionDate' => 'datetime',
        'originalTimeslot' => 'datetime:H:i',
        'requestedTimeslot' => 'datetime:H:i',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'userId');
    }

    public function originalClass()
    {
        return $this->belongsTo(\App\Models\ClassGroup::class, 'originalClassGroupId', 'classGroupId');
    }

    public function requestedClass()
    {
        return $this->belongsTo(\App\Models\ClassGroup::class, 'requestedClassGroupId', 'classGroupId');
    }

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'adminId', 'userId');
    }

    public function replacement()
    {
        return $this->hasOne(Replacement::class, 'appealId', 'appealId');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'Rejected');
    }
}
