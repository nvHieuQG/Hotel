<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\RoomType;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Đầu tiên tạo các loại phòng                                                                  
        $roomTypes = [
            [
                'name' => 'Phòng Đơn Tiêu Chuẩn',
                'description' => 'Phòng đơn tiêu chuẩn với đầy đủ tiện nghi cơ bản, phù hợp cho 1-2 người.',
                'price' => 500000,
                'capacity' => 2
            ],
            [
                'name' => 'Phòng Đôi Tiêu Chuẩn',
                'description' => 'Phòng đôi tiêu chuẩn với 2 giường đơn, thích hợp cho gia đình nhỏ hoặc nhóm bạn.',
                'price' => 800000,
                'capacity' => 4 
            ],
            [
                'name' => 'Phòng Deluxe',
                'description' => 'Phòng deluxe với thiết kế sang trọng, đầy đủ tiện nghi cao cấp, view đẹp.',
                'price' => 1200000,
                'capacity' => 3
            ],
            [
                'name' => 'Phòng Suite',
                'description' => 'Phòng suite sang trọng bậc nhất với không gian rộng rãi, bao gồm phòng khách và phòng ngủ riêng biệt.',
                'price' => 2000000,
                'capacity' => 4
            ],
            [
                'name' => 'Phòng Gia Đình',
                'description' => 'Phòng gia đình rộng rãi với 2 phòng ngủ, phù hợp cho gia đình 4-6 người.',
                'price' => 1500000,
                'capacity' => 6
            ],
        ];
        
        // Mảng các prefix cho phòng
        $prefixes = ['A', 'B', 'C', 'D', 'E'];
        
        // Tạo các room types
        foreach ($roomTypes as $index => $roomType) {
            $type = RoomType::create($roomType);
            
            // Với mỗi loại phòng, tạo 3 phòng thực tế
            for ($i = 1; $i <= 3; $i++) {
                Room::create([
                    'room_type_id' => $type->id,
                    'room_number' => $prefixes[$index] . '-' . sprintf('%03d', $i),
                    'status' => 'available',
                    'floor' => $index + 1
                ]);
            }
        }
    }
}
