<?php

namespace Javaabu\Translatable;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use function array_key_exists;

use DateInterval;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Collection;
use Javaabu\Translatable\Models\Language;

class LanguageRegistrar
{
    protected Repository $cache;

    protected CacheManager $cache_manager;

    protected string $language_class;

    protected ?Collection $languages = null;

    public static int|DateInterval $cache_expiration_time;

    public static string $cache_key;

    public static string $cache_driver;

    /**
     * PermissionRegistrar constructor.
     */
    public function __construct(
        string $language_class,
        CacheManager $cache_manager,
        DateInterval|int|null $cache_expiration_time = null,
        ?string $cache_key = null,
        ?string $cache_driver = null,
    ) {
        $this->language_class = $language_class;
        $this->cache_manager = $cache_manager;
        static::$cache_expiration_time = $cache_expiration_time ?? DateInterval::createFromDateString('24 hours');
        static::$cache_key = $cache_key ?? 'languages_cache';

        static::$cache_driver = $cache_driver ?? 'default';

        $this->cache = $this->getCacheStoreFromConfig();
    }

    /**
     * Get the cache store driver
     */
    protected function getCacheStoreFromConfig(): Repository
    {
        // the 'default' fallback here is from the translation.php config file, where 'default' means to use config(cache.default)
        $cache_driver = static::$cache_driver;

        // when 'default' is specified, no action is required since we already have the default instance
        if ($cache_driver === 'default') {
            return $this->cache_manager->store();
        }

        // if an undefined cache store is specified, fallback to 'array' which is Laravel's closest equiv to 'none'
        if ( ! array_key_exists($cache_driver, config('cache.stores'))) {
            $cache_driver = 'array';
        }

        return $this->cache_manager->store($cache_driver);
    }

    /**
     * Flush the cache.
     */
    public function forgetCachedLanguages(): void
    {
        $this->languages = null;

        $this->cache->forget(self::$cache_key);
    }

    /**
     * Get the languages based on the passed params.
     */
    public function getLanguages(array $params = []): Collection
    {
        // If the languages are empty, forget the cache
        if ($this->languages?->isEmpty()) {
            $this->forgetCachedLanguages();
        }

        // Load up languages from cache
        if ($this->languages === null) {
            $this->languages = $this->cache->remember(self::$cache_key, self::$cache_expiration_time, function () {
                try {
                    return $this->getLanguageClass()
                        ->active()
                        ->get();
                } catch (QueryException $e) {
                    if (app()->runningUnitTests()) {
                        // silently fail if languages can't be loaded in tests
                        Log::error('LanguageRegistrarError: ' . $e->getMessage());
                        return collect();
                    }

                    throw $e;
                }
            });
        }

        $languages = clone $this->languages;

        foreach ($params as $attr => $value) {
            $languages = $languages->where($attr, $value);
        }

        return $languages;
    }

    /**
     * Get an instance of the language class.
     */
    public function getLanguageClass(): Language
    {
        return app($this->language_class);
    }

    /**
     * Get the instance of the Cache Store.
     */
    public function getCacheStore(): Store
    {
        return $this->cache->getStore();
    }
}
