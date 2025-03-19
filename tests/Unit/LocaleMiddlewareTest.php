<?php

namespace Javaabu\Translatable\Tests\Unit;

use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Javaabu\Translatable\Middleware\LocaleMiddleware;
use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LocaleMiddlewareTest extends TestCase
{
    use RefreshDatabase;
    use MakesHttpRequests;

    private function runMiddlewareOn($req)
    {
        $middleware = new LocaleMiddleware;
        return $middleware->handle($req, function () {});
    }

    #[Test]
    public function it_can_redirect_to_default_language(): void
    {
        self::markTestIncomplete();
        Route::group(['middleware' => ['web', 'language']], function () {
          Route::get('/home', function () {
              return 'home';
          });
        });

//        $req = $this->get('home');
//        dd($req);

//        $this->markTestIncomplete();
        app()->setLocale('en');
        $req = $this->get('/home');
        $this->followRedirects($req);

//        $req = Request::create('/', 'GET');

//        dd($req->url());

        $req->assertRedirect('/en/home');
//        $this->assertEquals('/en', $req->url());
//        $this->assertEquals('en', app()->getLocale());
    }

    #[Test]
    public function it_can_get_user_locale()
    {
        $this->markTestIncomplete();
        app()->setLocale('dv');
        session()->put('locale', 'en');

//        $req = $this->get('/');
        $req = Request::create('/', 'GET');
//        $req->assertRedirect('/en');
        $this->runMiddlewareOn($req);

        $this->assertEquals('en', app()->getLocale());
    }
}
