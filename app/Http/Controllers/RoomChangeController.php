<?php

namespace App\Http\Controllers;

use App\Interfaces\Services\RoomChangeServiceInterface;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomChangeController extends Controller
{
    public function __construct(
        private RoomChangeServiceInterface $roomChangeService
    ) {}

    /**
     * Hiển thị form yêu cầu đổi phòng
     */
    public function showRequestForm(Booking $booking)
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Không có quyền truy cập');
        }

        // Kiểm tra xem booking có thể đổi phòng không
        if (!$this->roomChangeService->canChangeRoom($booking)) {
            return redirect()->back()->with('error', 'Booking không thể đổi phòng');
        }

        // Kiểm tra xem đã có yêu cầu đổi phòng đang chờ duyệt chưa
        if ($this->roomChangeService->getRoomChangeHistory($booking->id)->where('status', 'pending')->count() > 0) {
            return redirect()->back()->with('error', 'Đã có yêu cầu đổi phòng đang chờ duyệt');
        }

        // Lấy danh sách loại phòng có thể đổi
        $availableRoomTypes = $this->roomChangeService->getAvailableRoomTypesForChange($booking);

        return view('client.room-change.request', compact('booking', 'availableRoomTypes'));
    }

    /**
     * Xử lý yêu cầu đổi phòng
     */
    public function storeRequest(Request $request, Booking $booking)
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Không có quyền truy cập');
        }

        // Validate request
        $request->validate([
            'new_room_type_id' => 'required|exists:room_types,id',
            'reason' => 'nullable|string|max:500',
            'customer_note' => 'nullable|string|max:1000',
        ]);

        try {
            // Tạo yêu cầu đổi phòng
            $roomChange = $this->roomChangeService->createRoomChangeRequest($booking, $request->all());

            return redirect()->route('booking.detail', $booking->id)
                ->with('success', 'Yêu cầu đổi phòng của bạn đã được gửi và đang chờ xét duyệt.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Hiển thị lịch sử đổi phòng của booking
     */
    public function showHistory(Booking $booking)
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Không có quyền truy cập');
        }

        $roomChanges = $this->roomChangeService->getRoomChangeHistory($booking->id);

        return view('client.room-change.history', compact('booking', 'roomChanges'));
    }

    /**
     * API để lấy danh sách phòng có thể đổi (AJAX)
     */
    public function getAvailableRooms(Request $request, Booking $booking)
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['error' => 'Không có quyền truy cập'], 403);
        }

        $availableRoomTypes = $this->roomChangeService->getAvailableRoomTypesForChange($booking);

        return response()->json([
            'room_types' => $availableRoomTypes->map(function ($roomType) use ($booking) {
                $priceDifference = $this->roomChangeService->calculatePriceDifferenceByRoomType($booking, $roomType->id);
                return [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'price_difference' => $priceDifference,
                    'price_difference_formatted' => number_format($priceDifference, 0, ',', '.') . ' VNĐ',
                    'is_expensive' => $priceDifference > 0,
                    'is_cheaper' => $priceDifference < 0,
                    'no_difference' => $priceDifference == 0,
                    'available_rooms_count' => $roomType->rooms->count(),
                ];
            })
        ]);
    }

    /**
     * API để tính toán chênh lệch giá (AJAX)
     */
    public function calculatePriceDifference(Request $request, Booking $booking)
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['error' => 'Không có quyền truy cập'], 403);
        }

        $request->validate([
            'new_room_type_id' => 'required|exists:room_types,id',
        ]);

        $priceDifference = $this->roomChangeService->calculatePriceDifferenceByRoomType($booking, $request->new_room_type_id);

        return response()->json([
            'price_difference' => $priceDifference,
            'price_difference_formatted' => number_format($priceDifference, 0, ',', '.') . ' VNĐ',
            'is_expensive' => $priceDifference > 0,
            'is_cheaper' => $priceDifference < 0,
            'no_difference' => $priceDifference == 0,
        ]);
    }
}
