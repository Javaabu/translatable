---
title: Testing
sidebar_position: 4
---

`Javaabu\Translatable\Testing\InteractsWithLanguages` sets up languages in tests, in apps and in packages:

```php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Translatable\Testing\InteractsWithLanguages;

class PostsTest extends TestCase
{
    use InteractsWithLanguages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLanguages(['en', 'dv']);
    }

    #[Test]
    public function it_saves_the_dhivehi_title(): void
    {
        $this->post('/en/posts', ['title_en' => 'Welcome', 'title_dv' => 'މަރުޙަބާ', 'slug' => 'welcome', 'body_en' => 'Hello'])
            ->assertRedirect();

        $this->assertEquals('މަރުޙަބާ', Post::first()->title_dv);
    }

    #[Test]
    public function it_works_with_translations_off(): void
    {
        $this->withoutTranslatable(function () {
            $this->post('/en/posts', ['title' => 'Welcome', 'slug' => 'welcome', 'body' => 'Hello'])->assertRedirect();
        });
    }
}
```

| Method | What |
| --- | --- |
| `setUpLanguages(array $codes = ['en', 'dv'], ?string $current = null)` | Creates the languages table if it's missing, adds the languages from the presets, clears the cache, and sets the current locale (the first code by default) |
| `withoutTranslatable(callable $callback)` | Runs the callback with `translatable.enabled` off |

Use Thaana in tests of anything a user sees.
