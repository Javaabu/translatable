<?php

namespace Javaabu\Translatable\Tests\Unit;

use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class DocsTest extends TestCase
{
    protected string $root = __DIR__ . '/../..';

    /**
     * @return array<int, string>
     */
    protected function pages(): array
    {
        $pages = [];

        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator("{$this->root}/docs")) as $file) {
            if ($file->getExtension() === 'md') {
                $pages[] = str_replace(realpath($this->root) . '/', '', $file->getRealPath());
            }
        }

        sort($pages);

        return $pages;
    }

    #[Test]
    public function it_indexes_every_docs_page_in_llms_txt(): void
    {
        preg_match_all('/\]\((docs\/[^)#]+)\)/', file_get_contents("{$this->root}/llms.txt"), $matches);

        $indexed = $matches[1];
        sort($indexed);

        $this->assertEquals($this->pages(), $indexed);
    }

    #[Test]
    public function it_only_links_to_pages_that_exist(): void
    {
        $files = [...$this->pages(), 'README.md', 'llms.txt', 'resources/boost/skills/javaabu-translatable/SKILL.md'];

        foreach ($files as $file) {
            $path = "{$this->root}/{$file}";

            if (! file_exists($path)) {
                continue;
            }

            preg_match_all('/\]\(([^)\s]+)\)/', file_get_contents($path), $matches);

            foreach ($matches[1] as $link) {
                if (preg_match('/^(https?:|mailto:|#|\.\.\/\.\.\/)/', $link)) {
                    continue;
                }

                $target = dirname($path) . '/' . explode('#', $link)[0];

                $this->assertFileExists($target, "{$file} links to {$link}");
            }
        }
    }
}
