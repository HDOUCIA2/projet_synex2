<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\SiteFooterLink;
use App\Models\SiteSetting;
use Closure;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SiteSettingsController extends Controller
{
    public function edit()
    {
        $settings = SiteSetting::query()->find(1)
            ?? new SiteSetting(['name' => config('variables.templateName')]);
        $footerContent = array_replace_recursive(
            config('synex-footer'),
            $settings->footer_content ?? []
        );
        $footerPages = SiteFooterLink::query()->where('type', 'useful')->orderBy('sort_order')->get();
        $footerLegalLinks = SiteFooterLink::query()->where('type', 'legal')->orderBy('sort_order')->get();
        $footerSocialLinks = SiteFooterLink::query()->where('type', 'social')->orderBy('sort_order')->get();

        return view('content.admin.settings.site', compact(
            'settings',
            'footerContent',
            'footerPages',
            'footerLegalLinks',
            'footerSocialLinks'
        ) + ['isFooter' => false]);
    }

    public function update(Request $request)
    {
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'location' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'footer_content' => ['sometimes', 'array:description,copyright_text'],
            'footer_content.description' => ['required_with:footer_content', 'string', 'max:500'],
            'footer_content.copyright_text' => ['required_with:footer_content', 'string', 'max:160'],
        ];
        $attributes = $request->validate($rules);
        unset($attributes['logo']);

        if (isset($attributes['footer_content'])) {
            $settings = SiteSetting::query()->find(1) ?? new SiteSetting();
            $attributes['footer_content'] = array_replace(
                config('synex-footer'),
                $settings->footer_content ?? [],
                $attributes['footer_content']
            );
        }

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

    public function storeFooterLink(Request $request)
    {
        $attributes = $request->validate([
            'type' => ['required', Rule::in(['legal', 'social'])],
            'label' => ['required', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:500', $this->validFooterUrlRule()],
            'icon' => [
                Rule::requiredIf($request->input('type') === 'social'),
                'nullable',
                Rule::in(config('synex-footer.allowed_social_icons')),
            ],
        ]);
        $attributes['sort_order'] = (int) SiteFooterLink::query()
            ->where('type', $attributes['type'])
            ->max('sort_order') + 1;
        $attributes['is_visible'] = true;
        SiteFooterLink::query()->create($attributes);

        return redirect()->route('admin.settings.site.edit')->with('status', 'Le lien du pied de page a été ajouté.');
    }

    public function updateFooterLink(Request $request, SiteFooterLink $footerLink)
    {
        abort_unless(in_array($footerLink->type, ['legal', 'social'], true), 404);
        $attributes = $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'url' => ['required', 'string', 'max:500', $this->validFooterUrlRule()],
            'icon' => [
                Rule::requiredIf($footerLink->type === 'social'),
                'nullable',
                Rule::in(config('synex-footer.allowed_social_icons')),
            ],
        ]);
        unset($attributes['icon']);
        if ($footerLink->type === 'social') {
            $attributes['icon'] = $request->input('icon');
        }
        $footerLink->fill($attributes)->save();

        return redirect()->route('admin.settings.site.edit')->with('status', 'Le lien du pied de page a été modifié.');
    }

    public function destroyFooterLink(SiteFooterLink $footerLink)
    {
        abort_unless(in_array($footerLink->type, ['legal', 'social'], true), 404);
        $footerLink->delete();

        return redirect()->route('admin.settings.site.edit')->with('status', 'Le lien du pied de page a été supprimé.');
    }

    public function updateFooterPageVisibility(Request $request, SiteFooterLink $footerLink)
    {
        abort_unless($footerLink->type === 'useful', 404);
        $attributes = $request->validate([
            'is_visible' => ['required', 'boolean'],
        ]);
        $footerLink->update($attributes);

        return redirect()->route('admin.settings.site.edit')->with('status', 'L’affichage du lien a été mis à jour.');
    }

    private function validFooterUrlRule(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            if (
                str_starts_with($value, '/')
                && !str_starts_with($value, '//')
                && !str_contains($value, '\\')
                && !preg_match('/[\x00-\x20]/', $value)
            ) {
                return;
            }

            $scheme = parse_url($value, PHP_URL_SCHEME);
            if (filter_var($value, FILTER_VALIDATE_URL) && in_array(strtolower((string) $scheme), ['http', 'https'], true)) {
                return;
            }

            $fail('Le lien doit être un chemin interne ou une adresse HTTP(S).');
        };
    }
}
