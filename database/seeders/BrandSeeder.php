<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Brand\Entities\Brand;
use Modules\Brand\Entities\BrandTranslation;
use Illuminate\Database\Eloquent\Model;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $brands = [
            'Toyota',
            'Honda',
            'Daihatsu',
            'Suzuki',
            'Mitsubishi',
            'Nissan',
            'Hyundai',
            'Kia',
            'Mazda',
            'Wuling',
            'BMW',
            'Mercedes-Benz',
            'Lexus',
            'Audi',
            'Volkswagen',
            'Ford',
            'Chevrolet',
            'MG',
            'Chery',
            'Subaru'
        ];

        foreach ($brands as $brandName) {
            $brand = Brand::firstOrCreate(
                ['slug' => Str::slug($brandName)],
                [
                    'image' => 'default_brand.png',
                    'status' => 'enable'
                ]
            );

            // Add translation for 'id'
            BrandTranslation::firstOrCreate([
                'lang_code' => 'id',
                'brand_id' => $brand->id
            ], [
                'name' => $brandName
            ]);
            
            // Add translation for 'en'
            BrandTranslation::firstOrCreate([
                'lang_code' => 'en',
                'brand_id' => $brand->id
            ], [
                'name' => $brandName
            ]);
        }
        
        $this->command->info('✅ Car brands seeded successfully!');
    }
}
