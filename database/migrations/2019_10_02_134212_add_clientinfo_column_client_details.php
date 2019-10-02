<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientinfoColumnClientDetails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('client_details', function (Blueprint $table) {
            $table->string('phone_number')->nullable();
            $table->text('ext')->nullable();
            $table->string('fax')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('client_details', function (Blueprint $table) {
            $table->removeColumn('phone_number');
            $table->removeColumn('ext');
            $table->removeColumn('fax');
            $table->removeColumn('city');
            $table->removeColumn('state');
            $table->removeColumn('zip');

        });
    }
}
