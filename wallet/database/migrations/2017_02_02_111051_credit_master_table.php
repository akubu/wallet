<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class CreditMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_master', function (Blueprint $table) {
            $table->increments('id');
            $table->string('credit_type');
            $table->timestamps();
        });

        DB::table('credit_master')->insert([
                'credit_type'=>'prepayment'
            ]
        );
        DB::table('credit_master')->insert([
                'credit_type'=>'security_cheque'
            ]
        );
        DB::table('credit_master')->insert([
                'credit_type'=>'NBFC_approval'
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
        Schema::drop('credit_master');
        //
    }
}
