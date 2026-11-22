<?php

namespace App\Providers;

use App\Models\CareCenter;
use App\Models\ConflictEvent;
use App\Models\ContactSubmission;
use App\Models\PreventionInitiative;
use App\Models\PreventionResource;
use App\Models\ProgramNews;
use App\Models\SiteSetting;
use App\Models\SiteFooterLink;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            $request = request();
            $settings = $request->attributes->get('synex_site_settings');

            if ($settings === null) {
                $settings = SiteSetting::query()->find(1)
                    ?? new SiteSetting(['name' => config('variables.templateName')]);
                $request->attributes->set('synex_site_settings', $settings);
            }

            $view->with('siteSettings', $settings);
        });

        View::composer('layouts.sections.navbar.navbar-partial', function ($view) {
            if (!request()->user()?->isAdmin()) {
                return;
            }

            $unreadContactCount = ContactSubmission::query()->whereNull('read_at')->count();
            $pendingEventCount = ConflictEvent::query()->where('status', 'pending')->count();
            $unpublishedNewsCount = ProgramNews::query()->where('is_published', false)->count();
            $unpublishedInitiativeCount = PreventionInitiative::query()->where('is_published', false)->count();
            $unpublishedCareCenterCount = CareCenter::query()->where('is_published', false)->count();
            $unpublishedResourceCount = PreventionResource::query()->where('is_published', false)->count();

            $notifications = ContactSubmission::query()
                ->whereNull('read_at')
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'subject', 'created_at'])
                ->map(fn (ContactSubmission $submission) => [
                    'title' => 'Nouveau message de '.$submission->name,
                    'detail' => $submission->subject,
                    'date' => $submission->created_at,
                    'url' => route('admin.contact.show', $submission),
                    'icon' => 'bx-envelope',
                    'kind' => 'contact',
                ])
                ->concat(
                    ConflictEvent::query()
                        ->where('status', 'pending')
                        ->latest()
                        ->limit(5)
                        ->get(['id', 'event_type', 'province', 'created_at'])
                        ->map(fn (ConflictEvent $event) => [
                            'title' => 'Observation à valider',
                            'detail' => collect([$event->event_type, $event->province])->filter()->implode(' · ')
                                ?: 'Contribution en attente',
                            'date' => $event->created_at,
                            'url' => route('admin.events.edit', $event),
                            'icon' => 'bx-calendar-check',
                            'kind' => 'review',
                        ])
                )
                ->concat(
                    ProgramNews::query()
                        ->where('is_published', false)
                        ->latest()
                        ->limit(4)
                        ->get(['id', 'title', 'created_at'])
                        ->map(fn (ProgramNews $news) => [
                            'title' => 'Actualité non publiée',
                            'detail' => $news->title,
                            'date' => $news->created_at,
                            'url' => route('admin.program-news.edit', $news),
                            'icon' => 'bx-news',
                            'kind' => 'draft',
                        ])
                )
                ->concat(
                    PreventionInitiative::query()
                        ->where('is_published', false)
                        ->latest()
                        ->limit(4)
                        ->get(['id', 'title', 'created_at'])
                        ->map(fn (PreventionInitiative $initiative) => [
                            'title' => 'Initiative non publiée',
                            'detail' => $initiative->title,
                            'date' => $initiative->created_at,
                            'url' => route('admin.prevention.initiatives.edit', $initiative),
                            'icon' => 'bx-shield-quarter',
                            'kind' => 'draft',
                        ])
                )
                ->concat(
                    CareCenter::query()
                        ->where('is_published', false)
                        ->latest()
                        ->limit(4)
                        ->get(['id', 'slug', 'name', 'created_at'])
                        ->map(fn (CareCenter $center) => [
                            'title' => 'Centre non publié',
                            'detail' => $center->name,
                            'date' => $center->created_at,
                            'url' => route('admin.care.edit', $center),
                            'icon' => 'bx-first-aid',
                            'kind' => 'draft',
                        ])
                )
                ->concat(
                    PreventionResource::query()
                        ->where('is_published', false)
                        ->latest()
                        ->limit(4)
                        ->get(['id', 'title', 'created_at'])
                        ->map(fn (PreventionResource $resource) => [
                            'title' => 'Ressource non publiée',
                            'detail' => $resource->title,
                            'date' => $resource->created_at,
                            'url' => route('admin.prevention.index').'#prevention-resources',
                            'icon' => 'bx-file',
                            'kind' => 'draft',
                        ])
                )
                ->sortByDesc(fn (array $notification) => $notification['date']?->getTimestamp() ?? 0)
                ->take(8)
                ->values();

            $view->with([
                'adminNotificationCount' => $unreadContactCount
                    + $pendingEventCount
                    + $unpublishedNewsCount
                    + $unpublishedInitiativeCount
                    + $unpublishedCareCenterCount
                    + $unpublishedResourceCount,
                'adminNotifications' => $notifications,
                'unreadContactCount' => $unreadContactCount,
                'pendingEventCount' => $pendingEventCount,
            ]);
        });

        View::composer('content.pages._public-footer', function ($view) {
            $view->with([
                'footerUsefulLinks' => SiteFooterLink::query()
                    ->where('type', 'useful')
                    ->where('is_visible', true)
                    ->orderBy('sort_order')
                    ->get(),
                'footerLegalLinks' => SiteFooterLink::query()
                    ->where('type', 'legal')
                    ->where('is_visible', true)
                    ->orderBy('sort_order')
                    ->get(),
                'footerSocialLinks' => SiteFooterLink::query()
                    ->where('type', 'social')
                    ->where('is_visible', true)
                    ->orderBy('sort_order')
                    ->get(),
            ]);
        });
    }
}