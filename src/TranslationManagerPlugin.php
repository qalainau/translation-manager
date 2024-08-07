<?php

namespace Kenepa\TranslationManager;

use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\View\View;
use Kenepa\TranslationManager\Http\Middleware\SetLanguage;
use Kenepa\TranslationManager\Pages\QuickTranslate;
use Kenepa\TranslationManager\Resources\LanguageLineResource;

class TranslationManagerPlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'translation-manager';
    }

    public function register(Panel $panel): void
    {
        //ray($panel->getId());

        $panel
            ->resources([
                LanguageLineResource::class,
            ])
            ->pages([
                QuickTranslate::class,
            ]);

        if (config('translation-manager.language_switcher')) {


            if (!in_array(
                $panel->getId(),
                config('translation-manager.dont_register_language_switcher_on_panel_ids')
            )) {
                $panel->renderHook(
                    config('translation-manager.language_switcher_render_hook'),
                    fn (): View => $this->getLanguageSwitcherView()
                );

                $panel->authMiddleware([
                    SetLanguage::class,
                ]);
            }


        }

    }

    public function boot(Panel $panel): void
    {
        // ray(Filament::getCurrentPanel()->getId());

    }

    /**
     * Returns a View object that renders the language switcher component.
     *
     * @return \Illuminate\Contracts\View\View The View object that renders the language switcher component.
     */
    private function getLanguageSwitcherView(): View
    {
        $locales = config('translation-manager.available_locales');
        $currentLocale = app()->getLocale();
        $currentLanguage = collect($locales)->firstWhere('code', $currentLocale);
        $otherLanguages = $locales;
        $showFlags = config('translation-manager.show_flags');

        return view('translation-manager::language-switcher', compact(
            'otherLanguages',
            'currentLanguage',
            'showFlags',
        ));
    }
}
