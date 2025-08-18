<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ReviewService;
use Illuminate\Support\Facades\Auth;
use App\Services\RoomTypeReviewService;
use App\Interfaces\Services\RoomTypeServiceInterface;
use App\Interfaces\Services\RoomServiceInterface;
use App\Interfaces\Repositories\RoomRepositoryInterface;
use App\Interfaces\Repositories\BookingRepositoryInterface;
use App\Interfaces\Services\ServiceCategoryServiceInterface;
use App\Models\Promotion;

class HotelController extends Controller
{
    protected $roomRepository;
    protected $roomService;
    protected $reviewService;
    protected $roomTypeReviewService;
    protected $roomTypeService;
    protected $bookingRepository;
    protected $serviceCategoryService;

    public function __construct(
        RoomRepositoryInterface $roomRepository,
        RoomServiceInterface $roomService,
        RoomTypeReviewService $roomTypeReviewService,
        RoomTypeServiceInterface $roomTypeService,
        BookingRepositoryInterface $bookingRepository,
        ServiceCategoryServiceInterface $serviceCategoryService
    ) {
        $this->roomRepository = $roomRepository;
        $this->roomService = $roomService;
        $this->roomTypeReviewService = $roomTypeReviewService;
        $this->roomTypeService = $roomTypeService;
        $this->bookingRepository = $bookingRepository;
        $this->serviceCategoryService = $serviceCategoryService;
    }

    public function index()
    {
        // Lấy tất cả loại phòng để hiển thị ở trang chủ
        $roomTypes = $this->roomTypeService->getAllRoomTypes()->take(6); // Lấy 6 loại phòng đầu tiên

        // Lấy các đánh giá 5 sao ngẫu nhiên
        $fiveStarReviews = \App\Models\RoomTypeReview::where('rating', 5)
            ->where('status', 'approved')
            ->with(['user', 'roomType'])
            ->inRandomOrder()
            ->limit(5)
            ->get();

        return view('client.index', compact('roomTypes', 'fiveStarReviews'));
    }

    public function rooms(Request $request)
    {
        // Lấy thông tin tìm kiếm từ URL
        $searchParams = $request->only(['check_in_date', 'check_out_date', 'guests']);

        // Lấy tất cả phòng để hiển thị ở trang danh sách phòng
        $rooms = $this->roomRepository->getAll();
        $roomTypes = $this->roomTypeService->getAllRoomTypes();

        // Nếu có thông tin tìm kiếm, lọc phòng phù hợp
        if (!empty($searchParams['check_in_date']) && !empty($searchParams['check_out_date'])) {
            $checkInDate = $searchParams['check_in_date'];
            $checkOutDate = $searchParams['check_out_date'];
            $guests = $searchParams['guests'] ?? 2;

            // Lọc phòng theo số khách
            $rooms = $rooms->filter(function ($room) use ($guests) {
                return $room->roomType && $room->roomType->capacity >= $guests;
            });

            // TODO: Thêm logic lọc phòng theo ngày (kiểm tra booking conflicts)
            // Hiện tại chỉ lọc theo số khách

            // Thêm thông báo tìm kiếm
            $searchMessage = "Tìm kiếm phòng cho {$guests} khách từ {$checkInDate} đến {$checkOutDate}";
        } else {
            $searchMessage = null;
        }

        return view('client.rooms.index', compact('rooms', 'roomTypes', 'searchParams', 'searchMessage'));
    }

    public function restaurant()
    {
        return view('client.restaurant');
    }

    public function about()
    {
        return view('client.about');
    }

    public function blog()
    {
        return view('client.blog');
    }

    public function contact()
    {
        return view('client.contact');
    }

