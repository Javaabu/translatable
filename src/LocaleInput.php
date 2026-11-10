<?php

namespace Javaabu\Translatable;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * One locale's input for a translatable field: what a form renders for `title` in `dv`
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class LocaleInput implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $field,
        public string $name,
        public Locale $locale,
        public bool $is_primary,
    ) {}

    /**
     * The field label followed by the locale's own name: "Title (ދިވެހި)"
     */
    public function label(?string $label = null): string
    {
        $label ??= str($this->field)->replace('_', ' ')->ucfirst()->toString();

        return __('translatable::strings.field_in_locale', [
            'label' => $label,
            'locale' => $this->locale->native_name,
        ]);
    }

    /**
     * @return array{
     *     field: string,
     *     name: string,
     *     locale: string,
     *     direction: string,
     *     thaana: bool,
     *     primary: bool,
     *     language: array<string, mixed>
     * }
     */
    public function toArray(): array
    {
        return [
            'field' => $this->field,
            'name' => $this->name,
            'locale' => $this->locale->code,
            'direction' => $this->locale->direction,
            'thaana' => $this->locale->isThaana(),
            'primary' => $this->is_primary,
            'language' => $this->locale->toArray(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
