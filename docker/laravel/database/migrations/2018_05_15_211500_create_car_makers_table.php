<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarMakersTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        Schema::create('car_makers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
        });
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        Schema::dropIfExists('car_makers');
    }
}
