<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSkillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('skills', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('skill_user', function (Blueprint $table) {
            $table->unsignedInteger('skill_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->primary(['skill_id', 'user_id']);

            $table->foreign('skill_id')
                ->references('id')->on('skills')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('skill_user', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['skill_id']);
        });

        Schema::dropIfExists('skill_user');

        Schema::dropIfExists('skills');
    }
}
