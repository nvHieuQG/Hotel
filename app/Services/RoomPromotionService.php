<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Promotion;
use Illuminate\Support\Collection;

class RoomPromotionService
{
    /**
     * Lấy tất cả khuyến mại có thể áp dụng cho phòng
     */
    public function getAvailablePromotions(Room $room): Collection
    {
        // Lấy tất cả khuyến mại đang hoạt động
        $promotions = Promotion::active()
            ->available()
            ->get();
            
        // Lọc các khuyến mại có thể áp dụng cho phòng này
        return $promotions->filter(function($promotion) use ($room) {
            return $promotion->canApplyToRoom($room->id);
        });
    }

    /**
     * Lấy danh sách khuyến mại "hot" nhất cho phòng (ưu tiên nổi bật và giảm giá cao)
     */
    public function getTopPromotions(Room $room, float $amountContext, int $limit = 3): Collection
    {
        $available = $this->getAvailablePromotions($room)
            // Chỉ lấy khuyến mại thực sự áp dụng được ở mức giá hiện tại
            ->filter(function ($promotion) use ($amountContext) {
                return $promotion->canApplyToAmount($amountContext)
                    && $promotion->calculateDiscount($amountContext) > 0;
            });

        if ($available->isEmpty()) {
            return collect();
        }

        // Tính điểm hotness: ưu tiên is_featured, sau đó theo số tiền giảm được tại mức giá tham chiếu
        return $available
            ->sortByDesc(function ($promotion) use ($amountContext) {
                $featuredWeight = $promotion->is_featured ? 1_000_000_000 : 0;
                $discountWeight = (int) round($promotion->calculateDiscount($amountContext) * 1000);
                // Có thể cộng thêm ưu tiên sắp hết hạn nếu muốn: $expiryWeight = ...
                return $featuredWeight + $discountWeight;
            })
            ->take($limit)
            ->values();
    }
    
    /**
     * Lấy khuyến mại tốt nhất cho phòng
     */
    public function getBestPromotion(Room $room, float $amount): ?Promotion
    {
        $promotions = $this->getAvailablePromotions($room);
        
        if ($promotions->isEmpty()) {
            return null;
        }
        
        // Sắp xếp theo số tiền giảm giá
        return $promotions->sortByDesc(function($promotion) use ($amount) {
            return $promotion->calculateDiscount($amount);
        })->first();
    }
    
    /**
     * Tính toán giá cuối cùng sau khi áp dụng khuyến mại tốt nhất
     */
    public function calculateFinalPrice(Room $room, float $amount, int $nights = 1): array
    {
        $bestPromotion = $this->getBestPromotion($room, $amount);
        
        if (!$bestPromotion) {
            return [
                'original_price' => $amount,
                'discount_amount' => 0,
                'final_price' => $amount,
                'promotion' => null,
                'per_night' => $amount / $nights
            ];
        }
        
        $discountAmount = $bestPromotion->calculateDiscount($amount);
        $finalPrice = $amount - $discountAmount;
        
        return [
            'original_price' => $amount,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'promotion' => $bestPromotion,
            'per_night' => $finalPrice / $nights
        ];
    }
    
    /**
     * Kiểm tra phòng có khuyến mại không
     */
    public function hasPromotion(Room $room): bool
    {
        return $this->getAvailablePromotions($room)->isNotEmpty();
    }
    
    /**
     * Lấy text hiển thị khuyến mại cho phòng
     */
    public function getPromotionText(Room $room): string
    {
        $promotions = $this->getAvailablePromotions($room);
        
        if ($promotions->isEmpty()) {
            return '';
        }
        
        // Lấy khuyến mại có % giảm cao nhất
        $bestPromotion = $promotions->sortByDesc(function($promotion) {
            if ($promotion->discount_type === 'percentage') {
                return $promotion->discount_value;
            }
            return 0;
        })->first();
        
        return $bestPromotion->discount_text;
    }

    /**
     * Tính toán giá khi áp dụng một khuyến mãi cụ thể
     */
    public function calculateWithPromotion(float $originalPrice, ?Promotion $promotion = null): array
    {
        if (!$promotion) {
            return [
                'original_price' => $originalPrice,
                'discount_amount' => 0,
                'final_price' => $originalPrice,
                'promotion' => null
            ];
        }

        $discountAmount = $promotion->calculateDiscount($originalPrice);
        $finalPrice = $originalPrice - $discountAmount;

        return [
            'original_price' => $originalPrice,
            'discount_amount' => $discountAmount,
            'final_price' => $finalPrice,
            'promotion' => $promotion
        ];
    }
} 