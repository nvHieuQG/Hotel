<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExtraService;

class ExtraServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $extraServices = [
            [
                'name' => 'Bữa sáng buffet',
                'description' => 'Bữa sáng buffet đầy đủ với các món ăn Việt Nam và quốc tế, bao gồm bánh mì, trứng, thịt nguội, hoa quả tươi và đồ uống.',
                'applies_to' => 'both',
                'price_adult' => 150000,
                'price_child' => 75000,
                'charge_type' => 'per_person',
                'is_active' => true,
                'child_age_min' => 6,
                'child_age_max' => 11,
            ],
            [
                'name' => 'Đưa đón sân bay',
                'description' => 'Dịch vụ đưa đón từ sân bay về khách sạn và ngược lại bằng xe ô tô.',
                'applies_to' => 'both',
                'price_adult' => 50000,
                'price_child' => 20000,
                'charge_type' => 'per_service',
                'is_active' => true,
                'child_age_min' => 6,
                'child_age_max' => 12,
            ],
            [
                'name' => 'Thuê xe tự lái',
                'description' => 'Dịch vụ cho thuê xe tự lái với nhiều loại xe từ sedan đến SUV.',
                'applies_to' => 'adult',
                'price_adult' => 300000,
                'price_child' => null,
                'charge_type' => 'per_hour',
                'is_active' => true,
                'child_age_min' => null,
                'child_age_max' => null,
            ],
            [
                'name' => 'Massage',
                'description' => 'Dịch vụ massage thư giãn 60 phút với các kỹ thuật chuyên nghiệp.',
                'applies_to' => 'both',
                'price_adult' => 400000,
                'price_child' => 200000,
                'charge_type' => 'per_person',
                'is_active' => true,
                'child_age_min' => 6,
                'child_age_max' => 11,
            ],
        ];

        foreach ($extraServices as $service) {
            ExtraService::create($service);
        }
    }
} 