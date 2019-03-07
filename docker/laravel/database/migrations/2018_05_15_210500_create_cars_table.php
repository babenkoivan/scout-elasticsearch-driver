<?php

use App\Stubs\CarIndexConfigurator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCarsTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        Artisan::call(
            'elastic:create-index',
            ['index-configurator' => CarIndexConfigurator::class]
        );

        Schema::create('cars', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('maker_id');
            $table->string('title');
            $table->timestamps();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        Artisan::call(
            'elastic:drop-index',
            ['index-configurator' => CarIndexConfigurator::class]
        );

        Schema::dropIfExists('cars');
    }
}
