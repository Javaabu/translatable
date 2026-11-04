<?php

namespace Javaabu\Translatable\Tests\Feature\Views;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Testing\InteractsWithLanguages;
use Javaabu\Translatable\Tests\TestCase;
use Javaabu\Translatable\Tests\TestSupport\Models\Article;
use Javaabu\Translatable\Tests\TestSupport\Models\Author;
use Javaabu\Translatable\Tests\TestSupport\Models\Page;
use Javaabu\Translatable\Tests\TestSupport\Models\Post;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\DomCrawler\Crawler;

class BladeComponentsTest extends TestCase
{
    use InteractsWithLanguages;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpLanguages(['en', 'dv']);
    }

    protected function defineRoutes($router): void
    {
        parent::defineRoutes($router);

        $router->get('{language}/articles/{article}/edit', fn () => 'edit')->name('articles.edit');
        $router->get('{language}/posts/create', fn () => 'create')->name('posts.create');
        $router->get('{language}/posts/{post}/edit', fn () => 'edit')->name('posts.edit');

        $router->get('{language}/about', fn () => Blade::render('<x-translatable::language-switcher />'))
            ->middleware(['web', 'language'])
            ->name('about');

        $router->get('contact', fn () => Blade::render('<x-translatable::language-switcher />'))->middleware(['web', 'language']);
    }

    public static function frameworks(): array
    {
        return [
            'onu' => ['onu'],
            'bootstrap-6' => ['bootstrap-6'],
        ];
    }

    protected function render(string $blade, array $data = [], string $framework = 'onu'): Crawler
    {
        config(['forms.framework' => $framework]);

        return new Crawler(Blade::render($blade, $data));
    }

    #[Test]
    #[DataProvider('frameworks')]
    public function it_renders_a_tab_per_locale_with_the_bound_values(string $framework): void
    {
        $page = Page::query()->create(['title' => 'Welcome', 'lang' => 'en', 'title_dv' => 'މަރުޙަބާ']);

        $html = $this->render('<x-forms::form :model="$page"><x-translatable::text name="title" :label="__(\'Title\')" required /></x-forms::form>', compact('page'), $framework);

        $this->assertEquals(['English', 'ދިވެހި'], $html->filter('[role="tab"]')->each(fn (Crawler $tab) => trim($tab->text())));
        $this->assertCount(2, $html->filter('[role="tabpanel"]'));
        $this->assertEquals('Welcome', $html->filter('input[name="title_en"]')->attr('value'));
        $this->assertEquals('މަރުޙަބާ', $html->filter('input[name="title_dv"]')->attr('value'));

        $dhivehi = $html->filter('input[name="title_dv"]');
        $this->assertEquals('rtl', $dhivehi->attr('dir'));
        $this->assertEquals('dv', $dhivehi->attr('lang'));
        $this->assertNotNull($dhivehi->attr('data-thaana'));
        $this->assertNull($dhivehi->attr('required'));
        $this->assertNotNull($html->filter('input[name="title_en"]')->attr('required'));
        $this->assertStringContainsString('Title (ދިވެހި)', $html->filter('label[for="title_dv"]')->text());
    }

    #[Test]
    public function it_puts_the_models_primary_locale_first(): void
    {
        $page = Page::query()->create(['title' => 'މަރުޙަބާ', 'lang' => 'dv']);

        $html = $this->render('<x-translatable::textarea name="title" :model="$page" />', compact('page'));

        $this->assertEquals('ދިވެހި', trim($html->filter('[role="tab"]')->first()->text()));
        $this->assertEquals('މަރުޙަބާ', trim($html->filter('textarea[name="title_dv"]')->text()));
    }

    #[Test]
    public function it_opens_the_first_tab_with_an_error(): void
    {
        $errors = (new ViewErrorBag)->put('default', new MessageBag(['title_dv' => ['ސުރުޚީ ލިޔުއްވާ']]));

        session()->put('errors', $errors);
        view()->share('errors', $errors);

        $html = $this->render('<x-translatable::text name="title" />');

        $this->assertEquals('true', $html->filter('#title-dv-tab')->attr('aria-selected'));
        $this->assertStringContainsString('ސުރުޚީ ލިޔުއްވާ', $html->filter('#title-dv')->text());
    }

    #[Test]
    #[DataProvider('frameworks')]
    public function it_stacks_the_inputs_when_asked(string $framework): void
    {
        $html = $this->render('<x-translatable::field type="textarea" name="body" layout="stacked" class="my-field" :rows="3" />', [], $framework);

        $this->assertCount(0, $html->filter('[role="tab"]'));
        $this->assertCount(1, $html->filter('.my-field[data-translatable-field="body"]'));
        $this->assertEquals(['body_en', 'body_dv'], $html->filter('textarea')->each(fn (Crawler $input) => $input->attr('name')));
        $this->assertEquals('3', $html->filter('textarea[name="body_dv"]')->attr('rows'));
    }

    #[Test]
    public function it_gives_rtl_languages_without_thaana_their_direction(): void
    {
        $this->setUpLanguages(['en', 'dv', 'ar']);

        $html = $this->render('<x-translatable::text name="title" layout="stacked" />');

        $arabic = $html->filter('input[name="title_ar"]');

        $this->assertEquals('rtl', $arabic->attr('dir'));
        $this->assertEquals('ar', $arabic->attr('lang'));
        $this->assertNull($arabic->attr('data-thaana'));
        $this->assertEquals('ltr', $html->filter('input[name="title_en"]')->attr('dir'));
    }

    #[Test]
    public function it_renders_a_plain_field_when_the_model_or_field_is_not_translatable(): void
    {
        $author = Author::factory()->create(['name' => 'އަޙްމަދު']);
        $page = Page::query()->create(['title' => 'Welcome', 'slug' => 'welcome', 'lang' => 'en']);

        $html = $this->render('<x-translatable::text name="name" :model="$author" />', compact('author'));
        $this->assertEquals('އަޙްމަދު', $html->filter('input[name="name"]')->attr('value'));
        $this->assertCount(0, $html->filter('[role="tab"]'));

        $html = $this->render('<x-translatable::text name="slug" :model="$page" />', compact('page'));
        $this->assertEquals('welcome', $html->filter('input[name="slug"]')->attr('value'));

        $html = $this->withoutTranslatable(fn () => $this->render('<x-translatable::text name="title" />'));
        $this->assertCount(1, $html->filter('input[name="title"]'));
    }

    #[Test]
    #[DataProvider('frameworks')]
    public function it_links_to_the_current_page_in_each_language(string $framework): void
    {
        config(['forms.framework' => $framework]);

        $html = new Crawler($this->get('/dv/about?tab=2')->assertOk()->getContent());

        $links = $html->filter('[data-translatable="language-switcher"] a');

        $this->assertEquals(['English', 'ދިވެހި'], $links->each(fn (Crawler $link) => trim($link->text())));
        $this->assertEquals(url('/en/about') . '?tab=2', $links->first()->attr('href'));
        $this->assertEquals('page', $links->last()->attr('aria-current'));
    }

    #[Test]
    public function it_switches_with_a_query_string_on_routes_without_a_language(): void
    {
        $html = new Crawler($this->get('/contact?lang=dv')->getContent());

        $this->assertEquals(url('/contact') . '?lang=en', $html->filter('a')->first()->attr('href'));
        $this->assertEquals('page', $html->filter('a')->last()->attr('aria-current'));
    }

    #[Test]
    public function it_hides_the_switcher_with_one_language(): void
    {
        Languages::get('dv')->update(['active' => false]);

        $this->assertEquals('', trim(Blade::render('<x-translatable::language-switcher />')));
    }

    #[Test]
    public function it_links_the_switcher_to_the_models_pages(): void
    {
        $article = Article::factory()->withAuthor()->create(['lang' => 'en']);

        $html = $this->render('<x-translatable::language-switcher :model="$article" />', compact('article'));

        $this->assertEquals(url("/dv/articles/{$article->id}"), $html->filter('a')->last()->attr('href'));
    }

    #[Test]
    #[DataProvider('frameworks')]
    public function it_shows_the_translation_status_of_a_json_model(string $framework): void
    {
        $article = Article::factory()->withAuthor()->create(['lang' => 'en']);

        $html = $this->render('<x-translatable::translation-status :model="$article" />', compact('article'), $framework);

        $items = $html->filter('[data-translatable="translation-status"] li');

        $this->assertEquals(['true', 'false'], $items->each(fn (Crawler $item) => $item->attr('data-translated')));
        $this->assertEquals(url("/dv/articles/{$article->id}/edit"), $items->last()->filter('a')->attr('href'));
        $this->assertEquals('Not translated into ދިވެހި', $items->last()->filter('a')->attr('title'));
        $this->assertStringContainsString('ދިވެހި', $items->last()->text());
    }

    #[Test]
    public function it_links_missing_db_translations_to_the_create_page(): void
    {
        $post = Post::factory()->withAuthor()->create(['lang' => 'en']);

        $html = $this->render('<x-translatable::translation-status :model="$post" />', compact('post'));

        $this->assertEquals(url("/dv/posts/create?lang_parent={$post->id}"), $html->filter('li')->last()->filter('a')->attr('href'));

        $translation = $post->addTranslation('dv', 'title', 'މަރުޙަބާ');

        $html = $this->render('<x-translatable::translation-status :model="$post" />', compact('post'));

        $this->assertEquals(url("/dv/posts/{$translation->id}/edit"), $html->filter('li')->last()->filter('a')->attr('href'));
    }

    #[Test]
    public function it_shows_the_status_without_links(): void
    {
        $page = Page::query()->create(['title' => 'Welcome', 'lang' => 'en']);

        Languages::setCurrentLocale('dv');
        app()->setLocale('dv');

        $html = $this->render('<x-translatable::translation-status :model="$page" :links="false" />', compact('page'));

        $this->assertCount(0, $html->filter('a'));
        $this->assertEquals('ދިވެހި އަށް ތަރުޖަމާކުރެވިފައެއް ނުވޭ', $html->filter('li')->last()->filter('span')->attr('title'));
        $this->assertEquals('ތަރުޖަމާތައް', $html->filter('ul')->attr('aria-label'));
    }

    #[Test]
    #[DataProvider('frameworks')]
    public function it_renders_table_headings_and_cells_for_the_other_languages(string $framework): void
    {
        $article = Article::factory()->withAuthor()->create(['lang' => 'en']);

        $html = $this->render('<table><thead><tr><x-translatable::titles /></tr></thead><tbody><tr><x-translatable::table-cells :model="$article" /></tr></tbody></table>', compact('article'), $framework);

        $this->assertEquals(['ދިވެހި'], $html->filter('th')->each(fn (Crawler $th) => trim($th->text())));
        $this->assertEquals('dv', $html->filter('th')->attr('lang'));
        $this->assertCount(1, $html->filter('td'));
        $this->assertEquals(url("/dv/articles/{$article->id}/edit"), $html->filter('td a')->attr('href'));
        $this->assertEquals('Add ދިވެހި translation', $html->filter('td a')->attr('title'));
        $this->assertCount(1, $html->filter('td a svg'));
    }

    #[Test]
    public function it_renders_table_cells_without_links_when_the_routes_are_missing(): void
    {
        $page = Page::query()->create(['title' => 'Welcome', 'lang' => 'en', 'title_dv' => 'މަރުޙަބާ']);

        $html = $this->render('<x-translatable::table-cells :model="$page" />', compact('page'));

        $this->assertCount(0, $html->filter('a'));
        $this->assertEquals('Edit ދިވެހި translation', $html->filter('span[data-translated="true"]')->attr('title'));
    }

    #[Test]
    #[DataProvider('frameworks')]
    public function it_renders_the_notice_for_adding_a_db_translation(string $framework): void
    {
        $post = Post::factory()->withAuthor()->create(['lang' => 'en', 'title' => 'Welcome']);

        Languages::setCurrentLocale('dv');

        $html = $this->render('<form><x-translatable::translation-notice :parent="$post" /></form>', compact('post'), $framework);

        $this->assertStringContainsString('“Welcome” ގެ ދިވެހި ތަރުޖަމާ އިތުރުކުރަނީ.', $html->filter('[data-translatable="translation-notice"]')->text());
        $this->assertEquals('dv', $html->filter('input[name="lang"]')->attr('value'));
        $this->assertEquals((string) $post->id, $html->filter('input[name="lang_parent"]')->attr('value'));

        $this->assertEquals('', trim(Blade::render('<x-translatable::translation-notice />')));
    }
}
