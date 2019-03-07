<?php

use App\Stubs\Car;
use App\Stubs\Maker;
use Illuminate\Database\Seeder;

class CarsTableSeeder extends Seeder
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (Car::count() > 0) {
            return;
        }

        Maker::where('title', 'Audi')
            ->firstOrFail()
            ->cars()
            ->saveMany([
                new Car(['title' => 'A3']),
                new Car(['title' => 'A4']),
                new Car(['title' => 'Q3']),
            ]);

        Maker::where('title', 'BMW')
            ->firstOrFail()
            ->cars()
            ->saveMany([
                new Car(['title' => '1 Series']),
                new Car(['title' => '3 Series']),
                new Car(['title' => 'X1']),
            ]);

        Maker::where('title', 'Volkswagen')
            ->firstOrFail()
            ->cars()
            ->saveMany([
                new Car(['title' => 'Golf']),
                new Car(['title' => 'Passat']),
                new Car(['title' => 'Tiguan']),
            ]);

        Maker::where('title', 'Volvo')
            ->firstOrFail()
            ->cars()
            ->saveMany([
                new Car(['title' => 'S60']),
                new Car(['title' => 'XC40']),
            ]);

        Maker::where('title', 'Kia')
            ->firstOrFail()
            ->cars()
            ->saveMany([
                new Car(['title' => 'Rio']),
                new Car(['title' => 'Cerato']),
                new Car(['title' => 'Stinger']),
            ]);
    }
}
