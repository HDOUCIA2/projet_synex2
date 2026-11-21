<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterBasic extends Controller
{
  public function index()
  {
    if (Auth::check()) {
      return redirect()->route('dashboard-analytics');
    }

    return view('content.authentications.auth-register-basic');
  }

  public function store(Request $request)
  {
    if (Auth::check()) {
      return redirect()->route('dashboard-analytics');
    }

    $request->merge([
      'username' => mb_strtolower(trim((string) $request->input('username'))),
      'email' => mb_strtolower(trim((string) $request->input('email'))),
    ]);

    $attributes = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'username' => ['required', 'alpha_dash:ascii', 'min:3', 'max:30', 'unique:users,username'],
      'email' => ['required', 'email', 'max:255', 'unique:users,email'],
      'password' => ['required', 'string', 'min:12', 'confirmed'],
    ]);

    $user = User::create([
      ...$attributes,
      'role' => 'user',
    ]);

    Auth::login($user);
    $request->session()->regenerate();

    return redirect()->route('dashboard-analytics');
  }
}
