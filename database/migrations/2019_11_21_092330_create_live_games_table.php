<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveGamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('live_games', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('a_id', false, true)->default(0);
            $table->tinyInteger('b_id', false, true)->default(0);
            $table->integer('a_score', false, true)->default(0);
            $table->integer('b_score', false, true)->default(0);
            $table->string('narrator', 20)->default('');
            $table->string('image', 20)->default('');
            $table->timestamp('start_time')->nullable();
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
        Schema::dropIfExists('live_games');
    }
}
