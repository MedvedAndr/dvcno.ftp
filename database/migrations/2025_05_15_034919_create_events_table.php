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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->text('language_id');
            $table->string('slug', 255);
            $table->string('title', 255);
            $table->text('description');
            $table->text('content');
            $table->text('thumbnail');
            $table->text('address');
            $table->string('link_to_map', 255);
            $table->integer('enabled')->default(1);
            $table->dateTime('date_event');
            $table->dateTime('date_from')->nullable();
            $table->dateTime('date_to')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('bind_event_user', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->text('event_id');
            $table->text('user_id')->nullable();
            $table->json('user_values')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('bind_event_user');
    }
};
