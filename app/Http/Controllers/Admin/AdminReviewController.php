<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;
use App\Models\Review;

class AdminReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Hiển thị danh sách tất cả reviews
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'room_id', 'rating', 'user_id']);
        
        // Thêm filter tìm kiếm theo tên khách hàng
        if ($request->filled('search')) {
            $filters['search'] = $request->search;
        }
        
        $reviews = $this->reviewService->getAllReviewsForAdmin($filters, 15);
        $rooms = \App\Models\Room::all();

        return view('admin.reviews.index', compact('reviews', 'rooms'));
    }

    /**
     * Hiển thị form tạo review mới
     */
    public function create()
    {
        // Lấy danh sách booking đã hoàn thành và chưa được đánh giá
        $bookings = \App\Models\Booking::with(['user', 'room'])
            ->where('status', 'completed')
            ->whereDoesntHave('review')
            ->orderBy('check_out', 'desc')
            ->get();
        
        return view('admin.reviews.create', compact('bookings'));
    }

    /**
     * Lưu review mới
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'status' => 'required|in:pending,approved,rejected',
            'is_anonymous' => 'boolean'
        ]);

        try {
            // Lấy thông tin booking
            $booking = \App\Models\Booking::findOrFail($validated['booking_id']);
            
            // Kiểm tra booking đã hoàn thành chưa
            if ($booking->status !== 'completed') {
                throw new \Exception('Booking này chưa hoàn thành.');
            }
            
            // Kiểm tra booking đã được đánh giá chưa
            if ($booking->review) {
                throw new \Exception('Booking này đã được đánh giá.');
            }

            // Thêm thông tin user và room từ booking
            $reviewData = array_merge($validated, [
                'user_id' => $booking->user_id,
                'room_id' => $booking->room_id,
                'booking_id' => $booking->id
            ]);

            $this->reviewService->createReview($reviewData);
            return redirect()->route('admin.reviews.index')->with('success', 'Đánh giá đã được tạo thành công.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Hiển thị chi tiết review
     */
    public function show($id)
    {
        $review = $this->reviewService->getReviewById($id);
        
        if (!$review) {
            return redirect()->route('admin.reviews.index')
                ->with('error', 'Không tìm thấy review.');
        }

        $rooms = \App\Models\Room::all();

        return view('admin.reviews.show', compact('review', 'rooms'));
    }

    /**
     * Duyệt review
     */
    public function approve($id)
    {
        try {
            $this->reviewService->approveReview($id);
            return redirect()->back()->with('success', 'Đánh giá đã được duyệt thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Từ chối review
     */
    public function reject($id)
    {
        try {
            $this->reviewService->rejectReview($id);
            return redirect()->back()->with('success', 'Đánh giá đã bị từ chối.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Xóa review
     */
    public function destroy(Review $review)
    {
        try {
            $this->reviewService->deleteReviewForAdmin($review->id);
            return redirect()->route('admin.reviews.index')->with('success', 'Đánh giá đã được xóa thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Thống kê reviews
     */
    public function statistics()
    {
        $statistics = $this->reviewService->getReviewStatistics();
        
        return view('admin.reviews.statistics', $statistics);
    }
} 