<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

use App\Services\GenerateID;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->string('name', 255);
            $table->string('native_name', 255);
            $table->string('locale', 255);
            $table->string('locale_code', 255);
            $table->integer('order')->default(0);
            $table->integer('enabled')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $current_date = now();

        DB::table('languages')->insert([
            [
                'aid'           => (new GenerateID())->table('languages')->get(),
                'name'          => 'Русский',
                'native_name'   => 'Русский',
                'locale'        => 'ru_RU',
                'locale_code'   => 'ru',
                'order'         => 1,
                'enabled'       => 1,
                'created_at'    => $current_date,
                'updated_at'    => $current_date,
            ],
            [
                'aid'           => (new GenerateID())->table('languages')->get(),
                'name'          => 'Английский',
                'native_name'   => 'English',
                'locale'        => 'en_US',
                'locale_code'   => 'en',
                'order'         => 2,
                'enabled'       => 1,
                'created_at'    => $current_date,
                'updated_at'    => $current_date,
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
