<?php

namespace Javaabu\Translatable\Tests\Unit\Contract;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\Rule;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Facades\Translatable as TranslatableFacade;
use Javaabu\Translatable\Locale;
use Javaabu\Translatable\Testing\InteractsWithLanguages;
use Javaabu\Translatable\Tests\TestCase;
use Javaabu\Translatable\Tests\TestSupport\Models\Article;
use Javaabu\Translatable\Tests\TestSupport\Models\Author;
use Javaabu\Translatable\Tests\TestSupport\Models\Page;
use Javaabu\Translatable\Tests\TestSupport\Models\Post;
use Javaabu\Translatable\Translatable;
use PHPUnit\Framework\Attributes\Test;

class TranslatableContractTest extends TestCase
{
    use InteractsWithLanguages;
    use RefreshDatabase;

    #[Test]
    public function it_is_disabled_until_a_language_is_active(): void
    {
        $this->assertFalse(Translatable::enabled());

        $this->setUpLanguages(['en']);

        $this->assertTrue(Translatable::enabled());
    }

    #[Test]
    public function it_can_be_disabled_in_config(): void
    {
        $this->setUpLanguages();

        $this->withoutTranslatable(function () {
            $this->assertFalse(Translatable::enabled());
            $this->assertFalse(Translatable::supports(new Page));
            $this->assertEquals(['title' => ['required']], Translatable::rules(new Page, ['title' => ['required']]));
        });

        $this->assertTrue(Translatable::enabled());
    }

    #[Test]
    public function it_checks_whether_a_model_and_field_are_translatable(): void
    {
        $this->setUpLanguages();

        $this->assertTrue(Translatable::supports(new Page));
        $this->assertTrue(Translatable::supports(Page::class));
        $this->assertTrue(Translatable::supports(Page::class, 'title'));
        $this->assertTrue(Translatable::supports(new Post, 'body'));
        $this->assertFalse(Translatable::supports(Page::class, 'slug'));
        $this->assertFalse(Translatable::supports(new Author));
        $this->assertFalse(Translatable::supports(Author::class, 'name'));
        $this->assertFalse(Translatable::supports(null));
    }

    #[Test]
    public function it_tells_db_translatable_models_apart(): void
    {
        $this->assertTrue(Translatable::isDbTranslatable(Post::class));
        $this->assertFalse(Translatable::isDbTranslatable(new Article));
        $this->assertFalse(Translatable::isDbTranslatable(null));
    }

    #[Test]
    public function it_lists_the_active_locales_with_the_default_first(): void
    {
        $this->setUpLanguages(['dv', 'en']);

        $locales = Translatable::locales();

        $this->assertEquals(['en', 'dv'], $locales->pluck('code')->all());

        $dhivehi = $locales->last();

        $this->assertInstanceOf(Locale::class, $dhivehi);
        $this->assertEquals('ދިވެހި', $dhivehi->native_name);
        $this->assertEquals('rtl', $dhivehi->direction);
        $this->assertTrue($dhivehi->isRtl());
        $this->assertTrue($dhivehi->isThaana());
        $this->assertFalse($dhivehi->is_default);
        $this->assertTrue($locales->first()->is_default);

        $this->assertEquals([
            'code' => 'dv',
            'locale' => 'dv',
            'name' => 'Dhivehi',
            'native_name' => 'ދިވެހި',
            'direction' => 'rtl',
            'rtl' => true,
            'thaana' => true,
            'default' => false,
            'flag_url' => asset('vendors/flags/mv.svg'),
        ], $dhivehi->toArray());

        $this->assertEquals('dv', Translatable::locale('dv')->code);
        $this->assertEquals('dv', Translatable::locale()->code);
        $this->assertNull(Translatable::locale('fr'));
    }

    #[Test]
    public function it_gives_one_input_per_locale_with_the_primary_first(): void
    {
        $this->setUpLanguages(['en', 'dv']);

        $page = Page::query()->create(['title' => 'ފުރަތަމަ ސަފްހާ', 'lang' => 'dv']);

        $inputs = Translatable::inputs('title', $page);

        $this->assertEquals(['dv', 'en'], $inputs->keys()->all());
        $this->assertEquals('title_dv', $inputs['dv']->name);
        $this->assertTrue($inputs['dv']->is_primary);
        $this->assertFalse($inputs['en']->is_primary);
        $this->assertEquals('ސުރުޚީ (ދިވެހި)', $inputs['dv']->label('ސުރުޚީ'));
        $this->assertEquals('Title (English)', $inputs['en']->label());
        $this->assertEquals([
            'field' => 'title',
            'name' => 'title_dv',
            'locale' => 'dv',
            'direction' => 'rtl',
            'thaana' => true,
            'primary' => true,
            'language' => Translatable::locale('dv')->toArray(),
        ], $inputs['dv']->toArray());

        // A new model is written in the current locale
        $this->assertTrue(Translatable::inputs('title', Page::class)['en']->is_primary);
        $this->assertEquals('title_en', Translatable::inputName('title', 'en'));
    }

