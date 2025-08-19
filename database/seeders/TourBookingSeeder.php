<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TourBooking;
use App\Models\TourBookingRoom;
use App\Models\User;
use App\Models\RoomType;

class TourBookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo tour booking mẫu nếu có user và room type
        $user = User::first();
        $roomTypes = RoomType::all();

        if ($user && $roomTypes->count() > 0) {
            // Tour booking 1
            $tourBooking1 = TourBooking::create([
                'user_id' => $user->id,
                'booking_id' => 'TOUR' . date('Ymd') . '001',
                'tour_name' => 'Tour du lịch Đà Nẵng - Hội An',
                'total_guests' => 15,
                'total_rooms' => 5,
                'check_in_date' => now()->addDays(30),
                'check_out_date' => now()->addDays(33),
                'total_price' => 15000000,
                'status' => 'confirmed',
                'special_requests' => 'Cần phòng liền kề nhau, có view biển',
            ]);

            // Tạo tour booking rooms cho tour 1
            $roomType1 = $roomTypes->first();
            if ($roomType1) {
                TourBookingRoom::create([
                    'tour_booking_id' => $tourBooking1->id,
                    'room_type_id' => $roomType1->id,
                    'quantity' => 3,
                    'guests_per_room' => 3,
                    'price_per_room' => 3000000,
                    'total_price' => 9000000,
                ]);
            }

            $roomType2 = $roomTypes->skip(1)->first();
            if ($roomType2) {
                TourBookingRoom::create([
                    'tour_booking_id' => $tourBooking1->id,
                    'room_type_id' => $roomType2->id,
                    'quantity' => 2,
                    'guests_per_room' => 3,
                    'price_per_room' => 3000000,
                    'total_price' => 6000000,
                ]);
            }

            // Tour booking 2
            $tourBooking2 = TourBooking::create([
                'user_id' => $user->id,
                'booking_id' => 'TOUR' . date('Ymd') . '002',
                'tour_name' => 'Tour du lịch Sapa - Fansipan',
                'total_guests' => 20,
                'total_rooms' => 8,
                'check_in_date' => now()->addDays(45),
                'check_out_date' => now()->addDays(48),
                'total_price' => 20000000,
                'status' => 'pending',
                'special_requests' => 'Cần phòng ấm, có view núi',
            ]);

            // Tạo tour booking rooms cho tour 2
            if ($roomType1) {
                TourBookingRoom::create([
                    'tour_booking_id' => $tourBooking2->id,
                    'room_type_id' => $roomType1->id,
                    'quantity' => 5,
                    'guests_per_room' => 2,
                    'price_per_room' => 2500000,
                    'total_price' => 12500000,
                ]);
            }

            if ($roomType2) {
                TourBookingRoom::create([
                    'tour_booking_id' => $tourBooking2->id,
                    'room_type_id' => $roomType2->id,
                    'quantity' => 3,
                    'guests_per_room' => 3,
                    'price_per_room' => 2500000,
                    'total_price' => 7500000,
                ]);
            }

            // Tour booking 3 (hoàn thành)
            $tourBooking3 = TourBooking::create([
                'user_id' => $user->id,
                'booking_id' => 'TOUR' . date('Ymd') . '003',
                'tour_name' => 'Tour du lịch Nha Trang - Đảo Khỉ',
                'total_guests' => 12,
                'total_rooms' => 4,
                'check_in_date' => now()->subDays(10),
                'check_out_date' => now()->subDays(7),
                'total_price' => 12000000,
                'status' => 'completed',
                'special_requests' => 'Cần phòng gần biển',
            ]);

            // Tạo tour booking rooms cho tour 3
            if ($roomType1) {
                TourBookingRoom::create([
                    'tour_booking_id' => $tourBooking3->id,
                    'room_type_id' => $roomType1->id,
                    'quantity' => 2,
                    'guests_per_room' => 3,
                    'price_per_room' => 3000000,
                    'total_price' => 6000000,
                ]);
            }

            if ($roomType2) {
                TourBookingRoom::create([
                    'tour_booking_id' => $tourBooking3->id,
                    'room_type_id' => $roomType2->id,
                    'quantity' => 2,
                    'guests_per_room' => 3,
                    'price_per_room' => 3000000,
                    'total_price' => 6000000,
                ]);
            }

            $this->command->info('Tour Booking seeder completed successfully!');
        } else {
            $this->command->warn('No users or room types found. Please run UserSeeder and RoomTypeSeeder first.');
        }
    }
}
