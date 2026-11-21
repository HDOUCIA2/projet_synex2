<?php

namespace Tests\Feature;

use App\Models\ProgramNews;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProgramNewsManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_lists_published_news_and_filters_by_year(): void
    {
        $news2025 = $this->createNews('Actualité 2025', '2025-05-10', true, 'analyse', 'sud');
        $news2026 = $this->createNews('Actualité 2026', '2026-03-12', true, 'veille', 'est');
        $this->createNews('Brouillon privé', '2026-04-01', false);

        $this->get(route('program-news.index'))
            ->assertOk()
            ->assertSee('Actualités du programme')
            ->assertDontSee('Les nouvelles et les temps forts du programme.')
            ->assertSee('synex-public-footer', false)
            ->assertSee('Actualité 2025')
            ->assertSee('Actualité 2026')
            ->assertDontSee('Brouillon privé')
            ->assertSee(route('program-news.show', $news2025));

        $this->get(route('program-news.index', ['year' => 2025]))
            ->assertOk()
            ->assertSee('Actualité 2025')
            ->assertDontSee('Actualité 2026');

        $this->get(route('program-news.index', [
            'q' => 'Actualité 2025',
            'category' => 'analyse',
            'region' => 'sud',
            'year' => 2025,
        ]))
            ->assertOk()
            ->assertSee('Actualité 2025')
            ->assertDontSee('Actualité 2026')
            ->assertSee('name="category"', false)
            ->assertSee('name="region"', false);

        $this->get(route('program-news.show', $news2026))
            ->assertOk()
            ->assertSee('Article complet de Actualité 2026');
    }

    public function test_unpublished_news_cannot_be_viewed_publicly(): void
    {
        $draft = $this->createNews('Brouillon', '2026-04-01', false);

        $this->get(route('program-news.show', $draft))->assertNotFound();
    }

    public function test_public_article_shows_key_information_and_related_published_news(): void
    {
        $article = $this->createNews('Analyse de référence', '2026-04-01', true, 'analyse', 'sud');
        $this->createNews('Actualité liée', '2026-04-02', true, 'veille', 'sud');
        $this->createNews('Actualité sans lien', '2026-04-03', true, 'paix', 'nord');
        $this->createNews('Brouillon lié', '2026-04-04', false, 'analyse', 'sud');

        $this->get(route('program-news.show', $article))
            ->assertOk()
            ->assertSee('article-main-card', false)
            ->assertSee('Informations clés')
            ->assertSee('Sud')
            ->assertSee('Lire aussi')
            ->assertSee('Actualité liée')
            ->assertDontSee('Actualité sans lien')
            ->assertDontSee('Brouillon lié');
    }

    public function test_public_page_shows_responsive_navigation_and_bootstrap_pagination(): void
    {
        for ($index = 1; $index <= 12; $index++) {
            $this->createNews(sprintf('Actualité de test %02d', $index), '2025-05-10');
        }

        $this->get(route('program-news.index', ['year' => 2025]))
            ->assertOk()
            ->assertSee('data-bs-toggle="collapse"', false)
            ->assertSee('class="pagination"', false)
            ->assertSee('page=2', false)
            ->assertSee('Affichage de 1 à 9 sur 12 actualités');

        $this->get(route('program-news.index', ['year' => 2025, 'page' => 2]))
            ->assertOk()
            ->assertSee('Actualité de test 03')
            ->assertSee('Affichage de 10 à 12 sur 12 actualités')
            ->assertSee('synex-public-footer', false);
    }

    public function test_administrator_list_uses_bootstrap_pagination(): void
    {
        for ($index = 1; $index <= 16; $index++) {
            $this->createNews(sprintf('Brouillon de test %02d', $index), '2025-05-10', false);
        }

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.program-news.index'))
            ->assertOk()
            ->assertSee('class="pagination"', false)
            ->assertDontSee('synex-public-footer', false)
            ->assertSee('Affichage de 1 à 15 sur 16 actualités');
    }

    public function test_administrator_can_create_edit_publish_and_delete_news(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        $image = UploadedFile::fake()->createWithContent(
            'programme.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAIAAACQd1PeAAAADUlEQVR4nGP4z8AAAAMBAQDJ/pLvAAAAAElFTkSuQmCC')
        );

        $this->actingAs($admin)
            ->get(route('admin.program-news.index'))
            ->assertOk()
            ->assertSee('Actualités du programme');
        $this->get(route('admin.program-news.create'))
            ->assertOk()
            ->assertSee('Article complet')
            ->assertSee('news-category')
            ->assertSee('news-region');

        $this->post(route('admin.program-news.store'), [
            'title' => 'Lancement du programme',
            'summary' => 'Une courte présentation du lancement.',
            'content' => 'Le programme est lancé.',
            'category' => 'analyse',
            'region' => 'sud',
            'image' => $image,
            'published_at' => '2026-04-15T10:30',
            'is_published' => '1',
        ])->assertRedirect(route('admin.program-news.index'));

        $news = ProgramNews::query()->where('title', 'Lancement du programme')->firstOrFail();
        $this->get(route('admin.program-news.edit', $news))
            ->assertOk()
            ->assertSee('Lancement du programme');
        Storage::disk('public')->assertExists($news->image_path);
        $oldImage = $news->image_path;

        $this->put(route('admin.program-news.update', $news), [
            'title' => 'Lancement du programme modifié',
            'summary' => 'Description mise à jour.',
            'content' => 'Le contenu complet est mis à jour.',
            'category' => 'veille',
            'region' => 'est',
            'published_at' => '2026-04-16T10:30',
            'is_published' => '1',
        ])->assertRedirect(route('admin.program-news.index'));

        $this->assertDatabaseHas('program_news', [
            'id' => $news->id,
            'title' => 'Lancement du programme modifié',
            'category' => 'veille',
            'region' => 'est',
            'is_published' => true,
        ]);
        Storage::disk('public')->assertExists($oldImage);

        $this->patch(route('admin.program-news.publication', $news))
            ->assertRedirect(route('admin.program-news.index'));
        $this->assertDatabaseHas('program_news', ['id' => $news->id, 'is_published' => false]);

        $this->delete(route('admin.program-news.destroy', $news))
            ->assertRedirect(route('admin.program-news.index'));
        $this->assertDatabaseMissing('program_news', ['id' => $news->id]);
        Storage::disk('public')->assertMissing($oldImage);
    }

    public function test_regular_users_cannot_manage_program_news(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get(route('admin.program-news.index'))
            ->assertForbidden();
    }

    public function test_public_pages_share_the_sneat_navigation(): void
    {
        foreach ([route('home'), route('veille'), route('program-news.index')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('navbar-expand-xl navbar-light bg-white shadow-sm sticky-top synex-navbar', false)
                ->assertSee('assets/img/branding/svg/symbole_dore.svg')
                ->assertSee('data-bs-toggle="collapse"', false)
                ->assertSee('Actualités du programme');
        }
    }

    public function test_public_footer_is_shared_across_public_pages_but_not_administration(): void
    {
        foreach ([
            route('home'),
            route('home.page-2'),
            route('veille'),
            route('program-news.index'),
            route('dashboard-analytics'),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('synex-public-footer', false);
        }

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.program-news.index'))
            ->assertOk()
            ->assertDontSee('synex-public-footer', false)
            ->assertSee('content-footer');
    }

    private function createNews(
        string $title,
        string $publishedAt,
        bool $isPublished = true,
        string $category = 'publication',
        string $region = 'nord'
    ): ProgramNews {
        return ProgramNews::query()->create([
            'title' => $title,
            'summary' => 'Résumé de '.$title,
            'content' => 'Article complet de '.$title,
            'image_path' => 'program-news/'.$title.'.png',
            'category' => $category,
            'region' => $region,
            'published_at' => $publishedAt,
            'is_published' => $isPublished,
        ]);
    }
}
