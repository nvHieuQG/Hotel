<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\Services\ProfileServiceInterface;
use App\Interfaces\Services\BookingServiceInterface;
use App\Interfaces\Repositories\RoomTypeReviewRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class UserProfileController extends Controller
{
    protected $userProfileService;
    protected $userBookingService;
    protected $userRoomTypeReviewRepository;

    public function __construct(
        ProfileServiceInterface $profileService,
        BookingServiceInterface $bookingService,
        RoomTypeReviewRepositoryInterface $roomTypeReviewRepository
    ) {
        $this->userProfileService = $profileService;
        $this->userBookingService = $bookingService;
        $this->userRoomTypeReviewRepository = $roomTypeReviewRepository;
    }

    // Trang thông tin cá nhân (chỉ hiển thị thông tin profile, không load bookings/reviews)
    public function showUserProfile()
    {
        try {
            $profileData = $this->userProfileService->getProfileData();
            $profileData['dashboardData'] = $this->userProfileService->getDashboardData();
            
            // Xóa session active_tab và tab nếu có để reset về tab đầu tiên khi truy cập trực tiếp
            if (request()->session()->has('active_tab')) {
                request()->session()->forget('active_tab');
            }
            if (request()->session()->has('tab')) {
                request()->session()->forget('tab');
            }
            
            return view('client.profile.index', $profileData);
        } catch (\Exception $e) {
            // Log lỗi để debug
            Log::error('User Profile error: ' . $e->getMessage());
            Log::error('User Profile error trace: ' . $e->getTraceAsString());
            
            // Trả về lỗi đơn giản
            return response()->json([
                'error' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    // Cập nhật thông tin cá nhân
    public function updateUserProfile(Request $request)
    {
        try {
            $this->userProfileService->updateProfile($request->all());
            return redirect()->back()->with('success', 'Cập nhật thông tin thành công!')->with('tab', 'info');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('tab', 'info');
        }
    }

    // Đổi mật khẩu
    public function changeUserPassword(Request $request)
    {
        try {
            $this->userProfileService->changePassword($request->all());
            return redirect()->back()->with('success', 'Đổi mật khẩu thành công!')->with('tab', 'password');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput()->with('tab', 'password');
        }
    }

    // Danh sách booking của user (load dữ liệu riêng biệt)
    public function showUserBookings()
    {
        try {
            $profileData = $this->userProfileService->getProfileData();
            $profileData['dashboardData'] = $this->userProfileService->getDashboardData();
            $profileData['bookings'] = $this->userBookingService->getCurrentUserBookings(10);
            $profileData['active_tab'] = 'bookings';
            
            return view('client.profile.index', $profileData);
        } catch (\Exception $e) {
            Log::error('User Bookings error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải danh sách đặt phòng');
        }
    }

    // Danh sách review của user (load dữ liệu riêng biệt)
    public function showUserReviews()
    {
        try {
            $profileData = $this->userProfileService->getProfileData();
            $profileData['dashboardData'] = $this->userProfileService->getDashboardData();
            $profileData['reviews'] = $this->userRoomTypeReviewRepository->getReviewsByUser(Auth::id(), 10);
            $profileData['active_tab'] = 'reviews';
            
            return view('client.profile.index', $profileData);
        } catch (\Exception $e) {
            Log::error('User Reviews error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải danh sách đánh giá');
        }
    }

    // API method để trả về partial view cho bookings
    public function getBookingsPartial()
    {
        try {
            $bookings = $this->userBookingService->getCurrentUserBookings(10);
            $count = $bookings->total();
            Log::info('Bookings partial', ['count' => $count, 'user_id' => Auth::id()]);
            return view('client.profile.bookings.partial', compact('bookings'));
        } catch (\Exception $e) {
            Log::error('User Bookings Partial error: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi tải dữ liệu đặt phòng', 'exception' => $e->getMessage()], 500);
        }
    }

    // API method để trả về partial view cho reviews
    public function getReviewsPartial()
    {
        try {
            $reviews = $this->userRoomTypeReviewRepository->getReviewsByUser(Auth::id(), 10);
            $count = $reviews->total();
            Log::info('Reviews partial', ['count' => $count, 'user_id' => Auth::id()]);
            return view('client.profile.reviews.partial', compact('reviews'));
        } catch (\Exception $e) {
            Log::error('User Reviews Partial error: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi tải dữ liệu đánh giá', 'exception' => $e->getMessage()], 500);
        }
    }

    // API method để trả về chi tiết booking
    public function getBookingDetail($id)
    {
        try {
            $booking = $this->userBookingService->getBookingDetail($id);
            
            // Kiểm tra quyền truy cập
            if (!$booking || $booking->user_id != Auth::id()) {
                return response()->json(['error' => 'Không tìm thấy đặt phòng hoặc không có quyền truy cập'], 404);
            }
            
            return view('client.profile.bookings.detail', compact('booking'));
        } catch (\Exception $e) {
            Log::error('User Booking Detail error: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi tải chi tiết đặt phòng', 'exception' => $e->getMessage()], 500);
        }
    }

    // API method để trả về chi tiết review (HTML view)
    public function getReviewDetail($id)
    {
        try {
            $review = $this->userRoomTypeReviewRepository->getReviewById($id);
            
            // Kiểm tra quyền truy cập
            if (!$review || $review->user_id != Auth::id()) {
                return response()->json(['error' => 'Không tìm thấy đánh giá hoặc không có quyền truy cập'], 404);
            }
            
            return view('client.profile.reviews.detail', compact('review'));
        } catch (\Exception $e) {
            Log::error('User Review Detail error: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi tải chi tiết đánh giá', 'exception' => $e->getMessage()], 500);
        }
    }

    // API method để lấy dữ liệu review cho form chỉnh sửa (JSON)
    public function getReviewData($id)
    {
        try {
            $review = $this->userRoomTypeReviewRepository->getReviewById($id);
            
            // Kiểm tra quyền truy cập
            if (!$review || $review->user_id != Auth::id()) {
                return response()->json(['error' => 'Không tìm thấy đánh giá hoặc không có quyền truy cập'], 404);
            }
            
            return response()->json(['review' => $review]);
        } catch (\Exception $e) {
            Log::error('User Review Data error: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi tải dữ liệu đánh giá', 'exception' => $e->getMessage()], 500);
        }
    }

    // Tạo đánh giá mới
    public function createReview(Request $request, $roomTypeId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
                'cleanliness_rating' => 'nullable|integer|min:1|max:5',
                'comfort_rating' => 'nullable|integer|min:1|max:5',
                'location_rating' => 'nullable|integer|min:1|max:5',
                'facilities_rating' => 'nullable|integer|min:1|max:5',
                'value_rating' => 'nullable|integer|min:1|max:5',
                'is_anonymous' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $data = $request->all();
            $data['user_id'] = Auth::id();
            $data['room_type_id'] = $roomTypeId;
            $data['status'] = 'approved';

            $review = $this->userRoomTypeReviewRepository->createReview($data);

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá đã được tạo thành công!',
                'review' => $review
            ]);
        } catch (\Exception $e) {
            Log::error('Create Review error: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi tạo đánh giá: ' . $e->getMessage()], 500);
        }
    }

    // Cập nhật đánh giá
    public function updateReview(Request $request, $id)
    {
        try {
            $review = $this->userRoomTypeReviewRepository->getReviewById($id);
            
            if (!$review || $review->user_id != Auth::id()) {
                return response()->json(['error' => 'Không tìm thấy đánh giá hoặc không có quyền chỉnh sửa'], 404);
            }

            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
                'cleanliness_rating' => 'nullable|integer|min:1|max:5',
                'comfort_rating' => 'nullable|integer|min:1|max:5',
                'location_rating' => 'nullable|integer|min:1|max:5',
                'facilities_rating' => 'nullable|integer|min:1|max:5',
                'value_rating' => 'nullable|integer|min:1|max:5',
                'is_anonymous' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $this->userRoomTypeReviewRepository->updateReview($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá đã được cập nhật thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Update Review error: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi cập nhật đánh giá: ' . $e->getMessage()], 500);
        }
    }

    // Xóa đánh giá
    public function deleteReview($id)
    {
        try {
            $review = $this->userRoomTypeReviewRepository->getReviewById($id);
            
            if (!$review || $review->user_id != Auth::id()) {
                return response()->json(['error' => 'Không tìm thấy đánh giá hoặc không có quyền xóa'], 404);
            }

            $this->userRoomTypeReviewRepository->deleteReview($id);

            return response()->json([
                'success' => true,
                'message' => 'Đánh giá đã được xóa thành công!'
            ]);
        } catch (\Exception $e) {
            Log::error('Delete Review error: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi xóa đánh giá: ' . $e->getMessage()], 500);
        }
    }
} 