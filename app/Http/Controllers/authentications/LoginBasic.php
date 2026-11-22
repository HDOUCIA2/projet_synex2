<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginBasic extends Controller
{
  public function index()
  {
    if (Auth::check()) {
      return Auth::user()->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('dashboard-analytics');
    }

    return view('content.authentications.auth-login-basic');
  }

  public function authenticate(Request $request)
  {
    $credentials = $request->validate([
      'identifier' => ['required', 'string', 'max:255'],
      'password' => ['required', 'string'],
      'remember' => ['nullable', 'boolean'],
    ], [
      'identifier.required' => 'Saisissez votre identifiant.',
      'password.required' => 'Saisissez votre mot de passe.',
    ]);

    $identifier = mb_strtolower($credentials['identifier']);
    $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    if (!Auth::attempt([
      $field => $identifier,
      'password' => $credentials['password'],
    ], $request->boolean('remember'))) {
      return back()
        ->withErrors(['identifier' => 'Identifiant ou mot de passe incorrect.'])
        ->onlyInput('identifier');
    }

    $request->session()->regenerate();

    if (Auth::user()->isAdmin()) {
      return redirect()->route('admin.dashboard');
    }

    return redirect()->intended(route('dashboard-analytics'));
  }

  public function logout(Request $request)
  {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('auth-login-basic');
  }
}
