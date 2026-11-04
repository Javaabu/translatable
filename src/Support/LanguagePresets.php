<?php

namespace Javaabu\Translatable\Support;

/**
 * Ready-made rows for the languages table, used by `translatable:add-languages` and the testing helpers
 */
class LanguagePresets
{
    /**
     * @var array<string, array{name: string, local_name: string, locale: string, flag: string, is_rtl: bool}>
     */
    protected static array $presets = [
        'en' => ['name' => 'English', 'local_name' => 'English', 'locale' => 'en', 'flag' => 'gb', 'is_rtl' => false],
        'dv' => ['name' => 'Dhivehi', 'local_name' => 'ދިވެހި', 'locale' => 'dv', 'flag' => 'mv', 'is_rtl' => true],
        'ar' => ['name' => 'Arabic', 'local_name' => 'العربية', 'locale' => 'ar', 'flag' => 'sa', 'is_rtl' => true],
        'zh' => ['name' => 'Chinese', 'local_name' => '中文', 'locale' => 'zh', 'flag' => 'cn', 'is_rtl' => false],
        'ja' => ['name' => 'Japanese', 'local_name' => '日本語', 'locale' => 'ja', 'flag' => 'jp', 'is_rtl' => false],
        'hi' => ['name' => 'Hindi', 'local_name' => 'हिन्दी', 'locale' => 'hi', 'flag' => 'in', 'is_rtl' => false],
        'ru' => ['name' => 'Russian', 'local_name' => 'Русский', 'locale' => 'ru', 'flag' => 'ru', 'is_rtl' => false],
        'de' => ['name' => 'German', 'local_name' => 'Deutsch', 'locale' => 'de', 'flag' => 'de', 'is_rtl' => false],
        'fr' => ['name' => 'French', 'local_name' => 'Français', 'locale' => 'fr', 'flag' => 'fr', 'is_rtl' => false],
        'it' => ['name' => 'Italian', 'local_name' => 'Italiano', 'locale' => 'it', 'flag' => 'it', 'is_rtl' => false],
        'es' => ['name' => 'Spanish', 'local_name' => 'Español', 'locale' => 'es', 'flag' => 'es', 'is_rtl' => false],
    ];

    public static function has(string $code): bool
    {
        return array_key_exists($code, static::$presets);
    }

    /**
     * The attributes for a languages row: a preset, or a minimal row for an unknown code
     *
     * @return array{code: string, name: string, local_name: string, locale: string, flag: string, is_rtl: bool, active: bool}
     */
    public static function get(string $code): array
    {
        $preset = static::$presets[$code] ?? [
            'name' => mb_strtoupper($code),
            'local_name' => mb_strtoupper($code),
            'locale' => $code,
            'flag' => $code,
            'is_rtl' => false,
        ];

        return ['code' => $code, ...$preset, 'active' => true];
    }

    /**
     * @return array<int, string>
     */
    public static function codes(): array
    {
        return array_keys(static::$presets);
    }
}
