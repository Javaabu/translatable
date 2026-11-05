<?php

namespace Javaabu\Translatable\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Testing\InteractsWithLanguages;
use Javaabu\Translatable\Tests\TestCase;
use Javaabu\Translatable\Tests\TestSupport\Models\Author;
use Javaabu\Translatable\Tests\TestSupport\Models\Page;
use Javaabu\Translatable\Tests\TestSupport\Models\Post;
use Javaabu\Translatable\Translatable;
use PHPUnit\Framework\Attributes\Test;

class StagedTranslationsTest extends TestCase
{
    use InteractsWithLanguages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLanguages(['en', 'dv']);
    }

    #[Test]
    public function it_creates_a_json_model_from_per_locale_inputs(): void
    {
        $page = Page::query()->create([
            'title_dv' => 'މަރުޙަބާ',
            'title_en' => 'Welcome',
            'body_en' => 'Hello',
            'slug' => 'welcome',
        ]);

        $page = $page->fresh();

        $this->assertEquals('en', $page->lang);
        $this->assertEquals('Welcome', $page->title_en);
        $this->assertEquals('މަރުޙަބާ', $page->title_dv);
        $this->assertEquals('Hello', $page->body);
        $this->assertNull($page->body_dv);
    }

    #[Test]
    public function it_fills_a_new_json_model_from_a_translation_when_the_primary_value_is_missing(): void
    {
        $page = new Page(['title_dv' => 'މަރުޙަބާ', 'slug' => 'welcome']);

        $page->save();

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'މަރުޙަބާ', 'lang' => 'en']);
        $this->assertEquals('މަރުޙަބާ', $page->fresh()->title_dv);
    }

    #[Test]
    public function it_does_not_save_json_translations_set_through_attributes_until_the_model_is_saved(): void
    {
        $page = Page::query()->create(['title' => 'Welcome', 'lang' => 'en']);

        $page->title_dv = 'މަރުޙަބާ';

        $this->assertEquals('މަރުޙަބާ', $page->title_dv);
        $this->assertNull($page->fresh()->title_dv);

        $page->save();

        $this->assertEquals('މަރުޙަބާ', $page->fresh()->title_dv);
    }

    #[Test]
    public function it_casts_the_translations_column_when_the_model_does_not(): void
    {
        $page = Page::query()->create(['title' => 'Welcome', 'lang' => 'en', 'title_dv' => 'މަރުޙަބާ']);

        $this->assertEquals(['dv' => ['title' => 'މަރުޙަބާ', 'lang' => 'dv']], $page->fresh()->translations);
    }

    #[Test]
    public function it_creates_a_db_model_and_its_translation_rows_from_per_locale_inputs(): void
    {
        $author = Author::factory()->create();

        $post = Post::query()->create([
            'title_en' => 'Welcome',
            'title_dv' => 'މަރުޙަބާ',
            'body_en' => 'Hello',
            'body_dv' => 'ހެލޯ',
            'slug' => 'welcome',
            'author_id' => $author->id,
        ]);

        $this->assertEquals('en', $post->lang);
        $this->assertDatabaseCount('posts', 2);
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'title' => 'Welcome', 'translatable_parent_id' => null]);
        $this->assertDatabaseHas('posts', ['title' => 'މަރުޙަބާ', 'body' => 'ހެލޯ', 'lang' => 'dv', 'slug' => 'welcome', 'translatable_parent_id' => $post->id]);
        $this->assertEquals('މަރުޙަބާ', $post->fresh()->title_dv);
        $this->assertEquals([], $post->getPendingTranslations());
    }

    #[Test]
    public function it_writes_db_translations_set_through_attributes_when_the_model_is_saved(): void
    {
        $post = Post::factory()->withAuthor()->create(['lang' => 'en', 'title' => 'Welcome']);

        $post->title_dv = 'މަރުޙަބާ';
        $post->body_dv = 'ހެލޯ';

        $this->assertEquals('މަރުޙަބާ', $post->title_dv);
        $this->assertTrue($post->hasTranslation('dv'));
        $this->assertDatabaseCount('posts', 1);

        DB::enableQueryLog();
        $post->save();
        $inserts = collect(DB::getQueryLog())->filter(fn ($query) => str_starts_with($query['query'], 'insert'));

        $this->assertCount(1, $inserts);
        $this->assertDatabaseHas('posts', ['title' => 'މަރުޙަބާ', 'body' => 'ހެލޯ', 'translatable_parent_id' => $post->id]);
    }

    #[Test]
    public function it_gives_new_db_translation_rows_their_own_timestamps(): void
    {
        $this->travelTo(now()->subDay());
        $post = Post::factory()->withAuthor()->create(['lang' => 'en', 'title' => 'Welcome']);
        $this->travelBack();

        $post->addTranslation('dv', 'title', 'މަރުޙަބާ');

        $translation = $post->fresh()->getTranslation('dv');

        $this->assertTrue($translation->created_at->isToday());
        $this->assertTrue($translation->updated_at->isToday());
        $this->assertTrue($post->fresh()->created_at->isYesterday());
    }

    #[Test]
    public function it_updates_the_parent_row_from_a_translation_row(): void
    {
        $post = Post::factory()->withAuthor()->create(['lang' => 'en', 'title' => 'Welcome']);
        $translation = $post->addTranslation('dv', 'title', 'މަރުޙަބާ');

        $translation->fill(['title_en' => 'Welcome back', 'title_dv' => 'އަލުން މަރުޙަބާ'])->save();

        $this->assertEquals('Welcome back', $post->fresh()->title);
        $this->assertEquals('އަލުން މަރުޙަބާ', $translation->fresh()->title_dv);
        $this->assertDatabaseHas('posts', ['id' => $translation->id, 'title' => 'އަލުން މަރުޙަބާ']);
    }

    #[Test]
    public function it_treats_existing_rows_without_a_lang_as_the_default_locale(): void
    {
        DB::table('pages')->insert(['title' => 'Welcome', 'translations' => null, 'lang' => null]);

        Languages::setCurrentLocale('dv');

        $page = Page::query()->first();

        $this->assertEquals('en', $page->primaryLocale());
        $this->assertEquals('en', $page->getDefaultTranslationLocale());
        $this->assertEquals('ltr', $page->locale_direction);

        // Editing in Dhivehi adds a translation instead of overwriting the English columns
        $page->update(['title' => 'މަރުޙަބާ']);

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'Welcome', 'lang' => 'en']);
        $this->assertEquals('މަރުޙަބާ', $page->fresh()->title_dv);
    }

    #[Test]
    public function it_writes_the_columns_when_the_current_locale_is_not_an_allowed_language(): void
    {
        $page = Page::query()->create(['title' => 'Welcome', 'lang' => 'en']);

        Languages::setCurrentLocale('fr');

        $page->update(['title' => 'Bienvenue']);

        $this->assertDatabaseHas('pages', ['id' => $page->id, 'title' => 'Bienvenue']);
    }

    #[Test]
    public function it_validates_and_saves_a_form_with_the_expanded_rules(): void
    {
        $rules = Translatable::rules(Page::class, ['title' => ['required', 'string'], 'slug' => ['nullable']]);

        $validator = validator(['title_dv' => 'މަރުޙަބާ'], $rules);

        $this->assertTrue($validator->fails());
        $this->assertEquals(['title_en'], $validator->errors()->keys());

        $validated = validator(['title_en' => 'Welcome', 'title_dv' => 'މަރުޙަބާ', 'slug' => 'welcome'], $rules)->validate();

        $page = Page::query()->create($validated);

        $this->assertEquals(['title_en' => 'Welcome', 'title_dv' => 'މަރުޙަބާ'], Translatable::values($page->fresh(), ['title']));
    }
}
