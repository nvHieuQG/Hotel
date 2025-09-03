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
        $tourBooking = \App\Models\TourBooking::with(['tourBookingRooms.roomType', 'tourBookingRooms.assignedRooms'])->findOrFail($tourBookingId);
        return view('client.tour-bookings.room-change-form', compact('tourBooking'));
    }

    // Client tạo yêu cầu đổi phòng
    public function store(Request $request, $tourBookingId)
    {
        $validated = $request->validate([
            'from_room_id' => 'required|integer|exists:rooms,id',
            'to_room_id' => 'required|integer|exists:rooms,id',
            'reason' => 'nullable|string',
            'customer_note' => 'nullable|string',
        ]);
        $validated['tour_booking_id'] = (int)$tourBookingId;
        try {
            $change = $this->tourBookingService->createTourRoomChange($validated);
            return back()->with('success', 'Đã gửi yêu cầu đổi phòng tour.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}


