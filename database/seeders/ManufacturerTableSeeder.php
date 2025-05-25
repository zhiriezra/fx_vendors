<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Manufacturer;

class ManufacturerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Manufacturer::create([
            'name' => 'Golden',
            'slug' => 'golden',
            'email' => 'golden@gmail.com',
            'phone' => '1234567890',
            'logo' => 'logo1.png',
            'website' => 'https://www.golden.com',
            'description' => 'Golden is a leading manufacturer of fertilisers in the country.',
        ]);

        Manufacturer::create([
            'name' => 'Bayer',
            'slug' => 'bayer',
            'email' => 'bayer@gmail.com',
            'phone' => '1234567891',
            'logo' => 'logo2.png',
            'website' => 'https://www.bayer.com',
            'description' => 'Bayer is a leading manufacturer of fertilisers in the country.',
        ]); 

        Manufacturer::create([
            'name' => 'Dhanuka',
            'slug' => 'dhanuka',
            'email' => 'dhanuka@gmail.com',
            'phone' => '1234567892',
            'logo' => 'logo3.png',
            'website' => 'https://www.dhanuka.com',
            'description' => 'Dhanuka is a leading manufacturer of seeds in the country.',
        ]);
    }
}
