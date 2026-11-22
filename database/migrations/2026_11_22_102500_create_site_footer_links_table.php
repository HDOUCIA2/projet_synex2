<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_footer_links', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20);
            $table->string('route_name')->nullable()->unique();
            $table->string('label', 100);
            $table->string('url', 500)->nullable();
            $table->string('icon', 50)->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['type', 'is_visible', 'sort_order']);
        });

        $now = now();
        DB::table('site_footer_links')->insert([
            ['type' => 'useful', 'route_name' => 'home', 'label' => 'Accueil', 'is_visible' => true, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'useful', 'route_name' => 'dashboard-analytics', 'label' => 'Analyses des conflits', 'is_visible' => true, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'useful', 'route_name' => 'dashboard-analytics-two', 'label' => 'Tableau de bord', 'is_visible' => true, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'useful', 'route_name' => 'veille', 'label' => 'Veille', 'is_visible' => true, 'sort_order' => 4, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'useful', 'route_name' => 'program-news.index', 'label' => 'Actualités du programme', 'is_visible' => true, 'sort_order' => 5, 'created_at' => $now, 'updated_at' => $now],
            ['type' => 'useful', 'route_name' => 'public.care.index', 'label' => 'Prise en charge', 'is_visible' => true, 'sort_order' => 6, 'created_at' => $now, 'updated_at' => $now],
        ]);

        $this->migrateConfiguredLinks($now);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_footer_links');
    }

    private function migrateConfiguredLinks($now): void
    {
        $allowedSocialIcons = config('synex-footer.allowed_social_icons');
        $sortOrders = ['legal' => 0, 'social' => 0];

        foreach (DB::table('site_settings')->whereNotNull('footer_content')->pluck('footer_content') as $storedContent) {
            $content = is_array($storedContent) ? $storedContent : json_decode($storedContent, true);
            if (!is_array($content)) {
                continue;
            }

            foreach (['legal_links' => 'legal', 'social_links' => 'social'] as $field => $type) {
                foreach ($content[$field] ?? [] as $link) {
                    if (!is_array($link) || !is_string($link['label'] ?? null) || !is_string($link['url'] ?? null)) {
                        continue;
                    }

                    $label = trim($link['label']);
                    $url = trim($link['url']);
                    if ($label === '' || !$this->isSafeUrl($url)) {
                        continue;
                    }

                    $icon = $type === 'social' ? ($link['icon'] ?? null) : null;
                    if ($type === 'social' && !in_array($icon, $allowedSocialIcons, true)) {
                        continue;
                    }

                    $sortOrders[$type]++;
                    DB::table('site_footer_links')->insert([
                        'type' => $type,
                        'label' => $label,
                        'url' => $url,
                        'icon' => $icon,
                        'is_visible' => true,
                        'sort_order' => $sortOrders[$type],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    private function isSafeUrl(string $url): bool
    {
        if (
            str_starts_with($url, '/')
            && !str_starts_with($url, '//')
            && !str_contains($url, '\\')
            && !preg_match('/[\x00-\x20]/', $url)
        ) {
            return true;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return filter_var($url, FILTER_VALIDATE_URL)
            && in_array(strtolower((string) $scheme), ['http', 'https'], true);
    }
};
