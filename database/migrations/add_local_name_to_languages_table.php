<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Upgrading from v2: adds the language's own name (ދިވެހި), shown by the language switcher.
 * New installs get the column from create_languages_table, so this does nothing there.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('languages') || Schema::hasColumn('languages', 'local_name')) {
            return;
        }

        Schema::table('languages', function (Blueprint $table) {
            $table->string('local_name')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        // The column belongs to create_languages_table on new installs, so it stays
    }
};
