<?php

namespace Javaabu\Translatable\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Javaabu\Helpers\AdminModel\AdminModel;
use Javaabu\Helpers\AdminModel\IsAdminModel;
use Javaabu\Translatable\Enums\Flags;
use Javaabu\Translatable\Facades\Languages;

class Language extends Model implements AdminModel
{
    use IsAdminModel;
    use SoftDeletes;

    /**
     * The attributes that would be logged
     */
    protected static array $logAttributes = ['*'];

    /**
     * Changes to these attributes only will not trigger a log
     */
    protected static array $ignoreChangedAttributes = ['created_at', 'updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'locale',
        'local_name',
        'flag',
        'is_rtl',
        'active',
    ];

    /**
     * The attributes that are searchable.
     *
     * @var array
     */
    protected $searchable = [
        'name',
        'code',
        'locale',
        'local_name',
        'flag',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name'       => 'string',
            'code'       => 'string',
            'locale'     => 'string',
            'local_name' => 'string',
            'flag'       => 'string',
            'is_rtl'     => 'boolean',
            'active'     => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'locale';
    }

    /**
     * Get the admin url attribute
     */
    public function getAdminUrlAttribute(): string
    {
        return route('admin.languages.show', $this);
    }

    /**
     * Get default translation locale
     */
    public static function getDefaultTranslationLocale(): string
    {
        return config('translatable.default_locale');
    }

    /**
     * Get default translation locale
     */
    public static function getDefaultAppLocale(): string
    {
        return config('app.fallback_locale');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function isCurrent()
    {
        return Languages::isCurrent($this->code);
    }

    public function flagUrl(): Attribute
    {
        return Attribute::get(function () {
            return Flags::getFlagUrl($this->flag);
        });
    }
}
