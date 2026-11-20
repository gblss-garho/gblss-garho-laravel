<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/** Home-page "Digital School" tiles: send each visitor to the right tab for their role. */
class SectionRedirectController extends Controller
{
    private const MAP = [
        'attendance' => ['admin' => 'admin.daily-attendance', 'teacher' => 'teacher.attendance.index', 'parent' => 'public.lookup', 'student' => 'public.lookup'],
        'register' => ['admin' => 'admin.students.index', 'teacher' => 'teacher.class-attendance', 'parent' => 'public.alumni', 'student' => 'public.alumni'],
        'homework' => ['admin' => 'admin.content.index', 'teacher' => 'teacher.homework.index', 'parent' => 'public.lookup', 'student' => 'public.lookup'],
        'bookbank' => ['admin' => 'admin.bookbank.index', 'teacher' => 'teacher.bookbank.index', 'parent' => 'public.textbooks', 'student' => 'public.textbooks'],
    ];

    public function __invoke(Request $request, string $section)
    {
        abort_unless(isset(self::MAP[$section]), 404);
        $user = $request->user();
        if (! $user) {
            $request->session()->put('url.intended', '/go/'.$section);

            return redirect()->route('login');
        }

        return redirect()->route(self::MAP[$section][$user->role] ?? 'home');
    }
}
