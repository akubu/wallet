<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreditTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit', function (Blueprint $table) {
            $table->increments('id');
            $table->string('P2S_id');
            $table->string('credit_type');
            $table->string('credit_amount');
            $table->string('requested_by');
            $table->string('order_id');
            $table->string('NBFC_id');
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
        Schema::drop('credit');
        //
    }
}
