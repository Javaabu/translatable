---
title: Installation & Setup
sidebar_position: 1.2
---

# Installation & Setup

Getting started with Translatable is a straightforward process. This guide will walk you through installing the package, configuring it, and setting up your models to be translatable.

## 1. Installation

First, pull the package into your project using Composer:

```bash
composer require javaabu/translatable
```

Laravel's package auto-discovery feature will automatically register the necessary service provider.

## 2. Publishing Assets

Next, you'll need to publish the package's assets. This includes migrations, configuration files, and optional views.

### Migrations

Publish the migrations file, which will add a `languages` table to your database:

```bash
php artisan vendor:publish --provider="Javaabu\Translatable\TranslatableServiceProvider" --tag="translatable-migrations"
```

After publishing, run the migration to create the table:

```bash
php artisan migrate
```

### Configuration File (Optional)

If you need to customize the package's behavior, you can publish the configuration file:

```bash
php artisan vendor:publish --provider="Javaabu\Translatable\TranslatableServiceProvider" --tag="translatable-config"
```

This will create a `config/translatable.php` file. Here's an overview of the available options:

```php title="config/translatable.php"
return [
    // Specify a custom model for languages if you need to extend the default one.
    'language_model'                 => Javaabu\Translatable\Models\Language::class,

    // Define fields that should never be translated on any model.
    'fields_ignored_for_translation' => [
        'id',
        'lang',
        'created_at',
        'updated_at',
        'deleted_at',
    ],

    // Set to `true` if you want suffixed attributes (e.g., `title_dv`) to fall back
    // to the default application locale if a translation doesn't exist.
    'lang_suffix_should_fallback'    => false,

    // The default locale to use before any translations are added.
    'default_locale'                 => 'en',

    // Cache configuration for the Language Registrar, which stores language lists.
    'cache'                          => [
        'expiration_time' => DateInterval::createFromDateString('24 hours'),
        'key'             => 'translation.languages.cache',
        'driver'          => 'default',
    ],

    // CSS classes for styling the Blade components.
    'styles' => [
        'table-cell-wrapper' => 'd-flex justify-content-center align-items-center',

        'icons'              => [
            'add'  => 'fas fa-plus',
            'edit' => 'fas fa-edit',
        ],
    ],
];
```

## 3. Making Your Models Translatable

Now for the fun part! To make an Eloquent model translatable, you need to perform two steps: update its migration and add a trait to the model itself.

Translatable offers two strategies for storing translations:

- **Database-based (`DbTranslatable`):** Stores each language as a new row in the model's table. Better for complex queries and indexing.
- **JSON-based (`JsonTranslatable`):** Stores all translations in a single JSON column. Simpler and requires fewer database queries.

:::tip
To understand the pros and cons of each approach, check out the [Difference between DB and JSON translatable](./basic-usage/difference-isdbtranslatable-isjsontranslatable.md) page.
:::

### Step 3.1: Update the Migration

You need to add the necessary columns to your model's table. Choose **one** of the following methods.

```php title="database/migrations/your_create_model_table.php"
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            // ... other columns

            // Choose one of the following:
            $table->dbTranslatable();
            // OR
            $table->jsonTranslatable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

If you have an existing table, you can create a new migration to add the columns:

```php
Schema::table('posts', function (Blueprint $table) {
    // Choose one
    $table->dbTranslatable();
    // OR
    $table->jsonTranslatable();
});
```

### Step 3.2: Update the Model

Finally, implement the correct contract and add the corresponding trait to your model. Again, choose the setup that matches the migration you created.

#### For DB-based Translations

If you used `$table->dbTranslatable()`, your model should implement the `DbTranslatable` contract and use the `IsDbTranslatable` trait.

```php title="app/Models/Post.php"
use Illuminate\Database\Eloquent\Model;
use Javaabu\Translatable\Contracts\DbTranslatable as DbTranslatableContract;
use Javaabu\Translatable\DbTranslatable\IsDbTranslatable;

class Post extends Model implements DbTranslatableContract
{
    use IsDbTranslatable;

    // ... your model properties

    // IMPORTANT: Add the fields you want to translate here
    public array $translatable = ['title', 'content'];
}
```

#### For JSON-based Translations

If you used `$table->jsonTranslatable()`, your model should implement the `JsonTranslatable` contract and use the `IsJsonTranslatable` trait.

```php title="app/Models/Post.php"
use Illuminate\Database\Eloquent\Model;
use Javaabu\Translatable\Contracts\JsonTranslatable as JsonTranslatableContract;
use Javaabu\Translatable\JsonTranslatable\IsJsonTranslatable;

class Post extends Model implements JsonTranslatableContract
{
    use IsJsonTranslatable;

    // ... your model properties

    // IMPORTANT: Add the fields you want to translate here
    public array $translatable = ['title', 'content'];
}
```

And that's it! Your model is now set up for translations. You can start adding and fetching translated attributes right away.
