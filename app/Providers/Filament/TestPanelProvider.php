<?php

namespace App\Providers\Filament;

use App\Filament\Test\Pages\StudentLogin;
use App\Filament\Test\Pages\SubjectHistory;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('test')
            ->path('ujian')

            // Login khusus siswa
            ->login(StudentLogin::class)

            // Warna panel siswa
            ->colors([
                'primary' => Color::Indigo,
            ])

            // Resource siswa
            ->discoverResources(
                in: app_path('Filament/Test/Resources'),
                for: 'App\\Filament\\Test\\Resources'
            )

            // Halaman siswa
            ->discoverPages(
                in: app_path('Filament/Test/Pages'),
                for: 'App\\Filament\\Test\\Pages'
            )

            // Halaman utama panel siswa
            ->pages([
                Dashboard::class,
                SubjectHistory::class,
            ])

            // Widget siswa
            ->discoverWidgets(
                in: app_path('Filament/Test/Widgets'),
                for: 'App\\Filament\\Test\\Widgets'
            )

            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])

            // Middleware umum
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

            // Middleware autentikasi siswa
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
