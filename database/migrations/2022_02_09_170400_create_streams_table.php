<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStreamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('streams', function (Blueprint $table) {
            $table->increments('id');            
            $table->bigInteger('stream_id')->unsigned()->index();
            $table->unsignedInteger('client_id');
            $table->string('channel_name');
            $table->string('stream_title');
            $table->string('game_name');
            $table->unsignedInteger('number_of_viewers');
            $table->dateTime('started_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('streams');
    }
    
    
    
    
}
