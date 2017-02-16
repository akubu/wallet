<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lock', function (Blueprint $table) {
            $table->increments('id');
            $table->string('P2S_id');
            $table->string('lock_type');
            $table->string('lock_amount');
            $table->string('requested_by');
            $table->timestamps();
        });
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('lock');
        //
    }
}
