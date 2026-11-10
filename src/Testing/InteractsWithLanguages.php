<?php

namespace Javaabu\Translatable\Testing;

use Illuminate\Support\Facades\Schema;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\LanguageRegistrar;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Support\LanguagePresets;

/**
 * For tests in apps and in packages that support translatable optionally
 *
 * <code>$this->setUpLanguages(['en', 'dv']);</code> creates the languages table when it's
 * missing and adds the languages, so models can be translated straight away.
 */
trait InteractsWithLanguages
{
    /**
     * @param  array<int, string>  $codes
     */
    protected function setUpLanguages(array $codes = ['en', 'dv'], ?string $current = null): void
    {
        $this->createLanguagesTable();

        /** @var class-string<Language> $language_class */
        $language_class = config('translatable.language_model', Language::class);

        foreach ($codes as $code) {
            $language_class::query()->firstOrCreate(['code' => $code], LanguagePresets::get($code));
        }

        app(LanguageRegistrar::class)->forgetCachedLanguages();

        Languages::setCurrentLocale($current ?? $codes[0] ?? config('translatable.default_locale'));
    }

    protected function createLanguagesTable(): void
    {
        if (Schema::hasTable('languages')) {
            return;
        }

        $migration = require __DIR__ . '/../../database/migrations/create_languages_table.php';

        $migration->up();
    }

    /**
     * Run the callback with translatable switched off, as if the package weren't installed
     */
    protected function withoutTranslatable(callable $callback): mixed
    {
        $enabled = config('translatable.enabled');

        config(['translatable.enabled' => false]);

        try {
            return $callback();
        } finally {
            config(['translatable.enabled' => $enabled]);
        }
    }
}
