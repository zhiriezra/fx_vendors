<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $units = [
            ['name' => 'Bag'],
            ['name' => 'Litres'],
            ['name' => 'Kilogram'],
            ['name' => 'Metric Ton'],
            ['name' => 'Grams'],
            ['name' => 'Bags'],
            ['name' => 'Trucks'],
            ['name' => 'Satchet'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
