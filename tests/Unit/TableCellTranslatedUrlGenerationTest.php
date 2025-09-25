<?php

namespace Javaabu\Translatable\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Javaabu\Translatable\Contracts\JsonTranslatable;
use Javaabu\Translatable\Facades\Languages;
use Javaabu\Translatable\Models\Language;
use Javaabu\Translatable\Tests\TestCase;
use Javaabu\Translatable\Tests\TestSupport\Models\Article;
use Javaabu\Translatable\Views\Components\TableCells;
use PHPUnit\Framework\Attributes\Test;

class TableCellTranslatedUrlGenerationTest extends TestCase
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
    public function it_can_generate_show_url_for_json_translatable_record(): void
    {
        $this->withoutExceptionHandling();

        /** @var JsonTranslatable $article */
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
            ],
        ]);

        Languages::setCurrentLocale('en');
        $tableCellComponent = new TableCells($article, create_url: 'articles/create');

        $en_url = $tableCellComponent->getUrl('show', 'en');
        $this->assertTrue(str($en_url)->contains('/en/articles/' . $article->id), 'English URL does not contain expected path.');

        $dv_url = $tableCellComponent->getUrl('show', 'dv');
        $this->assertTrue(str($dv_url)->contains('/dv/articles/' . $article->id), 'Dhivehi URL does not contain expected path.');
    }
}
