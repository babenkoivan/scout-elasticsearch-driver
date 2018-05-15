<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarModelsTableSeeder extends Seeder
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        $table = DB::table('car_models');

        if ($table->count() > 0) {
            return;
        }

        $table->insert([
            'id' => 1,
            'title' => 'A3',
            'maker_id' => 1
        ]);

        $table->insert([
            'id' => 2,
            'title' => 'A4',
            'maker_id' => 1
        ]);

        $table->insert([
            'id' => 3,
            'title' => 'Q3',
            'maker_id' => 1
        ]);

        $table->insert([
            'id' => 5,
            'title' => '1 Series',
            'maker_id' => 2
        ]);

        $table->insert([
            'id' => 6,
            'title' => '3 Series',
            'maker_id' => 2
        ]);

        $table->insert([
            'id' => 7,
            'title' => 'X1',
            'maker_id' => 2
        ]);

        $table->insert([
            'id' => 8,
            'title' => 'Golf',
            'maker_id' => 3
        ]);

        $table->insert([
            'id' => 9,
            'title' => 'Passat',
            'maker_id' => 3
        ]);

        $table->insert([
            'id' => 10,
            'title' => 'Tiguan',
            'maker_id' => 3
        ]);

        $table->insert([
            'id' => 11,
            'title' => 'S60',
            'maker_id' => 4
        ]);

        $table->insert([
            'id' => 12,
            'title' => 'XC40',
            'maker_id' => 4
        ]);

        $table->insert([
            'id' => 13,
            'title' => 'Rio',
            'maker_id' => 5
        ]);

        $table->insert([
            'id' => 14,
            'title' => 'Cerato',
            'maker_id' => 5
        ]);

        $table->insert([
            'id' => 15,
            'title' => 'Stinger',
            'maker_id' => 5
        ]);
    }
}
