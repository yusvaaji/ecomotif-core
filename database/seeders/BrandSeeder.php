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

        // --- Brand Mobil ---
        $carBrands = [
            'Toyota', 'Honda', 'Daihatsu', 'Suzuki', 'Mitsubishi', 
            'Nissan', 'Hyundai', 'Kia', 'Mazda', 'Wuling', 
            'BMW', 'Mercedes-Benz', 'Lexus', 'Audi', 'Volkswagen', 
            'Ford', 'Chevrolet', 'MG', 'Chery', 'Subaru', 
            'Peugeot', 'Renault', 'Ferrari', 'Lamborghini', 'Porsche', 
            'Jeep', 'Land Rover', 'Jaguar', 'Volvo', 'Mini', 
            'Fiat', 'Maserati', 'Aston Martin', 'McLaren', 'Bentley', 
            'Rolls-Royce', 'Isuzu', 'Hino', 'DFSK', 'Tata', 'Datsun'
        ];

        // --- Brand Motor ---
        $motorcycleBrands = [
            'Yamaha', 'Kawasaki', 'Vespa', 'Harley-Davidson', 'Ducati', 
            'KTM', 'Triumph', 'Aprilia', 'Royal Enfield', 'Benelli', 
            'Husqvarna', 'MV Agusta', 'Piaggio', 'Bajaj', 'TVS', 
            'Kymco', 'SYM', 'Gesits', 'Viar', 'Indian Motorcycle', 
            'Norton', 'Moto Guzzi', 'Bimota', 'Zero Motorcycles'
        ];

        $brandsData = [
            'car' => $carBrands,
            'motorcycle' => $motorcycleBrands
        ];

        foreach ($brandsData as $type => $brandList) {
            foreach ($brandList as $brandName) {
                $slug = Str::slug($brandName);
                $brand = Brand::where('slug', $slug)->first();

                if ($brand) {
                    // Jika sudah ada, cukup update typenya saja (agar image/status tidak tertimpa)
                    $brand->update(['type' => $type]);
                } else {
                    // Jika belum ada, buat baru
                    $brand = Brand::create([
                        'slug' => $slug,
                        'image' => 'default_brand.png',
                        'status' => 'enable',
                        'type' => $type
                    ]);
                }

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
        }
        
        $this->command->info('✅ All brands (Car & Motorcycle) seeded successfully!');
    }
}
