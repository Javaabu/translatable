---
title: Introduction
sidebar_position: 1.0
---

# Translatable

:::danger

This package is currently under development. If anything works, that's a surprise.

:::

[Translatable](https://github.com/Javaabu/translatable) adds multi-lingual to Laravel models. 

To get started with this package, you can simply add `DbTranslatableSchema::columns($table);` to your migration `up` function.

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Javaabu\Translatable\DbTranslatable\DbTranslatableSchema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            ...

            DbTranslatableSchema::columns($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

:::danger

This function is not currently implemented but is in the plans at this moment.

> We also provide a `DBTranslatableSchema::revert($table)` function to put in the `down` function, this isn't necessary but it's good to have.

:::

And then all you need to do is add the `Translatable` implementation using the `IsDbTranslatable` or `IsJsonTranslatable` trait.

```php
...

class Post extends Model implements Translatable
{
    use IsDbTranslatable;

...
```

> Differences between `IsDbTranslatable` and `IsJsonTranslatable` are listed in []

Now, your models will automatically be translated according to the current `app()->getLocale()`. To add different translations, all you need to do is 

```php
// to add title for dv language
$post->title_dv = "Mee Dhivehi title eh";
```

> If adding translations give an error, make sure the locale is allowed in `allowed_translation_locales` in `config/translatable.php`. Check out [Installation and Setup > Publishing the config file](./installation-and-setup.md#publishing-the-config-file) for information on how to setup your config file. 
