<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLivePlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('live_players', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 20)->default('');
            $table->string('image', 20)->default('');
            $table->tinyInteger('age', false, true)->default(0);
            $table->tinyInteger('position', false, true)->default(0);
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
        Schema::dropIfExists('live_players');
    }
}
