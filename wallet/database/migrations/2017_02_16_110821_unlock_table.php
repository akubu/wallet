<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UnlockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unlock', function (Blueprint $table) {
            $table->increments('id');
            $table->string('P2S_id');
            $table->string('unlock_type');
            $table->string('unlock_amount');
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
        Schema::drop('unlock');
        //
    }
}
