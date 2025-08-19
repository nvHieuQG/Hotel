<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\BookingPromotionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookingPromotionController extends Controller
{
    protected $bookingPromotionService;

    public function __construct(BookingPromotionService $bookingPromotionService)
    {
        $this->middleware('auth');
        $this->bookingPromotionService = $bookingPromotionService;
    }

    /**
     * Áp dụng mã khuyến mại cho booking
     */
    public function applyPromotion(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'promotion_code' => 'required|string|max:50'
        ]);

        // Kiểm tra quyền truy cập
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập booking này'
            ], 403);
        }

        $result = $this->bookingPromotionService->applyPromotionToBooking(
            $booking, 
            $request->promotion_code
        );

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Gỡ bỏ mã khuyến mại khỏi booking
     */
    public function removePromotion(Request $request, Booking $booking): JsonResponse
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập booking này'
            ], 403);
        }

        $result = $this->bookingPromotionService->removePromotionFromBooking($booking);

        return response()->json($result);
    }

    /**
     * Lấy danh sách khuyến mại có thể áp dụng cho booking
     */
    public function getAvailablePromotions(Booking $booking): JsonResponse
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập booking này'
            ], 403);
        }

        $promotions = $this->bookingPromotionService->getAvailablePromotionsForBooking($booking);

        $promotionsData = $promotions->map(function($promotion) use ($booking) {
            $discount = $promotion->calculateDiscount($booking->price);
            return [
                'id' => $promotion->id,
                'title' => $promotion->title,
                'code' => $promotion->code,
                'description' => $promotion->description,
                'discount_type' => $promotion->discount_type,
                'discount_value' => $promotion->discount_value,
                'discount_text' => $promotion->discount_text,
                'discount_amount' => $discount,
                'final_price' => $booking->price - $discount,
                'minimum_amount' => $promotion->minimum_amount,
                'can_combine' => $promotion->can_combine
            ];
        });

        return response()->json([
            'success' => true,
            'promotions' => $promotionsData
        ]);
    }

    /**
     * Lấy thông tin khuyến mại đang áp dụng cho booking
     */
    public function getAppliedPromotion(Booking $booking): JsonResponse
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Bạn không có quyền truy cập booking này'
            ], 403);
        }

        $promotion = $this->bookingPromotionService->getAppliedPromotion($booking);

        return response()->json([
            'success' => true,
            'promotion' => $promotion
        ]);
    }
}
