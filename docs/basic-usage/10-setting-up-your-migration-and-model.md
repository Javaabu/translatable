---
title: Set up your Migration and Model
sidebar_position: 10
---

:::info

Translatables currently provides **two** different types of translatables, `Db` and `Json`. Check out [Advanced Usage > Difference between DB and JSON translatable](./70-difference-isdbtranslatable-isjsontranslatable) to learn the differences and design considerations for both

:::

## Setting up your migrations

If you are setting up a new model, you can simply add either `DbTranslatableSchema::columns($table);` or `JsonTranslatableSchema::columns($table);` into your migration schema create function. 

```php
use Javaabu\Translatable\DbTranslatable\DbTranslatableSchema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // ...

            DbTranslatableSchema::columns($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

Or if you have already made the model, you can write a migration to add the columns to the existing table. 

```php
use Javaabu\Translatable\DbTranslatable\DbTranslatableSchema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            DbTranslatableSchema::columns($table);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            DbTranslatableSchema::revert($table);
        });
    }
};
```

:::danger 

The revert function is **unstable** as the testing suite for this has not been written yet. Use it with caution.

:::

## Setting up your models


All you need to do is add the `Translatable` implementation using the `IsDbTranslatable` or `IsJsonTranslatable` trait.

```php
...
use Javaabu\Translatable\DbTranslatable\IsDbTranslatable;
use Javaabu\Translatable\Translatable;

class Post extends Model implements Translatable
{
    use IsDbTranslatable;

...
```

Once this is setup, you are good to go!
