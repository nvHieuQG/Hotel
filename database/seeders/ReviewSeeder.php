<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Room;
use App\Models\Booking;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy một số user, room và booking để tạo reviews
        $users = User::all();
        $rooms = Room::all();
        
        if ($users->isEmpty() || $rooms->isEmpty()) {
            $this->command->info('Không có user hoặc room nào để tạo reviews. Vui lòng chạy UserSeeder và RoomSeeder trước.');
            return;
        }

        $comments = [
            'Phòng rất sạch sẽ và thoải mái. Nhân viên phục vụ rất nhiệt tình.',
            'Vị trí thuận tiện, gần trung tâm thành phố. Giá cả hợp lý.',
            'Phòng có view đẹp, nội thất hiện đại. Rất hài lòng với dịch vụ.',
            'Khách sạn có không gian yên tĩnh, phù hợp để nghỉ ngơi.',
            'Dịch vụ tốt, phòng sạch sẽ. Sẽ quay lại vào lần sau.',
            'Phòng rộng rãi, có đầy đủ tiện nghi. Nhân viên thân thiện.',
            'Vị trí đắc địa, dễ dàng di chuyển. Phòng có view đẹp.',
            'Giá cả phải chăng, chất lượng tốt. Rất hài lòng.',
            'Phòng có thiết kế đẹp, không gian thoáng đãng.',
            'Dịch vụ chuyên nghiệp, phòng sạch sẽ. Khuyến nghị cho mọi người.'
        ];

        $anonymousComments = [
            'Trải nghiệm tốt, sẽ quay lại.',
            'Phòng đẹp, giá hợp lý.',
            'Dịch vụ tốt, nhân viên nhiệt tình.',
            'Vị trí thuận tiện, phòng sạch.',
            'Hài lòng với dịch vụ.'
        ];

        // Tạo reviews cho mỗi room
        foreach ($rooms as $room) {
            // Tạo 3-5 reviews cho mỗi room
            $numReviews = rand(3, 5);
            
            for ($i = 0; $i < $numReviews; $i++) {
                $user = $users->random();
                $rating = rand(3, 5); // Rating từ 3-5 sao
                $isAnonymous = rand(0, 1);
                
                // Tạo booking giả cho review
                $booking = Booking::create([
                    'user_id' => $user->id,
                    'room_id' => $room->id,
                    'booking_id' => 'BK' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                    'check_in_date' => now()->subDays(rand(30, 90)),
                    'check_out_date' => now()->subDays(rand(20, 29)),
                    'price' => $room->price * rand(1, 3),
                    'status' => 'completed',
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 30))
                ]);

                Review::create([
                    'user_id' => $user->id,
                    'booking_id' => $booking->id,
                    'room_id' => $room->id,
                    'rating' => $rating,
                    'comment' => $isAnonymous ? $anonymousComments[array_rand($anonymousComments)] : $comments[array_rand($comments)],
                    'is_anonymous' => $isAnonymous,
                    'status' => 'approved',
                    'created_at' => now()->subDays(rand(1, 30)),
                    'updated_at' => now()->subDays(rand(1, 30))
                ]);
            }
        }

        $this->command->info('Đã tạo ' . ($rooms->count() * 4) . ' reviews mẫu.');
    }
} 