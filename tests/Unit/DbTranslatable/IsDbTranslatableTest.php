<?php

namespace Javaabu\Translatable\Tests\Unit\DbTranslatable;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Translatable\Exceptions\CannotDeletePrimaryTranslationException;
use Javaabu\Translatable\Exceptions\FieldNotAllowedException;
use Javaabu\Translatable\Exceptions\LanguageNotAllowedException;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Tests\TestCase;
use Javaabu\Translatable\Tests\TestSupport\Models\Post;
use PHPUnit\Framework\Attributes\Test;

class IsDbTranslatableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
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
        $post = new Post();

        $this->assertEquals([
            'id',
            'translatable_parent_id',
            'lang',
            'created_at',
            'updated_at',
            'deleted_at',
        ], $post->getFieldsIgnoredForTranslation());
    }

    #[Test]
    public function it_can_get_translatable_fields()
    {
        $post = new Post();

        $this->assertEquals([
            'title',
            'body',
        ], $post->getTranslatables());
    }

    #[Test]
    public function it_can_get_non_translatable_fields()
    {
        $post = new Post();

        $this->assertEquals([
            'slug',
        ], $post->getNonTranslatables());
    }

    #[Test]
    public function it_can_get_non_translatable_pivots()
    {
        $post = new Post();

        $this->assertEquals([
            'author_id',
        ], $post->getNonTranslatablePivots());
    }

    #[Test]
    public function it_can_get_all_non_translatable_fields()
    {
        $post = new Post();

        $this->assertEquals([
            'slug',
            'id',
            'translatable_parent_id',
            'lang',
            'created_at',
            'updated_at',
            'deleted_at',
            'author_id',
        ], $post->getAllNonTranslatables());
    }

    #[Test]
    public function it_can_check_if_is_a_non_translatable_pivot()
    {
        $post = new Post();

        $this->assertFalse($post->isNonTranslatablePivot('id'));
        $this->assertTrue($post->isNonTranslatablePivot('author_id'));
    }

    #[Test]
    public function it_can_get_the_default_translation_locale()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $this->assertFalse($post->isDefaultTranslationLocale('fr'));
        $this->assertTrue($post->isDefaultTranslationLocale('en'));
    }

    #[Test]
    public function it_can_get_allowed_translation_locales()
    {
        $post = Post::factory()->withAuthor()->create();

        $this->assertEquals(['en', 'dv', 'jp'], $post->getAllowedTranslationLocales());
    }

    #[Test]
    public function it_can_check_if_given_locale_is_allowed()
    {
        $post = Post::factory()->withAuthor()->create();

        $this->assertTrue($post->isAllowedTranslationLocale('en'));
        $this->assertTrue($post->isAllowedTranslationLocale('dv'));
        $this->assertFalse($post->isAllowedTranslationLocale('fr'));
    }

    #[Test]
    public function it_can_translate_field_via_translate_function()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang'  => 'en',
            'title' => 'This is an English title',
            'slug'  => 'this-is-an-english-slug',
            'body'  => 'This is an English body',
        ]);
        $post_dv = Post::factory()->withAuthor()->create([
            'lang'                   => 'dv',
            'title'                  => 'Mee dhivehi title eh',
            'slug'                   => 'mee-dhivehi-slug-eh',
            'body'                   => 'Mee dhivehi liyumeh',
            'translatable_parent_id' => $post->id,
        ]);
        $post_jp = Post::factory()->withAuthor()->create([
            'lang'                   => 'jp',
            'title'                  => 'Kore wa taitorudesu',
            'slug'                   => 'kore-wa-namekujidesu',
            'body'                   => 'Kore wa kijidesu',
            'translatable_parent_id' => $post->id,
        ]);

        // it can fetch translations from the default post
        $this->assertEquals('Mee dhivehi title eh', $post->translate('title', 'dv'));
        $this->assertEquals('Mee dhivehi liyumeh', $post->translate('body', 'dv'));
        $this->assertNull($post->translate('slug', 'dv', false));

        // it can fetch translations from a translatable post row as well
        $this->assertEquals('Mee dhivehi title eh', $post_jp->translate('title', 'dv'));
        $this->assertEquals('Mee dhivehi liyumeh', $post_jp->translate('body', 'dv'));

        // it can fetch translation from default translation using a translatable post row as well
        $this->assertEquals('This is an English title', $post_jp->translate('title', 'en'));
        $this->assertEquals('This is an English body', $post_jp->translate('body', 'en'));

        $tmp = app()->getLocale();
        app()->setLocale('en');
        $this->assertEquals('This is an English title', $post->translate('title'));
        $this->assertEquals('This is an English body', $post->translate('body'));
        $this->assertEquals('this-is-an-english-slug', $post->translate('slug'));

        app()->setLocale('dv');
        $this->assertEquals('Mee dhivehi title eh', $post->translate('title'));
        $this->assertEquals('Mee dhivehi liyumeh', $post->translate('body'));
        $this->assertNull($post->translate('slug', fallback: false));
        app()->setLocale($tmp);

        $this->assertEquals('Kore wa taitorudesu', $post->translate('title', 'jp'));
        $this->assertEquals('Kore wa kijidesu', $post->translate('body', 'jp'));
        $this->assertNull($post->translate('slug', 'jp', false));

        $this->assertNull($post->translate('slug', 'fr', false));
    }

    #[Test]
    public function it_can_translate_field_via_translate_function_without_fallback()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang'  => 'en',
            'title' => 'This is an English title',
            'slug'  => 'this-is-an-english-slug',
            'body'  => 'This is an English body',
        ]);
        $post_dv = Post::factory()->withAuthor()->create([
            'lang'                   => 'dv',
            'title'                  => 'Mee dhivehi title eh',
            'slug'                   => 'mee-dhivehi-slug-eh',
            'body'                   => 'Mee dhivehi liyumeh',
            'translatable_parent_id' => $post->id,
        ]);

        $this->assertEquals('This is an English title', $post->translate('title', 'jp'));
        $this->assertEquals('this-is-an-english-slug', $post->translate('slug', 'jp'));
        $this->assertEquals('This is an English body', $post->translate('body', 'jp'));

        $this->assertNull($post->translate('title', 'jp', false));
        $this->assertNull($post->translate('slug', 'jp', false));
        $this->assertNull($post->translate('body', 'jp', false));
    }

    #[Test]
    public function it_can_translate_field_via_magic_method()
    {
        $post = Post::factory()->withAuthor()->create();
        $post_dv = Post::factory()->withAuthor()->create([
            'lang'                   => 'dv',
            'title'                  => 'Mee dhivehi title eh',
            'slug'                   => 'mee-dhivehi-slug-eh',
            'body'                   => 'Mee dhivehi liyumeh',
            'translatable_parent_id' => $post->id,
        ]);
        $post_jp = Post::factory()->withAuthor()->create([
            'lang'                   => 'jp',
            'title'                  => 'Kore wa taitorudesu',
            'slug'                   => 'kore-wa-namekujidesu',
            'body'                   => 'Kore wa kijidesu',
            'translatable_parent_id' => $post->id,
        ]);

        $this->assertEquals('Mee dhivehi title eh', $post->title_dv);
        $this->assertEquals('Mee dhivehi liyumeh', $post->body_dv);

        // slugs should not be translated
        $this->assertNull($post->slug_dv);
    }

    #[Test]
    public function it_can_translate_field_via_locale_change()
    {
        $post = Post::factory()->withAuthor()->create();
        $post_dv = Post::factory()->withAuthor()->create([
            'lang'                   => 'dv',
            'title'                  => 'Mee dhivehi title eh',
            'slug'                   => 'mee-dhivehi-slug-eh',
            'body'                   => 'Mee dhivehi liyumeh',
            'translatable_parent_id' => $post->id,
        ]);
        $post_jp = Post::factory()->withAuthor()->create([
            'lang'                   => 'jp',
            'title'                  => 'Kore wa taitorudesu',
            'slug'                   => 'kore-wa-namekujidesu',
            'body'                   => 'Kore wa kijidesu',
            'translatable_parent_id' => $post->id,
        ]);

        $tmp = app()->getLocale();
        app()->setLocale('dv');
        $this->assertEquals('Mee dhivehi title eh', $post->title);
        $this->assertEquals('Mee dhivehi liyumeh', $post->body);
        app()->setLocale($tmp);
    }

    #[Test]
    public function it_can_translate_fields_via_compoships()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);
        $post_dv = Post::factory()->withAuthor()->create([
            'lang'                   => 'dv',
            'title'                  => 'Mee dhivehi title eh',
            'slug'                   => 'mee-dhivehi-slug-eh',
            'body'                   => 'Mee dhivehi liyumeh',
            'translatable_parent_id' => $post->id,
        ]);
        $post_jp = Post::factory()->withAuthor()->create([
            'lang'                   => 'jp',
            'title'                  => 'Kore wa taitorudesu',
            'slug'                   => 'kore-wa-namekujidesu',
            'body'                   => 'Kore wa kijidesu',
            'translatable_parent_id' => $post->id,
        ]);

        $tmp = app()->getLocale();
        app()->setLocale('dv');
        [$title, $body] = $post->getAttribute(['title', 'body']);
        $this->assertEquals('Mee dhivehi title eh', $title);
        $this->assertEquals('Mee dhivehi liyumeh', $body);
        app()->setLocale($tmp);
    }

    #[Test]
    public function it_can_check_if_given_field_is_translatable()
    {
        $post = Post::factory()->withAuthor()->create();
        $post_dv = Post::factory()->withAuthor()->create([
            'lang'                   => 'dv',
            'title'                  => 'Mee dhivehi title eh',
            'slug'                   => 'mee-dhivehi-slug-eh',
            'body'                   => 'Mee dhivehi liyumeh',
            'translatable_parent_id' => $post->id,
        ]);
        $post_jp = Post::factory()->withAuthor()->create([
            'lang'                   => 'jp',
            'title'                  => 'Kore wa taitorudesu',
            'slug'                   => 'kore-wa-namekujidesu',
            'body'                   => 'Kore wa kijidesu',
            'translatable_parent_id' => $post->id,
        ]);

        $this->assertTrue($post->isTranslatable('title'));
    }

    #[Test]
    public function it_can_clear_translations_for_one_locale()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);
        $post_dv = Post::factory()->withAuthor()->create([
            'lang'                   => 'dv',
            'title'                  => 'Mee dhivehi title eh',
            'slug'                   => 'mee-dhivehi-slug-eh',
            'body'                   => 'Mee dhivehi liyumeh',
            'translatable_parent_id' => $post->id,
        ]);
        $post_jp = Post::factory()->withAuthor()->create([
            'lang'                   => 'jp',
            'title'                  => 'Kore wa taitorudesu',
            'slug'                   => 'kore-wa-namekujidesu',
            'body'                   => 'Kore wa kijidesu',
            'translatable_parent_id' => $post->id,
        ]);

        $post->clearTranslations('dv');

        // Ensure that dv is gone while jp is still there
        $this->assertEmpty($post?->title_dv);
        $this->assertEquals('Kore wa taitorudesu', $post->title_jp);
    }

    #[Test]
    public function it_can_clear_translations_for_default_locale()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);
        $post_dv = Post::factory()->withAuthor()->create([
            'lang'                   => 'dv',
            'title'                  => 'Mee dhivehi title eh',
            'slug'                   => 'mee-dhivehi-slug-eh',
            'body'                   => 'Mee dhivehi liyumeh',
            'translatable_parent_id' => $post->id,
        ]);
        $post_jp = Post::factory()->withAuthor()->create([
            'lang'                   => 'jp',
            'title'                  => 'Kore wa taitorudesu',
            'slug'                   => 'kore-wa-namekujidesu',
            'body'                   => 'Kore wa kijidesu',
            'translatable_parent_id' => $post->id,
        ]);

        $post->clearTranslations('en');

        // refresh post on memory
        $post = Post::find($post->id);

        // Ensure that everything is gone because it's the main language
        $this->assertEmpty($post?->title_en);
        $this->assertEmpty($post?->title_jp);
    }

    #[Test]
    public function it_can_clear_translations_for_locale_via_translatable_parent()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);
        $post_dv = Post::factory()->withAuthor()->create([
            'lang'                   => 'dv',
            'title'                  => 'Mee dhivehi title eh',
            'slug'                   => 'mee-dhivehi-slug-eh',
            'body'                   => 'Mee dhivehi liyumeh',
            'translatable_parent_id' => $post->id,
        ]);
        $post_jp = Post::factory()->withAuthor()->create([
            'lang'                   => 'jp',
            'title'                  => 'Kore wa taitorudesu',
            'slug'                   => 'kore-wa-namekujidesu',
            'body'                   => 'Kore wa kijidesu',
            'translatable_parent_id' => $post->id,
        ]);

        $post_jp->clearTranslations('en');

        // refresh post on memory
        $post = Post::find($post->id);

        // Ensure that everything is gone because it's the main language
        $this->assertEmpty($post?->title_en);
        $this->assertEmpty($post?->title_jp);
    }

    #[Test]
    public function it_can_clear_translations_for_all_locales()
    {
        $post = Post::factory()->withAuthor()->create();
        $post_dv = Post::factory()->withAuthor()->create([
            'lang'                   => 'dv',
            'title'                  => 'Mee dhivehi title eh',
            'slug'                   => 'mee-dhivehi-slug-eh',
            'body'                   => 'Mee dhivehi liyumeh',
            'translatable_parent_id' => $post->id,
        ]);
        $post_jp = Post::factory()->withAuthor()->create([
            'lang'                   => 'jp',
            'title'                  => 'Kore wa taitorudesu',
            'slug'                   => 'kore-wa-namekujidesu',
            'body'                   => 'Kore wa kijidesu',
            'translatable_parent_id' => $post->id,
        ]);

        $post->clearTranslations();

        $this->assertEmpty($post->title_dv);
        $this->assertEmpty($post->title_jp);
    }

    #[Test]
    public function it_can_check_if_any_translation_for_a_specific_locale()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);
        $post_dv = Post::factory()->withAuthor()->create([
            'lang'                   => 'dv',
            'title'                  => 'Mee dhivehi title eh',
            'slug'                   => 'mee-dhivehi-slug-eh',
            'body'                   => 'Mee dhivehi liyumeh',
            'translatable_parent_id' => $post->id,
        ]);
        $post_jp = Post::factory()->withAuthor()->create([
            'lang'                   => 'jp',
            'title'                  => 'Kore wa taitorudesu',
            'slug'                   => 'kore-wa-namekujidesu',
            'body'                   => 'Kore wa kijidesu',
            'translatable_parent_id' => $post->id,
        ]);

        $this->assertFalse($post->hasTranslation('fr'));
        $this->assertTrue($post_jp->hasTranslation('dv'));
        $this->assertTrue($post->hasTranslation('en'));
        $tmp = app()->getLocale();
        app()->setLocale('dv');
        $this->assertTrue($post->hasTranslation());
        app()->setLocale($tmp);
    }

    #[Test]
    public function it_can_check_if_is_default_translation_locale()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);
        $post_dv = Post::factory()->withAuthor()->create([
            'lang'                   => 'dv',
            'title'                  => 'Mee dhivehi title eh',
            'slug'                   => 'mee-dhivehi-slug-eh',
            'body'                   => 'Mee dhivehi liyumeh',
            'translatable_parent_id' => $post->id,
        ]);
        $post_jp = Post::factory()->withAuthor()->create([
            'lang'                   => 'jp',
            'title'                  => 'Kore wa taitorudesu',
            'slug'                   => 'kore-wa-namekujidesu',
            'body'                   => 'Kore wa kijidesu',
            'translatable_parent_id' => $post->id,
        ]);

        $this->assertFalse($post->isDefaultTranslationLocale('fr'));
        $this->assertTrue($post->isDefaultTranslationLocale('en'));
    }

    #[Test]
    /**
     * @throws LanguageNotAllowedException
     * @throws FieldNotAllowedException
     */
    public function it_can_add_new_translation_locales()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        //        $translation = $article->addTranslation('dv', [
        //            'title' => 'Mee dhivehi title eh',
        //            'slug' => 'mee-dhivehi-slug-eh',
        //            'body' => 'Mee dhivehi liyumeh',
        //        ]);
        //
        //        $translation->save();

        $post->addTranslation('dv', 'title', 'Mee dhivehi title eh');

        $this->assertEquals('Mee dhivehi title eh', $post->title_dv);
    }

    #[Test]
    public function it_can_add_new_translation_locales_via_setter()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $post->title_dv = 'Mee dhivehi title eh';

        // get via locale because assertEquals complains that it'll always be true with title_dv since I just set it
        $tmp = app()->getLocale();
        app()->setLocale('dv');
        $this->assertEquals('Mee dhivehi title eh', $post->title);
        app()->setLocale($tmp);
    }

    #[Test]
    public function it_can_add_new_translation_locales_via_app_locale()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang'  => 'en',
            'title' => 'This is an English title',
        ]);

        app()->setLocale('dv');
        $post->title = 'Mee dhivehi title eh';

        app()->setLocale('dv');
        $this->assertEquals('Mee dhivehi title eh', $post->title);
        app()->setLocale('en');
        $this->assertEquals('This is an English title', $post->title);
    }

    #[Test]
    /**
     * @throws LanguageNotAllowedException
     * @throws FieldNotAllowedException
     */
    public function it_can_add_translations_in_bulk()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $post->addTranslations('dv', [
            'title' => 'Mee dhivehi title eh',
            'body'  => 'Mee dhivehi liyumeh',
        ]);

        $this->assertEquals('Mee dhivehi title eh', $post->title_dv);
        $this->assertEquals('Mee dhivehi liyumeh', $post->body_dv);
    }

    #[Test]
    /**
     * @throws LanguageNotAllowedException
     * @throws FieldNotAllowedException
     */
    public function it_cannot_add_translations_in_bulk_for_locales_that_are_not_allowed()
    {
        $this->expectException(LanguageNotAllowedException::class);
        $this->expectExceptionMessage('zh-CN language not allowed');

        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $post->addTranslations('zh-CN', [
            'title' => '这是一个中文标题',
            'slug'  => '这是一只中国蛞蝓',
            'body'  => '这是一个中国人的身体',
        ]);
    }

    #[Test]
    public function it_can_add_new_translation_to_default_translation()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $post->title_en = 'This is an English title';

        app()->setLocale('en');
        $this->assertEquals('This is an English title', $post->title);
    }

    #[Test]
    public function it_cannot_add_translation_locales_that_are_not_allowed()
    {
        $this->expectException(LanguageNotAllowedException::class);
        $this->expectExceptionMessage('zh-CN language not allowed');

        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $post->addTranslation('zh-CN', 'title', '这是一个中文标题');
    }

    #[Test]
    public function it_cannot_add_translation_fields_that_are_not_allowed()
    {
        $this->expectException(FieldNotAllowedException::class);
        $this->expectExceptionMessage('slug field not allowed for locale dv');

        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $post->addTranslation('dv', 'slug', 'mee-dhivehi-slug-eh');
    }

    #[Test]
    public function it_can_implicitly_set_default_locale()
    {
        app()->setLocale('dv');
        $post = Post::factory()->withAuthor()->create([
            'lang' => null,
        ]);

        $post->slug = 'mee-dhivehi-slug-eh';
        $post->title = 'Mee dhivehi title eh';

        $this->assertEquals('dv', $post->lang);
        $this->assertEquals('Mee dhivehi title eh', $post->title);
    }

    #[Test]
    public function it_can_delete_translations_of_specific_locale()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $post->addTranslations('dv', [
            'title' => 'Mee dhivehi title eh',
        ]);

        $this->assertEquals('Mee dhivehi title eh', $post->title_dv);

        $post->deleteTranslation('dv');

        $this->assertEquals(null, $post->title_dv);
    }

    #[Test]
    public function it_can_delete_all_translations()
    {
        $post = Post::factory()->withAuthor()->create([
            'lang' => 'en',
        ]);

        $post->addTranslations('dv', [
            'title' => 'Mee dhivehi title eh',
        ]);

        $this->assertEquals('Mee dhivehi title eh', $post->title_dv);

        $post->deleteTranslations();

        $this->assertEquals(null, $post->title_dv);
    }

    #[Test]
    public function it_cannot_delete_primary_translation()
    {
        $this->expectException(CannotDeletePrimaryTranslationException::class);
        $post = Post::factory()->withAuthor()->create([
            'lang'  => 'en',
            'title' => 'This is an English title',
        ]);

        $post->addTranslations('dv', [
            'title' => 'Mee dhivehi title eh',
        ]);

        $this->assertEquals('This is an English title', $post->title_en);

        $post->deleteTranslation('en');
    }
}
