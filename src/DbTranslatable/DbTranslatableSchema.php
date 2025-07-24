<?php

namespace Javaabu\Translatable\DbTranslatable;

use Illuminate\Database\Schema\Blueprint;

class DbTranslatableSchema
{
    public static function columns(Blueprint $table): void
    {
        $table->foreignId('translatable_parent_id')->nullable();

        $table->string('lang')->index($table->getTable() . '_lang_index');
    }

    public static function revert(Blueprint $table): void
    {
        $table->dropIndex($table->getTable() . '_lang_index');

        $table->dropColumn(['translatable_parent_id', 'lang']);
    }
}
