<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\dashboard\SynexDashboard;
use App\Http\Controllers\dashboard\SynexDashboardTwo;
use App\Http\Controllers\pages\Landing;
use App\Http\Controllers\pages\HomePageTwo;
use App\Http\Controllers\pages\VeillePage;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\admin\AdminUserController;
use App\Http\Controllers\admin\ReferenceController;
use App\Http\Controllers\admin\ConflictEventController;
use App\Http\Controllers\admin\CsvImportController;
use App\Http\Controllers\admin\SiteSettingsController;
use App\Http\Controllers\admin\VeilleController;
use App\Http\Controllers\admin\ProgramNewsController;
use App\Http\Controllers\admin\AdminCareController;
use App\Http\Controllers\pages\ProgramNewsPage;
use App\Http\Controllers\pages\CarePageController;
use App\Http\Controllers\pages\PreventionPageController;
use App\Http\Controllers\pages\ContactPageController;
use App\Http\Controllers\admin\PreventionController;
use App\Http\Controllers\admin\ContactSubmissionController;
use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;
use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\Boxicons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\tables\Basic as TablesBasic;

// Landing page and analytics dashboard
Route::get('/', HomePageTwo::class)->name('home');
Route::get('/accueil-original', [Landing::class, 'index'])->name('home.original');
Route::get('/home-page-2', HomePageTwo::class)->name('home.page-2');
Route::get('/veille', [VeillePage::class, 'index'])->name('veille');
Route::get('/actualites-programme', [ProgramNewsPage::class, 'index'])->name('program-news.index');
Route::get('/actualites-programme/{news}', [ProgramNewsPage::class, 'show'])->whereNumber('news')->name('program-news.show');
Route::get('/prise-en-charge', [CarePageController::class, 'index'])->name('public.care.index');
Route::get('/prise-en-charge/{slug}', [CarePageController::class, 'show'])->name('public.care.show');
Route::get('/prevention', [PreventionPageController::class, 'index'])->name('prevention');
Route::get('/contact', [ContactPageController::class, 'index'])->name('contact');
Route::post('/contact', [ContactPageController::class, 'store'])
  ->middleware('throttle:5,1')
  ->name('contact.store');
Route::get('/dashboard', [SynexDashboard::class, 'index'])->name('dashboard-analytics');
Route::get('/dashboard-2', [SynexDashboardTwo::class, 'index'])->name('dashboard-analytics-two');

Route::get('/auth/login-basic', [LoginBasic::class, 'index'])->name('auth-login-basic');
Route::post('/auth/login-basic', [LoginBasic::class, 'authenticate'])
  ->middleware('throttle:5,1')
  ->name('auth.authenticate');
Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::post('/auth/register-basic', [RegisterBasic::class, 'store'])
  ->middleware('throttle:5,1')
  ->name('auth.register');

