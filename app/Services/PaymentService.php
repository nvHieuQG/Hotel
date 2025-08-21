<?php

namespace App\Services;

use App\Interfaces\Services\PaymentServiceInterface;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Promotion;
use App\Mail\PaymentConfirmationMail;
use App\Mail\BookingConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentService implements PaymentServiceInterface
{


    /**
     * Create bank transfer payment
     */
    public function createBankTransferPayment(Booking $booking, array $data = []): Payment
    {
        $transactionId = 'BANK_' . $booking->booking_id . '_' . time();

        // Promotion handling
        $promotionId = $data['promotion_id'] ?? null;
        $code = $data['promotion_code'] ?? null;
        $calc = $this->calculateFinalAmountWithPromotion($booking, $promotionId, $code);

        return $this->createPayment($booking, [
            'method' => 'bank_transfer',
            'amount' => $calc['final_amount'],
            'discount_amount' => $calc['discount_amount'],
            'currency' => 'VND',
            'status' => 'pending',
            'transaction_id' => $transactionId,
            'gateway_name' => 'Bank Transfer',
            'gateway_response' => [
                'bank_info' => $this->getBankTransferInfo(),
                'customer_note' => $data['customer_note'] ?? null,
                'promotion' => $calc['promotion']
            ],
            'promotion_id' => $calc['promotion_id'],
        ]);
    }

    /**
     * Create credit card payment
     */
    public function createCreditCardPayment(Booking $booking, array $data = []): Payment
    {
        $transactionId = 'CC_' . $booking->booking_id . '_' . time();

        // Promotion handling
        $promotionId = $data['promotion_id'] ?? null;
        $code = $data['promotion_code'] ?? null;
        $calc = $this->calculateFinalAmountWithPromotion($booking, $promotionId, $code);

        return $this->createPayment($booking, [
            'method' => 'credit_card',
            'amount' => $calc['final_amount'],
            'discount_amount' => $calc['discount_amount'],
            'currency' => 'VND',
            'status' => 'pending',
            'transaction_id' => $transactionId,
            'gateway_name' => 'Credit Card',
            'gateway_response' => [
                'card_info' => [
                    'last4' => $data['last4'] ?? null,
                    'brand' => $data['brand'] ?? null,
                    'exp_month' => $data['exp_month'] ?? null,
                    'exp_year' => $data['exp_year'] ?? null
                ],
                'customer_note' => $data['customer_note'] ?? null,
                'promotion' => $calc['promotion']
            ],
            'promotion_id' => $calc['promotion_id'],
        ]);
    }

    /**
     * Promotions: availability and calculation
     */
    public function getAvailablePromotionsForBooking(Booking $booking): array
    {
        $roomPrice = (float) $booking->base_room_price;
        $roomTypeId = (int) $booking->room->room_type_id;

        $promotions = Promotion::active()->available()->get();

        $list = [];
        foreach ($promotions as $promo) {
            if (!$promo->canApplyToRoomType($roomTypeId)) {
                continue;
            }
            if (!$promo->canApplyToAmount($roomPrice)) {
                continue;
            }
            $list[] = [
                'id' => $promo->id,
                'title' => $promo->title,
                'code' => $promo->code,
                'discount_text' => $promo->discount_text,
                'expired_at' => optional($promo->expired_at)->toDateString(),
            ];
        }
        return $list;
    }

    public function validatePromotionForBooking(Booking $booking, ?int $promotionId = null, ?string $code = null): array
    {
        if (!$promotionId && !$code) {
            return ['valid' => false, 'message' => 'Không có thông tin khuyến mại.', 'promotion' => null];
        }

        $promotion = null;

        if ($promotionId) {
            $promotion = Promotion::find($promotionId);
        } elseif ($code) {
            $promotion = Promotion::where('code', $code)->first();
        }

        if (!$promotion) {
            return ['valid' => false, 'message' => 'Không tìm thấy khuyến mại.', 'promotion' => null];
        }

        if (!$promotion->isActive()) {
            return ['valid' => false, 'message' => 'Khuyến mại không còn hiệu lực.', 'promotion' => null];
        }

        // Sử dụng giá phòng cơ bản để validate promotion (không bao gồm dịch vụ và phụ phí)
        $roomPrice = (float) $booking->base_room_price;
        $roomTypeId = (int) $booking->room->room_type_id;

        if (!$promotion->canApplyToRoomType($roomTypeId)) {
            return ['valid' => false, 'message' => 'Khuyến mại không áp dụng cho loại phòng này.', 'promotion' => null];
        }

        if (!$promotion->canApplyToAmount($roomPrice)) {
            return ['valid' => false, 'message' => 'Giá phòng không đủ điều kiện áp dụng khuyến mại.', 'promotion' => null];
        }

        return ['valid' => true, 'message' => 'Áp dụng khuyến mại thành công.', 'promotion' => $promotion];
    }

    public function calculateFinalAmountWithPromotion(Booking $booking, ?int $promotionId = null, ?string $code = null): array
    {
        // Chỉ áp dụng promotion cho giá phòng cơ bản, không cho dịch vụ và phụ phí
        $roomPrice = (float) $booking->base_room_price;
        $servicesAndSurcharge = $booking->surcharge + $booking->extra_services_total + $booking->total_services_price;
        $totalAmount = (float) $booking->total_booking_price;
        
        $discount = 0.0;
        $promotion = null;
        $promotionData = $this->validatePromotionForBooking($booking, $promotionId, $code);
        if ($promotionData['valid'] && $promotionData['promotion']) {
            $promotion = $promotionData['promotion'];
            // Chỉ tính discount trên giá phòng cơ bản
            $discount = (float) $promotion->calculateDiscount($roomPrice);
        }
        
        // Tổng cuối = giá phòng sau giảm giá + dịch vụ + phụ phí
        $final = max(0.0, $roomPrice - $discount + $servicesAndSurcharge);

        return [
            'base_amount' => $totalAmount, // Giữ nguyên để hiển thị
            'room_price' => $roomPrice,
            'services_surcharge' => $servicesAndSurcharge,
            'discount_amount' => $discount,
            'final_amount' => $final,
            'promotion_id' => $promotion?->id,
            'promotion' => $promotion ? [
                'id' => $promotion->id,
                'title' => $promotion->title,
                'code' => $promotion->code,
                'discount_text' => $promotion->discount_text,
            ] : null,
        ];
    }

    /**
     * Process credit card payment
     */
    public function processCreditCardPayment(Request $request, Booking $booking): array
    {
        $request->validate([
            'card_number' => 'required|string|regex:/^\d{16}$/',
            'expiry_month' => 'required|integer|between:1,12',
            'expiry_year' => 'required|integer|min:' . date('Y'),
            'cvv' => 'required|string|regex:/^\d{3,4}$/',
            'cardholder_name' => 'required|string|max:255',
            'transaction_id' => 'required|string'
        ]);

        $payment = $this->getPaymentByTransactionId($request->transaction_id);

        if (!$payment) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy thông tin thanh toán'
            ];
        }

        // Simulate credit card processing
        $cardNumber = $request->card_number;
        $last4 = substr($cardNumber, -4);
        $brand = $this->getCardBrand($cardNumber);

        // Simulate processing delay
        sleep(2);

        // Check if it's a test card
        $isTestCard = $this->isTestCard($cardNumber);

        if ($isTestCard) {
            // Simulate successful payment for test cards
            $this->updatePaymentStatus($payment, 'completed', [
                'gateway_code' => 'CC_SUCCESS',
                'gateway_message' => 'Thanh toán thẻ tín dụng thành công',
                'paid_at' => now(),
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'card_info' => [
                        'last4' => $last4,
                        'brand' => $brand,
                        'exp_month' => $request->expiry_month,
                        'exp_year' => $request->expiry_year
                    ],
                    'cardholder_name' => $request->cardholder_name,
                    'is_test_card' => true
                ])
            ]);

            // Send confirmation email
            $this->sendPaymentConfirmationEmail($payment);

            return [
                'success' => true,
                'message' => 'Thanh toán thẻ tín dụng thành công!',
                'payment' => $payment
            ];
        } else {
            // Simulate failed payment for non-test cards
            $this->updatePaymentStatus($payment, 'failed', [
                'gateway_code' => 'CC_FAILED',
                'gateway_message' => 'Thẻ không hợp lệ hoặc không đủ tiền',
                'gateway_response' => array_merge($payment->gateway_response ?? [], [
                    'card_info' => [
                        'last4' => $last4,
                        'brand' => $brand,
                        'exp_month' => $request->expiry_month,
                        'exp_year' => $request->expiry_year
                    ],
                    'cardholder_name' => $request->cardholder_name,
                    'is_test_card' => false
                ])
            ]);

            return [
                'success' => false,
                'message' => 'Thanh toán thất bại. Vui lòng kiểm tra lại thông tin thẻ hoặc sử dụng thẻ test.'
            ];
        }
    }

    /**
     * Get bank transfer information
     */
    public function getBankTransferInfo(): array
    {
        return [
            'banks' => [
                [
                    'name' => 'Vietcombank',
                    'account_number' => '1234567890',
                    'account_name' => 'CONG TY TNHH KHACH SAN MARRON',
                    'branch' => 'Chi nhánh TP.HCM',
                    'qr_code' => asset('images/qr-code.png'),
                    'transfer_content' => 'Thanh toan dat phong'
                ],
                [
                    'name' => 'BIDV',
                    'account_number' => '9876543210',
                    'account_name' => 'CONG TY TNHH KHACH SAN MARRON',
                    'branch' => 'Chi nhánh TP.HCM',
                    'qr_code' => asset('images/qr-code.png'),
                    'transfer_content' => 'Thanh toan dat phong'
                ],
                [
                    'name' => 'Techcombank',
                    'account_number' => '1122334455',
                    'account_name' => 'CONG TY TNHH KHACH SAN MARRON',
                    'branch' => 'Chi nhánh TP.HCM',
                    'qr_code' => asset('images/qr-code.png'),
                    'transfer_content' => 'Thanh toan dat phong'
                ]
            ],
            'instructions' => [
                'Bước 1: Chọn ngân hàng bạn muốn chuyển khoản',
                'Bước 2: Quét mã QR hoặc copy thông tin tài khoản',
                'Bước 3: Thực hiện chuyển khoản với nội dung: "Thanh toan dat phong"',
                'Bước 4: Sau khi chuyển khoản, vui lòng chụp ảnh biên lai và gửi cho chúng tôi',
                'Bước 5: Chúng tôi sẽ xác nhận thanh toán trong vòng 30 phút'
            ],
            'note' => 'Vui lòng chuyển khoản đúng số tiền và nội dung để tránh nhầm lẫn. Nếu có thắc mắc, vui lòng liên hệ với quản trị viên hoặc số điện thoại: 0979.833.135'
        ];
    }

    /**
     * Process bank transfer confirmation
     */
    public function processBankTransferConfirmation(Request $request, Booking $booking): array
    {
        $request->validate([
            'transaction_id' => 'required|string',
            'bank_name' => 'required|string',
            'transfer_amount' => 'required|numeric',
            'transfer_date' => 'required|date',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $payment = $this->getPaymentByTransactionId($request->transaction_id);

        if (!$payment) {
            return [
                'success' => false,
                'message' => 'Không tìm thấy thông tin thanh toán'
            ];
        }

        // Kiểm tra số tiền
        if ($payment->amount != $request->transfer_amount) {
            return [
                'success' => false,
                'message' => 'Số tiền chuyển khoản không khớp với số tiền cần thanh toán'
            ];
        }

        // Lưu ảnh biên lai nếu có
        $receiptPath = null;
        if ($request->hasFile('receipt_image')) {
            $receiptPath = $request->file('receipt_image')->store('receipts', 'public');
        }

        // Cập nhật trạng thái thanh toán
        $this->updatePaymentStatus($payment, 'processing', [
            'gateway_code' => 'BANK_TRANSFER',
            'gateway_message' => 'Đã nhận thông tin chuyển khoản, đang xác nhận',
            'paid_at' => now(), // Set paid_at khi xác nhận chuyển khoản
            'gateway_response' => array_merge($payment->gateway_response ?? [], [
                'bank_name' => $request->bank_name,
                'transfer_amount' => $request->transfer_amount,
                'transfer_date' => $request->transfer_date,
                'receipt_image' => $receiptPath,
                'customer_note' => $request->customer_note
            ])
        ]);

        return [
            'success' => true,
            'message' => 'Đã nhận thông tin chuyển khoản. Chúng tôi sẽ xác nhận trong thời gian sớm nhất.',
            'payment' => $payment
        ];
    }

    /**
     * Get credit card test information
     */
    public function getCreditCardTestInfo(): array
    {
        return [
            'test_cards' => [
                [
                    'number' => '4111111111111111',
                    'brand' => 'Visa',
                    'description' => 'Thẻ Visa test - Thanh toán thành công',
                    'cvv' => '123',
                    'expiry' => '12/25'
                ],
                [
                    'number' => '5555555555554444',
                    'brand' => 'Mastercard',
                    'description' => 'Thẻ Mastercard test - Thanh toán thành công',
                    'cvv' => '123',
                    'expiry' => '12/25'
                ],
                [
                    'number' => '378282246310005',
                    'brand' => 'American Express',
                    'description' => 'Thẻ American Express test - Thanh toán thành công',
                    'cvv' => '1234',
                    'expiry' => '12/25'
                ],
                [
                    'number' => '4000000000000002',
                    'brand' => 'Visa',
                    'description' => 'Thẻ Visa test - Thanh toán thất bại',
                    'cvv' => '123',
                    'expiry' => '12/25'
                ]
            ],
            'instructions' => [
                'Sử dụng các thẻ test để kiểm tra tính năng thanh toán',
                'Chỉ các thẻ có số bắt đầu bằng 4111, 5555, 3782 sẽ thanh toán thành công',
                'Các thẻ khác sẽ được xử lý như thanh toán thất bại',
                'Thông tin thẻ sẽ được mã hóa và bảo mật',
                'Không lưu trữ thông tin thẻ thực tế trong hệ thống'
            ],
            'security_note' => 'Đây là môi trường test. Trong môi trường production, hệ thống sẽ tích hợp với cổng thanh toán thực tế như VNPay, MoMo, hoặc các cổng thanh toán quốc tế.'
        ];
    }

    /**
     * Xác nhận booking khi thanh toán thành công
     */
    public function confirmBookingAfterPayment(Booking $booking): bool
    {
        try {
            // Chỉ xác nhận booking có trạng thái pending hoặc pending_payment
            if ($booking->status !== 'pending' && $booking->status !== 'pending_payment') {
                Log::warning('Cannot confirm booking: invalid status', [
                    'booking_id' => $booking->id,
                    'current_status' => $booking->status
                ]);
                return false;
            }

            $oldStatus = $booking->status;

            // Cập nhật trạng thái booking thành confirmed
            $booking->update([
                'status' => 'confirmed'
            ]);

            // Tạo ghi chú hệ thống
            $booking->notes()->create([
                'user_id' => $booking->user_id,
                'content' => 'Đặt phòng đã được xác nhận sau khi thanh toán thành công',
                'type' => 'system',
                'visibility' => 'internal',
                'is_internal' => true
            ]);

            // Tạo thông báo cho việc xác nhận booking
            $this->createBookingConfirmationNotification($booking, $oldStatus);

            // Gửi email xác nhận đặt phòng
            $this->sendBookingConfirmationEmail($booking);

            Log::info('Booking confirmed after successful payment', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'amount' => $booking->price,
                'old_status' => $oldStatus,
                'new_status' => 'confirmed'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error confirming booking after payment', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Tạo thông báo xác nhận booking
     */
    private function createBookingConfirmationNotification(Booking $booking, string $oldStatus): void
    {
        try {
            // Tạo thông báo cho việc xác nhận booking
            $notificationData = [
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => 'confirmed'
            ];

            // Gọi service để tạo thông báo
            $adminBookingService = app(\App\Services\Admin\AdminBookingService::class);
            $adminBookingService->createNotification(
                'booking_confirmed',
                'Đặt phòng đã được xác nhận',
                "Đặt phòng #{$booking->booking_id} đã được xác nhận sau khi thanh toán thành công.",
                $notificationData,
                'high',
                'fas fa-check-circle',
                'success'
            );
        } catch (\Exception $e) {
            Log::error('Failed to create booking confirmation notification: ' . $e->getMessage());
        }
    }

    /**
     * Gửi email xác nhận đặt phòng
     */
    private function sendBookingConfirmationEmail(Booking $booking): void
    {
        try {
            $latestPayment = $booking->payments->where('status', 'completed')->first();
            if ($latestPayment) {
                Mail::to($booking->user->email)->send(new \App\Mail\BookingConfirmationMail($booking, $latestPayment));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation email: ' . $e->getMessage());
        }
    }

    /**
     * Hủy booking khi thanh toán thất bại
     */
    public function cancelBookingAfterFailedPayment(Booking $booking): bool
    {
        try {
            // Chỉ hủy booking có trạng thái pending hoặc pending_payment
            if ($booking->status !== 'pending' && $booking->status !== 'pending_payment') {
                Log::warning('Cannot cancel booking: invalid status', [
                    'booking_id' => $booking->id,
                    'current_status' => $booking->status
                ]);
                return false;
            }

            $oldStatus = $booking->status;

            // Cập nhật trạng thái booking thành cancelled
            $booking->update([
                'status' => 'cancelled'
            ]);

            // Tạo ghi chú hệ thống
            $booking->notes()->create([
                'user_id' => $booking->user_id,
                'content' => 'Đặt phòng đã bị hủy do thanh toán thất bại',
                'type' => 'system',
                'visibility' => 'internal',
                'is_internal' => true
            ]);

            // Tạo thông báo cho việc hủy booking
            $this->createBookingCancellationNotification($booking, $oldStatus);

            Log::info('Booking cancelled after failed payment', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'old_status' => $oldStatus,
                'new_status' => 'cancelled'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error cancelling booking after failed payment', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Tạo thông báo hủy booking
     */
    private function createBookingCancellationNotification(Booking $booking, string $oldStatus): void
    {
        try {
            // Tạo thông báo cho việc hủy booking
            $notificationData = [
                'booking_id' => $booking->id,
                'old_status' => $oldStatus,
                'new_status' => 'cancelled'
            ];

            // Gọi service để tạo thông báo
            $adminBookingService = app(\App\Services\Admin\AdminBookingService::class);
            $adminBookingService->createNotification(
                'booking_cancelled',
                'Đặt phòng đã bị hủy',
                "Đặt phòng #{$booking->booking_id} đã bị hủy do thanh toán thất bại.",
                $notificationData,
                'high',
                'fas fa-times-circle',
                'danger'
            );
        } catch (\Exception $e) {
            Log::error('Failed to create booking cancellation notification: ' . $e->getMessage());
        }
    }

    /**
     * Get card brand from card number
     */
    private function getCardBrand(string $cardNumber): string
    {
        $firstTwo = substr($cardNumber, 0, 2);
        $firstFour = substr($cardNumber, 0, 4);
        $firstSix = substr($cardNumber, 0, 6);

        if (substr($cardNumber, 0, 1) === '4') {
            return 'Visa';
        } elseif (substr($cardNumber, 0, 2) >= '51' && substr($cardNumber, 0, 2) <= '55') {
            return 'Mastercard';
        } elseif (substr($cardNumber, 0, 2) === '34' || substr($cardNumber, 0, 2) === '37') {
            return 'American Express';
        } elseif (
            substr($cardNumber, 0, 2) === '36' || substr($cardNumber, 0, 2) === '38' ||
            ($firstSix >= '222100' && $firstSix <= '272099')
        ) {
            return 'Diners Club';
        } elseif (
            substr($cardNumber, 0, 4) === '6011' ||
            ($firstSix >= '622126' && $firstSix <= '622925') ||
            ($firstTwo >= '64' && $firstTwo <= '65')
        ) {
            return 'Discover';
        } else {
            return 'Unknown';
        }
    }

    /**
     * Check if card is a test card
     */
    private function isTestCard(string $cardNumber): bool
    {
        $testPrefixes = ['4111', '5555', '3782'];
        $prefix = substr($cardNumber, 0, 4);

        return in_array($prefix, $testPrefixes);
    }

    /**
     * Create payment record
     */
    public function createPayment(Booking $booking, array $data): Payment
    {
        $paymentData = [
            'booking_id' => $booking->id,
            'promotion_id' => $data['promotion_id'] ?? null,
            'method' => $data['method'],
            'amount' => $data['amount'],
            'discount_amount' => $data['discount_amount'] ?? 0,
            'currency' => $data['currency'] ?? 'VND',
            'status' => $data['status'],
            'transaction_id' => $data['transaction_id'],
            'gateway_name' => $data['gateway_name'],
            'gateway_response' => is_array($data['gateway_response'] ?? []) ? json_encode($data['gateway_response']) : ($data['gateway_response'] ?? '{}'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $id = DB::table('payments')->insertGetId($paymentData);
        
        return Payment::find($id);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Payment $payment, string $status, array $data = []): bool
    {
        $updateData = ['status' => $status];

        if (isset($data['gateway_code'])) {
            $updateData['gateway_code'] = $data['gateway_code'];
        }

        if (isset($data['gateway_message'])) {
            $updateData['gateway_message'] = $data['gateway_message'];
        }

        if (isset($data['gateway_response'])) {
            $updateData['gateway_response'] = $data['gateway_response'];
        }

        if (isset($data['paid_at'])) {
            $updateData['paid_at'] = $data['paid_at'];
        }

        $success = $payment->update($updateData);

        // Nếu thanh toán thành công và booking đang ở trạng thái pending hoặc pending_payment
        if ($success && $status === 'completed' && ($payment->booking->status === 'pending' || $payment->booking->status === 'pending_payment')) {
            $this->confirmBookingAfterPayment($payment->booking);
        }

        // Nếu thanh toán thất bại và booking đang ở trạng thái pending hoặc pending_payment
        if ($success && $status === 'failed' && ($payment->booking->status === 'pending' || $payment->booking->status === 'pending_payment')) {
            $this->cancelBookingAfterFailedPayment($payment->booking);
        }

        return $success;
    }

    /**
     * Get payment by transaction ID
     */
    public function getPaymentByTransactionId(string $transactionId): ?Payment
    {
        return Payment::where('transaction_id', $transactionId)->first();
    }

    /**
     * Get payment history for booking
     */
    public function getPaymentHistory(Booking $booking): \Illuminate\Database\Eloquent\Collection
    {
        return $booking->payments()->orderBy('id', 'desc')->get();
    }

    /**
     * Check if booking can be paid
     */
    public function canPayBooking(Booking $booking): bool
    {
        // Chỉ cho phép thanh toán booking có trạng thái pending hoặc pending_payment
        return ($booking->status === 'pending' || $booking->status === 'pending_payment') && !$booking->hasSuccessfulPayment();
    }

    /**
     * Get payment methods available
     */
    public function getAvailablePaymentMethods(): array
    {
        return [
            'credit_card' => [
                'name' => 'Thẻ tín dụng/ghi nợ',
                'description' => 'Thanh toán bằng thẻ Visa, Mastercard, American Express',
                'icon' => 'fas fa-credit-card',
                'enabled' => true
            ],
            'bank_transfer' => [
                'name' => 'Chuyển khoản ngân hàng',
                'description' => 'Chuyển khoản trực tiếp đến tài khoản ngân hàng',
                'icon' => 'fas fa-university',
                'enabled' => true
            ],
            'cod' => [
                'name' => 'Thanh toán tại khách sạn',
                'description' => 'Thanh toán khi nhận phòng',
                'icon' => 'fas fa-money-bill-wave',
                'enabled' => true
            ]
        ];
    }

    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmationEmail(Payment $payment): void
    {
        try {
            $booking = $payment->booking;
            Mail::to($booking->user->email)->send(new \App\Mail\PaymentConfirmationMail($booking, $payment));
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation email: ' . $e->getMessage());
        }
    }
}
