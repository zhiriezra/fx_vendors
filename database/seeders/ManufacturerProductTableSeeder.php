<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ManufacturerProduct;

class ManufacturerProductTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ManufacturerProduct::create([
            'manufacturer_id' => 1,
            'sub_category_id' => 1,
            'name' => 'NPK 15-15-15',
            'description' => 'NPK 15-15-15 is a leading manufacturer of fertilisers in the country.',
        ]);

        ManufacturerProduct::create([
            'manufacturer_id' => 1,
            'sub_category_id' => 1,
            'name' => 'NPK 10-20-10',
            'description' => 'NPK 10-20-10 is a leading manufacturer of fertilisers in the country.',
        ]);

        ManufacturerProduct::create([
            'manufacturer_id' => 2,
            'sub_category_id' => 1,
            'name' => 'NPK 10-20-10',
            'description' => 'NPK 10-20-10 is a leading manufacturer of fertilisers in the country.',
        ]);

        ManufacturerProduct::create([
            'manufacturer_id' => 3,
            'sub_category_id' => 2,
            'name' => 'Faro 45',
            'description' => 'Faro 45 is a leading manufacturer of rice seedlings in the country.',
        ]);

        ManufacturerProduct::create([
            'manufacturer_id' => 3,
            'sub_category_id' => 2,
            'name' => 'Faro 49',
            'description' => 'Faro 49 is a leading manufacturer of rice seedlings in the country.',
        ]); 
    }
}
