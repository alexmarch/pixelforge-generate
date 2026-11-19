<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Inertia\Response as InertiaResponse;

class AuthController extends Controller
{
    public function __construct(
    ){}
    public function show(): InertiaResponse | RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('user.dashboard');
        }
        return inertia('Auth/Show');
    }
    public function auth(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if (Auth::attempt($validated)) {
            return redirect()->route('user.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
        
    }
    public function reset(){}
    public function logout(): RedirectResponse
    {
        Auth::logout();
        return redirect()->route('login');
    }
}
