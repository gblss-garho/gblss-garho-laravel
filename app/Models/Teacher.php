<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id', 'user_id', 'name', 'cnic', 'pid', 'dob', 'subject', 'designation',
        'qualification', 'experience', 'entry_in_service', 'photo_url',
        'auth_email', 'portal_pin', 'intro_line', 'syllabus_plan',
        'progress_notes', 'school_name', 'assigned_class',
    ];

    protected $hidden = ['cnic', 'portal_pin']; // never exposed to public views

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'entry_in_service' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function classes()
    {
        return $this->hasMany(TeacherClass::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /** List of class names this teacher is assigned to (from teacher_classes + legacy assigned_class). */
    public function getAssignedClassesListAttribute(): array
    {
        $classes = $this->classes()->pluck('class_name')->toArray();
        if ($this->assigned_class && ! in_array($this->assigned_class, $classes, true)) {
            $classes[] = $this->assigned_class;
        }
        return $classes;
    }
}
