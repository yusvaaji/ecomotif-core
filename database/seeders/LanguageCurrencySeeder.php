<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Language\Entities\Language;
use Modules\Currency\app\Models\MultiCurrency;

class LanguageCurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set Indonesian as default language
        $indonesian = Language::where('lang_code', 'id')->first();
        if (!$indonesian) {
            // Create Indonesian language if it doesn't exist
            $indonesian = Language::create([
                'lang_name' => 'Indonesian',
                'lang_code' => 'id',
                'lang_direction' => 'left_to_right',
                'is_default' => 'Yes',
                'status' => 1, // 1 = active, 0 = inactive
            ]);
            $this->command->info('✅ Indonesian language created');
        } else {
            // Update existing Indonesian language
            $indonesian->update([
                'lang_name' => 'Indonesian',
                'lang_direction' => 'left_to_right',
                'status' => 1, // Ensure it's active
            ]);
        }
        
        // Remove default from other languages
        Language::where('is_default', 'Yes')
            ->where('lang_code', '!=', 'id')
            ->update(['is_default' => 'No']);
        
        // Set Indonesian as default
        $indonesian->update(['is_default' => 'Yes', 'status' => 1]);
        $this->command->info('✅ Indonesian language set as default and active');

        // Set Rupiah (IDR) as default currency
        $rupiah = MultiCurrency::where('currency_code', 'IDR')->first();
        
        if (!$rupiah) {
            // Create IDR currency if it doesn't exist
            $rupiah = MultiCurrency::create([
                'currency_name' => 'Rupiah',
                'country_code' => 'ID',
                'currency_code' => 'IDR',
                'currency_icon' => 'Rp',
                'is_default' => 'Yes',
                'currency_rate' => 1,
                'currency_position' => 'before_price',
                'status' => 'active',
            ]);
            $this->command->info('✅ Rupiah (IDR) currency created and set as default');
        } else {
            // Update existing Rupiah currency
            $rupiah->update([
                'currency_name' => 'Rupiah',
                'country_code' => 'ID',
                'currency_icon' => 'Rp',
                'status' => 'active', // Ensure it's active
            ]);
        }
        
        // Remove default from other currencies
        MultiCurrency::where('is_default', 'Yes')
            ->where('currency_code', '!=', 'IDR')
            ->update(['is_default' => 'No']);
        
        // Set Rupiah as default
        $rupiah->update(['is_default' => 'Yes', 'status' => 'active']);
        $this->command->info('✅ Rupiah (IDR) currency set as default and active');
    }
}

