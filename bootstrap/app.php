<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        // Read-only public look-ups (ID card, admit card, result, LC download): a stale page must never show "419 Page Expired".
        $middleware->validateCsrfTokens(except: [
            'id-cards', 'id-cards/teacher', 'admit-card', 'results', 'leaving-certificate', 'parent-lookup',
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Any other form left open too long: go back with a friendly message instead of a bare 419 page.
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            return redirect()->back(fallback: '/')->withInput($request->except(['_token', 'password']))
                ->withErrors(['csrf' => 'Page purana ho gaya tha. Dobara try karein.']);
        });
    })->create();
