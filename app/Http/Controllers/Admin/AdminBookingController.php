<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\Admin\AdminBookingServiceInterface;
use Illuminate\Http\Request;

class AdminBookingController extends Controller
{
    protected $bookingService;

    /**
     * Khởi tạo controller
     */
    public function __construct(AdminBookingServiceInterface $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Hiển thị danh sách đặt phòng
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        $bookings = $this->bookingService->getBookingsWithPagination($request);
        
        return view('admin.bookings.index', compact('bookings', 'status'));
    }

    /**
     * Hiển thị chi tiết đặt phòng
     */
    public function show($id)
    {
        $booking = $this->bookingService->getBookingDetails($id);
        $validNextStatuses = $this->bookingService->getValidNextStatuses($id);
        
        return view('admin.bookings.show', compact('booking', 'validNextStatuses'));
    }

    /**
     * Hiển thị form tạo đặt phòng mới
     */
    public function create()
    {
        $formData = $this->bookingService->getCreateFormData();
        
        return view('admin.bookings.create', [
            'rooms' => $formData['rooms'],
            'users' => $formData['users']
        ]);
    }

    /**
     * Lưu đặt phòng mới
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'status' => 'required|in:pending,confirmed',
        ]);
        
        $this->bookingService->createBooking($validatedData);
        
        return redirect()->route('admin.bookings.index')
            ->with('success', 'Đã tạo đặt phòng thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa đặt phòng
     */
    public function edit($id)
    {
        $formData = $this->bookingService->getEditFormData($id);
        $validNextStatuses = $this->bookingService->getValidNextStatuses($id);
        
        return view('admin.bookings.edit', [
            'booking' => $formData['booking'],
            'rooms' => $formData['rooms'],
            'users' => $formData['users'],
            'validNextStatuses' => $validNextStatuses
        ]);
    }

    /**
     * Cập nhật thông tin đặt phòng
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,completed,cancelled,no_show',
        ]);
        
        $booking = $this->bookingService->updateBooking($id, $validatedData);
        
        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', 'Đã cập nhật đặt phòng thành công.');
    }

    /**
     * Cập nhật trạng thái đặt phòng
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,completed,cancelled,no_show'
        ]);
        
        try {
            $this->bookingService->updateBookingStatus($id, $request->status);
            return redirect()->back()->with('success', 'Đã cập nhật trạng thái đặt phòng thành công.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }



    /**
     * Xóa đặt phòng
     */
    public function destroy($id)
    {
        $this->bookingService->deleteBooking($id);
        
        return redirect()->route('admin.bookings.index')
            ->with('success', 'Đã xóa đặt phòng thành công.');
    }
    
    /**
     * Hiển thị báo cáo đặt phòng
     */
    public function report(Request $request)
    {
        try {
            $reportData = $this->bookingService->getReportData($request);
            
            return view('admin.bookings.report', [
                'bookings' => $reportData['bookings'],
                'fromDate' => $reportData['filters']['from_date'] ?? null,
                'toDate' => $reportData['filters']['to_date'] ?? null,
                'status' => $reportData['filters']['status'] ?? null,
                'totalRevenue' => $reportData['totalRevenue'],
                'statusStats' => $reportData['statusStats']
            ]);
        } catch (\Exception $e) {
            return redirect()->route('admin.bookings.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Trả về partial chi tiết booking cho AJAX/modal (admin)
     */
    public function detailPartial($id)
    {
        $booking = $this->bookingService->getBookingDetails($id);
        return view('admin.bookings.detail', compact('booking'));
    }
} 