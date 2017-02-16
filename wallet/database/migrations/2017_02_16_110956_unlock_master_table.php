<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UnlockMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unlock_master',function (Blueprint $table){
            $table->increments('id');
            $table->string('unlock_type');
        });

        DB::table('unlock_master')->insert([
                'unlock_type'=>'Unlock by the SMT'
            ]
        );
        DB::table('unlock_master')->insert([
                'unlock_type'=>'unlock by the NBFC/Finance'
            ]
        );
        DB::table('unlock_master')->insert([
                'unlock_type'=>'unlock by other reasons'
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
        Schema::drop('unlock_master');
        //
    }
}
