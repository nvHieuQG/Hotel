<?php

namespace App\Http\Controllers;

use App\Services\ReviewService;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Hiển thị form tạo review
     */
    public function create($bookingId)
    {
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Kiểm tra xem booking có thể đánh giá không
        if (!$this->reviewService->canBeReviewed($booking)) {
            return redirect()->route('my-bookings')
                ->with('error', 'Booking này không thể đánh giá hoặc đã được đánh giá rồi.');
        }

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
            $validatedData = $this->reviewService->validateReviewData($request->all());
            $this->reviewService->createReview($validatedData, $bookingId);

            return redirect()->route('my-bookings')
                ->with('success', 'Đánh giá của bạn đã được gửi và đang chờ duyệt.');
        } catch (\Exception $e) {
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
            return redirect()->route('my-bookings')
                ->with('error', 'Không tìm thấy review hoặc không có quyền chỉnh sửa.');
        }

        if (!$this->reviewService->canBeEdited($review)) {
            return redirect()->route('my-bookings')
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

            return redirect()->route('my-bookings')
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

            return redirect()->route('my-bookings')
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