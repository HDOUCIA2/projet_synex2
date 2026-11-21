<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

class AccountSettingsAccount extends Controller
{
  public function index()
  {
    return view('content.pages.pages-account-settings-account');
  }

  public function update(Request $request)
  {
    $user = $request->user();
    $request->merge([
      'username' => mb_strtolower(trim((string) $request->input('username'))),
      'email' => mb_strtolower(trim((string) $request->input('email'))),
    ]);

    $attributes = $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'username' => ['required', 'alpha_dash:ascii', 'min:3', 'max:30', Rule::unique('users', 'username')->ignore($user->id)],
      'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
      'current_password' => ['nullable', 'required_with:password', 'current_password'],
      'password' => ['nullable', 'string', 'min:12', 'confirmed'],
      'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
      'remove_avatar' => ['nullable', 'boolean'],
    ]);

    if (empty($attributes['password'])) {
      unset($attributes['password']);
    }

    $removeAvatar = !empty($attributes['remove_avatar']) && !$request->hasFile('avatar');
    unset($attributes['current_password'], $attributes['remove_avatar'], $attributes['avatar']);
    $disk = Storage::disk('public');
    $oldAvatarPath = $user->avatar_path;
    $newAvatarPath = $request->file('avatar')?->store('avatars', 'public');

    if ($request->hasFile('avatar') && $newAvatarPath === false) {
      throw new \RuntimeException('Impossible d’enregistrer la photo de profil.');
    }

    if (is_string($newAvatarPath)) {
      $attributes['avatar_path'] = $newAvatarPath;
    } elseif ($removeAvatar) {
      $attributes['avatar_path'] = null;
    }

    try {
      $user->fill($attributes)->save();
    } catch (QueryException $exception) {
      if (is_string($newAvatarPath)) {
        $disk->delete($newAvatarPath);
      }

      throw $exception;
    }

    if (($removeAvatar || is_string($newAvatarPath)) && $oldAvatarPath) {
      $disk->delete($oldAvatarPath);
    }

    return redirect()->route('pages-account-settings-account')->with('status', 'Votre profil a été mis à jour.');
  }
}
