<?php

namespace App\Http\Controllers;

use App\Interfaces\Services\PaymentServiceInterface;
use App\Models\Booking;
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

        // Kiểm tra booking có trạng thái pending_payment
        if ($booking->status !== 'pending_payment') {
            return redirect()->route('booking.detail', $booking->id)
                ->with('error', 'Chỉ có thể thanh toán cho đặt phòng đang chờ thanh toán.');
        }

        // Kiểm tra có thể thanh toán không
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

        // Kiểm tra booking có trạng thái pending_payment
        if ($booking->status !== 'pending_payment') {
            return redirect()->route('booking.detail', $booking->id)
                ->with('error', 'Chỉ có thể thanh toán cho đặt phòng đang chờ thanh toán.');
        }

        // Kiểm tra có thể thanh toán không
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
     * Xử lý chuyển khoản ngân hàng
     */
    public function processBankTransfer(Request $request, Booking $booking)
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
            // Tạo payment record cho chuyển khoản
            $payment = $this->paymentService->createBankTransferPayment($booking, [
                'customer_note' => $request->input('customer_note'),
                'promotion_id' => $request->input('promotion_id'),
                'promotion_code' => $request->input('promotion_code'),
            ]);

            // Lấy thông tin ngân hàng
            $bankInfo = $this->paymentService->getBankTransferInfo();

            return view('client.payment.bank-transfer', compact('booking', 'payment', 'bankInfo'));
        } catch (\Exception $e) {
            Log::error('Bank transfer error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi tạo thanh toán chuyển khoản: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý xác nhận chuyển khoản
     */
    public function confirmBankTransfer(Request $request, Booking $booking)
    {
        // Kiểm tra quyền truy cập
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập đặt phòng này.');
        }

        try {
            $result = $this->paymentService->processBankTransferConfirmation($request, $booking);

            if ($result['success']) {
                // Xác nhận booking khi thanh toán thành công
                $this->paymentService->confirmBookingAfterPayment($booking);

                return redirect()->route('payment.success', $booking->id)
                    ->with('success', $result['message']);
            } else {
                // Hủy booking khi thanh toán thất bại
                $this->paymentService->cancelBookingAfterFailedPayment($booking);

                return redirect()->back()
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Bank transfer confirmation error', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi xác nhận chuyển khoản: ' . $e->getMessage());
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
