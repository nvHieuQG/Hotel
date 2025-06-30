<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\Repositories\RoomRepositoryInterface;
use App\Services\ReviewService;

class HotelController extends Controller
{
    protected $roomRepository;
    protected $reviewService;

    public function __construct(RoomRepositoryInterface $roomRepository, ReviewService $reviewService)
    {
        $this->roomRepository = $roomRepository;
        $this->reviewService = $reviewService;
    }

    public function index()
    {
        // Lấy 6 phòng ngẫu nhiên để hiển thị ở trang chủ
        $rooms = $this->roomRepository->getAll()->shuffle()->take(6);
        return view('client.index', compact('rooms'));
    }

    public function rooms()
    {
        // Lấy tất cả phòng để hiển thị ở trang danh sách phòng
        $rooms = $this->roomRepository->getAll();
        return view('client.rooms', compact('rooms'));
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
        
        // Lấy đánh giá và bình luận của phòng
        $reviews = $this->reviewService->getRoomReviews($room->id, 10);
        $averageRating = $this->reviewService->getRoomAverageRating($room->id);
        $reviewsCount = $this->reviewService->getRoomReviewsCount($room->id);
        
        return view('client.rooms-single', compact('room', 'relatedRooms', 'reviews', 'averageRating', 'reviewsCount'));
    }

    public function blogSingle()
    {
        return view('client.blog-single');
    }
}
