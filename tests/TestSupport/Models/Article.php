<?php

namespace Javaabu\Translatable\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;
use Javaabu\Translatable\Tests\TestSupport\Factories\ArticleFactory;
use Javaabu\Translatable\Translatable;

class Article extends Model implements Translatable
{
    use HasFactory;
    use SoftDeletes;
    use IsJsonTranslatable;

    protected $fillable = [
        'translations',
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
            'author_id'
        ];
    }
}
