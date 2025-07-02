<?php

return [
    // Allows for custom models
    'language_model'                 => \Javaabu\Translatable\Models\Language::class,

    // Standard fields to be ignored for translation on all models
    'fields_ignored_for_translation' => [
        'id',
        'lang',
        'created_at',
        'updated_at',
        'deleted_at',
    ],

    // Whether attr_dv should fall back to app locale if translations do not exist
    'lang_suffix_should_fallback'    => false,

    // Default locale before translations are added
    'default_locale'                 => 'en',

    // Cache configuration for the Language Registrar
    'cache'                          => [
        'expiration_time' => \DateInterval::createFromDateString('24 hours'),
        'key'             => 'translation.languages.cache',
        'driver'          => 'default',
    ],

    'styles' => [
        'table-cell-wrapper' => 'd-flex justify-content-center align-items-center',

        'icons' => [
            'add'  => 'fas fa-plus',
            'edit' => 'fas fa-edit',
        ]
    ]
];
