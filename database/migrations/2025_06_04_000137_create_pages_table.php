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
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->string('title', 255);
            $table->text('language_id');
            $table->string('slug', 255);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->text('page_id');
            $table->string('title', 255);
            $table->text('language_id');
            $table->string('type', 255);
            $table->json('content');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pages');
        Schema::dropIfExists('sections');
    }
};
