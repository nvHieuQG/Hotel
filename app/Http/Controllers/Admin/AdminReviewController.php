<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReviewService;
use Illuminate\Http\Request;

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
        $reviews = $this->reviewService->getAllReviewsForAdmin($filters, 15);
        $rooms = \App\Models\Room::all();

        return view('admin.reviews.index', compact('reviews', 'rooms'));
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

        return view('admin.reviews.show', compact('review'));
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
    public function destroy($id)
    {
        try {
            $review = $this->reviewService->getReviewById($id);
            if (!$review) {
                throw new \Exception('Không tìm thấy review.');
            }

            $this->reviewService->deleteReview($id);
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