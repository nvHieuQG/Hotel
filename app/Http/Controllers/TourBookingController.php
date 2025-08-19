<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\Services\TourBookingServiceInterface;
use App\Interfaces\Services\RoomTypeServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TourBookingController extends Controller
{
    protected $tourBookingService;
    protected $roomTypeService;

    public function __construct(
        TourBookingServiceInterface $tourBookingService,
        RoomTypeServiceInterface $roomTypeService
    ) {
        $this->tourBookingService = $tourBookingService;
        $this->roomTypeService = $roomTypeService;
    }

    /**
     * Hiển thị form tìm kiếm tour
     */
    public function searchForm()
    {
        return view('client.tour-booking.search');
    }

    /**
     * Xử lý tìm kiếm phòng cho tour
     */
    public function search(Request $request)
    {
        $request->validate([
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'total_guests' => 'required|integer|min:1|max:360',
            'tour_name' => 'required|string|max:255',
        ], [
            'total_guests.max' => 'Số khách không được vượt quá 360 người (sức chứa tối đa của khách sạn).',
            'total_guests.min' => 'Số khách phải ít nhất 1 người.',
        ]);

        try {
            $checkInDate = $request->check_in_date;
            $checkOutDate = $request->check_out_date;
            $totalGuests = $request->total_guests;
            $tourName = $request->tour_name;

            // Kiểm tra sức chứa tối đa của khách sạn
            $maxCapacity = \App\Models\RoomType::with('rooms')
                ->get()
                ->sum(function ($roomType) {
                    return $roomType->capacity * $roomType->rooms->count();
                });

            if ($totalGuests > $maxCapacity) {
                return back()->withErrors([
                    'total_guests' => "Số khách ({$totalGuests}) vượt quá sức chứa tối đa của khách sạn ({$maxCapacity} người)."
                ])->withInput();
            }

            // Debug log
            Log::info('Tour booking search request', [
                'check_in_date' => $checkInDate,
                'check_out_date' => $checkOutDate,
                'total_guests' => $totalGuests,
                'tour_name' => $tourName
            ]);

            // Lấy danh sách loại phòng có sẵn
            $availableRoomTypes = $this->tourBookingService->getAvailableRoomTypesForTour(
                $checkInDate,
                $checkOutDate,
                $totalGuests
            );

            // Debug log kết quả
            Log::info('Tour booking search result', [
                'available_room_types_count' => count($availableRoomTypes),
                'room_types' => collect($availableRoomTypes)->map(function ($rt) {
                    return [
                        'id' => $rt->id,
                        'name' => $rt->name,
                        'capacity' => $rt->capacity,
                        'available_rooms' => $rt->available_rooms ?? 0,
                        'rooms_needed' => $rt->rooms_needed ?? 0
                    ];
                })->toArray()
            ]);

            return view('client.tour-booking.select-rooms', compact(
                'availableRoomTypes',
                'checkInDate',
                'checkOutDate',
                'totalGuests',
                'tourName'
            ));
        } catch (\Exception $e) {
            Log::error('Tour booking search error: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Có lỗi xảy ra khi tìm kiếm phòng. Vui lòng thử lại.']);
        }
    }

    /**
     * Hiển thị form chọn phòng cho tour
     */
    public function selectRooms(Request $request)
    {
        $request->validate([
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date',
            'total_guests' => 'required|integer',
            'tour_name' => 'required|string',
        ]);

        $checkInDate = $request->check_in_date;
        $checkOutDate = $request->check_out_date;
        $totalGuests = $request->total_guests;
        $tourName = $request->tour_name;

        $availableRoomTypes = $this->tourBookingService->getAvailableRoomTypesForTour(
            $checkInDate,
            $checkOutDate,
            $totalGuests
        );

        return view('client.tour-booking.select-rooms', compact(
            'availableRoomTypes',
            'checkInDate',
            'checkOutDate',
            'totalGuests',
            'tourName'
        ));
    }

    /**
     * Tính toán giá tour booking
     */
    public function calculatePrice(Request $request)
    {
        $request->validate([
            'room_selections' => 'required|array',
            'room_selections.*.room_type_id' => 'required|exists:room_types,id',
            'room_selections.*.quantity' => 'required|integer|min:1',
            'room_selections.*.guests_per_room' => 'required|integer|min:1',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date',
        ]);

        try {
            $result = $this->tourBookingService->calculateTourBookingPrice(
                $request->room_selections,
                $request->check_in_date,
                $request->check_out_date
            );

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Tour booking price calculation error: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi tính toán giá.'], 500);
        }
    }

    /**
     * Hiển thị form xác nhận tour booking
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'tour_name' => 'required|string',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date',
            'total_guests' => 'required|integer',
            'room_selections' => 'required|array',
        ]);

        $tourData = $request->all();
        $totalRooms = collect($request->room_selections)->sum('quantity');

        return view('client.tour-booking.confirm', compact('tourData', 'totalRooms'));
    }

    /**
     * Lưu tour booking
     */
    public function store(Request $request)
    {
        $request->validate([
            'tour_name' => 'required|string|max:255',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date',
            'total_guests' => 'required|integer|min:1',
            'room_selections' => 'required|array',
            'special_requests' => 'nullable|string',
        ]);

        try {
            $totalRooms = collect($request->room_selections)->sum('quantity');

            $tourBookingData = [
                'user_id' => Auth::id(),
                'tour_name' => $request->tour_name,
                'total_guests' => $request->total_guests,
                'total_rooms' => $totalRooms,
                'check_in_date' => $request->check_in_date,
                'check_out_date' => $request->check_out_date,
                'special_requests' => $request->special_requests,
                'room_selections' => $request->room_selections,
            ];

            $tourBooking = $this->tourBookingService->createTourBooking($tourBookingData);

            return redirect()->route('tour-booking.payment', $tourBooking->booking_id)
                ->with('success', 'Đặt phòng tour thành công! Vui lòng thanh toán.');
        } catch (\Exception $e) {
            Log::error('Tour booking store error: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Có lỗi xảy ra khi đặt phòng. Vui lòng thử lại.']);
        }
    }

    /**
     * Hiển thị trang thanh toán tour booking
     */
    public function payment($bookingId)
    {
        $tourBooking = $this->tourBookingService->getTourBookingByBookingId($bookingId);

        if (!$tourBooking || $tourBooking->user_id !== Auth::id()) {
            return redirect()->route('index')->withErrors(['message' => 'Không tìm thấy đặt phòng tour.']);
        }

        return view('client.tour-booking.payment', compact('tourBooking'));
    }

    /**
     * Hiển thị danh sách tour bookings của user
     */
    public function index()
    {
        $tourBookings = $this->tourBookingService->getUserTourBookings(Auth::id());
        return view('client.tour-booking.index', compact('tourBookings'));
    }

    /**
     * Hiển thị chi tiết tour booking
     */
    public function show($id)
    {
        $tourBooking = $this->tourBookingService->getTourBookingById($id);

        if (!$tourBooking || $tourBooking->user_id !== Auth::id()) {
            return redirect()->route('tour-booking.index')->withErrors(['message' => 'Không tìm thấy đặt phòng tour.']);
        }

        return view('client.tour-booking.show', compact('tourBooking'));
    }

    /**
     * Xử lý thanh toán thẻ tín dụng cho tour booking
     */
    public function processCreditCardPayment(Request $request)
    {
        $request->validate([
            'tour_booking_id' => 'required|exists:tour_bookings,id',
            'card_number' => 'required|string|regex:/^\d{16}$/',
            'expiry_month' => 'required|integer|between:1,12',
            'expiry_year' => 'required|integer|min:' . date('Y'),
            'cvv' => 'required|string|regex:/^\d{3,4}$/',
            'cardholder_name' => 'required|string|max:255',
        ]);

        try {
            $tourBooking = $this->tourBookingService->getTourBookingById($request->tour_booking_id);

            if (!$tourBooking || $tourBooking->user_id !== Auth::id()) {
                return redirect()->back()->withErrors(['message' => 'Bạn không có quyền truy cập đặt phòng tour này.']);
            }

            // Xử lý thanh toán thẻ tín dụng
            $result = $this->tourBookingService->processCreditCardPayment($request, $tourBooking);

            if ($result['success']) {
                return redirect()->route('tour-booking.show', $tourBooking->id)
                    ->with('success', 'Thanh toán thành công! Tour booking của bạn đã được xác nhận.');
            } else {
                return redirect()->back()->withErrors(['message' => $result['message']]);
            }
        } catch (\Exception $e) {
            Log::error('Tour booking credit card payment error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['message' => 'Có lỗi xảy ra khi xử lý thanh toán. Vui lòng thử lại.']);
        }
    }

    /**
     * Xử lý thanh toán chuyển khoản cho tour booking
     */
    public function processBankTransferPayment(Request $request)
    {
        $request->validate([
            'tour_booking_id' => 'required|exists:tour_bookings,id',
            'customer_note' => 'nullable|string|max:500',
        ]);

        try {
            $tourBooking = $this->tourBookingService->getTourBookingById($request->tour_booking_id);

            if (!$tourBooking || $tourBooking->user_id !== Auth::id()) {
                return redirect()->back()->withErrors(['message' => 'Bạn không có quyền truy cập đặt phòng tour này.']);
            }

            // Xử lý thanh toán chuyển khoản
            $result = $this->tourBookingService->processBankTransferPayment($request, $tourBooking);

            if ($result['success']) {
                return redirect()->route('tour-booking.show', $tourBooking->id)
                    ->with('success', 'Đã ghi nhận thông tin chuyển khoản. Chúng tôi sẽ xác nhận trong thời gian sớm nhất.');
            } else {
                return redirect()->back()->withErrors(['message' => $result['message']]);
            }
        } catch (\Exception $e) {
            Log::error('Tour booking bank transfer payment error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['message' => 'Có lỗi xảy ra khi xử lý thanh toán. Vui lòng thử lại.']);
        }
    }
}
