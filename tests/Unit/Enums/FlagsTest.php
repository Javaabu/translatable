<?php

namespace Javaabu\Translatable\Tests\Unit\Enums;

use Javaabu\Translatable\Enums\Flags;
use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FlagsTest extends TestCase
{
    #[Test]
    public function it_can_get_flag_url(): void
    {
        $this->assertEquals(asset('vendors/flags/') . '/en.svg', Flags::getFlagUrl('en'));
    }

    #[Test]
    public function it_can_list_all_flags()
    {
        $flags = Flags::listFlags();

        $this->assertEquals('mv', $flags['mv']['code']);
        $this->assertEquals('Maldives (MV)', $flags['mv']['name']);
    }
}
