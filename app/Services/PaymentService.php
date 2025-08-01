<?php

namespace App\Services;

use App\Interfaces\Services\PaymentServiceInterface;
use App\Models\Booking;
use App\Models\Payment;
use App\Mail\PaymentConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class PaymentService implements PaymentServiceInterface
{


    /**
     * Create bank transfer payment
     */
    public function createBankTransferPayment(Booking $booking, array $data = []): Payment
    {
        $transactionId = 'BANK_' . $booking->booking_id . '_' . time();

        return $this->createPayment($booking, [
            'payment_method' => 'bank_transfer',
            'amount' => $booking->total_booking_price,
            'currency' => 'VND',
            'status' => 'pending',
            'transaction_id' => $transactionId,
            'gateway_name' => 'Bank Transfer',
            'gateway_response' => [
                'bank_info' => $this->getBankTransferInfo(),
                'customer_note' => $data['customer_note'] ?? null
            ]
        ]);
    }

    /**
     * Create credit card payment
     */
    public function createCreditCardPayment(Booking $booking, array $data = []): Payment
    {
        $transactionId = 'CC_' . $booking->booking_id . '_' . time();

        return $this->createPayment($booking, [
            'payment_method' => 'credit_card',
            'amount' => $booking->total_booking_price,
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
                'customer_note' => $data['customer_note'] ?? null
            ]
        ]);
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
            // Chỉ xác nhận booking có trạng thái pending_payment
            if ($booking->status !== 'pending_payment') {
                Log::warning('Cannot confirm booking: invalid status', [
                    'booking_id' => $booking->id,
                    'current_status' => $booking->status
                ]);
                return false;
            }

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

            Log::info('Booking confirmed after successful payment', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'amount' => $booking->price
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
     * Hủy booking khi thanh toán thất bại
     */
    public function cancelBookingAfterFailedPayment(Booking $booking): bool
    {
        try {
            // Chỉ hủy booking có trạng thái pending_payment
            if ($booking->status !== 'pending_payment') {
                Log::warning('Cannot cancel booking: invalid status', [
                    'booking_id' => $booking->id,
                    'current_status' => $booking->status
                ]);
                return false;
            }

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

            Log::info('Booking cancelled after failed payment', [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id
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
        return Payment::create([
            'booking_id' => $booking->id,
            'payment_method' => $data['payment_method'],
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'VND',
            'status' => $data['status'],
            'transaction_id' => $data['transaction_id'],
            'gateway_name' => $data['gateway_name'],
            'gateway_response' => $data['gateway_response'] ?? [],
        ]);
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

        return $payment->update($updateData);
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
        return $booking->payments()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Check if booking can be paid
     */
    public function canPayBooking(Booking $booking): bool
    {
        // Chỉ cho phép thanh toán booking có trạng thái pending_payment
        return $booking->status === 'pending_payment' && !$booking->hasSuccessfulPayment();
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
