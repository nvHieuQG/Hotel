<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourBooking;
use App\Models\Payment;
use App\Services\NotificationDataFormatterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AdminTourBookingController extends Controller
{
    protected $dataFormatterService;

    /**
     * Khởi tạo controller
     */
    public function __construct(NotificationDataFormatterService $dataFormatterService)
    {
        $this->dataFormatterService = $dataFormatterService;
    }

    /**
     * Hiển thị danh sách tour bookings
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        $paymentStatus = $request->get('payment_status');
        
        $query = TourBooking::with(['user', 'tourBookingRooms.roomType', 'payments'])
            ->orderBy('created_at', 'desc');

        // Lọc theo trạng thái
        if ($status) {
            $query->where('status', $status);
        }

        // Lọc theo trạng thái thanh toán
        if ($paymentStatus) {
            if ($paymentStatus === 'paid') {
                $query->whereHas('payments', function($q) {
                    $q->where('status', 'completed');
                });
            } elseif ($paymentStatus === 'unpaid') {
                $query->whereDoesntHave('payments', function($q) {
                    $q->where('status', 'completed');
                });
            }
        }

        $tourBookings = $query->paginate(15);

        return view('admin.tour-bookings.index', compact('tourBookings', 'status', 'paymentStatus'));
    }

    /**
     * Hiển thị chi tiết tour booking
     */
    public function show($id)
    {
        $tourBooking = TourBooking::with(['user', 'tourBookingRooms.roomType', 'payments'])
            ->findOrFail($id);

        $validNextStatuses = $this->getValidNextStatuses($tourBooking->status);

        return view('admin.tour-bookings.show', compact('tourBooking', 'validNextStatuses'));
    }

    /**
     * Hiển thị form tạo tour booking mới
     */
    public function create()
    {
        $users = \App\Models\User::orderBy('name')->get();
        $roomTypes = \App\Models\RoomType::orderBy('name')->get();

        return view('admin.tour-bookings.create', compact('users', 'roomTypes'));
    }

    /**
     * Lưu tour booking mới
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'tour_name' => 'required|string|max:255',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'total_guests' => 'required|integer|min:1',
            'room_selections' => 'required|array',
            'room_selections.*.room_type_id' => 'required|exists:room_types,id',
            'room_selections.*.quantity' => 'required|integer|min:1',
            'room_selections.*.guests_per_room' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        try {
            DB::beginTransaction();

            $totalRooms = collect($request->room_selections)->sum('quantity');

            $tourBooking = TourBooking::create([
                'user_id' => $validatedData['user_id'],
                'booking_id' => TourBooking::generateBookingId(),
                'tour_name' => $validatedData['tour_name'],
                'total_guests' => $validatedData['total_guests'],
                'total_rooms' => $totalRooms,
                'check_in_date' => $validatedData['check_in_date'],
                'check_out_date' => $validatedData['check_out_date'],
                'total_price' => 0, // Sẽ được tính toán sau
                'status' => $validatedData['status'],
                'special_requests' => $validatedData['special_requests'] ?? null,
            ]);

            // Tính toán giá và tạo tour booking rooms
            $totalPrice = 0;
            $totalNights = \Carbon\Carbon::parse($validatedData['check_in_date'])->diffInDays(\Carbon\Carbon::parse($validatedData['check_out_date']));

            foreach ($validatedData['room_selections'] as $selection) {
                $roomType = \App\Models\RoomType::find($selection['room_type_id']);
                $pricePerRoom = $roomType->price * $totalNights;
                $totalForRoomType = $pricePerRoom * $selection['quantity'];
                $totalPrice += $totalForRoomType;

                \App\Models\TourBookingRoom::create([
                    'tour_booking_id' => $tourBooking->id,
                    'room_type_id' => $selection['room_type_id'],
                    'quantity' => $selection['quantity'],
                    'guests_per_room' => $selection['guests_per_room'],
                    'price_per_room' => $pricePerRoom,
                    'total_price' => $totalForRoomType,
                ]);
            }

            // Cập nhật tổng tiền
            $tourBooking->update(['total_price' => $totalPrice]);

            DB::commit();

            return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                ->with('success', 'Tour booking đã được tạo thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating tour booking: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Có lỗi xảy ra khi tạo tour booking: ' . $e->getMessage()]);
        }
    }

    /**
     * Hiển thị form chỉnh sửa tour booking
     */
    public function edit($id)
    {
        $tourBooking = TourBooking::with(['tourBookingRooms.roomType'])->findOrFail($id);
        $users = \App\Models\User::orderBy('name')->get();
        $roomTypes = \App\Models\RoomType::orderBy('name')->get();

        return view('admin.tour-bookings.edit', compact('tourBooking', 'users', 'roomTypes'));
    }

    /**
     * Cập nhật tour booking
     */
    public function update(Request $request, $id)
    {
        $tourBooking = TourBooking::findOrFail($id);
        $oldStatus = $tourBooking->status;

        $validatedData = $request->validate([
            'tour_name' => 'required|string|max:255',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'total_guests' => 'required|integer|min:1',
            'special_requests' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'payment_status' => 'nullable|in:completed,partial,pending,overdue',
            'preferred_payment_method' => 'nullable|in:credit_card,bank_transfer,cash,online_payment',
        ]);

        try {
            // Chỉ tự động cập nhật payment_status nếu admin không chọn
            if (!isset($validatedData['payment_status']) || empty($validatedData['payment_status'])) {
                $completedAmount = $tourBooking->payments()->where('status', 'completed')->sum('amount');
                $totalAmount = $tourBooking->total_price;
                
                if ($completedAmount >= $totalAmount) {
                    $validatedData['payment_status'] = 'completed';
                } elseif ($completedAmount > 0) {
                    $validatedData['payment_status'] = 'partial';
                } else {
                    $validatedData['payment_status'] = 'pending';
                }
            }
            
            $tourBooking->update($validatedData);

            // Gửi email xác nhận nếu admin đổi sang trạng thái 'confirmed'
            if (($validatedData['status'] ?? null) === 'confirmed' && $oldStatus !== 'confirmed') {
                $this->sendTourBookingConfirmationEmail($tourBooking);
            }

            return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                ->with('success', 'Tour booking đã được cập nhật thành công!');

        } catch (\Exception $e) {
            Log::error('Error updating tour booking: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Có lỗi xảy ra khi cập nhật tour booking: ' . $e->getMessage()]);
        }
    }

    /**
     * Xóa tour booking
     */
    public function destroy($id)
    {
        $tourBooking = TourBooking::findOrFail($id);

        try {
            // Xóa các tour booking rooms trước
            $tourBooking->tourBookingRooms()->delete();
            
            // Xóa các payments
            $tourBooking->payments()->delete();
            
            // Xóa tour booking
            $tourBooking->delete();

            return redirect()->route('admin.tour-bookings.index')
                ->with('success', 'Tour booking đã được xóa thành công!');

        } catch (\Exception $e) {
            Log::error('Error deleting tour booking: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Có lỗi xảy ra khi xóa tour booking: ' . $e->getMessage()]);
        }
    }

    /**
     * Cập nhật trạng thái tour booking
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed'
        ]);

        $tourBooking = TourBooking::findOrFail($id);
        $oldStatus = $tourBooking->status;
        $newStatus = $request->status;

        try {
            $tourBooking->update(['status' => $newStatus]);

            // Gửi email xác nhận nếu trạng thái mới là 'confirmed'
            if ($newStatus === 'confirmed' && $oldStatus !== 'confirmed') {
                $this->sendTourBookingConfirmationEmail($tourBooking);
            }

            // Tạo thông báo cho user
            $this->createStatusChangeNotification($tourBooking, $oldStatus, $newStatus);

            return response()->json([
                'success' => true,
                'message' => 'Trạng thái tour booking đã được cập nhật thành công!',
                'new_status' => $newStatus
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating tour booking status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật trạng thái một payment của tour booking (admin xác nhận/đánh dấu thất bại)
     */
    public function updatePaymentStatus(Request $request, $id, $paymentId)
    {
        $request->validate([
            'status' => 'required|in:completed,failed'
        ]);

        $tourBooking = TourBooking::with('payments')->findOrFail($id);
        $payment = Payment::where('id', $paymentId)
            ->where('booking_id', $tourBooking->id)
            ->firstOrFail();

        try {
            // Cập nhật trạng thái payment
            $updateData = [
                'status' => $request->status,
            ];

            if ($request->status === 'completed') {
                $updateData['paid_at'] = now();
                $updateData['gateway_code'] = $payment->gateway_code ?? 'ADMIN_CONFIRMED';
                $updateData['gateway_message'] = $payment->gateway_message ?? 'Xác nhận thanh toán bởi admin';
            } else {
                $updateData['gateway_code'] = $payment->gateway_code ?? 'ADMIN_FAILED';
                $updateData['gateway_message'] = $payment->gateway_message ?? 'Đánh dấu thất bại bởi admin';
            }

            $payment->update($updateData);

            // Tính lại trạng thái thanh toán tổng quan
            $completedAmount = $tourBooking->payments()->where('status', 'completed')->sum('amount');
            $totalAmount = $tourBooking->total_price;

            $paymentStatus = 'pending';
            if ($completedAmount >= $totalAmount) {
                $paymentStatus = 'completed';
            } elseif ($completedAmount > 0) {
                $paymentStatus = 'partial';
            }

            // Cập nhật payment_status và xác nhận booking nếu đã thanh toán đủ
            $updateBooking = [
                'payment_status' => $paymentStatus,
            ];

            $wasPending = $tourBooking->status === 'pending';
            
            if ($paymentStatus === 'completed' && $wasPending) {
                $updateBooking['status'] = 'confirmed';
            }

            $tourBooking->update($updateBooking);

            // Gửi email xác nhận nếu trạng thái mới là 'confirmed'
            if ($updateBooking['status'] === 'confirmed' && $wasPending) {
                $this->sendTourBookingConfirmationEmail($tourBooking);
            }

            return back()->with('success', 'Cập nhật trạng thái thanh toán thành công.');
        } catch (\Exception $e) {
            Log::error('Error updating payment status: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Có lỗi xảy ra khi cập nhật: ' . $e->getMessage()]);
        }
    }

    /**
     * Hiển thị báo cáo tour bookings
     */
    public function report(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', now()->endOfMonth()->format('Y-m-d'));

        $query = TourBooking::whereBetween('created_at', [$startDate, $endDate]);

        // Thống kê theo trạng thái
        $statusStats = $query->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Thống kê theo tháng
        $monthlyStats = $query->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count, SUM(total_price) as revenue')
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Tổng doanh thu
        $totalRevenue = $query->sum('total_price');

        // Tổng số booking
        $totalBookings = $query->count();

        return view('admin.tour-bookings.report', compact(
            'statusStats',
            'monthlyStats',
            'totalRevenue',
            'totalBookings',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Lấy các trạng thái hợp lệ tiếp theo
     */
    private function getValidNextStatuses($currentStatus): array
    {
        $statusFlow = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['completed', 'cancelled'],
            'cancelled' => [],
            'completed' => []
        ];

        return $statusFlow[$currentStatus] ?? [];
    }

    /**
     * Gửi email xác nhận tour booking
     */
    private function sendTourBookingConfirmationEmail(TourBooking $tourBooking): void
    {
        try {
            $user = $tourBooking->user;
            
            Log::info('Attempting to send tour booking confirmation email', [
                'tour_booking_id' => $tourBooking->id,
                'user_email' => $user->email,
                'user_id' => $user->id,
                'mail_driver' => config('mail.default')
            ]);
            
            \Mail::to($user->email)->send(new \App\Mail\TourBookingConfirmationMail($tourBooking));
            
            Log::info('Tour booking confirmation email sent successfully by admin', [
                'tour_booking_id' => $tourBooking->id,
                'user_email' => $user->email
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending tour booking confirmation email by admin', [
                'tour_booking_id' => $tourBooking->id,
                'user_email' => $tourBooking->user->email ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Tạo thông báo khi thay đổi trạng thái
     */
    private function createStatusChangeNotification(TourBooking $tourBooking, $oldStatus, $newStatus): void
    {
        $statusMessages = [
            'confirmed' => 'Tour booking của bạn đã được xác nhận!',
            'cancelled' => 'Tour booking của bạn đã bị hủy.',
            'completed' => 'Tour booking của bạn đã hoàn thành!'
        ];

        if (isset($statusMessages[$newStatus])) {
            $tourBooking->user->notifications()->create([
                'type' => 'App\Notifications\TourBookingStatusChanged',
                'data' => [
                    'tour_booking_id' => $tourBooking->id,
                    'tour_name' => $tourBooking->tour_name,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'message' => $statusMessages[$newStatus]
                ],
                'read_at' => null
            ]);
        }
    }
}
