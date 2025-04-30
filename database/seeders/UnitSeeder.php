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
            ['name' => 'Acre'],
            ['name' => 'Bag'],
            ['name' => 'Barrel'],
            ['name' => 'Box'],
            ['name' => 'Bushel'],
            ['name' => 'Carton'],
            ['name' => 'Crate'],
            ['name' => 'Cubic Meter'],
            ['name' => 'Dozen'],
            ['name' => 'Gallon'],
            ['name' => 'Gram'],
            ['name' => 'Hectare'],
            ['name' => 'Kilogram'],
            ['name' => 'Litre'],
            ['name' => 'Metric Ton'],
            ['name' => 'Milliliter'],
            ['name' => 'Ounce'],
            ['name' => 'Pack'],
            ['name' => 'Pallet'],
            ['name' => 'Piece'],
            ['name' => 'Pound'],
            ['name' => 'Quart'],
            ['name' => 'Sack'],
            ['name' => 'Square Meter'],
            ['name' => 'Ton'],
            ['name' => 'Tray'],
            ['name' => 'Truck'],
            ['name' => 'Unit'],
        ];

        foreach ($units as $unit) {
            Unit::create($unit);
        }
    }
}
