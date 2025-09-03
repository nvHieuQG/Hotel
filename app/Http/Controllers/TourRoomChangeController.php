<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\Services\TourBookingServiceInterface;

class TourRoomChangeController extends Controller
{
    public function __construct(private TourBookingServiceInterface $tourBookingService) {}

    // Client hiển thị form đổi phòng
    public function create($tourBookingId)
    {
        $tourBooking = \App\Models\TourBooking::with(['tourBookingRooms.roomType'])->findOrFail($tourBookingId);
        return view('client.tour-bookings.room-change-form', compact('tourBooking'));
    }

    // Client tạo yêu cầu đổi phòng
    public function store(Request $request, $tourBookingId)
    {
        $validated = $request->validate([
            'from_room_id' => 'required|integer|exists:rooms,id',
            'to_room_id' => 'nullable|integer|exists:rooms,id|different:from_room_id',
            'reason' => 'required|string|max:500',
            'customer_note' => 'nullable|string|max:1000',
        ]);
        $validated['tour_booking_id'] = (int)$tourBookingId;
        try {
            // Chỉ cho phép đổi cùng loại phòng (VAT constraint)
            $from = \App\Models\Room::findOrFail((int)$validated['from_room_id']);
            if (!empty($validated['to_room_id'])) {
                $to = \App\Models\Room::findOrFail((int)$validated['to_room_id']);
                if ((int)$to->room_type_id !== (int)$from->room_type_id) {
                    return back()->withInput()->with('error', 'Vì yêu cầu hóa đơn VAT, chỉ được đổi sang phòng cùng loại.');
                }
            }
            $change = $this->tourBookingService->createTourRoomChange($validated);
            return back()->with('success', 'Đã gửi yêu cầu đổi phòng tour.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // API endpoint để lấy phòng trống theo loại phòng
    public function getAvailableRoomsByType(Request $request, $roomTypeId)
    {
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');
        
        if (!$checkIn || !$checkOut) {
            return response()->json(['success' => false, 'message' => 'Thiếu thông tin ngày check-in/check-out']);
        }

        try {
            // Lấy tất cả phòng cùng loại
            $allRooms = \App\Models\Room::with('roomType')
                ->where('room_type_id', $roomTypeId)
                ->where('status', 'available')
                ->get();

            // Lọc phòng trống trong khoảng thời gian
            $availableRooms = $allRooms->filter(function($room) use ($checkIn, $checkOut) {
                // Kiểm tra booking thường
                $hasBooking = $room->bookings()
                    ->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn)
                    ->where('status', '!=', 'cancelled')
                    ->exists();

                if ($hasBooking) {
                    return false;
                }

                // Kiểm tra tour booking (assigned_room_ids)
                $hasTourBooking = \App\Models\TourBookingRoom::whereJsonContains('assigned_room_ids', $room->id)
                    ->whereHas('tourBooking', function($query) use ($checkIn, $checkOut) {
                        $query->where('check_in_date', '<', $checkOut)
                              ->where('check_out_date', '>', $checkIn)
                              ->where('status', '!=', 'cancelled');
                    })
                    ->exists();

                return !$hasTourBooking;
            });

            $rooms = $availableRooms->values();

            \Log::info('API getAvailableRoomsByType', [
                'roomTypeId' => $roomTypeId,
                'checkIn' => $checkIn,
                'checkOut' => $checkOut,
                'totalRooms' => $allRooms->count(),
                'availableRooms' => $rooms->count(),
                'rooms' => $rooms->pluck('room_number', 'id')->toArray()
            ]);

            return response()->json([
                'success' => true,
                'rooms' => $rooms
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi khi lấy danh sách phòng']);
        }
    }
}


