<?php

namespace Javaabu\Translatable\Views\Components;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Translatable\Locale;
use Javaabu\Translatable\Translatable;

/**
 * A badge per language showing whether the model is translated, linking to add or edit it
 *
 * <code><x-translatable::translation-status :model="$post" /></code>
 */
class TranslationStatus extends Component
{
    protected string $view = 'translation-status';

    public function __construct(
        public ?Model $model = null,
        public bool $links = true,
        string $framework = '',
    ) {
        parent::__construct($framework);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function items(): array
    {
        return Translatable::status(
            $this->model,
            $this->links ? fn (Locale $locale, bool $translated) => Translatable::translationUrl($this->model, $locale->code, $translated) : null,
        );
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function color(array $item): string
    {
        return $item['translated'] ? 'success' : 'secondary';
    }

    /**
     * @param  array<string, mixed>  $item
     */
    public function description(array $item): string
    {
        $locale = $item['language']['native_name'];

        return match (true) {
            $item['primary'] => __('translatable::strings.original_in', ['locale' => $locale]),
            $item['translated'] => __('translatable::strings.translated_in', ['locale' => $locale]),
            default => __('translatable::strings.not_translated_in', ['locale' => $locale]),
        };
    }
}
