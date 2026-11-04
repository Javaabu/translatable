<?php

namespace Javaabu\Translatable\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Javaabu\Translatable\Enums\Flags;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\LanguageRegistrar;

/**
 * A language translations can be written in
 *
 * Doesn't depend on javaabu/helpers. Apps that list languages through helpers' admin
 * tooling can point `translatable.language_model` at {@see AdminLanguage}.
 */
class Language extends Model
{
    use SoftDeletes;

    /**
     * Fixed, so subclasses (AdminLanguage, an app's own model) use the same table
     *
     * @var string
     */
    protected $table = 'languages';

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
            'name' => 'string',
            'code' => 'string',
            'locale' => 'string',
            'local_name' => 'string',
            'flag' => 'string',
            'is_rtl' => 'boolean',
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        $forget = fn () => app(LanguageRegistrar::class)->forgetCachedLanguages();

        static::saved($forget);
        static::deleted($forget);

        if (method_exists(static::class, 'restored')) {
            static::restored($forget);
        }
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
     * The name shown in admin links
     */
    public function getAdminLinkNameAttribute(): string
    {
        return (string) $this->name;
    }

    /**
     * Languages whose searchable attributes contain the search term
     *
     * The signature matches javaabu/helpers' AdminModel, so {@see AdminLanguage} can use its trait.
     *
     * @param  Builder<static>  $query
     */
    public function scopeSearch($query, $search): mixed
    {
        if ($search === null || $search === '') {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            foreach ($this->searchable as $attribute) {
                $query->orWhere($this->qualifyColumn($attribute), 'like', '%' . $search . '%');
            }
        });
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
