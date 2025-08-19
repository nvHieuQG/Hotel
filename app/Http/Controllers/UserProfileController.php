<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\Services\ProfileServiceInterface;
use App\Interfaces\Services\BookingServiceInterface;
use App\Interfaces\Repositories\RoomTypeReviewRepositoryInterface;
use App\Models\User;
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

    // Entry mặc định cho /profile
    public function index()
    {
        return $this->showUserProfile();
    }

    // Trang thông tin cá nhân (alias)
    public function info()
    {
        return $this->showUserProfile();
    }

    // Cập nhật thông tin cá nhân (alias)
    public function updateInfo(Request $request)
    {
        return $this->updateUserProfile($request);
    }

    // Trang đổi mật khẩu (render cùng trang profile, set tab password)
    public function password()
    {
        try {
            $profileData = $this->userProfileService->getProfileData();
            $profileData['dashboardData'] = $this->userProfileService->getDashboardData();
            $profileData['active_tab'] = 'password';
            return view('client.profile.index', $profileData);
        } catch (\Exception $e) {
            Log::error('User Password page error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải trang đổi mật khẩu');
        }
    }

    // Cập nhật mật khẩu (alias)
    public function updatePassword(Request $request)
    {
        return $this->changeUserPassword($request);
    }

    // Danh sách đặt phòng (alias)
    public function bookings()
    {
        return $this->showUserBookings();
    }

    // Chi tiết đặt phòng trong profile: điều hướng về route hiện có
    public function bookingDetail($bookingId)
    {
        return redirect()->route('booking.detail', $bookingId);
    }

    // Danh sách đánh giá (alias)
    public function reviews()
    {
        return $this->showUserReviews();
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

    // Trang quản lý khuyến mại
    public function promotions()
    {
        try {
            $user = Auth::user();
            $bookingsWithPromotions = $user->bookings()
                ->whereNotNull('promotion_id')
                ->with(['promotion', 'room.roomType'])
                ->orderBy('created_at', 'desc')
                ->get();
            
            return view('client.profile.promotions', compact('bookingsWithPromotions'));
        } catch (\Exception $e) {
            Log::error('User Promotions error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tải danh sách khuyến mại');
        }
    }

    /**
     * Trả về partial danh sách đánh giá cho AJAX
     */
    public function partialReviews()
    {
        $reviews = $this->userRoomTypeReviewRepository->getReviewsByUser(Auth::id());
        return view('client.profile.reviews.partial', compact('reviews'));
    }

    /**
     * Trả về chi tiết đánh giá cho modal
     */
    public function reviewDetail($id)
    {
        $review = $this->userRoomTypeReviewRepository->getReviewById($id);
        
        // Kiểm tra quyền truy cập: chỉ cho phép user xem review của chính mình
        if (!$review || $review->user_id !== Auth::id()) {
            abort(403, 'Không tìm thấy review hoặc không có quyền truy cập.');
        }

        return view('client.profile.reviews.detail', compact('review'));
    }

    /**
     * Lấy dữ liệu review cho AJAX
     */
    public function getReviewData($id)
    {
        try {
            $review = $this->userRoomTypeReviewRepository->getReviewById($id);
            
            if (!$review || $review->user_id !== Auth::id()) {
                return response()->json(['error' => 'Không tìm thấy review hoặc không có quyền truy cập'], 404);
            }

            return response()->json([
                'success' => true,
                'review' => $review
            ]);
        } catch (\Exception $e) {
            Log::error('Get Review Data error: ' . $e->getMessage());
            return response()->json(['error' => 'Có lỗi xảy ra khi lấy dữ liệu review'], 500);
        }
    }


    // Tạo đánh giá mới
    public function createReview(Request $request, $roomTypeId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rating' => 'required|integer|min:1|max:5',
                'comment' => 'nullable|string|max:1000',
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