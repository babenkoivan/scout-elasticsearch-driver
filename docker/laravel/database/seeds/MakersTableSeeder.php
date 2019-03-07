<?php

use App\Stubs\Maker;
use Illuminate\Database\Seeder;

class MakersTableSeeder extends Seeder
{
    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (Maker::count() > 0) {
            return;
        }

        (new Maker(['title' => 'Audi']))->save();

        (new Maker(['title' => 'BMW']))->save();

        (new Maker(['title' => 'Volkswagen']))->save();

        (new Maker(['title' => 'Volvo']))->save();

        (new Maker(['title' => 'Kia']))->save();
    }
}
