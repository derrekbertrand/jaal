<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountContactTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_contact', function (Blueprint $table) {
            $table->uuid('account_id');
            $table->uuid('contact_id');
            $table->primary(['account_id', 'contact_id']);

            $table->boolean('is_billing')->default(false);
            $table->boolean('is_point_of_contact')->default(false);
        });

        Schema::table('account_contact', function (Blueprint $table) {
            $table->foreign('account_id')
                ->references('id')->on('accounts')
                ->onDelete('cascade');

            $table->foreign('contact_id')
                ->references('id')->on('contacts')
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
        Schema::table('account_contact', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropForeign(['contact_id']);
        });

        Schema::drop('account_contact');
    }
}
