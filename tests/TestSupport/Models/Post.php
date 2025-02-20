<?php

namespace Javaabu\Translatable\Tests\TestSupport\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Javaabu\Translatable\DbTranslatable\IsDbTranslatable;
use Javaabu\Translatable\Tests\TestSupport\Factories\PostFactory;
use Javaabu\Translatable\Translatable;

class Post extends Model implements Translatable
{
    use HasFactory;
    use SoftDeletes;
    use IsDbTranslatable;

    protected static function newFactory(): PostFactory
    {
        return new PostFactory();
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function getTranslatables(): array
    {
        return [
            'title',
            'body'
        ];
    }

    public function getNonTranslatablePivots(): array
    {
        return [
            'author_id'
        ];
    }
}
