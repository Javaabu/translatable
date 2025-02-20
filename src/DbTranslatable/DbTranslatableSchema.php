<?php

namespace Javaabu\Translatable\DbTranslatable;

use Illuminate\Database\Schema\Blueprint;

class DbTranslatableSchema
{
    public static function columns(Blueprint $table): void
    {
        $table->foreignId('translatable_parent_id')->nullable();

        $table->string('lang')->index();
    }
}
