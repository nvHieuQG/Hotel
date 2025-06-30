<?php

namespace App\Http\Controllers;

use App\Services\ReviewService;
use App\Services\BookingService;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{
    protected $reviewService;
    protected $bookingService;

    public function __construct(ReviewService $reviewService, BookingService $bookingService)
    {
        $this->reviewService = $reviewService;
        $this->bookingService = $bookingService;
    }

    /**
     * Hiển thị danh sách booking có thể đánh giá
     */
    public function index()
    {
        if (!Auth::check()) {
            return view('client.reviews.index', ['bookings' => collect()]);
        }
        
        $bookings = $this->bookingService->getCompletedBookingsWithoutReview();
        
        return view('client.reviews.index', compact('bookings'));
    }

    /**
     * Hiển thị form tạo review
     */
    public function create($bookingId)
    {
        // Kiểm tra xem booking có thể đánh giá không
        if (!$this->bookingService->canBeReviewed($bookingId)) {
            return redirect()->route('reviews.index')
                ->with('error', 'Booking này không thể đánh giá hoặc đã được đánh giá rồi.');
        }

        $booking = Booking::where('id', $bookingId)
            ->where('user_id', Auth::id())
            ->where('status', 'completed')
            ->whereDoesntHave('review')
            ->with('room.roomType')
            ->firstOrFail();

        return view('client.reviews.create', compact('booking'));
    }

    /**
     * Hiển thị form tạo review từ room_id
     */
    public function createFromRoom($roomId)
    {
        $room = \App\Models\Room::findOrFail($roomId);
        
        // Tìm booking đã hoàn thành của user cho phòng này
        $booking = Booking::where('room_id', $roomId)
            ->where('user_id', Auth::id())
            ->where('status', 'completed')
            ->whereDoesntHave('review')
            ->first();

        if (!$booking) {
            return redirect()->route('rooms-single', $roomId)
                ->with('error', 'Bạn chưa có booking hoàn thành cho phòng này hoặc đã đánh giá rồi.');
        }

        return view('client.reviews.create', compact('booking'));
    }

    /**
     * Lưu review mới
     */
    public function store(Request $request, $bookingId)
    {
        try {
            // Kiểm tra xem booking có thể đánh giá không
            if (!$this->bookingService->canBeReviewed($bookingId)) {
                return redirect()->route('reviews.index')
                    ->with('error', 'Booking này không thể đánh giá hoặc đã được đánh giá rồi.');
            }

            // Debug: Log request data
            Log::info('Review store request:', [
                'booking_id' => $bookingId,
                'data' => $request->all()
            ]);
            
            $validatedData = $this->reviewService->validateReviewData($request->all());
            
            // Debug: Log validated data
            Log::info('Validated data:', $validatedData);
            
            $this->reviewService->createReviewFromBooking($validatedData, $bookingId);

            return redirect()->route('reviews.index')
                ->with('success', 'Đánh giá của bạn đã được gửi và đang chờ duyệt.');
        } catch (\Exception $e) {
            // Debug: Log error
            Log::error('Review store error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa review
     */
    public function edit($id)
    {
        $review = $this->reviewService->getReviewById($id);
        
        if (!$review || $review->user_id !== Auth::id()) {
            return redirect()->route('reviews.index')
                ->with('error', 'Không tìm thấy review hoặc không có quyền chỉnh sửa.');
        }

        if (!$this->reviewService->canBeEdited($review)) {
            return redirect()->route('reviews.index')
                ->with('error', 'Không thể chỉnh sửa đánh giá này.');
        }

        return view('client.reviews.edit', compact('review'));
    }

    /**
     * Cập nhật review
     */
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $this->reviewService->validateReviewData($request->all());
            $this->reviewService->updateReview($id, $validatedData);

            return redirect()->route('reviews.index')
                ->with('success', 'Đánh giá đã được cập nhật thành công.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Xóa review
     */
    public function destroy($id)
    {
        try {
            $this->reviewService->deleteReview($id);

            return redirect()->route('reviews.index')
                ->with('success', 'Đánh giá đã được xóa thành công.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Hiển thị reviews của một phòng
     */
    public function roomReviews($roomId)
    {
        $room = \App\Models\Room::findOrFail($roomId);
        $reviews = $this->reviewService->getRoomReviews($roomId, 10);

        return view('client.reviews.room-reviews', compact('room', 'reviews'));
    }

    /**
     * Hiển thị tất cả reviews của user
     */
    public function myReviews()
    {
        $reviews = $this->reviewService->getUserReviews(10);
        return view('client.reviews.my-reviews', compact('reviews'));
    }
} 