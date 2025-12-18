<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $primaryKey = 'userId';
    public $incrementing = false;     // because userId is string (U0001)
    protected $keyType = 'string';
    
    protected $fillable = [
        'userId',
        'classGroupId',
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Check if user is admin
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    // Check if user is student
    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    // Check if user is lecturer
    public function isLecturer(): bool
    {
        return $this->role === 'lecturer';
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'classGroupId', 'classGroupId');
    }

}
