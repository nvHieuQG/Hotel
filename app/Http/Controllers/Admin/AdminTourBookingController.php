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
use Illuminate\Support\Facades\Auth;

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
        $tourBooking = TourBooking::with(['user', 'tourBookingRooms.roomType', 'payments', 'tourBookingServices', 'tourBookingNotes.user'])
            ->findOrFail($id);

        $validNextStatuses = $this->getValidNextStatuses($tourBooking->status);

        // Tính toán các giá trị cần thiết theo thực tế
        $totalRoomsAmount = $tourBooking->tourBookingRooms->sum('total_price');
        $totalServicesAmount = $tourBooking->tourBookingServices->sum('total_price');
        $totalAmountBeforeDiscount = $totalRoomsAmount + $totalServicesAmount;
        $totalPaid = $tourBooking->payments->where('status', 'completed')->sum('amount');
        
        // Tính toán tổng tiền đã thanh toán (bao gồm cả pending để hiển thị chính xác)
        $totalPaidIncludingPending = $tourBooking->payments->whereIn('status', ['completed', 'pending'])->sum('amount');
        
        // Tính toán promotion discount nếu chưa có
        $totalDiscount = $tourBooking->promotion_discount ?? 0;
        if ($totalDiscount == 0 && $tourBooking->promotion_code) {
            // Tìm promotion và tính toán discount
            $promotion = \App\Models\Promotion::where('code', $tourBooking->promotion_code)
                ->where('status', 'active')
                ->first();
            
            if ($promotion) {
                $totalDiscount = $promotion->calculateDiscount($totalAmountBeforeDiscount);
                Log::info('Calculated promotion discount', [
                    'promotion_code' => $tourBooking->promotion_code,
                    'discount_amount' => $totalDiscount,
                    'promotion_type' => $promotion->type,
                    'discount_value' => $promotion->discount_value
                ]);
            }
        }
        
        // Tính toán giá cuối - LUÔN tính từ tổng hiện tại để bao gồm dịch vụ mới
        if ($totalDiscount > 0) {
            $finalAmount = $totalAmountBeforeDiscount - $totalDiscount;
        } else {
            $finalAmount = $totalAmountBeforeDiscount;
        }
        
        // QUAN TRỌNG: Số tiền còn thiếu phải dựa trên giá cuối (đã giảm giá)
        // Không được tính giảm giá thêm lần nữa
        $outstandingAmount = $finalAmount - $totalPaidIncludingPending;
        
        // Số tiền còn thiếu thực tế (chỉ tính completed payments)
        $actualOutstandingAmount = $finalAmount - $totalPaid;
        
        // Debug logging để kiểm tra promotion
        Log::info('Tour booking price calculation - FIXED', [
            'tour_booking_id' => $tourBooking->id,
            'total_rooms_amount' => $totalRoomsAmount,
            'total_services_amount' => $totalServicesAmount,
            'total_amount_before_discount' => $totalAmountBeforeDiscount,
            'promotion_discount' => $totalDiscount,
            'promotion_code' => $tourBooking->promotion_code ?? 'N/A',
            'final_amount' => $finalAmount,
            'total_paid_completed' => $totalPaid,
            'total_paid_including_pending' => $totalPaidIncludingPending,
            'outstanding_amount' => $outstandingAmount,
            'actual_outstanding_amount' => $actualOutstandingAmount,
            'note' => 'Final amount already includes promotion discount'
        ]);

        // Lấy dịch vụ và ghi chú
        $tourBookingServices = $tourBooking->tourBookingServices;
        $tourBookingNotes = $tourBooking->tourBookingNotes;

        // Sử dụng VatInvoiceService để lấy thông tin thanh toán cho VAT
        $vatInvoiceService = app(\App\Services\VatInvoiceService::class);
        $paymentInfo = $vatInvoiceService->getTourPaymentStatusInfo($tourBooking);

        return view('admin.tour-bookings.show', compact(
            'tourBooking', 
            'validNextStatuses',
            'totalRoomsAmount',
            'totalServicesAmount',
            'totalAmountBeforeDiscount',
            'totalPaid',
            'totalDiscount', 
            'outstandingAmount',
            'actualOutstandingAmount', // Thêm actualOutstandingAmount
            'finalAmount',
            'tourBookingServices',
            'tourBookingNotes',
            'paymentInfo'
        ));
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
            'status' => 'required|in:pending,pending_payment,confirmed,checked_in,checked_out,completed,cancelled,no_show'
        ]);

        $tourBooking = TourBooking::findOrFail($id);
        $oldStatus = $tourBooking->status;
        $newStatus = $request->status;

        // Kiểm tra logic chuyển trạng thái
        if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể chuyển từ trạng thái "' . $this->getStatusText($oldStatus) . '" sang "' . $this->getStatusText($newStatus) . '"'
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Cập nhật trạng thái
            $tourBooking->update(['status' => $newStatus]);

            // Xử lý logic đặc biệt cho từng trạng thái
            $this->handleStatusChangeLogic($tourBooking, $oldStatus, $newStatus);

            // Gửi email xác nhận nếu trạng thái mới là 'confirmed'
            if ($newStatus === 'confirmed' && $oldStatus !== 'confirmed') {
                $this->sendTourBookingConfirmationEmail($tourBooking);
            }

            // Tạo thông báo cho user
            $this->createStatusChangeNotification($tourBooking, $oldStatus, $newStatus);
            // Kết thúc thông báo tour booking

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Trạng thái tour booking đã được cập nhật thành công!',
                'new_status' => $newStatus,
                'new_status_text' => $this->getStatusText($newStatus)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating tour booking status: ' . $e->getMessage());
            
            // Kiểm tra lỗi cụ thể
            if (str_contains($e->getMessage(), 'Data truncated for column \'status\'')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lỗi: Cột status trong database chưa được cập nhật để hỗ trợ trạng thái mới. Vui lòng chạy migration trước.'
                ], 500);
            }
            
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
            $totalAmount = $tourBooking->total_price; // Sử dụng total_price (giá cuối sau giảm giá)

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
            'pending' => ['pending_payment', 'confirmed', 'cancelled', 'no_show'],
            'pending_payment' => ['confirmed', 'cancelled', 'no_show'],
            'confirmed' => ['checked_in', 'cancelled', 'no_show'],
            'checked_in' => ['checked_out', 'cancelled'],
            'checked_out' => ['completed'],
            'completed' => [],
            'cancelled' => [],
            'no_show' => []
        ];

        return $statusFlow[$currentStatus] ?? [];
    }

    /**
     * Kiểm tra chuyển trạng thái có hợp lệ không
     */
    private function isValidStatusTransition($oldStatus, $newStatus): bool
    {
        $validNextStatuses = $this->getValidNextStatuses($oldStatus);
        return in_array($newStatus, $validNextStatuses);
    }

    /**
     * Lấy text hiển thị cho trạng thái
     */
    private function getStatusText($status): string
    {
        $statusMap = [
            'pending' => 'Chờ xác nhận',
            'pending_payment' => 'Chờ thanh toán',
            'confirmed' => 'Đã xác nhận',
            'checked_in' => 'Đã check-in',
            'checked_out' => 'Đã check-out',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'no_show' => 'Không đến'
        ];

        return $statusMap[$status] ?? $status;
    }

    /**
     * Xử lý logic đặc biệt khi thay đổi trạng thái
     */
    private function handleStatusChangeLogic(TourBooking $tourBooking, $oldStatus, $newStatus): void
    {
        // Logic cho từng trạng thái
        switch ($newStatus) {
            case 'pending_payment':
                // Chuyển sang chờ thanh toán - có thể gửi email nhắc nhở
                break;
                
            case 'confirmed':
                // Đã xác nhận - có thể gửi email xác nhận
                break;
                
            case 'checked_in':
                // Đã check-in - ghi lại thời gian check-in
                $tourBooking->update(['check_in_time' => now()]);
                break;
                
            case 'checked_out':
                // Đã check-out - ghi lại thời gian check-out
                $tourBooking->update(['check_out_time' => now()]);
                break;
                
            case 'completed':
                // Hoàn thành - có thể gửi email feedback
                break;
                
            case 'cancelled':
                // Đã hủy - có thể gửi email thông báo hủy
                break;
                
            case 'no_show':
                // Không đến - có thể gửi email thông báo
                break;
        }
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
            
            Mail::to($user->email)->send(new \App\Mail\TourBookingConfirmationMail($tourBooking));
            
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
            'pending_payment' => 'Tour booking của bạn đang chờ thanh toán.',
            'confirmed' => 'Tour booking của bạn đã được xác nhận!',
            'checked_in' => 'Bạn đã check-in thành công!',
            'checked_out' => 'Bạn đã check-out thành công!',
            'completed' => 'Tour booking của bạn đã hoàn thành!',
            'cancelled' => 'Tour booking của bạn đã bị hủy.',
            'no_show' => 'Tour booking của bạn đã bị đánh dấu là không đến.'
        ];

        if (isset($statusMessages[$newStatus])) {
            // Sử dụng bảng notifications đúng với cấu trúc hiện tại
            \App\Models\Notification::create([
                'user_id' => $tourBooking->user_id,
                'message' => $statusMessages[$newStatus],
                'is_read' => false
            ]);
            
            // Log thông báo thay đổi trạng thái
            Log::info('Tour booking status changed notification created', [
                'tour_booking_id' => $tourBooking->id,
                'user_id' => $tourBooking->user_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'message' => $statusMessages[$newStatus]
            ]);
        }
    }

    /**
     * Thu tiền bổ sung cho tour booking
     */
    public function collectPayment(Request $request, $id)
    {
        $tourBooking = TourBooking::with(['tourBookingRooms', 'tourBookingServices', 'payments'])->findOrFail($id);
        $amount = $request->amount;

        // Tính toán lại các giá trị để đảm bảo chính xác
        $totalRoomsAmount = $tourBooking->tourBookingRooms->sum('total_price');
        $totalServicesAmount = $tourBooking->tourBookingServices->sum('total_price');
        $totalAmountBeforeDiscount = $totalRoomsAmount + $totalServicesAmount;
        $totalDiscount = $tourBooking->promotion_discount ?? 0;
        $finalAmount = $totalDiscount > 0 ? $totalAmountBeforeDiscount - $totalDiscount : $totalAmountBeforeDiscount;
        $totalPaid = $tourBooking->payments->where('status', 'completed')->sum('amount');
        $outstandingAmount = $finalAmount - $totalPaid;

        // Kiểm tra số tiền
        if ($amount <= 0) {
            return back()->withErrors(['message' => 'Số tiền phải lớn hơn 0.']);
        }

        if ($amount > $outstandingAmount) {
            return back()->withErrors(['message' => 'Số tiền thu không được vượt quá số tiền còn thiếu (' . number_format($outstandingAmount, 0, ',', '.') . ' VNĐ).']);
        }

        try {
            DB::beginTransaction();

            // Tạo payment mới
            $payment = Payment::create([
                'tour_booking_id' => $tourBooking->id,
                'amount' => $amount,
                'method' => 'cash', // Mặc định là tiền mặt
                'status' => 'completed',
                'notes' => 'Thu tiền bổ sung bởi admin',
                'admin_id' => Auth::id(),
                'completed_at' => now(),
            ]);

            // Tự động xử lý các giao dịch chuyển khoản chờ xác nhận
            $this->processPendingPayments($tourBooking);

            // Tính lại tổng tiền đã thanh toán sau khi thu
            $newTotalPaid = $tourBooking->payments->where('status', 'completed')->sum('amount');

            // Cập nhật trạng thái thanh toán
            if ($newTotalPaid >= $finalAmount) {
                $tourBooking->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed' // Tự động chuyển sang trạng thái đã xác nhận
                ]);

                // Debug logging để kiểm tra tour booking update
                Log::info('Tour booking updated to confirmed', [
                    'tour_booking_id' => $tourBooking->id,
                    'new_status' => 'confirmed',
                    'new_payment_status' => 'paid',
                    'total_paid' => $newTotalPaid,
                    'final_amount' => $finalAmount
                ]);

                // Gửi email xác nhận
                $this->sendTourBookingConfirmationEmail($tourBooking);

                // Tạo thông báo
                $this->createStatusChangeNotification($tourBooking, $tourBooking->getOriginal('status'), 'confirmed');
            }

            DB::commit();

            return redirect()->back()->with('success', 'Đã thu tiền thành công: ' . number_format($amount, 0, ',', '.') . ' VNĐ và tự động xử lý các giao dịch chờ xác nhận.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Tour booking collect payment error: ' . $e->getMessage());
            return back()->withErrors(['message' => 'Có lỗi xảy ra khi thu tiền. Vui lòng thử lại.']);
        }
    }

    /**
     * Tự động xử lý các giao dịch chờ xác nhận khi đã có payment completed
     */
    private function processPendingPayments(TourBooking $tourBooking): void
    {
        // Kiểm tra xem có payment completed nào không
        $hasCompletedPayment = $tourBooking->payments->where('status', 'completed')->count() > 0;
        
        if ($hasCompletedPayment) {
            // Tự động xử lý các giao dịch chuyển khoản chờ xác nhận
            $pendingBankTransfers = Payment::where('tour_booking_id', $tourBooking->id)
                ->where('method', 'bank_transfer')
                ->where('status', 'pending')
                ->get();

            foreach ($pendingBankTransfers as $pendingPayment) {
                $pendingPayment->update([
                    'status' => 'completed',
                    'notes' => 'Tự động xác nhận khi admin thu tiền bổ sung',
                    'admin_id' => Auth::id(),
                    'completed_at' => now(),
                ]);
            }

            // Xóa các giao dịch pending quá 30 phút
            $this->cleanExpiredPendingPayments($tourBooking);
        }
    }

    /**
     * Xóa các giao dịch pending quá 30 phút
     */
    private function cleanExpiredPendingPayments(TourBooking $tourBooking): void
    {
        $thirtyMinutesAgo = now()->subMinutes(30);
        
        $expiredPayments = Payment::where('tour_booking_id', $tourBooking->id)
            ->where('status', 'pending')
            ->where('created_at', '<', $thirtyMinutesAgo)
            ->get();

        foreach ($expiredPayments as $expiredPayment) {
            $expiredPayment->delete();
        }

        if ($expiredPayments->count() > 0) {
            // Log đã được bỏ
        }
    }

    /**
     * Xác nhận chuyển khoản cho tour booking
     */
    public function confirmBankTransfer(Request $request, $id)
    {
        $tourBooking = TourBooking::with(['tourBookingRooms', 'tourBookingServices', 'payments'])->findOrFail($id);
        $paymentId = $request->payment_id;
        $transactionId = $request->transaction_id;

        // Tìm payment cần xác nhận
        $payment = Payment::where('id', $paymentId)
            ->where('tour_booking_id', $tourBooking->id) // Sửa: tour_booking_id thay vì booking_id
            ->where('method', 'bank_transfer')
            ->where('status', 'pending')
            ->first();

        if (!$payment) {
            return back()->withErrors(['message' => 'Không tìm thấy giao dịch chuyển khoản cần xác nhận.']);
        }

        try {
            DB::beginTransaction();

            // Cập nhật trạng thái payment
            $payment->update([
                'status' => 'completed',
                'notes' => 'Đã xác nhận chuyển khoản bởi admin: ' . Auth::user()->name,
                'admin_id' => Auth::id(),
                'completed_at' => now(),
            ]);

            // Refresh tour booking để lấy thông tin payment mới nhất
            $tourBooking->refresh();
            $tourBooking->load(['payments', 'tourBookingRooms', 'tourBookingServices']);

            // Tính toán lại các giá trị để đảm bảo chính xác
            $totalRoomsAmount = $tourBooking->tourBookingRooms->sum('total_price');
            $totalServicesAmount = $tourBooking->tourBookingServices->sum('total_price');
            $totalAmountBeforeDiscount = $totalRoomsAmount + $totalServicesAmount;
            $totalDiscount = $tourBooking->promotion_discount ?? 0;
            $finalAmount = $totalDiscount > 0 ? $totalAmountBeforeDiscount - $totalDiscount : $totalAmountBeforeDiscount;

            // Kiểm tra xem tour booking đã thanh toán đủ chưa
            $totalPaid = $tourBooking->payments->where('status', 'completed')->sum('amount');

            // Cập nhật trạng thái thanh toán của tour booking
            if ($totalPaid >= $finalAmount) {
                $tourBooking->update([
                    'payment_status' => 'paid',
                    'status' => 'confirmed' // Tự động chuyển sang trạng thái đã xác nhận
                ]);
            } else {
                $tourBooking->update([
                    'payment_status' => 'partial'
                ]);
            }

            if ($totalPaid >= $finalAmount) {
                // Gửi email xác nhận
                $this->sendTourBookingConfirmationEmail($tourBooking);

                // Tạo thông báo
                $this->createStatusChangeNotification($tourBooking, $tourBooking->getOriginal('status'), 'confirmed');

                DB::commit();

                return redirect()->back()->with('success', 'Đã xác nhận chuyển khoản thành công. Tour booking đã được xác nhận và gửi email thông báo cho khách hàng.');
            } else {
                DB::commit();

                return redirect()->back()->with('success', 'Đã xác nhận chuyển khoản thành công. Khách hàng còn thiếu ' . number_format($finalAmount - $totalPaid, 0, ',', '.') . ' VNĐ.');
            }

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['message' => 'Có lỗi xảy ra khi xác nhận chuyển khoản. Vui lòng thử lại.']);
        }
    }
}
