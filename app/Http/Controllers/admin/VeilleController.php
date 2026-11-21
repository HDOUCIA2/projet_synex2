<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\VeilleCard;
use App\Models\VeilleSection;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use RuntimeException;

class VeilleController extends Controller
{
    private const ICONS = [
        'bx-radar' => 'Incidents',
        'bx-analyse' => 'Réponses de paix',
        'bx-network-chart' => 'Qui affronte qui',
        'bx-shield-quarter' => 'Violences basées sur le genre',
        'bx-bar-chart-alt-2' => 'Gravité rapportée',
        'bx-bolt' => 'Éclair',
        'bx-handshake' => 'Poignée de main',
        'bx-group' => 'Groupe',
        'bx-line-chart' => 'Tendance',
        'bx-map' => 'Carte',
        'bx-book-open' => 'Livre ouvert',
    ];

    public function index()
    {
        $section = VeilleSection::query()
            ->with('cards')
            ->where('key', 'home_features')
            ->firstOrFail();
        $icons = self::ICONS;

        return view('content.admin.veille.index', compact('section', 'icons'));
    }

    public function updateSection(Request $request, VeilleSection $section)
    {
        abort_unless($section->key === 'home_features', 404);

        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:10000'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $attributes['is_active'] = $request->boolean('is_active');
        $section->update($attributes);

        return back()->with('status', 'Les textes de la page Veille ont été mis à jour.');
    }

    public function storeCard(Request $request, VeilleSection $section)
    {
        abort_unless($section->key === 'home_features', 404);

        $attributes = $this->validatedCard($request);
        $image = $attributes['image'] ?? null;
        unset($attributes['image'], $attributes['remove_image']);
        if ($image) {
            $attributes['image_path'] = $this->storeImage($image);
        }
        $attributes['display_order'] = ((int) $section->cards()->max('display_order')) + 1;
        $section->cards()->create($attributes);

        return back()->with('status', 'La lecture a été ajoutée.');
    }

    public function updateCard(Request $request, VeilleCard $card)
    {
        abort_unless($card->section()->where('key', 'home_features')->exists(), 404);

        $attributes = $this->validatedCard($request);
        $image = $attributes['image'] ?? null;
        $removeImage = $request->boolean('remove_image');
        unset($attributes['image'], $attributes['remove_image']);

        $previousImage = $card->image_path;
        if ($image) {
            $attributes['image_path'] = $this->storeImage($image);
        } elseif ($removeImage) {
            $attributes['image_path'] = null;
        }

        $card->update($attributes);
        if ($previousImage && ($image || $removeImage)) {
            $this->deleteImage($previousImage);
        }

        return back()->with('status', 'La lecture a été mise à jour.');
    }

    public function destroyCard(VeilleCard $card)
    {
        abort_unless($card->section()->where('key', 'home_features')->exists(), 404);
        $imagePath = $card->image_path;
        $card->delete();
        if ($imagePath) {
            $this->deleteImage($imagePath);
        }

        return back()->with('status', 'La lecture a été supprimée.');
    }

    private function validatedCard(Request $request): array
    {
        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:10000'],
            'icon' => ['required', 'string', Rule::in(array_keys(self::ICONS))],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $attributes['is_active'] = $request->boolean('is_active');

        return $attributes;
    }

    private function storeImage(UploadedFile $image): string
    {
        $path = $image->store('veille/cards', 'public');

        if (!is_string($path) || $path === '') {
            throw new RuntimeException('Impossible d’enregistrer l’image de la lecture.');
        }

        return $path;
    }

    private function deleteImage(string $path): void
    {
        $disk = Storage::disk('public');

        if ($disk->exists($path) && !$disk->delete($path)) {
            throw new RuntimeException('Impossible de supprimer l’image de la lecture.');
        }
    }
}
