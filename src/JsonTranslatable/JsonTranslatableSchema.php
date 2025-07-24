<?php

namespace Javaabu\Translatable\JsonTranslatable;

use Illuminate\Database\Schema\Blueprint;

class JsonTranslatableSchema
{
    /**
     * Adds the columns needed for email verification
     */
    public static function columns(Blueprint $table): void
    {
        //        if (app()->runningUnitTests()) {
        //            $table->text('translations')->nullable();
        //        } else {
        $table->json('translations')->nullable();
        //        }

        $table->string('lang')->index($table->getTable() . '_lang_index');
    }

    public static function revert(Blueprint $table): void
    {
        $table->dropIndex($table->getTable() . '_lang_index');
        $table->dropColumn(['translations', 'lang']);
    }
}