    public function roomsSingle($id = null)
    {
        // Nếu không có id, chuyển hướng đến trang danh sách loại phòng
        if (!$id) {
            return redirect()->route('rooms');
        }

        // Lấy thông tin chi tiết loại phòng
        $roomType = $this->roomTypeService->findById($id);

        if (!$roomType) {
            return redirect()->route('rooms')->with('error', 'Không tìm thấy loại phòng');
        }

        // Lấy các phòng thuộc loại phòng này
        $rooms = $this->roomService->getAll()
            ->where('room_type_id', $roomType->id);

        // Lấy đánh giá và bình luận của loại phòng
        $reviews = $this->roomTypeReviewService->getRoomTypeReviews($roomType->id, 10);
        $averageRating = $this->roomTypeReviewService->getRoomTypeAverageRating($roomType->id);
        $reviewsCount = $this->roomTypeReviewService->getRoomTypeReviewsCount($roomType->id);

        // Kiểm tra quyền đánh giá
        $canReview = false;
        $completedBookings = collect();
        if (Auth::check()) {
            // Lấy tất cả booking hoàn thành cho loại phòng này mà chưa đánh giá
            $completedBookings = \App\Models\Booking::where('user_id', Auth::id())
                ->where('status', 'completed')
                ->whereHas('room', function ($query) use ($roomType) {
                    $query->where('room_type_id', $roomType->id);
                })
                ->whereDoesntHave('review', function ($q) {
                    $q->where('user_id', Auth::id());
                })
                ->get();

            $canReview = $completedBookings->isNotEmpty();
        }

        $roomTypes = $this->roomTypeService->getAllRoomTypes();
        $serviceCategories = $this->serviceCategoryService->getAll();

        // Promotions for room type detail (use RoomPromotionService to match listings/home)
        $checkIn = request()->get('check_in_date');
        $checkOut = request()->get('check_out_date');
        $nights = 1;
        if ($checkIn && $checkOut) {
            try {
                $start = \Carbon\Carbon::parse($checkIn);
                $end = \Carbon\Carbon::parse($checkOut);
                $diff = $start->diffInDays($end);
                $nights = max(1, $diff);
            } catch (\Exception $e) {
                $nights = 1;
            }
        }

        $baseAmount = (float) $roomType->price * $nights;
        $representativeRoom = $rooms->first() ?? $roomType->rooms()->where('status', 'available')->first();
        $topPromotions = collect();
        $allPromotions = collect();
        if ($representativeRoom) {
            $promoService = app(\App\Services\RoomPromotionService::class);
            $topPromotions = $promoService->getTopPromotions($representativeRoom, $baseAmount, 3);
            $allPromotions = $promoService->getAvailablePromotions($representativeRoom)
                ->filter(function($p) use ($baseAmount) { return $p->canApplyToAmount($baseAmount) && $p->calculateDiscount($baseAmount) > 0; });
        } else {
            $allPromotions = collect();
        }

        $mapPromotion = function($promo) use ($baseAmount) {
            $discount = (float) $promo->calculateDiscount($baseAmount);
            return [
                'id' => $promo->id,
                'title' => $promo->title,
                'code' => $promo->code,
                'discount_type' => $promo->discount_type,
                'discount_value' => (float) $promo->discount_value,
                'discount_text' => $promo->discount_text,
                'discount_amount' => $discount,
                'final_amount' => max(0, $baseAmount - $discount),
            ];
        };

        $topPromotionsArr = $topPromotions->map($mapPromotion)->values()->all();
        $allPromotionsArr = $allPromotions->map($mapPromotion)->values()->all();

        return view('client.rooms.single', compact(
            'roomType', 'rooms', 'reviews', 'averageRating', 'reviewsCount',
            'roomTypes', 'canReview', 'completedBookings', 'serviceCategories',
            'nights'
        ))->with([
            'topPromotions' => $topPromotionsArr,
            'allPromotions' => $allPromotionsArr,
        ]);
    }

    public function blogSingle()
    {
        return view('client.blog-single');
    }

    /**
     * Lấy danh sách reviews của loại phòng qua AJAX
     */
    public function getRoomReviewsAjax($id)
    {
        $roomType = $this->roomTypeService->findById($id);

        if (!$roomType) {
            return response()->json(['error' => 'Không tìm thấy loại phòng'], 404);
        }

        $reviews = $this->roomTypeReviewService->getRoomTypeReviews($roomType->id, 10);

        return view('client.rooms.reviews-list', compact('reviews'))->render();
    }

    /**
     * Preview khuyến mại cho loại phòng theo query (check_in_date, check_out_date, promotion_id|code)
     */
    public function promotionPreviewForRoomType(Request $request)
    {
        try {
            $roomTypeId = (int) $request->query('room_type_id');
            $roomType = $this->roomTypeService->findById($roomTypeId);
            if (!$roomType) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy loại phòng'], 404);
            }

            $checkIn = $request->query('check_in_date');
            $checkOut = $request->query('check_out_date');
            $nights = 1;
            if ($checkIn && $checkOut) {
                $start = \Carbon\Carbon::parse($checkIn);
                $end = \Carbon\Carbon::parse($checkOut);
                $nights = max(1, $start->diffInDays($end));
            }

            $promotionId = $request->query('promotion_id');
            $promotionCode = $request->query('promotion_code');

            $baseAmount = (float) $roomType->price * $nights;

            $promotion = null;
            if ($promotionId) {
                $promotion = Promotion::find($promotionId);
            } elseif ($promotionCode) {
                $promotion = Promotion::where('code', $promotionCode)->first();
            }

            $discount = 0.0;
            if ($promotion && $promotion->isValid() && $promotion->canApplyToRoomType($roomType->id) && $promotion->canApplyToAmount($baseAmount)) {
                $discount = (float) $promotion->calculateDiscount($baseAmount);
            }
            $final = max(0.0, $baseAmount - $discount);

            return response()->json([
                'success' => true,
                'base_amount' => (int) $baseAmount,
                'discount_amount' => (int) $discount,
                'final_amount' => (int) $final,
                'nights' => $nights,
                'promotion' => $promotion ? [
                    'id' => $promotion->id,
                    'title' => $promotion->title,
                    'code' => $promotion->code,
                    'discount_text' => $promotion->discount_text,
                ] : null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Không thể xem trước khuyến mại'], 422);
        }
    }
}
