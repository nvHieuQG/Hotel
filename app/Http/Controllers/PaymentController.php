<?php

namespace App\Http\Controllers;

use App\Interfaces\Services\PaymentServiceInterface;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentServiceInterface $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Xác nhận thông tin thanh toán
     */
    public function confirmInfo(Booking $booking)
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập đặt phòng này.');
        }

        // Kiểm tra có thể thanh toán không (cho phép cả confirmed nếu còn thiếu)
        if (!$this->paymentService->canPayBooking($booking)) {
            return redirect()->route('booking.detail', $booking->id)
                ->with('error', 'Không thể thanh toán cho đặt phòng này.');
        }

        // Redirect đến payment-method với booking
        return redirect()->route('payment-method', $booking->id);
    }

    /**
     * Hiển thị trang chọn phương thức thanh toán
     */
    public function paymentMethod(Booking $booking = null)
    {
        // Nếu không có booking, chuyển hướng đến trang confirm
        if (!$booking) {
            return redirect()->route('booking.confirm');
        }

        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập đặt phòng này.');
        }

        // Kiểm tra có thể thanh toán không (cho phép cả confirmed nếu còn thiếu)
        if (!$this->paymentService->canPayBooking($booking)) {
            return redirect()->route('booking.detail', $booking->id)
                ->with('error', 'Không thể thanh toán cho đặt phòng này.');
        }

        $paymentMethods = $this->paymentService->getAvailablePaymentMethods();
        $availablePromotions = $this->paymentService->getAvailablePromotionsForBooking($booking);

        // Nhận promotion_id|code từ query (được truyền từ trang confirm)
        $promotionId = request()->get('promotion_id');
        $promotionCode = request()->get('promotion_code');

        return view('client.booking.payment-method', compact('booking', 'paymentMethods', 'availablePromotions'))
            ->with('promotionId', $promotionId)
            ->with('promotionCode', $promotionCode);
    }



    /**
     * Hiển thị trang thanh toán thành công
     */
    public function success(Booking $booking)
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập đặt phòng này.');
        }

        $latestPayment = $booking->latestPayment;

        // Kiểm tra nếu không có payment nào
        if (!$latestPayment) {
            return redirect()->route('booking.detail', $booking->id)
                ->with('error', 'Không tìm thấy thông tin thanh toán cho đặt phòng này.');
        }

        return view('client.payment.success', compact('booking', 'latestPayment'));
    }

    /**
     * Hiển thị trang thanh toán thất bại
     */
    public function failed()
    {
        return view('client.payment.failed');
    }

    /**
     * Preview khuyến mại theo thời gian thực
     */
    public function promotionPreview(Request $request, Booking $booking)
    {
        // Kiểm tra quyền
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $promotionId = $request->query('promotion_id');
        $promotionCode = $request->query('promotion_code');

        $calc = $this->paymentService->calculateFinalAmountWithPromotion($booking, $promotionId, $promotionCode);

        return response()->json([
            'success' => true,
            'base_amount' => (int) $calc['base_amount'],
            'discount_amount' => (int) $calc['discount_amount'],
            'final_amount' => (int) $calc['final_amount'],
            'promotion' => $calc['promotion'],
        ]);
    }

    /**
     * Hiển thị lịch sử thanh toán
     */
    public function history(Booking $booking)
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập đặt phòng này.');
        }

        $paymentHistory = $this->paymentService->getPaymentHistory($booking);

        return view('client.payment.history', compact('booking', 'paymentHistory'));
    }

    /**
     * Hiển thị form thanh toán chuyển khoản với số tiền tùy chọn
     */
    public function showBankTransferForm($bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập đặt phòng này.');
        }

        // Kiểm tra có thể thanh toán không
        if (!$this->paymentService->canPayBooking($booking)) {
            return redirect()->route('booking.detail', $booking->id)
                ->with('error', 'Không thể thanh toán cho đặt phòng này.');
        }

        try {
            // Lấy thông tin ngân hàng
            $bankInfo = $this->paymentService->getBankTransferInfo();
            
            // Tính toán số tiền tối thiểu và tối đa
            $calc = $this->paymentService->calculateFinalAmountWithPromotion($booking, null, null);
            $finalNeed = (int) round($calc['final_amount']);
            $alreadyPaid = (int) $booking->total_paid;
            $remaining = max(0, $finalNeed - $alreadyPaid);

            if ($alreadyPaid > 0) {
                // Thanh toán thêm: giới hạn theo phần còn thiếu
                $minAmount = (int) max(1, min(round($remaining * 0.2), $remaining));
                $maxAmount = (int) $remaining;
                $defaultAmount = (int) $remaining;
            } else {
                // Lần đầu: mặc định full, vẫn cho phép tối thiểu 20%
                $minAmount = (int) max(1, round($finalNeed * 0.2));
                $maxAmount = (int) $finalNeed;
                $defaultAmount = (int) $finalNeed;
            }

            // Sinh mã giao dịch khách hàng phải nhập khi chuyển khoản (hiển thị công khai)
            $expectedTransactionId = 'TT_' . $booking->booking_id . '_' . substr((string) time(), -6);
            
            // Tạo dữ liệu tạm thời
            $tempPaymentData = [
                'amount' => $defaultAmount, // Mặc định: full nếu chưa trả; còn lại nếu thanh toán thêm
                'discount_amount' => $calc['discount_amount'] ?? 0,
                'min_amount' => $minAmount,
                'max_amount' => $maxAmount,
                'expected_transaction_id' => $expectedTransactionId,
            ];

            // Lưu session để bước xác nhận đối chiếu mã giao dịch
            session(['temp_payment_data' => $tempPaymentData]);

            return view('client.payment.bank-transfer', compact('booking', 'tempPaymentData', 'bankInfo'));
        } catch (\Exception $e) {
            Log::error('Bank transfer form error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi hiển thị form thanh toán: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý chuyển khoản ngân hàng
     */
    public function processBankTransfer(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập đặt phòng này.');
        }
        
        // Kiểm tra trạng thái booking
        if (!$this->paymentService->canPayBooking($booking)) {
            return redirect()->back()->with('error', 'Không thể thanh toán cho đặt phòng này.');
        }

        // Validate số tiền thanh toán
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'promotion_id' => 'nullable|exists:promotions,id',
            'promotion_code' => 'nullable|string'
        ]);

        try {
            // Kiểm tra số tiền thanh toán tối thiểu 20%
            $calc = $this->paymentService->calculateFinalAmountWithPromotion($booking, $request->promotion_id, $request->promotion_code);
            $minAmount = (int) round($calc['final_amount'] * 0.2);
            $maxAmount = (int) $calc['final_amount'];
            
            if ($request->amount < $minAmount) {
                return redirect()->back()->withErrors(['amount' => "Số tiền thanh toán tối thiểu phải là " . number_format($minAmount) . " VNĐ (20%)"]);
            }
            
            if ($request->amount > $maxAmount) {
                return redirect()->back()->withErrors(['amount' => "Số tiền thanh toán không được vượt quá " . number_format($maxAmount) . " VNĐ"]);
            }

            // Tạo payment với số tiền tùy chọn
            $payment = $this->paymentService->createBankTransferPayment($booking, [
                'promotion_id' => $request->promotion_id,
                'promotion_code' => $request->promotion_code,
                'amount' => (int) $request->amount
            ]);

            // Lưu thông tin tạm thời vào session
            session([
                'temp_payment_data' => [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'discount_amount' => $payment->discount_amount,
                    'promotion_id' => $request->promotion_id,
                    'promotion_code' => $request->promotion_code,
                    'transaction_id' => $payment->transaction_id
                ]
            ]);

            return redirect()->route('payment.bank-transfer', $booking->id)
                           ->with('success', 'Đã tạo yêu cầu thanh toán chuyển khoản. Vui lòng thực hiện chuyển khoản và báo đã thanh toán.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý xác nhận chuyển khoản
     */
    public function confirmBankTransfer(Request $request, $bookingId)
    {
        $booking = Booking::findOrFail($bookingId);
        $tempPaymentData = session('temp_payment_data', []);

        $request->validate([
            'transaction_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'bank_name' => 'required|string',
            'customer_note' => 'nullable|string'
        ]);

        try {
            // Tính min/max theo final_amount để validate số tiền khách nhập (>=20%)
            $calc = $this->paymentService->calculateFinalAmountWithPromotion(
                $booking,
                $tempPaymentData['promotion_id'] ?? null,
                $tempPaymentData['promotion_code'] ?? null
            );
            $minAmount = (int) round($calc['final_amount'] * 0.2);
            $maxAmount = (int) round($calc['final_amount']);
            if ((int)$request->amount < $minAmount) {
                return redirect()->back()->withErrors(['amount' => "Số tiền thanh toán tối thiểu phải là " . number_format($minAmount) . " VNĐ (20%)"]);
            }
            if ((int)$request->amount > $maxAmount) {
                return redirect()->back()->withErrors(['amount' => "Số tiền thanh toán không được vượt quá " . number_format($maxAmount) . " VNĐ"]);
            }

            // 1-step: nếu không có temp payment trong session, tạo payment mới ngay
            $payment = null;
            if (!empty($tempPaymentData) && !empty($tempPaymentData['payment_id'])) {
                $payment = Payment::find($tempPaymentData['payment_id']);
            }

            // Đối chiếu mã giao dịch khách phải nhập với mã hệ thống sinh ra
            if (!empty($tempPaymentData['expected_transaction_id'])) {
                if ($request->transaction_id !== $tempPaymentData['expected_transaction_id']) {
                    return redirect()->back()->withErrors(['transaction_id' => 'Mã giao dịch không khớp. Vui lòng nhập đúng mã: ' . $tempPaymentData['expected_transaction_id']]);
                }
            }

            if (!$payment) {
                $payment = $this->paymentService->createBankTransferPayment($booking, [
                    'promotion_id' => $tempPaymentData['promotion_id'] ?? null,
                    'promotion_code' => $tempPaymentData['promotion_code'] ?? null,
                    'amount' => (int) $request->amount,
                ]);
            }
            if (!$payment) {
                return redirect()->back()->with('error', 'Không thể tạo giao dịch thanh toán.');
            }

            // Cập nhật payment -> processing để admin xác nhận; lưu thông tin giao dịch mà khách khai báo
            $payment->update([
                'status' => 'processing',
                'gateway_response' => [
                    'bank_name' => $request->bank_name,
                    'customer_transaction_id' => $request->transaction_id,
                    'reported_amount' => (int)$request->amount,
                    'customer_note' => $request->customer_note,
                    'reported_at' => now(),
                ]
            ]);

            // Dọn session tạm nếu có
            if (!empty($tempPaymentData)) {
                session()->forget('temp_payment_data');
            }

            return redirect()->route('payment.success', $booking->id)
                           ->with('success', 'Đã ghi nhận thanh toán. Admin sẽ kiểm tra và xác nhận trong thời gian sớm nhất.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xác nhận thanh toán: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý thanh toán thẻ tín dụng
     */
    public function processCreditCard(Request $request, Booking $booking)
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập đặt phòng này.');
        }

        // Kiểm tra có thể thanh toán không
        if (!$this->paymentService->canPayBooking($booking)) {
            return redirect()->route('booking.detail', $booking->id)
                ->with('error', 'Không thể thanh toán cho đặt phòng này.');
        }

        try {
            // Tạo payment record cho credit card
            $payment = $this->paymentService->createCreditCardPayment($booking, [
                'customer_note' => $request->input('customer_note'),
                'promotion_id' => $request->input('promotion_id'),
                'promotion_code' => $request->input('promotion_code'),
            ]);

            // Lấy thông tin thẻ test
            $creditCardInfo = $this->paymentService->getCreditCardTestInfo();

            return view('client.payment.credit-card', compact('booking', 'payment', 'creditCardInfo'));
        } catch (\Exception $e) {
            Log::error('Credit card payment error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi tạo thanh toán thẻ tín dụng: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý xác nhận thanh toán thẻ tín dụng
     */
    public function confirmCreditCard(Request $request, Booking $booking)
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập đặt phòng này.');
        }

        try {
            $result = $this->paymentService->processCreditCardPayment($request, $booking);

            if ($result['success']) {
                // Xác nhận booking khi thanh toán thành công
                $this->paymentService->confirmBookingAfterPayment($booking);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => $result['message'],
                        'redirect' => route('payment.success', $booking->id)
                    ]);
                }
                return redirect()->route('payment.success', $booking->id)
                    ->with('success', $result['message']);
            } else {
                // Hủy booking khi thanh toán thất bại
                $this->paymentService->cancelBookingAfterFailedPayment($booking);

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => $result['message']
                    ]);
                }
                return redirect()->back()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Credit card payment error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            $errorMessage = 'Có lỗi xảy ra khi xử lý thanh toán thẻ tín dụng: ' . $e->getMessage();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage
                ]);
            }

            return redirect()->back()
                ->with('error', $errorMessage);
        }
    }
}
