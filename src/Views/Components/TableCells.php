<?php

namespace Javaabu\Translatable\Views\Components;

use Illuminate\Support\Collection;
use Javaabu\Translatable\Contracts\Translatable as TranslatableContract;
use Javaabu\Translatable\Locale;
use Javaabu\Translatable\Translatable;

/**
 * One table cell per language (except the current one) with a link to add or edit the translation
 *
 * Pair with <code><x-translatable::titles /></code> in the table head.
 */
class TableCells extends Component
{
    protected string $view = 'table-cells';

    /** @var Collection<int, Locale> */
    public Collection $languages;

    public function __construct(
        public TranslatableContract $model,
        string $framework = '',
    ) {
        parent::__construct($framework);

        $current = Translatable::currentLocale();

        $this->languages = Translatable::locales()->reject(fn (Locale $locale) => $locale->code === $current)->values();
    }

    public function isTranslated(Locale $locale): bool
    {
        return $this->model->hasTranslation($locale->code);
    }

    /**
     * The link to add or edit the translation in the locale
     */
    public function translationUrl(Locale $locale): ?string
    {
        return Translatable::translationUrl($this->model, $locale->code, $this->isTranslated($locale));
    }

    /**
     * The model's URL for an action in a locale (v2 API)
     */
    public function getUrl(string $action, Locale|string $locale): string
    {
        $locale = $locale instanceof Locale ? $locale->code : $locale;

        $url = $this->model->url($action, $locale);

        if ($this->isModelDbTranslatable() && $action === 'create' && ! str_contains($url, 'lang_parent=')) {
            $url = add_query_arg('lang_parent', $this->model->getKey(), $url);
        }

        return $url;
    }

    public function linkTitle(Locale $locale): string
    {
        return $this->isTranslated($locale)
            ? __('translatable::strings.edit_translation_in', ['locale' => $locale->native_name])
            : __('translatable::strings.add_translation_in', ['locale' => $locale->native_name]);
    }

    public function isModelDbTranslatable(): bool
    {
        return Translatable::isDbTranslatable($this->model);
    }
}
