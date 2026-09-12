<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class FaceEnrollController extends Controller
{
    public function show(Request $request)
    {
        $teacher = $request->user()->teacher;

        return view('teacher.face-enroll', [
            'teacher' => $teacher,
            'isEnrolled' => $teacher && ! empty($teacher->face_descriptor),
        ]);
    }

    public function store(Request $request)
    {
        $descriptor = $this->decodeDescriptor($request->input('descriptor_json'));

        $teacher = $request->user()->teacher;
        if (! $teacher) {
            return back()->withErrors(['face' => 'Aapke account se koi teacher record linked nahi hai.']);
        }

        $teacher->face_descriptor = $descriptor;
        $teacher->save();

        return redirect()->route('teacher.attendance.index', [], false)
            ->with('status', 'Chehra successfully enroll ho gaya. Ab aap face se check-in/check-out kar sakte hain.');
    }

    /** Shared by the attendance controller too — decode + validate a descriptor JSON string. */
    public static function decodeDescriptorOrFail(?string $json): array
    {
        $data = json_decode((string) $json, true);

        if (! is_array($data) || count($data) !== 128) {
            throw ValidationException::withMessages([
                'attendance' => 'Chehra capture theek se nahi hua. Dobara try karen.',
            ]);
        }

        foreach ($data as $value) {
            if (! is_numeric($value)) {
                throw ValidationException::withMessages([
                    'attendance' => 'Chehra capture theek se nahi hua. Dobara try karen.',
                ]);
            }
        }

        return array_map('floatval', $data);
    }

    protected function decodeDescriptor(?string $json): array
    {
        return static::decodeDescriptorOrFail($json);
    }
}
