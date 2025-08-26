<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\TourBooking;
use Carbon\Carbon;

class BookingPriceCalculationService
{
    /**
     * Tính toán tổng tiền cho regular booking
     */
    public function calculateRegularBookingTotal(Booking $booking): array
    {
        // 1) Số đêm và tiền phòng
        // Tính số đêm theo logic khách sạn: check-out - check-in
        // Sử dụng startOfDay() để tránh vấn đề về giờ phút
        $checkIn = $booking->check_in_date->copy()->startOfDay();
        $checkOut = $booking->check_out_date->copy()->startOfDay();
        // diffInDays() trả về khoảng cách giữa 2 ngày
        // Ví dụ: check-in 24/08, check-out 27/08 → diffInDays = 3, khách ở 3 đêm (24, 25, 26)
        $nights = $checkIn->diffInDays($checkOut);
        // Nếu check-in và check-out cùng ngày, tính 1 đêm
        if ($nights < 1) $nights = 1;
        
        // 2) Phụ thu/hoàn đổi phòng (tổng tất cả lần đổi đã approved/completed)
        $roomChangeSurcharge = (float) $booking->roomChanges()
            ->whereIn('status', ['approved', 'completed'])
            ->sum('price_difference');

        // 1') Tính tiền phòng mới theo room type hiện tại (sau đổi)
        $nightlyNew = (int)($booking->room->roomType->price ?? 0);
        $finalRoomCost = max(0, $nights) * $nightlyNew; // tiền phòng mới

        // 1'') Suy ra tiền phòng cũ từ tổng phụ thu/hoàn: old = new - diff
        $roomCost = $finalRoomCost - $roomChangeSurcharge; // tiền phòng cũ
        $nightly = $nights > 0 ? (int) round($roomCost / $nights) : $nightlyNew;

        // 3) Phụ phí người lớn/trẻ em (KHÔNG bao gồm phụ thu đổi phòng)
        $guestSurcharge = (float)($booking->surcharge ?? 0);

        // 4) Dịch vụ (khách chọn + admin thêm)
        $svcFromAdmin = (float)($booking->total_services_price ?? 0);
        $svcFromClient = (float)($booking->extra_services_total ?? 0);
        $svcTotal = $svcFromAdmin + $svcFromClient;

        // 5) Khuyến mại - Lấy từ nhiều nguồn
        $totalDiscount = 0;
        
        // Ưu tiên lấy từ promotion_discount của booking
        if ((float)($booking->promotion_discount ?? 0) > 0) {
            $totalDiscount = (float) $booking->promotion_discount;
        }
        // Nếu không có, lấy từ payments
        elseif ($booking->payments()->where('status', '!=', 'failed')->sum('discount_amount') > 0) {
            $totalDiscount = (float) $booking->payments()->where('status', '!=', 'failed')->sum('discount_amount');
        }
        
        // Debug khuyến mại
        \Illuminate\Support\Facades\Log::info('Promotion Debug', [
            'booking_id' => $booking->id,
            'promotion_discount' => $booking->promotion_discount,
            'payments_discount' => $booking->payments()->where('status', '!=', 'failed')->sum('discount_amount'),
            'final_discount' => $totalDiscount
        ]);

        // 6) Tổng tiền cuối - Logic chính xác
        // Tổng tiền trước khuyến mại = Tiền phòng + Dịch vụ + Phụ phí
        $totalBeforeDiscount = $finalRoomCost + $svcTotal + $guestSurcharge;
        
        // Khuyến mại áp dụng trên tổng tiền trước khuyến mại
        $finalTotal = $totalBeforeDiscount - $totalDiscount;
        
        // totalAmount giữ nguyên để tương thích với code cũ
        $totalAmount = $finalTotal;

        // Debug: Log các giá trị để kiểm tra
        \Illuminate\Support\Facades\Log::info('BookingPriceCalculationService Debug', [
            'booking_id' => $booking->id,
            'nights' => $nights,
            'nightly' => $nightly,
            'roomCost' => $roomCost,
            'booking_price' => $booking->price,
            'room_type_price' => $booking->room->roomType->price ?? 'N/A',
            'finalRoomCost' => $finalRoomCost,
            'svcTotal' => $svcTotal,
            'totalAmount' => $totalAmount,
            'note' => '$booking->price là tổng tiền, $nightly là giá/đêm từ room type'
        ]);

        return [
            'nights' => $nights,
            'nightly' => $nightly,
            'roomCost' => $roomCost,
            'roomChangeSurcharge' => $roomChangeSurcharge,
            'finalRoomCost' => $finalRoomCost,
            'guestSurcharge' => $guestSurcharge,
            'svcFromAdmin' => $svcFromAdmin,
            'svcFromClient' => $svcFromClient,
            'svcTotal' => $svcTotal,
            'totalDiscount' => $totalDiscount,
            'totalBeforeDiscount' => $totalBeforeDiscount,
            'totalAmount' => $totalAmount,
            'fullTotal' => $finalTotal, // Tổng tiền cuối sau khuyến mại
            'breakdown' => [
                'room' => [
                    'base' => $roomCost,
                    'change_surcharge' => $roomChangeSurcharge,
                    'final' => $finalRoomCost
                ],
                'services' => [
                    'client' => $svcFromClient,
                    'admin' => $svcFromAdmin,
                    'total' => $svcTotal
                ],
                'fees' => [
                    'guest_surcharge' => $guestSurcharge
                ],
                'discount' => $totalDiscount,
                'total' => $finalTotal,
                'full_total' => $finalTotal
            ]
        ];
    }

