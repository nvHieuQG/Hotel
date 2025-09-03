<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\Services\TourBookingServiceInterface;
use App\Interfaces\Repositories\TourRoomChangeRepositoryInterface;

class AdminTourRoomChangeController extends Controller
{
    public function __construct(
        private TourBookingServiceInterface $tourBookingService,
        private TourRoomChangeRepositoryInterface $tourRoomChangeRepository
    ) {}

    public function index($tourBookingId)
    {
        $changes = $this->tourRoomChangeRepository->getByTourBookingId((int)$tourBookingId);
        $tourBooking = \App\Models\TourBooking::findOrFail($tourBookingId);
        return view('admin.tour-bookings.room-changes.index', compact('changes', 'tourBookingId', 'tourBooking'));
    }

    public function create($tourBookingId)
    {
        $tourBooking = \App\Models\TourBooking::with(['tourBookingRooms.roomType', 'tourBookingRooms'])->findOrFail($tourBookingId);
        
        // Lấy danh sách phòng đã gán cho tour
        $assignedRooms = [];
        foreach ($tourBooking->tourBookingRooms as $tbr) {
            if (!empty($tbr->assigned_room_ids)) {
                foreach ($tbr->assigned_room_ids as $roomId) {
                    $room = \App\Models\Room::with('roomType')->find($roomId);
                    if ($room) {
                        $assignedRooms[] = $room;
                    }
                }
            }
        }
        
        // Lấy tất cả phòng trống trong khoảng thời gian tour
        $availableRooms = \App\Models\Room::with('roomType')
            ->where('status', 'available')
            ->whereDoesntHave('bookings', function($query) use ($tourBooking) {
                $query->where('check_in_date', '<', $tourBooking->check_out_date)
                      ->where('check_out_date', '>', $tourBooking->check_in_date)
                      ->where('status', '!=', 'cancelled');
            })
            ->whereDoesntHave('tourHolds', function($query) use ($tourBooking) {
                $query->where('date', '>=', $tourBooking->check_in_date)
                      ->where('date', '<', $tourBooking->check_out_date);
            })
            ->get();
        
        return view('admin.tour-bookings.room-changes.create', compact('tourBooking', 'assignedRooms', 'availableRooms'));
    }

    public function store(Request $request, $tourBookingId)
    {
        $request->validate([
            'from_room_id' => 'required|exists:rooms,id',
            'to_room_id' => 'required|exists:rooms,id|different:from_room_id',
            'reason' => 'required|string|max:500',
            'customer_note' => 'nullable|string|max:1000',
        ]);

        try {
            $data = [
                'tour_booking_id' => $tourBookingId,
                'from_room_id' => $request->from_room_id,
                'to_room_id' => $request->to_room_id,
                'reason' => $request->reason,
                'customer_note' => $request->customer_note,
            ];

            $tourRoomChange = $this->tourBookingService->createTourRoomChange($data);
            
            return redirect()->route('staff.admin.tour-bookings.room-changes.index', $tourBookingId)
                ->with('success', 'Đã tạo yêu cầu đổi phòng thành công.');
                
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Không thể tạo yêu cầu đổi phòng: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, $id)
    {
        $request->validate(['admin_note' => 'nullable|string|max:1000']);
        $ok = $this->tourBookingService->approveTourRoomChange((int)$id, ['admin_note' => $request->admin_note]);
        if ($request->ajax()) {
            return response()->json(['success' => $ok, 'message' => $ok ? 'Đã duyệt đổi phòng tour.' : 'Không thể duyệt yêu cầu.'], $ok?200:400);
        }
        return back()->with($ok? 'success':'error', $ok? 'Đã duyệt đổi phòng tour.' : 'Không thể duyệt yêu cầu.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate(['admin_note' => 'nullable|string|max:1000']);
        $ok = $this->tourBookingService->rejectTourRoomChange((int)$id, ['admin_note' => $request->admin_note]);
        if ($request->ajax()) {
            return response()->json(['success' => $ok, 'message' => $ok ? 'Đã từ chối yêu cầu đổi phòng tour.' : 'Không thể từ chối yêu cầu.'], $ok?200:400);
        }
        return back()->with($ok? 'success':'error', $ok? 'Đã từ chối yêu cầu đổi phòng tour.' : 'Không thể từ chối yêu cầu.');
    }

    public function complete(Request $request, $id)
    {
        $ok = $this->tourBookingService->completeTourRoomChange((int)$id);
        if ($request->ajax()) {
            return response()->json(['success' => $ok, 'message' => $ok ? 'Đã hoàn tất đổi phòng tour.' : 'Không thể hoàn tất yêu cầu.'], $ok?200:400);
        }
        return back()->with($ok? 'success':'error', $ok? 'Đã hoàn tất đổi phòng tour.' : 'Không thể hoàn tất yêu cầu.');
    }
}


