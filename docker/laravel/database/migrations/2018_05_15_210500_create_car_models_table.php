<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarModelsTable extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        Schema::create('car_models', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('maker_id');
            $table->string('title');
        });
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        Schema::dropIfExists('car_models');
    }
}
