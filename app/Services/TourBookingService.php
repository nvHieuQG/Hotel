<?php

namespace App\Services;

use App\Interfaces\Services\TourBookingServiceInterface;
use App\Interfaces\Repositories\TourBookingRepositoryInterface;
use App\Interfaces\Services\RoomTypeServiceInterface;
use App\Models\TourBooking;
use App\Models\TourBookingRoom;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class TourBookingService implements TourBookingServiceInterface
{
    protected $tourBookingRepository;
    protected $roomTypeService;

    public function __construct(
        TourBookingRepositoryInterface $tourBookingRepository,
        RoomTypeServiceInterface $roomTypeService
    ) {
        $this->tourBookingRepository = $tourBookingRepository;
        $this->roomTypeService = $roomTypeService;
    }

    /**
     * Tạo tour booking mới
     */
    public function createTourBooking(array $data)
    {
        try {
            DB::beginTransaction();

            // Tính toán giá cho room selections
            $calculatedData = $this->calculateTourBookingPrice(
                $data['room_selections'],
                $data['check_in_date'],
                $data['check_out_date']
            );

            // Tạo tour booking
            $tourBooking = $this->tourBookingRepository->create([
                'user_id' => $data['user_id'],
                'booking_id' => TourBooking::generateBookingId(),
                'tour_name' => $data['tour_name'],
                'total_guests' => $data['total_guests'],
                'total_rooms' => $data['total_rooms'],
                'check_in_date' => $data['check_in_date'],
                'check_out_date' => $data['check_out_date'],
                'total_price' => $data['total_price'] ?? $calculatedData['total_price'], // Sử dụng giá sau giảm giá nếu có
                'status' => 'pending',
                'special_requests' => $data['special_requests'] ?? null,
                'tour_details' => $data['tour_details'] ?? null,
                'promotion_code' => $data['promotion_code'] ?? null,
                'promotion_discount' => $data['promotion_discount'] ?? 0,
                'promotion_id' => $data['promotion_id'] ?? null,
                'final_price' => $data['final_price'] ?? $calculatedData['total_price'],
            ]);

            // Tạo tour booking rooms với giá đã tính toán
            $createdRooms = [];
            foreach ($calculatedData['room_selections'] as $roomSelection) {
                $created = TourBookingRoom::create([
                    'tour_booking_id' => $tourBooking->id,
                    'room_type_id' => $roomSelection['room_type_id'],
                    'quantity' => $roomSelection['quantity'],
                    'guests_per_room' => $roomSelection['guests_per_room'],
                    'price_per_room' => $roomSelection['price_per_room'],
                    'total_price' => $roomSelection['total_price'],
                    'guest_details' => $roomSelection['guest_details'] ?? null,
                ]);
                $createdRooms[] = $created;
            }

            // Gán cố định phòng theo từng room_type ngay tại thời điểm tạo (best-effort)
            foreach ($createdRooms as $tbr) {
                if (!$tbr instanceof \App\Models\TourBookingRoom) {
                    continue;
                }
                $assigned = $this->assignFixedRoomsForTourSelection(
                    $tbr->room_type_id,
                    (int)$tbr->quantity,
                    $data['check_in_date'],
                    $data['check_out_date']
                );
                if (!empty($assigned)) {
                    $tbr->update(['assigned_room_ids' => $assigned]);
                }
            }

            DB::commit();
            return $tourBooking;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tour booking creation error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gán cố định N phòng thuộc room_type cho khoảng ngày, ưu tiên phòng có id nhỏ và thực sự trống toàn dải.
     */
    private function assignFixedRoomsForTourSelection(int $roomTypeId, int $quantity, $checkInDate, $checkOutDate): array
    {
        $assigned = [];
        $roomType = RoomType::with('rooms')->find($roomTypeId);
        if (!$roomType || !$roomType->rooms || $quantity <= 0) return $assigned;

        $rooms = $roomType->rooms->sortBy('id')->values();
        foreach ($rooms as $room) {
            if (count($assigned) >= $quantity) break;
            // Chỉ chọn phòng thật sự trống toàn dải
            if ($room->isStrictlyAvailableForRange($checkInDate, $checkOutDate)) {
                $assigned[] = $room->id;
            }
        }
        return $assigned;
    }

    /**
     * Lấy danh sách tour bookings của user
     */
    public function getUserTourBookings($userId)
    {
        return $this->tourBookingRepository->getByUserId($userId);
    }

    /**
     * Lấy chi tiết tour booking
     */
    public function getTourBookingById($id)
    {
        return $this->tourBookingRepository->findById($id);
    }

    /**
     * Lấy chi tiết tour booking theo booking ID
     */
    public function getTourBookingByBookingId($bookingId)
    {
        return $this->tourBookingRepository->findByBookingId($bookingId);
    }

    /**
     * Cập nhật trạng thái tour booking
     */
    public function updateTourBookingStatus($id, $status)
    {
        $updated = $this->tourBookingRepository->update($id, ['status' => $status]);
        if ($updated && in_array($status, ['cancelled','completed'])) {
            // Clear assigned_room_ids khi tour kết thúc/hủy
            try {
                $tbrs = \App\Models\TourBookingRoom::where('tour_booking_id', $id)->get();
                foreach ($tbrs as $tbr) {
                    if (!empty($tbr->assigned_room_ids)) {
                        $tbr->update(['assigned_room_ids' => null]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Clear assigned_room_ids failed: '.$e->getMessage());
            }
        }
        return $updated;
    }

    /**
     * Reassign assigned_room_ids cho một TourBooking (admin trigger)
     */
    public function reassignTourRooms(int $tourBookingId): array
    {
        $tb = $this->tourBookingRepository->findById($tourBookingId);
        if (!$tb) return ['success' => false, 'message' => 'Tour booking không tồn tại'];
        $tbrs = \App\Models\TourBookingRoom::where('tour_booking_id', $tb->id)->get();
        $changed = 0; $errors = 0;
        foreach ($tbrs as $tbr) {
            try {
                $assigned = $this->assignFixedRoomsForTourSelection(
                    (int)$tbr->room_type_id,
                    (int)$tbr->quantity,
                    $tb->check_in_date,
                    $tb->check_out_date
                );
                $tbr->update(['assigned_room_ids' => $assigned]);
                $changed++;
            } catch (\Throwable $e) {
                Log::warning('Reassign room failed: '.$e->getMessage());
                $errors++;
            }
        }
        return ['success' => true, 'changed' => $changed, 'errors' => $errors];
    }

    /**
     * Tính toán giá tour booking
     */
    public function calculateTourBookingPrice(array $roomSelections, $checkInDate, $checkOutDate)
    {
        $totalPrice = 0;
        $totalNights = Carbon::parse($checkInDate)->diffInDays(Carbon::parse($checkOutDate));
        $calculatedSelections = [];

        foreach ($roomSelections as $selection) {
            $roomType = RoomType::find($selection['room_type_id']);
            if ($roomType) {
                $pricePerNight = $roomType->price;
                $pricePerRoom = $pricePerNight * $totalNights;
                $totalForRoomType = $pricePerRoom * $selection['quantity'];

                $calculatedSelections[] = [
                    'room_type_id' => $selection['room_type_id'],
                    'quantity' => $selection['quantity'],
                    'guests_per_room' => $selection['guests_per_room'],
                    'price_per_room' => $pricePerRoom,
                    'total_price' => $totalForRoomType,
                    'guest_details' => $selection['guest_details'] ?? null,
                ];

                $totalPrice += $totalForRoomType;
            }
        }

        return [
            'room_selections' => $calculatedSelections,
            'total_price' => $totalPrice,
            'total_nights' => $totalNights
        ];
    }

    /**
     * Kiểm tra tính khả dụng của phòng cho tour
     */
    public function checkRoomAvailabilityForTour($roomTypeId, $quantity, $checkInDate, $checkOutDate)
    {
        // Đếm số phòng thật sự trống theo từng ngày (không trùng với booking thường & tour holds)
        $roomType = RoomType::with('rooms')->find($roomTypeId);
        if (!$roomType) return false;
        $available = 0;
        foreach ($roomType->rooms as $room) {
            if ($room->isStrictlyAvailableForRange($checkInDate, $checkOutDate)) {
                $available++;
                if ($available >= (int)$quantity) return true;
            }
        }
        return false;
    }

    /**
     * Lấy danh sách loại phòng có sẵn cho tour
     */
    public function getAvailableRoomTypesForTour($checkInDate, $checkOutDate, $totalGuests)
    {
        $roomTypes = RoomType::with('rooms')->get();
        $availableRoomTypes = [];

        foreach ($roomTypes as $roomType) {
            // Đếm số phòng thực sự trống theo khoảng ngày
            $totalRooms = $roomType->rooms ? $roomType->rooms->count() : 0;
            if ($totalRooms == 0) continue;

            $availableRooms = 0;
            foreach ($roomType->rooms as $room) {
                if ($room->isStrictlyAvailableForRange($checkInDate, $checkOutDate)) {
                    $availableRooms++;
                }
            }

            if ($availableRooms <= 0) continue;

            // Tính số phòng cần cho tổng số khách
            $roomsNeeded = max(1, (int)ceil($totalGuests / max(1, (int)$roomType->capacity)));

            if ($availableRooms >= $roomsNeeded) {
                $roomType->available_rooms = $availableRooms;
                $roomType->rooms_needed = $roomsNeeded;
                $roomType->max_guests = $availableRooms * max(1, (int)$roomType->capacity);
                $availableRoomTypes[] = $roomType;
            }
        }

        return $availableRoomTypes;
    }

    /**
     * Xử lý thanh toán thẻ tín dụng cho tour booking
     */
    public function processCreditCardPayment(Request $request, TourBooking $tourBooking): array
    {
        try {
            // Lấy payment đã được tạo trước (theo transaction_id ẩn trong form)
            $transactionId = $request->input('transaction_id');
            $payment = \App\Models\Payment::where('transaction_id', $transactionId)
                ->where('tour_booking_id', $tourBooking->id)
                ->first();

            if (!$payment) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy giao dịch thanh toán.'
                ];
            }

            // Validate số tiền tối thiểu dựa vào số tiền trên payment (đã chốt khi tạo)
            $final = (int) round($tourBooking->final_price ?? $tourBooking->total_price);
            $minDeposit = (int) round($final * 0.2);
            if ((int)$payment->amount < $minDeposit) {
                return [
                    'success' => false,
                    'message' => 'Số tiền thanh toán nhỏ hơn mức tối thiểu cho phép.'
                ];
            }

            // Bổ sung thông tin thẻ vào gateway_response
            $gatewayResponse = [
                'card_info' => [
                    'last4' => substr($request->card_number, -4),
                    'brand' => $this->getCardBrand($request->card_number),
                    'exp_month' => $request->expiry_month,
                    'exp_year' => $request->expiry_year
                ],
                'cardholder_name' => $request->cardholder_name
            ];

            // Kiểm tra thẻ test
            $isTestCard = $this->isTestCard($request->card_number);

            if ($isTestCard) {
                // Cập nhật trạng thái thanh toán thành công
                $this->updateTourBookingPaymentStatus($payment, 'completed', [
                    'gateway_code' => 'CC_SUCCESS',
                    'gateway_message' => 'Thanh toán thẻ tín dụng thành công',
                    'paid_at' => now(),
                    'gateway_response' => $gatewayResponse
                ]);

                // Nếu đã trả đủ tổng, xác nhận tour
                $totalPaid = (int) $tourBooking->payments()->where('status', 'completed')->sum('amount');
                if ($totalPaid >= $final) {
                    $this->confirmTourBookingAfterPayment($tourBooking);
                    $tourBooking->update([
                        'payment_status' => 'completed',
                        'preferred_payment_method' => 'credit_card',
                    ]);
                } else {
                    $tourBooking->update([
                        'payment_status' => 'partial',
                        'preferred_payment_method' => 'credit_card',
                    ]);
                }

                return [
                    'success' => true,
                    'message' => 'Thanh toán thẻ tín dụng thành công!',
                    'payment' => $payment
                ];
            } else {
                // Cập nhật trạng thái thanh toán thất bại
                $this->updateTourBookingPaymentStatus($payment, 'failed', [
                    'gateway_code' => 'CC_FAILED',
                    'gateway_message' => 'Thẻ không hợp lệ hoặc không đủ tiền',
                    'gateway_response' => $gatewayResponse
                ]);

                return [
                    'success' => false,
                    'message' => 'Thanh toán thất bại. Vui lòng kiểm tra lại thông tin thẻ hoặc sử dụng thẻ test.'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Tour booking credit card payment error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Xử lý thanh toán chuyển khoản cho tour booking
     */
    public function processBankTransferPayment(Request $request, TourBooking $tourBooking): array
    {
        try {
            $final = (int) round($tourBooking->final_price ?? $tourBooking->total_price);
            $requestedAmount = (int) ($request->input('amount') ?? $final);
            $minDeposit = (int) round($final * 0.2);
            if ($requestedAmount < $minDeposit) {
                throw new \InvalidArgumentException('Số tiền thanh toán tối thiểu phải là ' . number_format($minDeposit) . ' VNĐ');
            }

            // Tạo payment record
            $payment = $this->createTourBookingPayment($tourBooking, [
                'promotion_id' => $request->input('promotion_id'),
                'method' => 'bank_transfer',
                'amount' => $requestedAmount,
                'discount_amount' => $tourBooking->promotion_discount ?? 0,
                'currency' => 'VND',
                'status' => 'pending',
                'gateway_name' => 'Bank Transfer',
                'gateway_response' => [
                    'customer_note' => $request->customer_note
                ]
            ]);

            // Cập nhật trạng thái tổng quan
            $tourBooking->update([
                'payment_status' => 'pending',
                'preferred_payment_method' => 'bank_transfer',
            ]);

            return [
                'success' => true,
                'message' => 'Đã ghi nhận thông tin chuyển khoản. Chúng tôi sẽ xác nhận trong thời gian sớm nhất.',
                'payment' => $payment
            ];
        } catch (\Exception $e) {
            Log::error('Tour booking bank transfer payment error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Tạo payment record cho tour booking
     */
    private function createTourBookingPayment(TourBooking $tourBooking, array $data): \App\Models\Payment
    {
        $paymentData = [
            'booking_id' => null, // Tour booking không có trong bảng bookings
            'tour_booking_id' => $tourBooking->id, // Sử dụng tour_booking_id
            'promotion_id' => $data['promotion_id'] ?? null,
            'method' => $data['method'],
            'amount' => $data['amount'],
            'discount_amount' => $data['discount_amount'] ?? 0,
            'currency' => $data['currency'],
            'status' => $data['status'],
            'transaction_id' => $data['transaction_id'] ?? 'TOUR_' . $tourBooking->booking_id . '_' . time(),
            'gateway_name' => $data['gateway_name'],
            'gateway_response' => is_array($data['gateway_response'] ?? []) ? json_encode($data['gateway_response']) : ($data['gateway_response'] ?? '{}'),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $id = DB::table('payments')->insertGetId($paymentData);
        
        return \App\Models\Payment::find($id);
    }

    /**
     * Cập nhật trạng thái payment
     */
    private function updateTourBookingPaymentStatus(\App\Models\Payment $payment, string $status, array $data = []): bool
    {
        $updateData = ['status' => $status];

        if (isset($data['gateway_code'])) {
            $updateData['gateway_code'] = $data['gateway_code'];
        }

        if (isset($data['gateway_message'])) {
            $updateData['gateway_message'] = $data['gateway_message'];
        }

        if (isset($data['gateway_response'])) {
            $updateData['gateway_response'] = is_array($data['gateway_response']) ? json_encode($data['gateway_response']) : $data['gateway_response'];
        }

        if (isset($data['paid_at'])) {
            $updateData['paid_at'] = $data['paid_at'];
        }

        return $payment->update($updateData);
    }

    /**
     * Xác nhận tour booking sau khi thanh toán thành công
     */
    private function confirmTourBookingAfterPayment(TourBooking $tourBooking): bool
    {
        try {
            // Cập nhật trạng thái tour booking thành confirmed
            $tourBooking->update([
                'status' => 'confirmed'
            ]);

            // Gửi email thông báo xác nhận
            $this->sendTourBookingConfirmationEmail($tourBooking);

            Log::info('Tour booking confirmed after successful payment', [
                'tour_booking_id' => $tourBooking->id,
                'user_id' => $tourBooking->user_id,
                'amount' => $tourBooking->total_price
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error confirming tour booking after payment', [
                'tour_booking_id' => $tourBooking->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Gửi email xác nhận tour booking
     */
    private function sendTourBookingConfirmationEmail(TourBooking $tourBooking): void
    {
        try {
            $user = $tourBooking->user;
            
            Log::info('Attempting to send tour booking confirmation email from service', [
                'tour_booking_id' => $tourBooking->id,
                'user_email' => $user->email,
                'user_id' => $user->id,
                'mail_driver' => config('mail.default')
            ]);
            
            Mail::to($user->email)->send(new \App\Mail\TourBookingConfirmationMail($tourBooking));
            
            Log::info('Tour booking confirmation email sent successfully from service', [
                'tour_booking_id' => $tourBooking->id,
                'user_email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending tour booking confirmation email from service', [
                'tour_booking_id' => $tourBooking->id,
                'user_email' => $tourBooking->user->email ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Lấy brand thẻ từ số thẻ
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
     * Kiểm tra thẻ test
     */
    private function isTestCard(string $cardNumber): bool
    {
        $testPrefixes = ['4111', '5555', '3782'];
        $prefix = substr($cardNumber, 0, 4);

        return in_array($prefix, $testPrefixes);
    }

    /**
     * Tạo payment record cho thẻ tín dụng
     */
    public function createCreditCardPayment(Request $request, TourBooking $tourBooking): \App\Models\Payment
    {
        $transactionId = 'CC_TOUR_' . $tourBooking->booking_id . '_' . time();
        $final = (int) round($tourBooking->final_price ?? $tourBooking->total_price);
        $requestedAmount = (int) ($request->input('amount') ?? $final);
        $minDeposit = (int) round($final * 0.2);
        if ($requestedAmount < $minDeposit) {
            throw new \InvalidArgumentException('Số tiền thanh toán tối thiểu phải là ' . number_format($minDeposit) . ' VNĐ');
        }

        return $this->createTourBookingPayment($tourBooking, [
            'promotion_id' => $request->input('promotion_id'),
            'method' => 'credit_card',
            'amount' => $requestedAmount,
            'discount_amount' => $tourBooking->promotion_discount ?? 0,
            'currency' => 'VND',
            'status' => 'pending',
            'gateway_name' => 'Credit Card',
            'gateway_response' => [
                'card_info' => [
                    'last4' => substr($request->card_number ?? '', -4),
                    'brand' => $this->getCardBrand($request->card_number ?? ''),
                    'exp_month' => $request->expiry_month,
                    'exp_year' => $request->expiry_year
                ],
                'cardholder_name' => $request->cardholder_name
            ]
        ]);
    }

    /**
     * Tạo payment record cho chuyển khoản
     */
    public function createBankTransferPayment(Request $request, TourBooking $tourBooking): \App\Models\Payment
    {
        $transactionId = 'BANK_TOUR_' . $tourBooking->booking_id . '_' . time();
        $final = (int) round($tourBooking->final_price ?? $tourBooking->total_price);
        $requestedAmount = (int) ($request->input('amount') ?? $final);
        $minDeposit = (int) round($final * 0.2);
        if ($requestedAmount < $minDeposit) {
            throw new \InvalidArgumentException('Số tiền thanh toán tối thiểu phải là ' . number_format($minDeposit) . ' VNĐ');
        }

        return $this->createTourBookingPayment($tourBooking, [
            'promotion_id' => $request->input('promotion_id'),
            'method' => 'bank_transfer',
            'amount' => $requestedAmount,
            'discount_amount' => $tourBooking->promotion_discount ?? 0,
            'currency' => 'VND',
            'status' => 'pending',
            'gateway_name' => 'Bank Transfer',
            'gateway_response' => [
                'customer_note' => $request->customer_note
            ]
        ]);
    }

    /**
     * Tạo payment record cho chuyển khoản từ session data
     */
    public function createBankTransferPaymentFromSession(array $tempPaymentData, TourBooking $tourBooking): \App\Models\Payment
    {
        $transactionId = 'BANK_TOUR_' . $tourBooking->booking_id . '_' . time();

        return $this->createTourBookingPayment($tourBooking, [
            'promotion_id' => $tempPaymentData['promotion_id'] ?? null,
            'method' => 'bank_transfer',
            'amount' => $tempPaymentData['amount'],
            'discount_amount' => $tempPaymentData['discount_amount'] ?? 0,
            'currency' => 'VND',
            'status' => 'pending',
            'gateway_name' => 'Bank Transfer',
            'gateway_response' => [
                'customer_note' => $tempPaymentData['customer_note'] ?? null,
                'created_from_session' => true
            ]
        ]);
    }
}
