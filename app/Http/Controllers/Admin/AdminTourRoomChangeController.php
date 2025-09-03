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


