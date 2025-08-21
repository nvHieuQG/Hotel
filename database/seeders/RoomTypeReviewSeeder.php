<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\RoomTypeReview;
use App\Models\RoomType;
use App\Models\User;

class RoomTypeReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy danh sách room types và users
        $roomTypes = RoomType::all();
        $users = User::all();

        if ($roomTypes->isEmpty() || $users->isEmpty()) {
            $this->command->info('Không có dữ liệu room types hoặc users để tạo reviews!');
            return;
        }

        $reviews = [
            [
                'rating' => 5,
                'comment' => 'Phòng rất đẹp và sạch sẽ, view tuyệt vời!',
            ],
            [
                'rating' => 4,
                'comment' => 'Phòng tốt, đầy đủ tiện nghi, giá cả hợp lý.',
            ],
            [
                'rating' => 5,
                'comment' => 'Tuyệt vời! Nhân viên phục vụ rất chu đáo, phòng sạch sẽ.',
            ],
            [
                'rating' => 3,
                'comment' => 'Phòng ổn, nhưng có thể cải thiện thêm về tiện nghi.',
            ],
            [
                'rating' => 4,
                'comment' => 'Vị trí thuận tiện, phòng đẹp, giá cả phải chăng.',
            ],
            [
                'rating' => 5,
                'comment' => 'Trải nghiệm tuyệt vời! Sẽ quay lại lần nữa.',
            ],
            [
                'rating' => 4,
                'comment' => 'Phòng đẹp, view đẹp, nhưng wifi hơi chậm.',
            ],
            [
                'rating' => 3,
                'comment' => 'Phòng cơ bản, đủ dùng cho chuyến đi ngắn.',
            ],
            [
                'rating' => 5,
                'comment' => 'Khách sạn sang trọng, phòng cao cấp, dịch vụ tốt.',
            ],
            [
                'rating' => 4,
                'comment' => 'Phòng đẹp, sạch sẽ, nhân viên thân thiện.',
            ],
        ];

        $createdCount = 0;

        foreach ($roomTypes as $roomType) {
            // Tạo 2-4 reviews cho mỗi room type
            $numReviews = rand(2, 4);
            
            for ($i = 0; $i < $numReviews; $i++) {
                $reviewData = $reviews[array_rand($reviews)];
                $user = $users->random();
                
                // Kiểm tra xem user đã review room type này chưa
                $existingReview = RoomTypeReview::where('user_id', $user->id)
                    ->where('room_type_id', $roomType->id)
                    ->first();
                
                if (!$existingReview) {
                    RoomTypeReview::create([
                        'user_id' => $user->id,
                        'room_type_id' => $roomType->id,
                        'rating' => $reviewData['rating'],
                        'comment' => $reviewData['comment'],
                        'status' => 'approved',
                        'created_at' => now()->subDays(rand(1, 30)),
                        'updated_at' => now()->subDays(rand(1, 30)),
                    ]);
                    $createdCount++;
                }
            }
        }

        $this->command->info("Đã tạo thành công {$createdCount} reviews mẫu cho hệ thống đánh giá loại phòng!");
    }
}