    /**
     * Tính toán tổng tiền cho tour booking
     */
    public function calculateTourBookingTotal(TourBooking $tourBooking): array
    {
        // 1) Tiền phòng
        $roomCost = (float)($tourBooking->total_rooms_amount ?? 0);

        // 2) Dịch vụ
        $serviceCost = (float)($tourBooking->total_services_amount ?? 0);

        // 3) Khuyến mại
        $totalDiscount = (float)($tourBooking->promotion_discount ?? 0);

        // 4) Tổng tiền cuối
        $totalBeforeDiscount = $roomCost + $serviceCost;
        $totalAmount = $totalBeforeDiscount - $totalDiscount;

        return [
            'roomCost' => $roomCost,
            'serviceCost' => $serviceCost,
            'totalDiscount' => $totalDiscount,
            'totalBeforeDiscount' => $totalBeforeDiscount,
            'totalAmount' => $totalAmount,
            'breakdown' => [
                'room' => $roomCost,
                'services' => $serviceCost,
                'discount' => $totalDiscount,
                'total' => $totalAmount
            ]
        ];
    }

    /**
     * Tính toán số tiền còn thiếu
     */
    public function calculateOutstandingAmount($booking, float $totalAmount): float
    {
        $totalPaid = (float)($booking->total_paid ?? 0);
        return max(0, $totalAmount - $totalPaid);
    }

    /**
     * Kiểm tra xem đã thanh toán đầy đủ chưa
     */
    public function isFullyPaid($booking, float $totalAmount): bool
    {
        $totalPaid = (float)($booking->total_paid ?? 0);
        return $totalPaid >= $totalAmount;
    }

    /**
     * Lấy thông tin thanh toán
     */
    public function getPaymentInfo($booking, float $totalAmount): array
    {
        $totalPaid = (float)($booking->total_paid ?? 0);
        $outstanding = $this->calculateOutstandingAmount($booking, $totalAmount);
        $isFullyPaid = $this->isFullyPaid($booking, $totalAmount);

        return [
            'totalAmount' => $totalAmount,
            'totalPaid' => $totalPaid,
            'outstanding' => $outstanding,
            'isFullyPaid' => $isFullyPaid,
            'paymentPercentage' => $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100, 2) : 0
        ];
    }
}
