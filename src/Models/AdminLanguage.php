<?php

namespace Javaabu\Translatable\Models;

use Javaabu\Helpers\AdminModel\AdminModel;
use Javaabu\Helpers\AdminModel\IsAdminModel;

/**
 * The language model as a javaabu/helpers admin model, for apps that install javaabu/helpers
 *
 * Set `translatable.language_model` to this class. It's only loaded when referenced, so
 * it's safe to ship without helpers; referencing it without helpers installed fails.
 */
class AdminLanguage extends Language implements AdminModel
{
    use IsAdminModel;

    public function getMorphClass(): string
    {
        return Language::class;
    }
}
