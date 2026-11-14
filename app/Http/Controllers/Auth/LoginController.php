<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

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

        $key = 'login:'.Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $minutes = (int) ceil(RateLimiter::availableIn($key) / 60);

            return back()->withErrors([
                'email' => 'ڪيتريون ئي غلط ڪوششون ٿيون. '.$minutes.' منٽن کانپوءِ ٻيهر ڪوشش ڪريو.',
            ])->onlyInput('email');
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, 900);

            return back()->withErrors([
                'email' => 'لاگ ان ٿي نه سگهيو. اي ميل/پاسورڊ چيڪ ڪريو.',
            ])->onlyInput('email');
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect($this->dashboardPathFor(Auth::user()));
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
