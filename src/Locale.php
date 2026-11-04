<?php

namespace Javaabu\Translatable;

use Illuminate\Contracts\Support\Arrayable;
use Javaabu\Translatable\Models\Language;
use JsonSerializable;

/**
 * An allowed translation locale, as other packages and the views see it
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class Locale implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $code,
        public string $locale,
        public string $name,
        public string $native_name,
        public string $direction,
        public bool $is_default,
        public ?string $flag_url = null,
    ) {}

    public static function fromLanguage(Language $language): self
    {
        return new self(
            code: $language->code,
            locale: $language->locale ?: $language->code,
            name: $language->name,
            native_name: $language->local_name ?: $language->name,
            direction: $language->is_rtl ? 'rtl' : 'ltr',
            is_default: $language->code === Translatable::defaultLocale(),
            flag_url: $language->flag ? $language->flag_url : null,
        );
    }

    public function isRtl(): bool
    {
        return $this->direction === 'rtl';
    }

    /**
     * Whether free-text inputs in this locale should use the Thaana keyboard
     */
    public function isThaana(): bool
    {
        return in_array($this->code, config('translatable.thaana_locales', ['dv']), true);
    }

    /**
     * @return array{
     *     code: string,
     *     locale: string,
     *     name: string,
     *     native_name: string,
     *     direction: string,
     *     rtl: bool,
     *     thaana: bool,
     *     default: bool,
     *     flag_url: ?string
     * }
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'locale' => $this->locale,
            'name' => $this->name,
            'native_name' => $this->native_name,
            'direction' => $this->direction,
            'rtl' => $this->isRtl(),
            'thaana' => $this->isThaana(),
            'default' => $this->is_default,
            'flag_url' => $this->flag_url,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
