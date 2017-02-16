<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class LockMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lock_master',function (Blueprint $table){
           $table->increments('id');
           $table->string('lock_type');
        });

        DB::table('lock_master')->insert([
                'lock_type'=>'Lock by the SMT'
            ]
        );
        DB::table('lock_master')->insert([
                'lock_type'=>'lock by the NBFC/Finance'
            ]
        );
        DB::table('lock_master')->insert([
                'lock_type'=>'lock by other reasons'
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
        Schema::drop('lock_master');
        //
    }
}
