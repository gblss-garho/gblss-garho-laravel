<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Ye credentials hamare records se match nahi karte.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($this->dashboardPathFor(Auth::user()));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    protected function dashboardPathFor(User $user): string
    {
        return match ($user->role) {
            User::ROLE_ADMIN => '/admin/dashboard',
            User::ROLE_TEACHER => '/teacher/dashboard',
            User::ROLE_PARENT => '/parent/dashboard',
            User::ROLE_STUDENT => '/student/dashboard',
            default => '/',
        };
    }
}
