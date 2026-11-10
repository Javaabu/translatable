<?php

namespace Javaabu\Translatable\Commands;

use Illuminate\Console\Command;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Support\LanguagePresets;

class AddLanguagesCommand extends Command
{
    protected $signature = 'translatable:add-languages
                            {codes?* : Language codes, such as en dv (defaults to the default locale and dv)}
                            {--inactive : Add the languages switched off}';

    protected $description = 'Add languages to the languages table from the built-in presets';

    public function handle(): int
    {
        $codes = $this->argument('codes') ?: array_values(array_unique([config('translatable.default_locale'), 'dv']));

        /** @var class-string<Language> $language_class */
        $language_class = config('translatable.language_model', Language::class);

        $added = 0;

        foreach ($codes as $code) {
            $this->info("Adding `{$code}`...");

            $query = method_exists($language_class, 'bootSoftDeletes')
                ? $language_class::query()->withTrashed()
                : $language_class::query();

            if ($query->where('code', $code)->exists()) {
                $this->comment("`{$code}` is already in the languages table.");

                continue;
            }

            if (! LanguagePresets::has($code)) {
                $this->warn("There's no preset for `{$code}`; check its name and direction in the languages table.");
            }

            $language_class::query()->create([
                ...LanguagePresets::get($code),
                'active' => ! $this->option('inactive'),
            ]);

            $added++;
        }

        $this->comment("Added {$added} language(s).");

        return self::SUCCESS;
    }
}
