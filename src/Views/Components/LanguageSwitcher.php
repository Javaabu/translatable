<?php

namespace Javaabu\Translatable\Views\Components;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Translatable\Locale;
use Javaabu\Translatable\Translatable;
use Throwable;

/**
 * Links to the current page (or a model's page) in each active language
 *
 * <code><x-translatable::language-switcher /></code>
 * <code><x-translatable::language-switcher :model="$post" action="show" /></code>
 */
class LanguageSwitcher extends Component
{
    protected string $view = 'language-switcher';

    public function __construct(
        public ?Model $model = null,
        public string $action = 'show',
        string $framework = '',
    ) {
        parent::__construct($framework);
    }

    /**
     * @return array<int, array{title: string, url: string, active: bool}>
     */
    public function items(): array
    {
        $current = Translatable::currentLocale();

        return Translatable::locales()
            ->map(fn (Locale $locale) => [
                'title' => $locale->native_name,
                'url' => $this->url($locale),
                'active' => $locale->code === $current,
            ])
            ->all();
    }

    protected function url(Locale $locale): string
    {
        if ($this->model && method_exists($this->model, 'url')) {
            try {
                return $this->model->url($this->action, $locale->code);
            } catch (Throwable) {
                // fall back to the current page
            }
        }

        return Translatable::switchUrl($locale->code);
    }
}
