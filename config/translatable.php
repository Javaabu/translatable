<?php

use Javaabu\Translatable\Models\Language;

return [
    /*
     * Turn translations off without uninstalling the package. Other packages
     * check this through Translatable::enabled().
     */
    'enabled' => env('TRANSLATABLE_ENABLED', true),

    /*
     * The model for the languages table. Extend Javaabu\Translatable\Models\Language,
     * or use Javaabu\Translatable\Models\AdminLanguage if your app has javaabu/helpers
     * and wants the language to be a helpers AdminModel.
     */
    'language_model' => Language::class,

    /*
     * Columns that are never translated, on every model
     */
    'fields_ignored_for_translation' => [
        'id',
        'lang',
        'created_at',
        'updated_at',
        'deleted_at',
    ],

    /*
     * Whether suffixed attributes (title_dv) fall back to the model's own
     * value when the translation is missing
     */
    'lang_suffix_should_fallback' => false,

    /*
     * The default translation locale: the language of a model's own columns
     * when it has no `lang`, and the first language in the views
     */
    'default_locale' => 'en',

    /*
     * Locales whose free-text inputs get the Thaana keyboard in the views
     */
    'thaana_locales' => ['dv'],

    /*
     * Which views the app uses: 'blade' (javaabu/forms) or 'inertia'
     * (React with @javaabu/forms, shipped through javaabu/manifest)
     */
    'views' => env('TRANSLATABLE_VIEWS', 'blade'),

    /*
     * With 'inertia' views, the languages are shared with every page as this prop
     */
    'inertia' => [
        'share' => true,
        'prop' => 'translatable',
    ],

    /*
     * Cache for the active languages
     */
    'cache' => [
        'expiration_time' => DateInterval::createFromDateString('24 hours'),
        'key' => 'translation.languages.cache',
        'driver' => 'default',
    ],

    /*
     * Used by $model->url() when a DB translatable model has no translation in
     * the requested locale on the default portal
     */
    'default_portal' => 'web',
    'default_redirect_route' => 'home',
];
