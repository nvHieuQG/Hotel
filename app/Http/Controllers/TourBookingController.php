<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\Services\TourBookingServiceInterface;
use App\Interfaces\Services\RoomTypeServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\TourBooking; // Added missing import for TourBooking model

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
                'promotion_code' => $request->promotion_code,
                'promotion_discount' => $request->promotion_discount ?? 0,
                'promotion_id' => $request->promotion_id,
                'total_price' => $request->total_price, // Giá cuối sau khi giảm giá (số tiền khách thanh toán)
            ];

            // Tính toán giá gốc trước khi giảm giá
            if (($request->promotion_discount ?? 0) > 0) {
                $tourBookingData['original_price'] = $request->total_price + ($request->promotion_discount);
                $tourBookingData['final_price'] = $request->total_price; // Giá cuối = total_price
            } else {
                $tourBookingData['original_price'] = $request->total_price;
                $tourBookingData['final_price'] = $request->total_price;
            }

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

        return view('client.tour-booking.payment-method', compact('tourBooking'));
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
        $tourBooking = TourBooking::with([
            'tourBookingRooms.roomType',
            'tourBookingServices',
            'payments',
            'user'
        ])->where('id', $id)->first();

        if (!$tourBooking || $tourBooking->user_id !== Auth::id()) {
            return redirect()->route('tour-booking.index')->withErrors(['message' => 'Không tìm thấy đặt phòng tour.']);
        }

        // Tính toán các giá trị cần thiết
        $tourBooking->total_rooms_amount = $tourBooking->tourBookingRooms->sum('total_price');
        $tourBooking->total_services_amount = $tourBooking->tourBookingServices->sum('total_price');
        $tourBooking->total_amount_before_discount = $tourBooking->total_rooms_amount + $tourBooking->total_services_amount;
        
        // Tính toán giá cuối cùng - sử dụng final_price nếu có, nếu không thì tính từ total_price
        if ($tourBooking->final_price && $tourBooking->final_price > 0) {
            // Giữ nguyên final_price đã có
        } elseif ($tourBooking->promotion_discount > 0) {
            $tourBooking->final_price = $tourBooking->total_amount_before_discount - $tourBooking->promotion_discount;
        } else {
            $tourBooking->final_price = $tourBooking->total_amount_before_discount;
        }

        return view('client.tour-booking.show', compact('tourBooking'));
    }

    /**
     * Xử lý thanh toán thẻ tín dụng cho tour booking
     */
    public function processCreditCardPayment(Request $request, TourBooking $tourBooking)
    {
        // Kiểm tra quyền truy cập
        if ($tourBooking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập tour booking này.');
        }

        try {
            // Tạo payment record cho credit card
            $payment = $this->tourBookingService->createCreditCardPayment($request, $tourBooking);

            // Lấy thông tin thẻ test
            $creditCardInfo = $this->getCreditCardTestInfo();

            return view('client.tour-booking.credit-card', compact('tourBooking', 'payment', 'creditCardInfo'));
        } catch (\Exception $e) {
            Log::error('Tour booking credit card payment error', [
                'tour_booking_id' => $tourBooking->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi tạo thanh toán thẻ tín dụng: ' . $e->getMessage());
        }
    }

    /**
     * Áp dụng mã giảm giá cho tour booking
     */
    public function applyPromotion(Request $request, $id)
    {
        try {
            $tourBooking = TourBooking::findOrFail($id);
            
            // Kiểm tra quyền truy cập
            if ($tourBooking->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập tour booking này.'
                ], 403);
            }
            
            $promotionId = $request->input('promotion_id');
            $promotion = Promotion::findOrFail($promotionId);
            
            // Kiểm tra promotion có hợp lệ không
            if (!$promotion->isActive() || !$promotion->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mã giảm giá không hợp lệ hoặc đã hết hạn.'
                ], 400);
            }
            
            // Tính toán giảm giá - sử dụng giá gốc (total_rooms_amount + total_services_amount)
            $basePrice = $tourBooking->tourBookingRooms->sum('total_price') + $tourBooking->tourBookingServices->sum('total_price');
            $discountAmount = $promotion->calculateDiscount($basePrice);
            
            // Cập nhật tour booking
            $tourBooking->update([
                'promotion_id' => $promotionId,
                'promotion_code' => $promotion->code,
                'promotion_discount' => $discountAmount,
                'final_price' => $basePrice - $discountAmount
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Áp dụng mã giảm giá thành công!',
                'data' => [
                    'promotion_code' => $promotion->code,
                    'discount_amount' => $discountAmount,
                    'final_price' => $basePrice - $discountAmount,
                    'formatted_discount' => number_format($discountAmount, 0, ',', '.') . ' VNĐ',
                    'formatted_final_price' => number_format($basePrice - $discountAmount, 0, ',', '.') . ' VNĐ'
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error applying promotion to tour booking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi áp dụng mã giảm giá.'
            ], 500);
        }
    }
    
    /**
     * Xóa mã giảm giá khỏi tour booking
     */
    public function removePromotion(Request $request, $id)
    {
        try {
            $tourBooking = TourBooking::findOrFail($id);
            
            // Kiểm tra quyền truy cập
            if ($tourBooking->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Bạn không có quyền truy cập tour booking này.'
                ], 403);
            }
            
            // Xóa promotion
            $tourBooking->update([
                'promotion_id' => null,
                'promotion_code' => null,
                'promotion_discount' => 0,
                'final_price' => $tourBooking->total_price
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa mã giảm giá!',
                'data' => [
                    'final_price' => $tourBooking->total_price,
                    'formatted_final_price' => number_format($tourBooking->total_price, 0, ',', '.') . ' VNĐ'
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error removing promotion from tour booking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa mã giảm giá.'
            ], 500);
        }
    }

    /**
     * Xử lý thanh toán chuyển khoản cho tour booking
     */
    public function processBankTransferPayment(Request $request, TourBooking $tourBooking)
    {
        // Kiểm tra quyền truy cập
        if ($tourBooking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập tour booking này.');
        }

        try {
            // Tạo payment record cho chuyển khoản
            $payment = $this->tourBookingService->createBankTransferPayment($request, $tourBooking);

            // Lấy thông tin ngân hàng (chuẩn hóa theo cấu trúc view cần)
            $bankInfoRaw = $this->getBankTransferInfo();
            $firstBank = $bankInfoRaw['banks'][0] ?? [];
            $bankInfo = [
                'bank_name' => $firstBank['name'] ?? 'N/A',
                'account_number' => $firstBank['account_number'] ?? 'N/A',
                'account_holder' => $firstBank['account_name'] ?? 'N/A',
                'branch' => $firstBank['branch'] ?? 'N/A',
                'swift_code' => $firstBank['swift_code'] ?? 'N/A',
                'instructions' => $bankInfoRaw['instructions'] ?? [],
                'note' => $bankInfoRaw['note'] ?? ''
            ];

            return view('client.tour-booking.bank-transfer', compact('tourBooking', 'payment', 'bankInfo'));
        } catch (\Exception $e) {
            Log::error('Tour booking bank transfer error', [
                'tour_booking_id' => $tourBooking->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra khi tạo thanh toán chuyển khoản: ' . $e->getMessage());
        }
    }

    /**
     * Xác nhận thanh toán thẻ tín dụng
     */
    public function confirmCreditCardPayment(Request $request, TourBooking $tourBooking)
    {
        // Kiểm tra quyền truy cập
        if ($tourBooking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập tour booking này.');
        }

        $request->validate([
            'transaction_id' => 'required|string',
            'cardholder_name' => 'required|string|max:255',
            'card_number' => 'required|string|regex:/^\d{16}$/',
            'expiry_month' => 'required|integer|between:1,12',
            'expiry_year' => 'required|integer|min:' . date('Y'),
            'cvv' => 'required|string|regex:/^\d{3,4}$/',
        ]);

        try {
            // Xử lý thanh toán thẻ tín dụng
            $result = $this->tourBookingService->processCreditCardPayment($request, $tourBooking);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Thanh toán thành công! Tour booking của bạn đã được xác nhận.',
                    'redirect' => route('tour-booking.show', $tourBooking->id)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'] ?? 'Có lỗi xảy ra khi thanh toán.'
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Tour booking credit card confirmation error', [
                'tour_booking_id' => $tourBooking->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xử lý thanh toán: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Xác nhận chuyển khoản
     */
    public function confirmBankTransferPayment(Request $request, TourBooking $tourBooking)
    {
        // Kiểm tra quyền truy cập
        if ($tourBooking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập tour booking này.');
        }

        $request->validate([
            'transaction_id' => 'required|string',
            'bank_name' => 'required|string|max:255',
            'transfer_amount' => 'required|numeric|min:0',
            'transfer_date' => 'required|date',
            'customer_note' => 'nullable|string|max:500',
            'receipt_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Xử lý xác nhận chuyển khoản
            $result = $this->tourBookingService->processBankTransferPayment($request, $tourBooking);

            if ($result['success']) {
                return redirect()->route('tour-booking.show', $tourBooking->id)
                    ->with('success', 'Đã ghi nhận thông tin chuyển khoản. Chúng tôi sẽ xác nhận trong thời gian sớm nhất.');
            } else {
                return redirect()->back()
                    ->withErrors(['message' => $result['message'] ?? 'Có lỗi xảy ra khi xử lý chuyển khoản.']);
            }
        } catch (\Exception $e) {
            Log::error('Tour booking bank transfer confirmation error', [
                'tour_booking_id' => $tourBooking->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withErrors(['message' => 'Có lỗi xảy ra khi xử lý chuyển khoản: ' . $e->getMessage()]);
        }
    }

    /**
     * Lấy thông tin thẻ test
     */
    private function getCreditCardTestInfo(): array
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
     * Lấy thông tin ngân hàng
     */
    private function getBankTransferInfo(): array
    {
        return [
            'banks' => [
                [
                    'name' => 'Vietcombank',
                    'account_number' => '1234567890',
                    'account_name' => 'CONG TY TNHH KHACH SAN MARRON',
                    'branch' => 'Chi nhánh TP.HCM',
                    'qr_code' => asset('images/qr-code.png'),
                    'transfer_content' => 'Thanh toan tour'
                ],
                [
                    'name' => 'BIDV',
                    'account_number' => '9876543210',
                    'account_name' => 'CONG TY TNHH KHACH SAN MARRON',
                    'branch' => 'Chi nhánh TP.HCM',
                    'qr_code' => asset('images/qr-code.png'),
                    'transfer_content' => 'Thanh toan tour'
                ],
                [
                    'name' => 'Techcombank',
                    'account_number' => '1122334455',
                    'account_name' => 'CONG TY TNHH KHACH SAN MARRON',
                    'branch' => 'Chi nhánh TP.HCM',
                    'qr_code' => asset('images/qr-code.png'),
                    'transfer_content' => 'Thanh toan tour'
                ]
            ],
            'instructions' => [
                'Bước 1: Chọn ngân hàng bạn muốn chuyển khoản',
                'Bước 2: Quét mã QR hoặc copy thông tin tài khoản',
                'Bước 3: Thực hiện chuyển khoản với nội dung: "Thanh toan tour"',
                'Bước 4: Sau khi chuyển khoản, vui lòng chụp ảnh biên lai và gửi cho chúng tôi',
                'Bước 5: Chúng tôi sẽ xác nhận thanh toán trong vòng 30 phút'
            ],
            'note' => 'Vui lòng chuyển khoản đúng số tiền và nội dung để tránh nhầm lẫn. Nếu có thắc mắc, vui lòng liên hệ với quản trị viên hoặc số điện thoại: 0979.833.135'
        ];
    }


}
