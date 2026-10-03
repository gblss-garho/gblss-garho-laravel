<?php

namespace App\Http\Controllers;

use App\Models\AttendanceMark;
use App\Models\AttendanceRecord;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherAbsenteesController extends Controller
{
    public function index(Request $request)
        {
                $teacher = $request->user()->teacher;
                        $classes = $teacher ? array_values($teacher->assigned_classes_list) : [];

                                $class = $request->query('class', $classes[0] ?? '');
                                        $date = (string) $request->query('date', Carbon::now()->toDateString());
                                                if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                                                            $date = Carbon::now()->toDateString();
                                                                    }

                                                                            $absentees = collect();
                                                                                    $noRecord = false;

                                                                                            if ($teacher && in_array($class, $classes, true)) {
                                                                                                        $record = AttendanceRecord::whereDate('date', $date)
                                                                                                                        ->where('class_name', $class)
                                                                                                                                        ->first();

                                                                                                                                                    if (! $record) {
                                                                                                                                                                    $noRecord = true;
                                                                                                                                                                                } else {
                                                                                                                                                                                                $markedIds = AttendanceMark::where('attendance_record_id', $record->id)
                                                                                                                                                                                                                    ->whereIn('status', ['P', 'L'])
                                                                                                                                                                                                                                        ->pluck('student_id');

                                                                                                                                                                                                                                                        $absentees = Student::where('class', $class)
                                                                                                                                                                                                                                                                            ->where('passed_out', false)
                                                                                                                                                                                                                                                                                                ->whereNotIn('id', $markedIds)
                                                                                                                                                                                                                                                                                                                    ->orderBy('name')
                                                                                                                                                                                                                                                                                                                                        ->get();
                                                                                                                                                                                                                                                                                                                                                    }
                                                                                                                                                                                                                                                                                                                                                            }

                                                                                                                                                                                                                                                                                                                                                                    return view('teacher.absentees', compact(
                                                                                                                                                                                                                                                                                                                                                                                'teacher', 'classes', 'class', 'date', 'absentees', 'noRecord'
                                                                                                                                                                                                                                                                                                                                                                                        ));
                                                                                                                                                                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                                                                                                                                                                            }
                                                                                                                                                                                                                                                                                                                                                                                        