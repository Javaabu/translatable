<?php

namespace Javaabu\Translatable\JsonTranslatable;

use Illuminate\Database\Schema\Blueprint;

class JsonTranslatableSchema
{

    /**
     * Adds the columns needed for email verification
     *
     * @param  Blueprint  $table
     */
    public static function columns(Blueprint $table): void
    {
//        if (app()->runningUnitTests()) {
//            $table->text('translations')->nullable();
//        } else {
            $table->json('translations')->nullable();
//        }

        $table->string('lang')->index();
    }

    public static function revert(Blueprint $table): void
    {
        $table->dropIndex('lang');
        $table->dropColumn(['translations', 'lang']);
    }
}
