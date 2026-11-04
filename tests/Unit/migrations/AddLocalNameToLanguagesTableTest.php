<?php

namespace Javaabu\Translatable\Tests\Unit\migrations;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Javaabu\Translatable\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AddLocalNameToLanguagesTableTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_adds_the_local_name_to_a_v2_languages_table(): void
    {
        Schema::table('languages', function (Blueprint $table) {
            $table->dropColumn('local_name');
        });

        $migration = require __DIR__ . '/../../../database/migrations/add_local_name_to_languages_table.php';

        $migration->up();

        $this->assertTrue(Schema::hasColumn('languages', 'local_name'));

        // Running it again (or on a new install) changes nothing
        $migration->up();
        $migration->down();

        $this->assertTrue(Schema::hasColumn('languages', 'local_name'));
    }

    #[Test]
    public function it_does_nothing_before_the_languages_table_exists(): void
    {
        Schema::drop('languages');

        $migration = require __DIR__ . '/../../../database/migrations/add_local_name_to_languages_table.php';

        $migration->up();

        $this->assertFalse(Schema::hasTable('languages'));
    }
}
