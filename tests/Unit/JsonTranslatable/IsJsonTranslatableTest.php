<?php

namespace Javaabu\Translatable\Tests\Unit\JsonTranslatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Translatable\Exceptions\CannotDeletePrimaryTranslationException;
use Javaabu\Translatable\Exceptions\FieldNotAllowedException;
use Javaabu\Translatable\Exceptions\LanguageNotAllowedException;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Tests\TestCase;
use Javaabu\Translatable\Tests\TestSupport\Models\Article;
use PHPUnit\Framework\Attributes\Test;

class IsJsonTranslatableTest extends TestCase
{
    use RefreshDatabase;

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
    public function it_can_get_fields_ignored_for_translation()
    {
        $article = new Article();

        $this->assertEquals([
            'id',
            'translations',
            'lang',
            'created_at',
            'updated_at',
            'deleted_at',
        ], $article->getFieldsIgnoredForTranslation());
    }

    #[Test]
    public function it_can_get_translatable_fields()
    {
        $article = new Article();

        $this->assertEquals([
            'title',
            'body',
        ], $article->getTranslatables());
    }

    #[Test]
    public function it_can_get_non_translatable_fields()
    {
        $article = new Article();

        $this->assertEquals([
            'slug',
        ], $article->getNonTranslatables());
    }

    #[Test]
    public function it_can_get_non_translatable_pivots()
    {
        $article = new Article();

        $this->assertEquals([
            'author_id',
        ], $article->getNonTranslatablePivots());
    }

    #[Test]
    public function it_can_get_all_non_translatable_fields()
    {
        $article = new Article();

        $this->assertEquals([
            'slug',
            'id',
            'translations',
            'lang',
            'created_at',
            'updated_at',
            'deleted_at',
            'author_id',
        ], $article->getAllNonTranslatables());
    }

    #[Test]
    public function it_can_check_if_is_a_non_translatable_pivot()
    {
        $article = new Article();

        $this->assertFalse($article->isNonTranslatablePivot('id'));
        $this->assertTrue($article->isNonTranslatablePivot('author_id'));
    }

