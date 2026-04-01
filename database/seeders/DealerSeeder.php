<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Country\Entities\Country;
use Illuminate\Support\Str;

class DealerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get Indonesia country
        $indonesia = Country::where('name', 'LIKE', '%Indonesia%')
            ->orWhere('name', 'LIKE', '%Indonesia%')
            ->orWhere('code', 'ID')
            ->first();

        // If Indonesia doesn't exist, create it
        if (!$indonesia) {
            $indonesia = Country::create([
                'name' => 'Indonesia',
                'code' => 'ID',
                'is_default' => 1,
            ]);
        } else {
            // Set as default
            $indonesia->update(['is_default' => 1]);
        }

        // Sample dealer data
        $dealers = [
            [
                'name' => 'Daris Motor',
                'username' => 'darismotor',
                'email' => 'daris@ecomotif.com',
                'phone' => '+6281234567890',
                'designation' => 'Showroom Partner',
                'address' => 'Jl. Raya Jakarta Selatan No. 123, Jakarta',
                'country' => $indonesia->id,
                'about_me' => 'Showroom mobil bekas terpercaya dengan pengalaman lebih dari 10 tahun. Melayani berbagai jenis mobil dengan harga kompetitif.',
                'rating' => 4.8,
                'total_reviews' => 45,
            ],
            [
                'name' => 'Mekarsari Mobilindo',
                'username' => 'mekarsari',
                'email' => 'mekarsari@ecomotif.com',
                'phone' => '+6281234567891',
                'designation' => 'Dealer Partner',
                'address' => 'Jl. Gatot Subroto No. 456, Jakarta',
                'country' => $indonesia->id,
                'about_me' => 'Dealer resmi dengan layanan lengkap mulai dari jual beli hingga service. Terpercaya dan profesional.',
                'rating' => 4.9,
                'total_reviews' => 78,
            ],
            [
                'name' => 'Tio Auto Gallery',
                'username' => 'tioauto',
                'email' => 'tio@ecomotif.com',
                'phone' => '+6281234567892',
                'designation' => 'Showroom Partner',
                'address' => 'Jl. Sudirman No. 789, Jakarta',
                'country' => $indonesia->id,
                'about_me' => 'Showroom premium dengan koleksi mobil berkualitas tinggi. Lokasi strategis dan pelayanan prima.',
                'rating' => 4.7,
                'total_reviews' => 32,
            ],
            [
                'name' => 'Bintang Motor',
                'username' => 'bintangmotor',
                'email' => 'bintang@ecomotif.com',
                'phone' => '+6281234567893',
                'designation' => 'Showroom Partner',
                'address' => 'Jl. Thamrin No. 321, Jakarta',
                'country' => $indonesia->id,
                'about_me' => 'Showroom dengan fokus pada mobil keluarga. Harga terjangkau dan proses mudah.',
                'rating' => 4.6,
                'total_reviews' => 28,
            ],
            [
                'name' => 'Surya Auto',
                'username' => 'suryaauto',
                'email' => 'surya@ecomotif.com',
                'phone' => '+6281234567894',
                'designation' => 'Dealer Partner',
                'address' => 'Jl. Kebon Jeruk No. 654, Jakarta',
                'country' => $indonesia->id,
                'about_me' => 'Dealer terpercaya dengan berbagai pilihan mobil baru dan bekas. Layanan purna jual terjamin.',
                'rating' => 4.5,
                'total_reviews' => 56,
            ],
            [
                'name' => 'Prima Mobil',
                'username' => 'primamobil',
                'email' => 'prima@ecomotif.com',
                'phone' => '+6281234567895',
                'designation' => 'Showroom Partner',
                'address' => 'Jl. Kemang Raya No. 987, Jakarta',
                'country' => $indonesia->id,
                'about_me' => 'Showroom eksklusif dengan koleksi mobil mewah dan sport. Pelayanan VIP untuk setiap customer.',
                'rating' => 4.9,
                'total_reviews' => 67,
            ],
        ];

        foreach ($dealers as $dealerData) {
            // Check if dealer already exists
            $existingDealer = User::where('email', $dealerData['email'])
                ->orWhere('username', $dealerData['username'])
                ->first();

            if (!$existingDealer) {
                $dealer = User::create([
                    'name' => $dealerData['name'],
                    'username' => $dealerData['username'],
                    'email' => $dealerData['email'],
                    'phone' => $dealerData['phone'],
                    'designation' => $dealerData['designation'],
                    'address' => $dealerData['address'],
                    'country' => $dealerData['country'],
                    'about_me' => $dealerData['about_me'],
                    'password' => Hash::make('password123'),
                    'is_dealer' => 1,
                    'status' => 'enable',
                    'is_banned' => 'no',
                    'email_verified_at' => now(),
                ]);

                // Create reviews for this dealer
                $totalReviews = $dealerData['total_reviews'];
                $targetRating = $dealerData['rating'];
                
                // Create sample reviews to achieve target rating
                for ($i = 0; $i < $totalReviews; $i++) {
                    // Distribute ratings to achieve target average
                    $rating = $this->getRatingForAverage($targetRating, $i, $totalReviews);
                    
                    Review::create([
                        'user_id' => 1, // Assuming admin or first user exists
                        'agent_id' => $dealer->id,
                        'car_id' => 1, // Placeholder car ID
                        'rating' => $rating,
                        'comment' => $this->getRandomComment($rating),
                        'status' => 'enable',
                    ]);
                }
            }
        }

        $this->command->info('Dealers seeded successfully!');
    }

    /**
     * Get rating value to achieve target average
     */
    private function getRatingForAverage($target, $index, $total): int
    {
        // Simple distribution: mostly target rating, some variation
        if ($index < $total * 0.7) {
            return (int)round($target);
        } elseif ($index < $total * 0.9) {
            return (int)round($target) + 1;
        } else {
            return max(1, (int)round($target) - 1);
        }
    }

    /**
     * Get random comment based on rating
     */
    private function getRandomComment($rating): string
    {
        $comments = [
            5 => [
                'Pelayanan sangat memuaskan!',
                'Sangat recommended, proses cepat dan mudah.',
                'Dealer terpercaya dengan harga kompetitif.',
                'Sangat puas dengan pelayanannya.',
            ],
            4 => [
                'Pelayanan bagus, harga sesuai.',
                'Proses transaksi lancar.',
                'Showroom rapi dan profesional.',
                'Satisfied dengan pelayanan.',
            ],
            3 => [
                'Cukup baik, ada beberapa hal yang perlu diperbaiki.',
                'Pelayanan standar.',
                'Harga cukup kompetitif.',
            ],
            2 => [
                'Perlu perbaikan dalam pelayanan.',
                'Proses agak lama.',
            ],
            1 => [
                'Tidak sesuai ekspektasi.',
            ],
        ];

        $ratingKey = min(5, max(1, (int)$rating));
        $commentList = $comments[$ratingKey] ?? $comments[3];
        
        return $commentList[array_rand($commentList)];
    }
}

