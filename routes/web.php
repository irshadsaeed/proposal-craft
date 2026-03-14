<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\NewsletterController;
use App\Http\Controllers\Frontend\PublicProposalController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\PricingController;

use App\Http\Controllers\ClientBackend\AuthController;
use App\Http\Controllers\ClientBackend\DashboardController;
use App\Http\Controllers\ClientBackend\ProposalController;
use App\Http\Controllers\ClientBackend\SettingsController;
use App\Http\Controllers\ClientBackend\BillingController;
use App\Http\Controllers\ClientBackend\TemplatesController;


use App\Http\Controllers\AdminBackend\AdminAuthController;
use App\Http\Controllers\AdminBackend\ClientUsersController;
use App\Http\Controllers\AdminBackend\AdminDashboardController;
use App\Http\Controllers\AdminBackend\AdminUsersController;
use App\Http\Controllers\AdminBackend\AdminPlansController;
use App\Http\Controllers\AdminBackend\AdminRevenueController;
use App\Http\Controllers\AdminBackend\AdminSettingsController;

use App\Http\Controllers\AdminBackend\AdminBlogController;
use App\Http\Controllers\AdminBackend\AdminContactsController;

// ── Home ──────────────────────────────────────────────────────
Route::get('/', [FrontendController::class, 'index'])->name('home');

// ── Static Pages ──────────────────────────────────────────────
Route::get('/about',     [FrontendController::class, 'about'])->name('about');
Route::get('/changelog', [FrontendController::class, 'changelog'])->name('changelog');
Route::get('/press',     [FrontendController::class, 'press'])->name('press');
Route::get('/security',  [FrontendController::class, 'security'])->name('security');
Route::get('/demo',      fn() => redirect()->route('signup'))->name('demo');
Route::get('/compare', [FrontendController::class, 'comparison'])->name('comparison');
Route::get('/customers', [FrontendController::class, 'socialProof'])->name('social-proof');

// ── Plans ─────────────────────────────────────────────────────
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing');

// ── Legal ─────────────────────────────────────────────────────
Route::get('/privacy', [FrontendController::class, 'privacy'])->name('privacy');
Route::get('/terms',   [FrontendController::class, 'terms'])->name('terms');
Route::get('/cookies', [FrontendController::class, 'cookies'])->name('cookies');
Route::get('/security', [FrontendController::class, 'security'])->name('security');

// ── Blog ──────────────────────────────────────────────────────
Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/',            [BlogController::class, 'index'])->name('index');
    Route::get('/search',      [BlogController::class, 'search'])->name('search');
    Route::get('/suggestions', [BlogController::class, 'suggestions'])->name('suggestions');
    Route::get('/{slug}',      [BlogController::class, 'show'])->name('show');
});

// ── Templates & Integrations ──────────────────────────────────
Route::get('/templates',           [FrontendController::class, 'templatesIndex'])->name('templates.index');
Route::get('/templates/{slug}',    [FrontendController::class, 'templatesShow'])->name('templates.show');
Route::get('/integrations',        [FrontendController::class, 'integrationsIndex'])->name('integrations.index');
Route::get('/integrations/{slug}', [FrontendController::class, 'integrationsShow'])->name('integrations.show');

// ── Contact & Newsletter ──────────────────────────────────────
Route::post('/contact',              [ContactController::class, 'submitContact'])->name('contact.submit');
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

// ── Public Proposal (no auth) ─────────────────────────────────
Route::prefix('p/{token}')->name('proposals.')->group(function () {
    Route::get('/',         [PublicProposalController::class, 'show'])->name('show');
    Route::get('/accepted', [PublicProposalController::class, 'accepted'])->name('accepted');
    Route::post('/track',   [PublicProposalController::class, 'track'])->name('track');
    Route::post('/accept',  [PublicProposalController::class, 'accept'])->name('accept');
    Route::post('/decline', [PublicProposalController::class, 'decline'])->name('decline');
    Route::post('/comment', [PublicProposalController::class, 'comment'])->name('comment');
    Route::get('/pdf',      [PublicProposalController::class, 'pdf'])->name('pdf');
});

// ── Auth (guests only) ────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/client/login',            [AuthController::class, 'showLogin'])->name('login');
    Route::post('/client/login',           [AuthController::class, 'login'])->name('login.submit');
    Route::get('/client/signup',           [AuthController::class, 'showRegister'])->name('signup');
    Route::post('/client/signup',          [AuthController::class, 'register'])->name('signup.submit');
    Route::get('/client/forgot-password',  [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/client/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
});

