<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Javaabu\Translatable\Tests\TestSupport\Models\Author;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            $table->string('title');
            $table->string('slug');
            $table->text('body');
            $table->foreignIdFor(Author::class);

            $table->dbTranslatable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        // This is how I get code coverage
        Schema::table('posts', function (Blueprint $table) {
            $table->dropDbTranslatable();
        });

        Schema::dropIfExists('posts');
    }
};
