<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourBooking;
use App\Models\TourBookingNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TourBookingNoteController extends Controller
{
    /**
     * Thêm ghi chú mới cho tour booking
     */
    public function store(Request $request, $tourBookingId)
    {
        $request->validate([
            'type' => 'required|string|in:customer,staff,admin',
            'content' => 'required|string|max:1000',
            'is_internal' => 'boolean',
        ]);

        try {
            $tourBooking = TourBooking::findOrFail($tourBookingId);
            
            $note = TourBookingNote::create([
                'tour_booking_id' => $tourBookingId,
                'user_id' => Auth::id(),
                'content' => $request->content,
                'type' => $request->type,
                'visibility' => $request->boolean('is_internal') ? 'internal' : 'public',
                'is_internal' => $request->boolean('is_internal'),
            ]);

            return redirect()->back()->with('success', 'Ghi chú đã được thêm thành công!');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Xóa ghi chú
     */
    public function destroy($tourBookingId, $noteId)
    {
        try {
            $note = TourBookingNote::where('tour_booking_id', $tourBookingId)
                ->where('id', $noteId)
                ->firstOrFail();

            $note->delete();

            return redirect()->back()->with('success', 'Ghi chú đã được xóa thành công!');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }
}
