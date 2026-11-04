<?php

namespace Javaabu\Translatable\Tests\Unit;

use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class StringsTest extends TestCase
{
    public static function locales(): array
    {
        return [
            'en' => ['en'],
            'dv' => ['dv'],
        ];
    }

    #[Test]
    public function it_has_the_same_lines_in_english_and_dhivehi(): void
    {
        $en = require __DIR__ . '/../../lang/en/strings.php';
        $dv = require __DIR__ . '/../../lang/dv/strings.php';

        $this->assertEquals(array_keys($en), array_keys($dv));

        foreach ($en as $key => $line) {
            preg_match_all('/:\w+/', $line, $en_placeholders);
            preg_match_all('/:\w+/', $dv[$key], $dv_placeholders);

            sort($en_placeholders[0]);
            sort($dv_placeholders[0]);

            $this->assertEquals($en_placeholders[0], $dv_placeholders[0], "Placeholders of `{$key}` differ");
        }
    }

    #[Test]
    #[DataProvider('locales')]
    public function it_bundles_the_same_lines_for_the_react_components(string $locale): void
    {
        $php = require __DIR__ . "/../../lang/{$locale}/strings.php";
        $ts = file_get_contents(__DIR__ . "/../../resources/js/locales/{$locale}.ts");

        preg_match_all('/^\s+(\w+): "(.*)",$/m', $ts, $matches);

        $this->assertEquals($php, array_combine($matches[1], $matches[2]));
    }

    #[Test]
    public function it_translates_the_lines_into_dhivehi(): void
    {
        app()->setLocale('dv');

        $this->assertEquals('ބަސް', __('translatable::strings.language'));
        $this->assertEquals('ދިވެހި ތަރުޖަމާ ބަދަލުކުރުން', __('translatable::strings.edit_translation_in', ['locale' => 'ދިވެހި']));
    }
}
