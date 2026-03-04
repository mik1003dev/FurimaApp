<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // id: bigint unsigned（主キー）
            $table->id();

            // name: varchar(255)
            $table->string('name');

            // email: varchar(255) unique
            $table->string('email')->unique();

            // email_verified_at: timestamp
            $table->timestamp('email_verified_at')->nullable();

            // password: varchar(255)
            $table->string('password');

            // remember_token: varchar(100)
            $table->rememberToken();

            // avatar_path: varchar(255)
            $table->string('avatar_path')->nullable();

            // postal_code: varchar(20)
            $table->string('postal_code', 20)->nullable();

            // address: varchar(255)
            $table->string('address')->nullable();

            // building: varchar(255)
            $table->string('building')->nullable();

            // created_at, updated_at: timestamp
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
