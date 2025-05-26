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
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->dateTime('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->string('last_name', 255);
            $table->string('first_name', 255);
            $table->string('middle_name', 255);
            $table->string('login', 255)->unique();
            $table->string('email')->unique();
            $table->dateTime('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->string('name', 255);
            $table->text('description');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('accesses', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->string('name', 255);
            $table->text('description');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('bind_user_role', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->text('user_id');
            $table->text('role_id');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('bind_role_access', function (Blueprint $table) {
            $table->id();
            $table->text('aid')->collation('utf8_bin');
            $table->text('role_id');
            $table->text('access_id');
            $table->integer('enabled')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        $userAID        = (new GenerateID())->table('users')->get();
        $roleAID        = (new GenerateID())->table('roles')->get();
        $accessAID      = (new GenerateID())->table('accesses')->get();

        $bUserRoleAID   = (new GenerateID())->table('bind_user_role')->get();
        $bRoleAccessAID = (new GenerateID())->table('bind_role_access')->get();

        $current_date   = now();

        DB::table('users')->insert([
            [
                'aid'           => $userAID,
                'last_name'     => '',
                'first_name'    => 'Администратор',
                'middle_name'   => '',
                'login'         => 'admin',
                'email'         => 'admin@admin.ru',
                'password'      => '$2y$10$oW7IexPJMF.Idv0OttVG0OeFPDBFFhJBcm/FczlixmEbqbnszkWZi',
                'created_at'    => $current_date,
                'updated_at'    => $current_date,
            ],
        ]);

        DB::table('roles')->insert([
            [
                'aid'           => $roleAID,
                'name'          => 'admin',
                'description'   => 'Администратор',
                'created_at'    => $current_date,
                'updated_at'    => $current_date,
            ],
        ]);

        DB::table('bind_user_role')->insert([
            [
                'aid'           => $bUserRoleAID,
                'user_id'       => $userAID,
                'role_id'       => $roleAID,
                'created_at'    => $current_date,
                'updated_at'    => $current_date,
            ],
        ]);

        DB::table('accesses')->insert([
            [
                'aid'           => $accessAID,
                'name'          => 'admin_panel',
                'description'   => 'Доступ в панель администратора',
                'created_at'    => $current_date,
                'updated_at'    => $current_date,
            ],
        ]);

        DB::table('bind_role_access')->insert([
            [
                'aid'           => $bRoleAccessAID,
                'role_id'       => $roleAID,
                'access_id'     => $accessAID,
                'enabled'       => '1',
                'created_at'    => $current_date,
                'updated_at'    => $current_date,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('accesses');
        Schema::dropIfExists('bind_user_role');
        Schema::dropIfExists('bind_role_access');
    }
};
