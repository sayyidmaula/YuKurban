<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('YuKurban Admin')
            ->login()
            ->colors([
                'primary' => [
                    50 => '#f1f9f5',
                    100 => '#ddf0e6',
                    200 => '#bee2d0',
                    300 => '#92ceb1',
                    400 => '#5fb48c',
                    500 => '#1e8a5e',
                    600 => '#14704d',
                    700 => '#0f5a3f',
                    800 => '#0d4833',
                    900 => '#0b3b2c',
                    950 => '#052119',
                ],
                'warning' => [
                    50 => '#fbf8eb',
                    100 => '#f7efd0',
                    200 => '#efdfa2',
                    300 => '#e3cb76',
                    400 => '#d5b34a',
                    500 => '#c9a227',
                    600 => '#b68a1b',
                    700 => '#926818',
                    800 => '#79531b',
                    900 => '#67441c',
                    950 => '#3c240c',
                ],
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
