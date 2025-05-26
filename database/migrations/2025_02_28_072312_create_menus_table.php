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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->text('language_id');
            $table->string('title', 255);
            $table->text('description');
            $table->string('alias', 255);
            $table->integer('enabled')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->text('language_id');
            $table->text('menu_id');
            $table->text('parent_id')->nullable();
            $table->text('item_type');
            $table->text('item_id')->nullable();
            $table->string('title', 255);
            $table->text('url')->nullable();
            $table->string('icon', 255)->nullable();
            $table->json('access_roles')->nullable();
            $table->integer('order')->default(0);
            $table->integer('enabled')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        // Допилить в будущем привязку к ролям юзеров для видимости пунктов меню в зависимости от роли
        // Возможно проще сделать через json поле в БД
        // Schema::create('bind_menu_item_role', function (Blueprint $table) {

        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
        Schema::dropIfExists('menu_items');
    }
};