Route::middleware('auth')->group(function () {
Route::post('/logout', [LoginBasic::class, 'logout'])->name('logout');
Route::get('/admin', function () {
  return auth()->user()->isAdmin()
    ? redirect()->route('admin.dashboard')
    : redirect()->route('admin.events.index');
})->name('admin.home');

Route::middleware('admin')->group(function () {
  Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
});

Route::get('/admin/events', [ConflictEventController::class, 'index'])->name('admin.events.index');
Route::get('/admin/events/create', [ConflictEventController::class, 'create'])->name('admin.events.create');
Route::post('/admin/events', [ConflictEventController::class, 'store'])->name('admin.events.store');
Route::get('/admin/events/{event}/edit', [ConflictEventController::class, 'edit'])->name('admin.events.edit');
Route::put('/admin/events/{event}', [ConflictEventController::class, 'update'])->name('admin.events.update');
Route::delete('/admin/events/{event}', [ConflictEventController::class, 'destroy'])->name('admin.events.destroy');
Route::get('/admin/import', [CsvImportController::class, 'index'])->name('admin.import.index');
Route::get('/admin/import/template', [CsvImportController::class, 'template'])->name('admin.import.template');
Route::post('/admin/import/preview', [CsvImportController::class, 'preview'])->middleware('throttle:10,1')->name('admin.import.preview');
Route::post('/admin/import/commit', [CsvImportController::class, 'commit'])->middleware('throttle:10,1')->name('admin.import.commit');

Route::middleware('admin')->group(function () {
  Route::get('/admin/contact', [ContactSubmissionController::class, 'index'])->name('admin.contact.index');
  Route::get('/admin/contact/{submission}', [ContactSubmissionController::class, 'show'])->name('admin.contact.show');
  Route::post('/admin/contact/{submission}/reply', [ContactSubmissionController::class, 'reply'])->name('admin.contact.reply');
  Route::delete('/admin/contact/{submission}', [ContactSubmissionController::class, 'destroy'])->name('admin.contact.destroy');
  Route::get('/admin/prise-en-charge', [AdminCareController::class, 'index'])->name('admin.care.index');
  Route::put('/admin/prise-en-charge/contenu', [AdminCareController::class, 'updatePageContent'])->name('admin.care.content');
  Route::put('/admin/prise-en-charge/image', [AdminCareController::class, 'updateCismImage'])->name('admin.care.image');
  Route::get('/admin/prise-en-charge/create', [AdminCareController::class, 'create'])->name('admin.care.create');
  Route::post('/admin/prise-en-charge', [AdminCareController::class, 'store'])->name('admin.care.store');
  Route::get('/admin/prise-en-charge/{careCenter}/edit', [AdminCareController::class, 'edit'])->name('admin.care.edit');
  Route::put('/admin/prise-en-charge/{careCenter}', [AdminCareController::class, 'update'])->name('admin.care.update');
  Route::patch('/admin/prise-en-charge/{careCenter}/publication', [AdminCareController::class, 'togglePublication'])->name('admin.care.publication');
  Route::delete('/admin/prise-en-charge/{careCenter}', [AdminCareController::class, 'destroy'])->name('admin.care.destroy');
  Route::post('/admin/prise-en-charge/services', [AdminCareController::class, 'storeService'])->name('admin.care.services.store');
  Route::put('/admin/prise-en-charge/services/{service}', [AdminCareController::class, 'updateService'])->name('admin.care.services.update');
  Route::delete('/admin/prise-en-charge/services/{service}', [AdminCareController::class, 'destroyService'])->name('admin.care.services.destroy');
  Route::get('/admin/prevention', [PreventionController::class, 'index'])->name('admin.prevention.index');
  Route::put('/admin/prevention/contenu', [PreventionController::class, 'updateContent'])->name('admin.prevention.content');
  Route::get('/admin/prevention/initiatives/create', [PreventionController::class, 'createInitiative'])->name('admin.prevention.initiatives.create');
  Route::post('/admin/prevention/initiatives', [PreventionController::class, 'storeInitiative'])->name('admin.prevention.initiatives.store');
  Route::get('/admin/prevention/initiatives/{initiative}/edit', [PreventionController::class, 'editInitiative'])->name('admin.prevention.initiatives.edit');
  Route::put('/admin/prevention/initiatives/{initiative}', [PreventionController::class, 'updateInitiative'])->name('admin.prevention.initiatives.update');
  Route::delete('/admin/prevention/initiatives/{initiative}', [PreventionController::class, 'destroyInitiative'])->name('admin.prevention.initiatives.destroy');
  Route::post('/admin/prevention/ressources', [PreventionController::class, 'storeResource'])->name('admin.prevention.resources.store');
  Route::put('/admin/prevention/ressources/{resource}', [PreventionController::class, 'updateResource'])->name('admin.prevention.resources.update');
  Route::delete('/admin/prevention/ressources/{resource}', [PreventionController::class, 'destroyResource'])->name('admin.prevention.resources.destroy');
  Route::get('/admin/actualites', [ProgramNewsController::class, 'index'])->name('admin.program-news.index');
  Route::get('/admin/actualites/create', [ProgramNewsController::class, 'create'])->name('admin.program-news.create');
  Route::post('/admin/actualites', [ProgramNewsController::class, 'store'])->name('admin.program-news.store');
  Route::get('/admin/actualites/{news}/edit', [ProgramNewsController::class, 'edit'])->name('admin.program-news.edit');
  Route::put('/admin/actualites/{news}', [ProgramNewsController::class, 'update'])->name('admin.program-news.update');
  Route::patch('/admin/actualites/{news}/publication', [ProgramNewsController::class, 'togglePublication'])->name('admin.program-news.publication');
  Route::delete('/admin/actualites/{news}', [ProgramNewsController::class, 'destroy'])->name('admin.program-news.destroy');
  Route::get('/admin/veille', [VeilleController::class, 'index'])->name('admin.veille.index');
  Route::put('/admin/veille/sections/{section}', [VeilleController::class, 'updateSection'])->name('admin.veille.sections.update');
  Route::post('/admin/veille/sections/{section}/cards', [VeilleController::class, 'storeCard'])->name('admin.veille.cards.store');
  Route::put('/admin/veille/cards/{card}', [VeilleController::class, 'updateCard'])->name('admin.veille.cards.update');
  Route::delete('/admin/veille/cards/{card}', [VeilleController::class, 'destroyCard'])->name('admin.veille.cards.destroy');
  Route::get('/admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
  Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
  Route::get('/admin/settings/site', [SiteSettingsController::class, 'edit'])->name('admin.settings.site.edit');
  Route::put('/admin/settings/site', [SiteSettingsController::class, 'update'])->name('admin.settings.site.update');
  Route::post('/admin/settings/site/footer-links', [SiteSettingsController::class, 'storeFooterLink'])->name('admin.settings.site.footer-links.store');
  Route::put('/admin/settings/site/footer-links/{footerLink}', [SiteSettingsController::class, 'updateFooterLink'])->name('admin.settings.site.footer-links.update');
  Route::delete('/admin/settings/site/footer-links/{footerLink}', [SiteSettingsController::class, 'destroyFooterLink'])->name('admin.settings.site.footer-links.destroy');
  Route::patch('/admin/settings/site/footer-pages/{footerLink}/visibility', [SiteSettingsController::class, 'updateFooterPageVisibility'])->name('admin.settings.site.footer-pages.visibility');
  Route::get('/admin/references/{catalog}', [ReferenceController::class, 'index'])->name('admin.references.index');
  Route::post('/admin/references/{catalog}', [ReferenceController::class, 'store'])->name('admin.references.store');
  Route::put('/admin/references/{catalog}/{item}', [ReferenceController::class, 'update'])->name('admin.references.update');
  Route::delete('/admin/references/{catalog}/{item}', [ReferenceController::class, 'destroy'])->name('admin.references.destroy');
  Route::patch('/admin/references/{catalog}/{item}/status', [ReferenceController::class, 'toggleStatus'])->name('admin.references.status');
  Route::patch('/admin/events/{event}/review', [ConflictEventController::class, 'review'])->name('admin.events.review');
});

Route::get('/dashboard/base', [Analytics::class, 'index'])->name('dashboard-analytics-base');

// layout
Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

// pages
Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
Route::put('/pages/account-settings-account', [AccountSettingsAccount::class, 'update'])->name('pages-account-settings-account.update');
Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');

// cards
Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');

// User Interface
Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-tooltips-popovers');
Route::get('/ui/typography', [Typography::class, 'index'])->name('ui-typography');

// extended ui
Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

// icons
Route::get('/icons/boxicons', [Boxicons::class, 'index'])->name('icons-boxicons');

// form elements
Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

// form layouts
Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

// tables
Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');
});