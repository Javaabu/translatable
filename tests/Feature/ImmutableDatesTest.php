<?php

namespace Javaabu\Translatable\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ServiceProvider;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Testing\InteractsWithLanguages;
use Javaabu\Translatable\Tests\TestCase;
use Javaabu\Translatable\Tests\TestSupport\Models\Article;
use Javaabu\Translatable\Tests\TestSupport\Models\Post;
use Javaabu\Translatable\TranslatableServiceProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Apps on Laravel's starter kits call Date::use(CarbonImmutable::class) (#12)
 */
class ImmutableDatesTest extends TestCase
{
    use InteractsWithLanguages;
    use RefreshDatabase;

    protected bool $immutable_dates = true;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLanguages(['en', 'dv']);
    }

    #[Test]
    public function it_boots_in_the_console_with_immutable_dates(): void
    {
        $this->assertInstanceOf(CarbonImmutable::class, now());
        $this->assertTrue($this->app->runningInConsole());

        $published = array_map('basename', array_values(
            ServiceProvider::pathsToPublish(TranslatableServiceProvider::class, 'translatable-migrations')
        ));

        $this->assertCount(2, $published);
        $this->assertMatchesRegularExpression('/^\d{4}_\d{2}_\d{2}_\d{6}_create_languages_table\.php$/', $published[0]);
        $this->assertMatchesRegularExpression('/^\d{4}_\d{2}_\d{2}_\d{6}_add_local_name_to_languages_table\.php$/', $published[1]);

        // The create migration runs first
        $sorted = $published;
        sort($sorted);
        $this->assertSame($published, $sorted);
    }

    #[Test]
    public function it_saves_json_translations_with_immutable_dates(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-01-01 08:00:00'));

        $article = Article::factory()->withAuthor()->create(['lang' => 'en', 'title' => 'Welcome']);

        $this->travelTo(CarbonImmutable::parse('2026-01-02 08:00:00'));

        $article->title_dv = 'މަރުޙަބާ';
        $article->save();
        $article->addTranslation('dv', 'body', 'ހެލޯ');

        $article = $article->fresh();

        $this->assertInstanceOf(CarbonImmutable::class, $article->created_at);
        $this->assertEquals('2026-01-01 08:00:00', $article->created_at->toDateTimeString());
        $this->assertEquals('2026-01-02 08:00:00', $article->updated_at->toDateTimeString());
        $this->assertEquals('މަރުޙަބާ', $article->title_dv);
        $this->assertEquals('ހެލޯ', $article->body_dv);
    }

    #[Test]
    public function it_dates_db_translations_when_they_are_created_with_immutable_dates(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-01-01 08:00:00'));

        $post = Post::factory()->withAuthor()->create(['lang' => 'en', 'title' => 'Welcome']);

        $this->travelTo(CarbonImmutable::parse('2026-01-02 08:00:00'));

        $post->title_dv = 'މަރުޙަބާ';
        $post->save();

        $translation = $post->fresh()->getTranslation('dv');

        $this->assertInstanceOf(CarbonImmutable::class, $translation->created_at);
        $this->assertEquals('މަރުޙަބާ', $translation->getRawOriginal('title'));
        // The translation row gets its own timestamps, not a copy of the parent's
        $this->assertEquals('2026-01-02 08:00:00', $translation->created_at->toDateTimeString());
        $this->assertEquals('2026-01-02 08:00:00', $translation->updated_at->toDateTimeString());

        // The parent's dates aren't changed by its translation
        $this->assertEquals('2026-01-01 08:00:00', $post->fresh()->created_at->toDateTimeString());
    }

    #[Test]
    public function it_deletes_and_restores_languages_with_immutable_dates(): void
    {
        Languages::get('dv')->delete();

        $this->assertNull(Languages::get('dv'));

        $language = Language::withTrashed()->where('code', 'dv')->first();
        $this->assertInstanceOf(CarbonImmutable::class, $language->deleted_at);

        $language->restore();

        $this->assertEquals('dv', Languages::get('dv')?->code);
    }

    #[Test]
    public function it_adds_languages_from_the_command_with_immutable_dates(): void
    {
        $this->artisan('translatable:add-languages', ['codes' => ['ar']])->assertSuccessful();

        $this->assertInstanceOf(CarbonImmutable::class, Language::where('code', 'ar')->first()->created_at);
    }
}
