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
                'total_price' => $calculatedData['total_price'],
                'status' => 'pending',
                'special_requests' => $data['special_requests'] ?? null,
                'tour_details' => $data['tour_details'] ?? null,
            ]);

            // Tạo tour booking rooms với giá đã tính toán
            foreach ($calculatedData['room_selections'] as $roomSelection) {
                TourBookingRoom::create([
                    'tour_booking_id' => $tourBooking->id,
                    'room_type_id' => $roomSelection['room_type_id'],
                    'quantity' => $roomSelection['quantity'],
                    'guests_per_room' => $roomSelection['guests_per_room'],
                    'price_per_room' => $roomSelection['price_per_room'],
                    'total_price' => $roomSelection['total_price'],
                    'guest_details' => $roomSelection['guest_details'] ?? null,
                ]);
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
        return $this->tourBookingRepository->update($id, ['status' => $status]);
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
        // Lấy tổng số phòng của loại này
        $totalRooms = RoomType::find($roomTypeId)->rooms()->count();

        // Lấy số phòng đã được đặt trong khoảng thời gian này
        $bookedRooms = DB::table('bookings')
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->where('rooms.room_type_id', $roomTypeId)
            ->where(function ($query) use ($checkInDate, $checkOutDate) {
                $query->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                    ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                    ->orWhere(function ($q) use ($checkInDate, $checkOutDate) {
                        $q->where('check_in_date', '<=', $checkInDate)
                            ->where('check_out_date', '>=', $checkOutDate);
                    });
            })
            ->where('bookings.status', '!=', 'cancelled')
            ->count();

        $availableRooms = $totalRooms - $bookedRooms;
        return $availableRooms >= $quantity;
    }

    /**
     * Lấy danh sách loại phòng có sẵn cho tour
     */
    public function getAvailableRoomTypesForTour($checkInDate, $checkOutDate, $totalGuests)
    {
        $roomTypes = RoomType::with('rooms')->get();
        $availableRoomTypes = [];

        foreach ($roomTypes as $roomType) {
            // Kiểm tra tính khả dụng
            $totalRooms = $roomType->rooms()->count();

            if ($totalRooms == 0) {
                continue; // Bỏ qua loại phòng không có phòng nào
            }

            $bookedRooms = DB::table('bookings')
                ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
                ->where('rooms.room_type_id', $roomType->id)
                ->where(function ($query) use ($checkInDate, $checkOutDate) {
                    $query->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                        ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                        ->orWhere(function ($q) use ($checkInDate, $checkOutDate) {
                            $q->where('check_in_date', '<=', $checkInDate)
                                ->where('check_out_date', '>=', $checkOutDate);
                        });
                })
                ->where('bookings.status', '!=', 'cancelled')
                ->count();

            $availableRooms = $totalRooms - $bookedRooms;

            if ($availableRooms > 0) {
                // Tính toán số phòng cần thiết cho tổng số khách
                $roomsNeeded = ceil($totalGuests / $roomType->capacity);

                // Chỉ hiển thị nếu có đủ phòng
                if ($availableRooms >= $roomsNeeded) {
                    $roomType->available_rooms = $availableRooms;
                    $roomType->rooms_needed = $roomsNeeded;
                    $roomType->max_guests = $availableRooms * $roomType->capacity;
                    $availableRoomTypes[] = $roomType;
                }
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
            // Tạo payment record
            $payment = $this->createTourBookingPayment($tourBooking, [
                'payment_method' => 'credit_card',
                'amount' => $tourBooking->total_price,
                'currency' => 'VND',
                'status' => 'pending',
                'gateway_name' => 'Credit Card',
                'gateway_response' => [
                    'card_info' => [
                        'last4' => substr($request->card_number, -4),
                        'brand' => $this->getCardBrand($request->card_number),
                        'exp_month' => $request->expiry_month,
                        'exp_year' => $request->expiry_year
                    ],
                    'cardholder_name' => $request->cardholder_name
                ]
            ]);

            // Kiểm tra thẻ test
            $isTestCard = $this->isTestCard($request->card_number);

            if ($isTestCard) {
                // Cập nhật trạng thái thanh toán thành công
                $this->updateTourBookingPaymentStatus($payment, 'completed', [
                    'gateway_code' => 'CC_SUCCESS',
                    'gateway_message' => 'Thanh toán thẻ tín dụng thành công',
                    'paid_at' => now()
                ]);

                // Cập nhật trạng thái tour booking
                $this->confirmTourBookingAfterPayment($tourBooking);
                // Ghi nhận trạng thái thanh toán tổng và phương thức ưu tiên
                $tourBooking->update([
                    'payment_status' => 'completed',
                    'preferred_payment_method' => 'credit_card',
                ]);

                return [
                    'success' => true,
                    'message' => 'Thanh toán thẻ tín dụng thành công!',
                    'payment' => $payment
                ];
            } else {
                // Cập nhật trạng thái thanh toán thất bại
                $this->updateTourBookingPaymentStatus($payment, 'failed', [
                    'gateway_code' => 'CC_FAILED',
                    'gateway_message' => 'Thẻ không hợp lệ hoặc không đủ tiền'
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
            // Tạo payment record
            $payment = $this->createTourBookingPayment($tourBooking, [
                'payment_method' => 'bank_transfer',
                'amount' => $tourBooking->total_price,
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
        $transactionId = 'TOUR_' . $tourBooking->booking_id . '_' . time();

        return \App\Models\Payment::create([
            'booking_id' => $tourBooking->id,
            'payment_method' => $data['payment_method'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'status' => $data['status'],
            'transaction_id' => $transactionId,
            'gateway_name' => $data['gateway_name'],
            'gateway_response' => $data['gateway_response'] ?? [],
        ]);
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
            $updateData['gateway_response'] = $data['gateway_response'];
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
            
            \Mail::to($user->email)->send(new \App\Mail\TourBookingConfirmationMail($tourBooking));
            
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
}
