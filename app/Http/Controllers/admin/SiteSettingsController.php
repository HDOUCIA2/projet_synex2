<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteSettingsController extends Controller
{
    public function edit()
    {
        $settings = SiteSetting::query()->find(1)
            ?? new SiteSetting(['name' => config('variables.templateName')]);

        return view('content.admin.settings.site', compact('settings'));
    }

    public function update(Request $request)
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        unset($attributes['logo']);

        $disk = Storage::disk('public');
        $settings = SiteSetting::query()->find(1) ?? new SiteSetting();
        $oldLogoPath = $settings?->logo_path;
        $newLogoPath = $request->file('logo')?->store('site', 'public');

        if ($request->hasFile('logo') && $newLogoPath === false) {
            throw new \RuntimeException('Unable to store the site logo.');
        }

        if (is_string($newLogoPath)) {
            $attributes['logo_path'] = $newLogoPath;
        }

        try {
            $settings->fill($attributes)->save();
        } catch (QueryException $exception) {
            if (is_string($newLogoPath)) {
                $disk->delete($newLogoPath);
            }

            throw $exception;
        }

        if (is_string($newLogoPath) && $oldLogoPath) {
            $disk->delete($oldLogoPath);
        }

        return redirect()->route('admin.settings.site.edit')->with('status', 'Les paramètres du site ont été enregistrés.');
    }
}
