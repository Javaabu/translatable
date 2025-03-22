<?php

namespace Javaabu\Translatable\Tests\Unit;

use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LocaleMiddlewareTest extends TestCase
{
    use RefreshDatabase;
    use MakesHttpRequests;

    public function setUp(): void
    {
        parent::setUp();

        Language::create([
            'name'   => 'English',
            'code'   => 'en',
            'locale' => 'en',
            'flag'   => '🇬🇧',
            'is_rtl' => false,
            'active' => true,
        ]);
        Language::create([
            'name'   => 'Dhivehi',
            'code'   => 'dv',
            'locale' => 'dv',
            'flag'   => '🇲🇻',
            'is_rtl' => true,
            'active' => true,
        ]);
        Language::create([
            'name'   => 'Japanese',
            'code'   => 'jp',
            'locale' => 'jp',
            'flag'   => '🇯🇵',
            'is_rtl' => false,
            'active' => true,
        ]);
    }

    #[Test]
    public function it_can_get_lang_from_query_string(): void
    {
        Route::group(['middleware' => ['web', 'language']], function () {
            Route::get('/getlang', function () {
                return app()->getLocale();
            });
        });

        $req_en = $this->get('/getlang?lang=en');
        $req_en->assertContent('en');

        $req_dv = $this->get('/getlang?lang=dv');
        $req_dv->assertContent('dv');

        $req_fr = $this->get('/getlang?lang=fr');
        // when language doesn't exist, it falls back to user session if available
        $req_fr->assertContent('dv');

        $this->flushSession();

        $req_fr = $this->get('/getlang?lang=fr');
        // when language doesn't exist, it falls back to default language
        $req_fr->assertContent('en');
    }

    #[Test]
    public function it_can_get_lang_from_named_route()
    {
        Route::group(['middleware' => ['web', 'language']], function () {
            Route::get('/{language}/getlang', function () {
                return app()->getLocale();
            });
        });

        $req_en = $this->get('/en/getlang');
        $req_en->assertContent('en');

        $req_dv = $this->get('/dv/getlang');
        $req_dv->assertContent('dv');
    }

    #[Test]
    public function it_fails_to_get_lang_from_named_route_when_language_does_not_exist()
    {
        Route::group(['middleware' => ['web', 'language']], function () {
            Route::get('/{language}/getlang', function () {
                return app()->getLocale();
            });
        });

        $req_en = $this->get('/fr/getlang');
        $req_en->assertStatus(404);
    }

    #[Test]
    public function it_can_get_lang_from_request_input(): void
    {
        Route::group(['middleware' => ['web', 'language']], function () {
            Route::post('/getlang', function () {
                return app()->getLocale();
            });
        });

        $req_en = $this->post('/getlang', [
            'language' => 'en',
        ]);
        $req_en->assertContent('en');

        $req_dv = $this->post('/getlang', [
            'language' => 'dv',
        ]);
        $req_dv->assertContent('dv');

        $req_fr = $this->post('/getlang', [
            'language' => 'fr',
        ]);
        // when language doesn't exist, it falls back to user session if available
        $req_fr->assertContent('dv');

        $this->flushSession();

        $req_fr = $this->post('/getlang', [
            'language' => 'fr',
        ]);
        // when language doesn't exist, it falls back to default language
        $req_fr->assertContent('en');
    }
}
