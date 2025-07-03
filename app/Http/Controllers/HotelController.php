<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RoomTypeReviewService;
use App\Interfaces\Services\RoomTypeServiceInterface;
use App\Interfaces\Repositories\RoomRepositoryInterface;

class HotelController extends Controller
{
    protected $roomRepository;
    protected $roomTypeReviewService;
    protected $roomTypeService;
    
    public function __construct(RoomRepositoryInterface $roomRepository, RoomTypeReviewService $roomTypeReviewService, RoomTypeServiceInterface $roomTypeService)
    {
        $this->roomRepository = $roomRepository;
        $this->roomTypeReviewService = $roomTypeReviewService;
        $this->roomTypeService = $roomTypeService;
    }

    public function index()
    {
        // Lấy tất cả phòng để hiển thị ở trang chủ
        $rooms = $this->roomRepository->getAll()->take(6); // Lấy 6 phòng đầu tiên
        $roomTypes = $this->roomTypeService->getAllRoomTypes();
        
        return view('client.index', compact('rooms', 'roomTypes'));
    }

    public function rooms()
    {
        // Lấy tất cả phòng để hiển thị ở trang danh sách phòng
        $rooms = $this->roomRepository->getAll();
        $roomTypes = $this->roomTypeService->getAllRoomTypes();
        return view('client.rooms', compact('rooms', 'roomTypes'));
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
        // Nếu không có id, chuyển hướng đến trang danh sách phòng
        if (!$id) {
            return redirect()->route('rooms');
        }
        
        // Lấy thông tin chi tiết phòng
        $room = $this->roomRepository->findById($id);
        
        if (!$room) {
            return redirect()->route('rooms')->with('error', 'Không tìm thấy phòng');
        }
        
        // Lấy các phòng khác cùng loại
        $relatedRooms = $this->roomRepository->getAll()
            ->where('room_type_id', $room->room_type_id)
            ->where('id', '!=', $room->id)
            ->take(2);
        
        // Lấy đánh giá và bình luận của loại phòng
        $reviews = $this->roomTypeReviewService->getRoomTypeReviews($room->room_type_id, 10);
        $averageRating = $this->roomTypeReviewService->getRoomTypeAverageRating($room->room_type_id);
        $reviewsCount = $this->roomTypeReviewService->getRoomTypeReviewsCount($room->room_type_id);

        // Kiểm tra quyền đánh giá
        $canReview = false;
        if (Auth::check()) {
            // Kiểm tra xem user đã có booking hoàn thành cho loại phòng này chưa
            $hasCompletedBooking = Auth::user()->bookings()
                ->where('status', 'completed')
                ->whereHas('room', function($query) use ($room) {
                    $query->where('room_type_id', $room->room_type_id);
                })
                ->exists();
            
            // Kiểm tra xem user đã đánh giá loại phòng này chưa
            $hasReviewed = $this->roomTypeReviewService->hasUserReviewedRoomType(Auth::id(), $room->room_type_id);
            
            $canReview = $hasCompletedBooking && !$hasReviewed;
        }

        $roomTypes = $this->roomTypeService->getAllRoomTypes();
        return view('client.rooms-single', compact('room', 'relatedRooms', 'reviews', 'averageRating', 'reviewsCount', 'roomTypes', 'canReview'));
    }

    public function blogSingle()
    {
        return view('client.blog-single');
    }
    
    /**
     * Lấy danh sách reviews của phòng qua AJAX
     */
    public function getRoomReviewsAjax($id)
    {
        $room = $this->roomRepository->findById($id);
        
        if (!$room) {
            return response()->json(['error' => 'Không tìm thấy phòng'], 404);
        }
        
        $reviews = $this->roomTypeReviewService->getRoomTypeReviews($room->room_type_id, 10);
        
        return view('client.partials.room-reviews-list', compact('reviews'))->render();
    }
}
