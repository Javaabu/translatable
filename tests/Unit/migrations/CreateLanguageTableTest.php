<?php

namespace Javaabu\Translatable\Tests\Unit\migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CreateLanguageTableTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_can_migrate_up_languages_table()
    {
        //        self::markTestIncomplete();
        $this->withoutExceptionHandling();
        $this->assertTrue(Schema::hasTable('languages'));
        $this->assertTrue(Schema::hasColumn('languages', 'name'));
        $this->assertTrue(Schema::hasColumn('languages', 'code'));
        $this->assertTrue(Schema::hasColumn('languages', 'locale'));
        $this->assertTrue(Schema::hasColumn('languages', 'active'));
        $this->assertTrue(Schema::hasColumn('languages', 'is_rtl'));
    }
}
