<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Some config option
    |--------------------------------------------------------------------------
    |
    | Give a description of what each config option is like this
    |
    */

    'fields_ignored_for_translation' => [
        'id',
        'lang',
        'created_at',
        'updated_at',
        'deleted_at',
    ],

    'allowed_translation_locales' => [
        'en' => 'English',
        'dv' => 'Dhivehi',
        'jp' => 'Japanese',
    ],

    'lang_suffix_should_fallback' => false,

    'default_locale' => 'en',
];
