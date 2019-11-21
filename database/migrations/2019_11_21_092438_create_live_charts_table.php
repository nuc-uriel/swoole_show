<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveChartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('live_charts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id', false, true)->default(0);
            $table->tinyInteger('user_id', false, true)->default(0);
            $table->string('content', 200)->default('');
            $table->tinyInteger('status', false, true)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('live_charts');
    }
}
