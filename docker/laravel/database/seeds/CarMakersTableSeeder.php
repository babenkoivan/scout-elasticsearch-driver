<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarMakersTableSeeder extends Seeder
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        $table = DB::table('car_makers');

        if ($table->count() > 0) {
            return;
        }

        $table->insert([
            'id' => 1,
            'title' => 'Audi'
        ]);

        $table->insert([
            'id' => 2,
            'title' => 'BMW'
        ]);

        $table->insert([
            'id' => 3,
            'title' => 'Volkswagen'
        ]);

        $table->insert([
            'id' => 4,
            'title' => 'Volvo'
        ]);

        $table->insert([
            'id' => 5,
            'title' => 'Kia'
        ]);
    }
}
