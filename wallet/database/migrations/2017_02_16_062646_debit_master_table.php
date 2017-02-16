<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DebitMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('debit_master', function (Blueprint $table) {
            $table->increments('id');
            $table->string('debit_type');
            $table->timestamps();
        });

        DB::table('debit_master')->insert([
                'debit_type'=>'postpayment'
            ]
        );
        DB::table('debit_master')->insert([
                'debit_type'=>'payment_against_current_order'
            ]
        );

        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('debit_master');
        //
    }
}