    #[Test]
    public function it_can_get_the_default_translation_locale()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $this->assertFalse($article->isDefaultTranslationLocale('fr'));
        $this->assertTrue($article->isDefaultTranslationLocale('en'));
    }

    #[Test]
    public function it_can_get_allowed_translation_locales()
    {
        $article = Article::factory()->withAuthor()->create();

        $this->assertEquals(['en', 'dv', 'jp'], $article->getAllowedTranslationLocales());
    }

    #[Test]
    public function it_can_check_if_given_locale_is_allowed()
    {
        $article = Article::factory()->withAuthor()->create();

        $this->assertTrue($article->isAllowedTranslationLocale('en'));
        $this->assertTrue($article->isAllowedTranslationLocale('dv'));
        $this->assertFalse($article->isAllowedTranslationLocale('fr'));
    }

    #[Test]
    public function it_can_translate_field_via_translate_function()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang'         => 'en',
            'title'        => 'This is an English title',
            'slug'         => 'this-is-an-english-slug',
            'body'         => 'This is an English body',
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug'  => 'mee-dhivehi-slug-eh',
                    'body'  => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug'  => 'kore-wa-namekujidesu',
                    'body'  => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $this->assertEquals('Mee dhivehi title eh', $article->translate('title', 'dv'));
        $this->assertEquals('Mee dhivehi liyumeh', $article->translate('body', 'dv'));
        $this->assertNull($article->translate('slug', 'dv', false));

        $tmp = app()->getLocale();
        app()->setLocale('en');
        $this->assertEquals('This is an English title', $article->translate('title'));
        $this->assertEquals('This is an English body', $article->translate('body'));
        $this->assertEquals('this-is-an-english-slug', $article->translate('slug'));

        app()->setLocale('dv');
        $this->assertEquals('Mee dhivehi title eh', $article->translate('title'));
        $this->assertEquals('Mee dhivehi liyumeh', $article->translate('body'));
        $this->assertNull($article->translate('slug', fallback: false));
        app()->setLocale($tmp);

        $this->assertEquals('Kore wa taitorudesu', $article->translate('title', 'jp'));
        $this->assertEquals('Kore wa kijidesu', $article->translate('body', 'jp'));
        $this->assertNull($article->translate('slug', 'jp', false));

        $this->assertNull($article->translate('slug', 'fr', false));
    }

    #[Test]
    public function it_can_translate_field_via_translate_function_without_fallback()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang'         => 'en',
            'title'        => 'This is an English title',
            'slug'         => 'this-is-an-english-slug',
            'body'         => 'This is an English body',
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug'  => 'mee-dhivehi-slug-eh',
                    'body'  => 'Mee dhivehi liyumeh',
                ],
            ]
        ]);

        $this->assertEquals('This is an English title', $article->translate('title', 'jp'));
        $this->assertEquals('this-is-an-english-slug', $article->translate('slug', 'jp'));
        $this->assertEquals('This is an English body', $article->translate('body', 'jp'));

        $this->assertNull($article->translate('title', 'jp', false));
        $this->assertNull($article->translate('slug', 'jp', false));
        $this->assertNull($article->translate('body', 'jp', false));
    }

    #[Test]
    public function it_can_translate_field_via_magic_method()
    {
        $article = Article::factory()->withAuthor()->create([
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug'  => 'mee-dhivehi-slug-eh',
                    'body'  => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug'  => 'kore-wa-namekujidesu',
                    'body'  => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $this->assertEquals('Mee dhivehi title eh', $article->title_dv);
        $this->assertEquals('Mee dhivehi liyumeh', $article->body_dv);

        // slugs should not be translated
        $this->assertNull($article->slug_dv);
    }

    #[Test]
    public function it_can_translate_field_via_locale_change()
    {
        $article = Article::factory()->withAuthor()->create([
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug'  => 'mee-dhivehi-slug-eh',
                    'body'  => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug'  => 'kore-wa-namekujidesu',
                    'body'  => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $tmp = app()->getLocale();
        app()->setLocale('dv');
        $this->assertEquals('Mee dhivehi title eh', $article->title);
        $this->assertEquals('Mee dhivehi liyumeh', $article->body);
        app()->setLocale($tmp);
    }

    #[Test]
    public function it_can_check_if_given_field_is_translatable()
    {
        $article = Article::factory()->withAuthor()->create([
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug'  => 'mee-dhivehi-slug-eh',
                    'body'  => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug'  => 'kore-wa-namekujidesu',
                    'body'  => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $this->assertTrue($article->isTranslatable('title'));
    }

    #[Test]
    public function it_can_clear_translations_for_one_locale()
    {
        $article = Article::factory()->withAuthor()->create([
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug'  => 'mee-dhivehi-slug-eh',
                    'body'  => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug'  => 'kore-wa-namekujidesu',
                    'body'  => 'Kore wa kijidesu',
                ]
            ]
        ]);

        // check if it can clear language that doesn't exist
        $article->clearTranslations('fr');

        $article->clearTranslations('dv');

        // Ensure that dv is gone while jp is still there
        $this->assertEmpty($article->title_dv);
        $this->assertEquals('Kore wa taitorudesu', $article->title_jp);
    }

    #[Test]
    public function it_can_clear_translations_for_all_locales()
    {
        $article = Article::factory()->withAuthor()->create([
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug'  => 'mee-dhivehi-slug-eh',
                    'body'  => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug'  => 'kore-wa-namekujidesu',
                    'body'  => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $article->clearTranslations();

        $this->assertEmpty($article->title_dv);
        $this->assertEmpty($article->title_jp);
    }

    #[Test]
    public function it_can_check_if_any_translation_for_a_specific_locale()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang'         => 'en',
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug'  => 'mee-dhivehi-slug-eh',
                    'body'  => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug'  => 'kore-wa-namekujidesu',
                    'body'  => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $this->assertFalse($article->hasTranslation('fr'));
        $this->assertTrue($article->hasTranslation('dv'));
        $tmp = app()->getLocale();
        app()->setLocale('en');
        $this->assertTrue($article->hasTranslation());
        app()->setLocale($tmp);
    }

    #[Test]
    public function it_can_check_if_is_default_translation_locale()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang'         => 'en',
            'translations' => [
                'dv' => [
                    'title' => 'Mee dhivehi title eh',
                    'slug'  => 'mee-dhivehi-slug-eh',
                    'body'  => 'Mee dhivehi liyumeh',
                ],
                'jp' => [
                    'title' => 'Kore wa taitorudesu',
                    'slug'  => 'kore-wa-namekujidesu',
                    'body'  => 'Kore wa kijidesu',
                ]
            ]
        ]);

        $this->assertFalse($article->isDefaultTranslationLocale('fr'));
        $this->assertTrue($article->isDefaultTranslationLocale('en'));
    }

    #[Test]
    /**
     * @throws LanguageNotAllowedException|FieldNotAllowedException
     */
    public function it_can_add_new_translation_locales()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

//        $translation = $article->addTranslation('dv', [
//            'title' => 'Mee dhivehi title eh',
//            'slug' => 'mee-dhivehi-slug-eh',
//            'body' => 'Mee dhivehi liyumeh',
//        ]);
//
//        $translation->save();

        $article->addTranslation('dv', 'title', 'Mee dhivehi title eh');

        $this->assertEquals('Mee dhivehi title eh', $article->title_dv);
    }

    #[Test]
    public function it_can_add_new_translation_locales_via_setter()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $article->title_dv = 'Mee dhivehi title eh';

        // get via locale because assertEquals complains that it'll always be true with title_dv since I just set it
        $tmp = app()->getLocale();
        app()->setLocale('dv');
        $this->assertEquals('Mee dhivehi title eh', $article->title);
        app()->setLocale($tmp);
    }

    #[Test]
    public function it_can_add_new_translation_locales_via_app_locale()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang'  => 'en',
            'title' => 'This is an English title',
        ]);

        app()->setLocale('dv');
        $article->title = 'Mee dhivehi title eh';
        app()->setLocale('en');

        app()->setLocale('dv');
        $this->assertEquals('Mee dhivehi title eh', $article->title);
        app()->setLocale('en');
        $this->assertEquals('This is an English title', $article->title);
    }

    #[Test]
    /**
     * @throws LanguageNotAllowedException|FieldNotAllowedException
     */
    public function it_can_add_translations_in_bulk()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $article->addTranslations('dv', [
            'title' => 'Mee dhivehi title eh',
            'body'  => 'Mee dhivehi liyumeh',
        ]);

        $this->assertEquals('Mee dhivehi title eh', $article->title_dv);
        $this->assertEquals('Mee dhivehi liyumeh', $article->body_dv);
    }

    #[Test]
    /**
     * @throws LanguageNotAllowedException
     */
    public function it_cannot_add_translations_in_bulk_for_locales_that_are_not_allowed()
    {
        $this->expectException(LanguageNotAllowedException::class);
        $this->expectExceptionMessage('zh-CN language not allowed');

        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $article->addTranslations('zh-CN', [
            'title' => '这是一个中文标题',
            'slug'  => '这是一只中国蛞蝓',
            'body'  => '这是一个中国人的身体',
        ]);
    }

    #[Test]
    public function it_cannot_add_translation_locales_that_are_not_allowed()
    {
        $this->expectException(LanguageNotAllowedException::class);
        $this->expectExceptionMessage('zh-CN language not allowed');

        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $article->addTranslation('zh-CN', 'title', '这是一个中文标题');
    }

    #[Test]
    public function it_cannot_add_translation_fields_that_are_not_allowed()
    {
        $this->expectException(FieldNotAllowedException::class);
        $this->expectExceptionMessage('slug field not allowed for locale dv');

        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $article->addTranslation('dv', 'slug', 'mee-dhivehi-slug-eh');
    }

    #[Test]
    public function it_can_implicitly_set_default_locale()
    {
        app()->setLocale('dv');
        $article = Article::factory()->withAuthor()->create([
            'lang' => null,
        ]);

        $article->slug = "mee-dhivehi-slug-eh";
        $article->addTranslations('dv', [
            'title' => 'Mee dhivehi title eh',
        ]);

        $this->assertEquals('dv', $article->lang);
        $this->assertEquals('Mee dhivehi title eh', $article->title);
    }

    #[Test]
    public function it_can_delete_translations_of_specific_locale()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $article->addTranslations('dv', [
            'title' => 'Mee dhivehi title eh',
        ]);

        $this->assertEquals('Mee dhivehi title eh', $article->title_dv);

        $article->deleteTranslation('dv');

        $this->assertEquals(null, $article->title_dv);
    }

    #[Test]
    public function it_can_delete_all_translations()
    {
        $article = Article::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $article->addTranslations('dv', [
            'title' => 'Mee dhivehi title eh',
        ]);

        $this->assertEquals('Mee dhivehi title eh', $article->title_dv);

        $article->deleteTranslations();

        $this->assertEquals(null, $article->title_dv);
    }

    #[Test]
    public function it_cannot_delete_primary_translation()
    {
        $this->expectException(CannotDeletePrimaryTranslationException::class);
        $article = Article::factory()->withAuthor()->create([
            'lang'  => 'en',
            'title' => 'This is an English title'
        ]);

        $article->addTranslations('dv', [
            'title' => 'Mee dhivehi title eh',
        ]);

        $this->assertEquals('This is an English title', $article->title_en);

        $article->deleteTranslation('en');
    }
}
