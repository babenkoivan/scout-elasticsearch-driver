<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->call([
            CarMakersTableSeeder::class,
            CarModelsTableSeeder::class
        ]);
    }
}
