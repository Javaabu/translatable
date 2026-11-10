<?php

namespace Javaabu\Translatable\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Model;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

/**
 * A JSON translatable model that lists its fields in $translatable and has no translations cast
 */
class Page extends Model implements JsonTranslatable
{
    use IsJsonTranslatable;

    protected $fillable = [
        'title',
        'body',
        'slug',
        'lang',
    ];

    protected array $translatable = [
        'title',
        'body',
    ];
}
