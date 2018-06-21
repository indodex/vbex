<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('mobile');
            $table->string('id_card');
            $table->rememberToken();
            $table->tinyInteger('is_verified', false, true);
            $table->tinyInteger('is_freeze', false, true);
            $table->tinyInteger('is_lock', false, true);
            $table->tinyInteger('is_delete', false, true);
            $table->string('activation_code');
            $table->string('registere_ip');
            $table->integer('invite_uid', false, true);
            $table->decimal('money_total', 10, 2);
            $table->decimal('currency_total', 32, 18);
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
