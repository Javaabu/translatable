<?php

return [
    // Allows for custom models
    'language_model' => \Javaabu\Translatable\Models\Language::class,

    // Standard fields to be ignored for translation on all models
    'fields_ignored_for_translation' => [
        'id',
        'lang',
        'created_at',
        'updated_at',
        'deleted_at',
    ],

    // Whether attr_dv should fall back to app locale if translations do not exist
    'lang_suffix_should_fallback' => false,

    // Default locale before translations are added
    'default_locale' => 'en',
];
