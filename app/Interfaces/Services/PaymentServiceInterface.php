<?php

namespace App\Interfaces\Services;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentServiceInterface
{


    /**
     * Create bank transfer payment
     */
    public function createBankTransferPayment(Booking $booking, array $data = []): Payment;

    /**
     * Get bank transfer information
     */
    public function getBankTransferInfo(): array;

    /**
     * Process bank transfer confirmation
     */
    public function processBankTransferConfirmation(Request $request, Booking $booking): array;

    /**
     * Create credit card payment
     */
    public function createCreditCardPayment(Booking $booking, array $data = []): Payment;

    /**
     * Promotions: availability and calculation
     */
    public function getAvailablePromotionsForBooking(Booking $booking): array;
    public function validatePromotionForBooking(Booking $booking, ?int $promotionId = null, ?string $code = null): array;
    public function calculateFinalAmountWithPromotion(Booking $booking, ?int $promotionId = null, ?string $code = null): array;

    /**
     * Process credit card payment
     */
    public function processCreditCardPayment(Request $request, Booking $booking): array;

    /**
     * Get credit card test information
     */
    public function getCreditCardTestInfo(): array;

    /**
     * Create payment record
     */
    public function createPayment(Booking $booking, array $data): Payment;

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Payment $payment, string $status, array $data = []): bool;

    /**
     * Get payment by transaction ID
     */
    public function getPaymentByTransactionId(string $transactionId): ?Payment;

    /**
     * Get payment history for booking
     */
    public function getPaymentHistory(Booking $booking): \Illuminate\Database\Eloquent\Collection;

    /**
     * Check if booking can be paid
     */
    public function canPayBooking(Booking $booking): bool;

    /**
     * Get payment methods available
     */
    public function getAvailablePaymentMethods(): array;

    /**
     * Xác nhận booking khi thanh toán thành công
     */
    public function confirmBookingAfterPayment(Booking $booking): bool;

    /**
     * Hủy booking khi thanh toán thất bại
     */
    public function cancelBookingAfterFailedPayment(Booking $booking): bool;
}
