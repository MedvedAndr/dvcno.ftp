<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dictionaries', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->text('language_id');
            $table->string('name', 255);
            $table->text('description');
            $table->string('alias',255);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('dictionary_items', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->text('language_id');
            $table->text('dictionary_id');
            $table->string('item_key',255);
            $table->text('item_value');
            $table->text('description');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dictionaries');
        Schema::dropIfExists('dictionary_items');
    }
};