    #[Test]
    public function it_expands_rules_for_translatable_fields(): void
    {
        $this->setUpLanguages(['en', 'dv']);

        $unique = Rule::unique('pages', 'title');

        $rules = Translatable::rules(Page::class, [
            'title' => ['required', 'string', 'max:255'],
            'body' => 'required|string',
            'slug' => ['required', 'alpha_dash'],
        ]);

        $this->assertEquals([
            'title_en' => ['required', 'string', 'max:255'],
            'title_dv' => ['nullable', 'string', 'max:255'],
            'body_en' => 'required|string',
            'body_dv' => 'nullable|string',
            'slug' => ['required', 'alpha_dash'],
        ], $rules);

        $this->assertEquals(
            ['title_en' => ['required'], 'title_dv' => ['required']],
            Translatable::rules(Page::class, ['title' => ['required']], require_all: true),
        );

        $this->assertEquals(
            ['title_en' => $unique, 'title_dv' => ['nullable', $unique]],
            Translatable::rules(Page::class, ['title' => $unique]),
        );

        $this->assertEquals(['name' => ['required']], Translatable::rules(Author::class, ['name' => ['required']]));
    }

    #[Test]
    public function it_gives_the_values_in_every_locale_without_falling_back(): void
    {
        $this->setUpLanguages(['en', 'dv']);

        $page = Page::query()->create(['title' => 'Welcome', 'body' => 'Hello', 'lang' => 'en']);
        $page->addTranslation('dv', 'title', 'މަރުޙަބާ');

        $this->assertEquals([
            'title_en' => 'Welcome',
            'title_dv' => 'މަރުޙަބާ',
            'body_en' => 'Hello',
            'body_dv' => null,
        ], Translatable::values($page));

        $this->assertEquals(['title_en' => 'Welcome', 'title_dv' => 'މަރުޙަބާ'], Translatable::values($page, ['title']));
        $this->assertEquals([], Translatable::values(Author::factory()->create()));
    }

    #[Test]
    public function it_reports_the_translation_status_of_a_model(): void
    {
        $this->setUpLanguages(['en', 'dv', 'ar']);

        $page = Page::query()->create(['title' => 'Welcome', 'lang' => 'en']);
        $page->addTranslation('dv', 'title', 'މަރުޙަބާ');

        $status = Translatable::status($page, fn (Locale $locale, bool $translated) => ($translated ? 'edit/' : 'add/') . $locale->code);

        $this->assertEquals(['en', 'dv', 'ar'], array_column($status, 'locale'));
        $this->assertEquals([true, true, false], array_column($status, 'translated'));
        $this->assertEquals([true, false, false], array_column($status, 'primary'));
        $this->assertEquals(['edit/en', 'edit/dv', 'add/ar'], array_column($status, 'url'));
        $this->assertNull(Translatable::status($page)[0]['url']);
        $this->assertEquals([false, false, false], array_column(Translatable::status(new Page), 'translated'));
    }

    #[Test]
    public function it_shares_the_languages_for_the_front_end(): void
    {
        $this->setUpLanguages(['en', 'dv'], current: 'dv');

        $shared = Translatable::shared();

        $this->assertTrue($shared['enabled']);
        $this->assertEquals('dv', $shared['current']);
        $this->assertEquals('en', $shared['default']);
        $this->assertEquals('rtl', $shared['direction']);
        $this->assertEquals(['en', 'dv'], array_column($shared['locales'], 'code'));
    }

    #[Test]
    public function it_answers_through_the_facade_too(): void
    {
        $this->setUpLanguages(['en', 'dv']);

        $this->assertTrue(TranslatableFacade::enabled());
        $this->assertEquals(['en', 'dv'], TranslatableFacade::getAllowedTranslationLocales());
        $this->assertEquals('en', TranslatableFacade::defaultLocale());
        $this->assertEquals('en', TranslatableFacade::currentLocale());

        Languages::setCurrentLocale('dv');

        $this->assertEquals('dv', Translatable::currentLocale());
        $this->assertEquals('dv', Translatable::primaryLocale());
    }
}
