<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration {
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('author_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('slug');
            $table->text('body');

            $table->jsonTranslatable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('articles', function(Blueprint $table) {
            $table->dropJsonTranslatable();
        });
        Schema::dropIfExists('articles');
    }
}
