<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\RoomChangeServiceInterface;
use App\Models\RoomChange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminRoomChangeController extends Controller
{
    public function __construct(
        private RoomChangeServiceInterface $roomChangeService
    ) {}

    /**
     * Hiển thị danh sách yêu cầu đổi phòng
     */
    public function index(Request $request)
    {
        $filters = [
            'status' => $request->get('status'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        $roomChanges = $this->roomChangeService->getRoomChangesForAdmin($filters);

        return view('admin.room-changes.index', compact('roomChanges', 'filters'));
    }

    /**
     * Hiển thị chi tiết yêu cầu đổi phòng
     */
    public function show(RoomChange $roomChange)
    {
        $roomChange->load(['booking', 'oldRoom', 'newRoom', 'requestedBy', 'approvedBy']);

        return view('admin.room-changes.show', compact('roomChange'));
    }

    /**
     * Duyệt yêu cầu đổi phòng
     */
    public function approve(Request $request, RoomChange $roomChange)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:1000',
        ]);

        try {
            Log::info('Attempting to approve room change', [
                'room_change_id' => $roomChange->id,
                'current_status' => $roomChange->status,
                'admin_note' => $request->admin_note
            ]);

            $success = $this->roomChangeService->approveRoomChange($roomChange->id, [
                'admin_note' => $request->admin_note,
            ]);

            Log::info('Room change approval result', [
                'room_change_id' => $roomChange->id,
                'success' => $success
            ]);

            if ($success) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Đã duyệt yêu cầu đổi phòng thành công.'
                    ]);
                }
                return redirect()->route('admin.room-changes.index')
                    ->with('success', 'Đã duyệt yêu cầu đổi phòng thành công.');
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể duyệt yêu cầu đổi phòng.'
                    ], 400);
                }
                return redirect()->back()
                    ->with('error', 'Không thể duyệt yêu cầu đổi phòng.');
            }
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Từ chối yêu cầu đổi phòng
     */
    public function reject(Request $request, RoomChange $roomChange)
    {
        $request->validate([
            'admin_note' => 'required|string|max:1000',
        ]);

        try {
            $success = $this->roomChangeService->rejectRoomChange($roomChange->id, [
                'admin_note' => $request->admin_note,
            ]);

            if ($success) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Đã từ chối yêu cầu đổi phòng.'
                    ]);
                }
                return redirect()->route('admin.room-changes.index')
                    ->with('success', 'Đã từ chối yêu cầu đổi phòng.');
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể từ chối yêu cầu đổi phòng.'
                    ], 400);
                }
                return redirect()->back()
                    ->with('error', 'Không thể từ chối yêu cầu đổi phòng.');
            }
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Hoàn thành đổi phòng
     */
    public function complete(Request $request, RoomChange $roomChange)
    {
        try {
            $success = $this->roomChangeService->completeRoomChange($roomChange->id);

            if ($success) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Đã hoàn thành đổi phòng thành công.'
                    ]);
                }
                return redirect()->route('admin.room-changes.index')
                    ->with('success', 'Đã hoàn thành đổi phòng thành công.');
            } else {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể hoàn thành đổi phòng.'
                    ], 400);
                }
                return redirect()->back()
                    ->with('error', 'Không thể hoàn thành đổi phòng.');
            }
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Đánh dấu đã thanh toán tại quầy lễ tân
     */
    public function markAsPaid(Request $request, RoomChange $roomChange)
    {
        try {
            // Kiểm tra xem room change có cần thanh toán không
            if (!$roomChange->requiresPayment()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Yêu cầu đổi phòng này không cần thanh toán.'
                ], 400);
            }

            // Kiểm tra trạng thái hiện tại
            if ($roomChange->isPaidAtReception()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Đã được đánh dấu là đã thanh toán rồi.'
                ], 400);
            }

            DB::transaction(function () use ($roomChange) {
                // Only update payment status on RoomChange. Do NOT touch booking amounts.
                $roomChange->update([
                    'payment_status' => 'paid_at_reception',
                    'paid_at' => now(),
                    'paid_by' => Auth::id(),
                ]);
            });

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã đánh dấu thanh toán thành công.'
                ]);
            }

            return redirect()->route('admin.room-changes.index')
                ->with('success', 'Đã đánh dấu thanh toán thành công.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * Xác nhận đã hoàn tiền tại quầy lễ tân (áp dụng khi chênh lệch âm)
     */
    public function markAsRefunded(Request $request, RoomChange $roomChange)
    {
        try {
            // Chỉ cho phép hoàn tiền nếu chênh lệch âm
            if ($roomChange->price_difference >= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Yêu cầu đổi phòng này không có khoản hoàn tiền.'
                ], 400);
            }

            // Phải đang ở trạng thái chờ hoàn tiền
            if (!$roomChange->isRefundPending()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Yêu cầu này không ở trạng thái chờ hoàn tiền.'
                ], 400);
            }

            // Không xử lý lại nếu đã hoàn
            if ($roomChange->isRefunded()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Yêu cầu này đã được xác nhận hoàn tiền trước đó.'
                ], 400);
            }

            DB::transaction(function () use ($roomChange) {
                // Chỉ cập nhật trạng thái hoàn tiền, không đổi số tiền booking
                $roomChange->update([
                    'payment_status' => 'refunded',
                    'paid_at' => now(), // tái sử dụng cột thời gian thanh toán cho mốc hoàn tiền
                    'paid_by' => Auth::id(),
                ]);
            });

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã xác nhận hoàn tiền thành công.'
                ]);
            }

            return redirect()->route('admin.room-changes.index')
                ->with('success', 'Đã xác nhận hoàn tiền thành công.');

        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    /**
     * API để lấy thống kê yêu cầu đổi phòng
     */
    public function statistics()
    {
        $pendingCount = $this->roomChangeService->getRoomChangesForAdmin(['status' => 'pending'])->count();
        $approvedCount = $this->roomChangeService->getRoomChangesForAdmin(['status' => 'approved'])->count();
        $rejectedCount = $this->roomChangeService->getRoomChangesForAdmin(['status' => 'rejected'])->count();
        $completedCount = $this->roomChangeService->getRoomChangesForAdmin(['status' => 'completed'])->count();

        return response()->json([
            'pending' => $pendingCount,
            'approved' => $approvedCount,
            'rejected' => $rejectedCount,
            'completed' => $completedCount,
            'total' => $pendingCount + $approvedCount + $rejectedCount + $completedCount,
        ]);
    }

    /**
     * API để cập nhật trạng thái yêu cầu đổi phòng (AJAX)
     */
    public function updateStatus(Request $request, RoomChange $roomChange)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected,completed',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        try {
            $data = [];
            if ($request->admin_note) {
                $data['admin_note'] = $request->admin_note;
            }

            $success = false;
            switch ($request->status) {
                case 'approved':
                    $success = $this->roomChangeService->approveRoomChange($roomChange->id, $data);
                    break;
                case 'rejected':
                    $success = $this->roomChangeService->rejectRoomChange($roomChange->id, $data);
                    break;
                case 'completed':
                    $success = $this->roomChangeService->completeRoomChange($roomChange->id);
                    break;
            }

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật trạng thái thành công.',
                    'status' => $request->status,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể cập nhật trạng thái.',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
            ], 500);
        }
    }
}
