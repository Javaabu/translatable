<?php

namespace Javaabu\Translatable\Views\Components;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Translatable\Translatable;

/**
 * The "adding a translation" flow for DB translatable models: an alert naming the original,
 * and hidden `lang` and `lang_parent` inputs for the create form
 *
 * <code><x-translatable::translation-notice :parent="$lang_parent" /></code>
 */
class TranslationNotice extends Component
{
    protected string $view = 'translation-notice';

    public string $locale;

    public function __construct(
        public ?Model $parent = null,
        ?string $locale = null,
        public string $titleAttribute = '',
        string $framework = '',
    ) {
        parent::__construct($framework);

        $this->locale = $locale ?? Translatable::currentLocale();
    }

    public function shouldRender(): bool
    {
        return $this->parent !== null;
    }

    public function message(): string
    {
        return __('translatable::strings.adding_translation', [
            'locale' => Translatable::locale($this->locale)?->native_name ?? $this->locale,
            'title' => $this->parentTitle(),
        ]);
    }

    protected function parentTitle(): string
    {
        $attribute = $this->titleAttribute ?: collect(['title', 'name'])->first(fn ($key) => filled($this->parent->getAttribute($key)));

        if (! $attribute) {
            return (string) $this->parent->getKey();
        }

        return (string) $this->parent->translate($attribute, $this->parent->primaryLocale());
    }
}
