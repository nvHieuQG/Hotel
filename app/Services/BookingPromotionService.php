<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Promotion;
use Illuminate\Support\Collection;

class BookingPromotionService
{
    /**
     * Lấy danh sách khuyến mại có thể áp dụng cho booking
     */
    public function getAvailablePromotionsForBooking(Booking $booking): Collection
    {
        return Promotion::active()
            ->available()
            ->get()
            ->filter(function($promotion) use ($booking) {
                return $promotion->canApplyToRoomType($booking->room->room_type_id)
                    && $promotion->canApplyToAmount($booking->price)
                    && $promotion->canApplyToUser($booking->user_id);
            });
    }

    /**
     * Áp dụng khuyến mại cho booking
     */
    public function applyPromotionToBooking(Booking $booking, string $promotionCode): array
    {
        $promotion = Promotion::where('code', $promotionCode)
            ->active()
            ->available()
            ->first();

        if (!$promotion) {
            return [
                'success' => false,
                'message' => 'Mã khuyến mại không hợp lệ hoặc đã hết hạn'
            ];
        }

        if (!$promotion->canApplyToRoomType($booking->room->room_type_id)) {
            return [
                'success' => false,
                'message' => 'Mã khuyến mại không áp dụng cho loại phòng này'
            ];
        }

        if (!$promotion->canApplyToAmount($booking->price)) {
            return [
                'success' => false,
                'message' => 'Giá trị đơn hàng không đủ để áp dụng mã khuyến mại'
            ];
        }

        if (!$promotion->canApplyToUser($booking->user_id)) {
            return [
                'success' => false,
                'message' => 'Bạn đã sử dụng mã khuyến mại này hoặc đã hết lượt sử dụng'
            ];
        }

        $discount = $promotion->calculateDiscount($booking->price);
        
        $booking->update([
            'promotion_id' => $promotion->id,
            'promotion_discount' => $discount,
            'promotion_code' => $promotion->code
        ]);

        // Tăng số lần sử dụng
        $promotion->incrementUsage();

        return [
            'success' => true,
            'message' => 'Áp dụng mã khuyến mại thành công',
            'promotion' => $promotion,
            'discount' => $discount,
            'final_price' => $booking->price - $discount
        ];
    }

    /**
     * Gỡ bỏ khuyến mại khỏi booking
     */
    public function removePromotionFromBooking(Booking $booking): array
    {
        if (!$booking->promotion_id) {
            return [
                'success' => false,
                'message' => 'Booking không có khuyến mại nào được áp dụng'
            ];
        }

        $promotion = $booking->promotion;
        
        $booking->update([
            'promotion_id' => null,
            'promotion_discount' => 0,
            'promotion_code' => null
        ]);

        // Giảm số lần sử dụng
        if ($promotion) {
            $promotion->decrement('used_count');
        }

        return [
            'success' => true,
            'message' => 'Đã gỡ bỏ mã khuyến mại',
            'final_price' => $booking->price
        ];
    }

    /**
     * Lấy thông tin khuyến mại đang áp dụng cho booking
     */
    public function getAppliedPromotion(Booking $booking): ?array
    {
        if (!$booking->promotion_id) {
            return null;
        }

        return [
            'id' => $booking->promotion->id,
            'title' => $booking->promotion->title,
            'code' => $booking->promotion->code,
            'discount_text' => $booking->promotion->discount_text,
            'discount_amount' => $booking->promotion_discount,
            'final_price' => $booking->final_price
        ];
    }
}