Route::get('/reset-password/{token}',  [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password',         [AuthController::class, 'resetPassword'])->name('password.update');

// ── Google OAuth ──────────────────────────────────────────────
Route::get('/auth/google',          [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);

// ── Logout ────────────────────────────────────────────────────
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── Backend (auth required) ───────────────────────────────────
Route::middleware('auth')->prefix('dashboard')->group(function () {

    Route::get('/',         [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tracking', [DashboardController::class, 'tracking'])->name('tracking');
    Route::get('/tracking/export', [DashboardController::class, 'exportTracking'])->name('tracking.export');

    // Proposals
    Route::get('/proposals',                     [ProposalController::class, 'index'])->name('proposals');
    Route::get('/new-proposal',                  [ProposalController::class, 'newProposal'])->name('new-proposal');
    Route::get('/proposals/preview',             [ProposalController::class, 'proposalPreview'])->name('proposals.preview');
    Route::post('/proposals/send',               [ProposalController::class, 'send'])->name('proposals.send');
    Route::get('/proposals/{proposal}/edit',     [ProposalController::class, 'edit'])->name('proposals.edit');
    Route::delete('/proposals/{proposal}',       [ProposalController::class, 'destroy'])->name('proposals.destroy');

    // Settings
    Route::get('/settings',                      [SettingsController::class, 'index'])->name('settings');
    Route::put('/settings/profile',              [SettingsController::class, 'updateProfile'])->name('settings.profile');
    Route::put('/settings/password',             [SettingsController::class, 'updatePassword'])->name('settings.password');
    Route::put('/settings/branding',             [SettingsController::class, 'updateBranding'])->name('settings.branding');
    Route::put('/settings/notifications',        [SettingsController::class, 'updateNotifications'])->name('settings.notifications');
    Route::put('/settings/preferences',          [SettingsController::class, 'updatePreferences'])->name('settings.preferences');
    Route::get('/settings/export',               [SettingsController::class, 'exportData'])->name('settings.export');
    Route::delete('/settings/proposals',         [SettingsController::class, 'deleteProposals'])->name('settings.delete-proposals');
    Route::delete('/settings/account',           [SettingsController::class, 'deleteAccount'])->name('settings.delete-account');
    Route::post('/settings/sessions/revoke',     [SettingsController::class, 'revokeOtherSessions'])->name('settings.sessions.revoke');

    // Billing
    Route::get('/billing',                       [BillingController::class, 'index'])->name('billing');
    Route::get('/billing/invoice/{id}',          [BillingController::class, 'invoice'])->name('billing.invoice');
    Route::post('/billing/add-card',             [BillingController::class, 'addCard'])->name('billing.add-card');
    Route::delete('/billing/remove-card',        [BillingController::class, 'removeCard'])->name('billing.remove-card');
    Route::post('/billing/change-plan',          [BillingController::class, 'changePlan'])->name('billing.change-plan');
    Route::delete('/billing/cancel',             [BillingController::class, 'cancel'])->name('billing.cancel');

    // Templates
    Route::get('/templates',                              [TemplatesController::class, 'index'])->name('templates');
    Route::post('/templates',                             [TemplatesController::class, 'store'])->name('templates.store');
    Route::post('/templates/duplicate-library',           [TemplatesController::class, 'duplicateLibrary'])->name('templates.duplicate-library');
    Route::post('/templates/{template}/duplicate',        [TemplatesController::class, 'duplicate'])->name('templates.duplicate');
    Route::delete('/templates/{template}',                [TemplatesController::class, 'destroy'])->name('templates.delete');
});













// =============================================================

// ── Admin Auth (guests only) ─────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest:admin')->group(function () {
        Route::get('/login',  [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // ── Admin Protected ───────────────────────────────────────
    Route::middleware('auth:admin')->group(function () {

        // Dashboard
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // client Users
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/',                    [ClientUsersController::class, 'index'])->name('index');
            Route::get('/{user}',              [ClientUsersController::class, 'show'])->name('show');
            Route::patch('/{user}',            [ClientUsersController::class, 'update'])->name('update'); // ← ADD THIS
            Route::patch('/{user}/suspend',    [ClientUsersController::class, 'suspend'])->name('suspend');
            Route::patch('/{user}/unsuspend',  [ClientUsersController::class, 'unsuspend'])->name('unsuspend');
            Route::delete('/{user}',           [ClientUsersController::class, 'destroy'])->name('destroy');
        });

        // admin Users
        Route::prefix('admins')->name('admins.')->group(function () {
            Route::get('/',                   [AdminUsersController::class, 'index'])->name('index');
            Route::get('/{user}',             [AdminUsersController::class, 'show'])->name('show');
            Route::patch('/{user}',           [AdminUsersController::class, 'update'])->name('update');
            Route::patch('/{user}/suspend',   [AdminUsersController::class, 'suspend'])->name('suspend');
            Route::patch('/{user}/unsuspend', [AdminUsersController::class, 'unsuspend'])->name('unsuspend');
            Route::delete('/{user}',          [AdminUsersController::class, 'destroy'])->name('destroy');
        });

        // Plans
        Route::prefix('plans')->name('plans.')->group(function () {
            Route::get('/',                     [AdminPlansController::class, 'index'])->name('index');
            Route::get('/{plan}',               [AdminPlansController::class, 'show'])->name('show');
            Route::post('/',                    [AdminPlansController::class, 'store'])->name('store');
            Route::patch('/{plan}/toggle',      [AdminPlansController::class, 'toggle'])->name('toggle');
            Route::post('/{plan}/general',      [AdminPlansController::class, 'updateGeneral'])->name('general');
            Route::post('/{plan}/pricing',      [AdminPlansController::class, 'updatePricing'])->name('pricing');
            Route::post('/{plan}/features',     [AdminPlansController::class, 'updateFeatures'])->name('features');
            Route::post('/{plan}/limits',       [AdminPlansController::class, 'updateLimits'])->name('limits');
            Route::post('/{plan}/archive',      [AdminPlansController::class, 'archive'])->name('archive');
            Route::put('/{plan}',               [AdminPlansController::class, 'update'])->name('update');
            Route::delete('/{plan}',            [AdminPlansController::class, 'destroy'])->name('destroy');
        });

        // Revenue
        Route::prefix('revenue')->name('revenue.')->group(function () {
            Route::get('/',                    [AdminRevenueController::class, 'index'])->name('index');
            Route::get('/chart',               [AdminRevenueController::class, 'chartData'])->name('chart');
            Route::get('/export',              [AdminRevenueController::class, 'export'])->name('export');
        });

        // Settings
        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/',                    [AdminSettingsController::class, 'index'])->name('index');
            Route::post('/update',             [AdminSettingsController::class, 'update'])->name('update');
            Route::post('/test-mail',          [AdminSettingsController::class, 'testMail'])->name('test-mail');
        });

        // Blog
        Route::prefix('blog')->name('blog.')->group(function () {
            Route::get('/',          [AdminBlogController::class, 'index'])->name('index');
            Route::get('/create',    [AdminBlogController::class, 'create'])->name('create');
            Route::get('/{post}/show', [AdminBlogController::class, 'show'])->name('show');
            Route::post('/',         [AdminBlogController::class, 'store'])->name('store');
            Route::get('/{post}/edit', [AdminBlogController::class, 'edit'])->name('edit');
            Route::put('/{post}',    [AdminBlogController::class, 'update'])->name('update');
            Route::delete('/{post}', [AdminBlogController::class, 'destroy'])->name('destroy');
        });

        // Contacts
        Route::prefix('contacts')->name('contacts.')->group(function () {
            Route::get('/',              [AdminContactsController::class, 'index'])->name('index');
            Route::get('/{contact}',     [AdminContactsController::class, 'show'])->name('show');
            Route::patch('/{contact}',   [AdminContactsController::class, 'update'])->name('update');
            Route::delete('/{contact}',  [AdminContactsController::class, 'destroy'])->name('destroy');
        });
    });
});


// Route::get('/test/404', fn() => abort(404));
// Route::get('/test/500', fn() => abort(500));
// Route::get('/test/503', fn() => abort(503));


// to open proposal-accepted links in browser:
// http://127.0.0.1:9000/p/prop-beta-002


// to open public-proposal links in browser (without accepted status):
// http://127.0.0.1:9000/p/prop-alpha-001



// =============== admin ====================
// http://127.0.0.1:9000/admin/login

// username = admin123@gmail.com
// password = admin123


// ================ client ===================
// http://127.0.0.1:9000/client/login

// username = test@test.com
// password = password


// ========== specific migration =============

// php artisan migrate --path=database/migrations/2026_03_10_142034_create_contacts_table.php
