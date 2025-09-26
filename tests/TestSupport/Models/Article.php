<?php

namespace Javaabu\Translatable\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;
use Javaabu\Translatable\Tests\TestSupport\Factories\ArticleFactory;

class Article extends Model implements JsonTranslatable
{
    use HasFactory;
    use IsJsonTranslatable;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'author_id',
        'translations',
        'lang',
    ];

    protected $casts = [
        'translations' => 'array',
    ];

    protected static function newFactory(): ArticleFactory
    {
        return new ArticleFactory();
    }

    public function getTranslatables(): array
    {
        return [
            'title',
            'body',
        ];
    }

    public function getNonTranslatablePivots(): array
    {
        return [
            'author_id',
        ];
    }
}
