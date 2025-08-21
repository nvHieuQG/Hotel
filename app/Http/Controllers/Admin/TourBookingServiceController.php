<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourBooking;
use App\Models\TourBookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TourBookingServiceController extends Controller
{
    /**
     * Thêm dịch vụ mới cho tour booking
     */
    public function store(Request $request, $tourBookingId)
    {
        $request->validate([
            'service_type' => 'required|string|in:transport,guide,meal,entertainment,other',
            'service_name' => 'required|string|max:255',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $tourBooking = TourBooking::findOrFail($tourBookingId);
            
            $totalPrice = $request->unit_price * $request->quantity;

            $service = TourBookingService::create([
                'tour_booking_id' => $tourBookingId,
                'service_type' => $request->service_type,
                'service_name' => $request->service_name,
                'unit_price' => $request->unit_price,
                'quantity' => $request->quantity,
                'total_price' => $totalPrice,
                'notes' => $request->notes,
                'status' => 'active'
            ]);

            // Không cập nhật total_price của tour booking, để tính toán tự động
            // thông qua accessor total_amount_before_discount

            DB::commit();

            return redirect()->back()->with('success', 'Dịch vụ đã được thêm thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    /**
     * Xóa dịch vụ
     */
    public function destroy($tourBookingId, $serviceId)
    {
        try {
            DB::beginTransaction();

            $service = TourBookingService::where('tour_booking_id', $tourBookingId)
                ->where('id', $serviceId)
                ->firstOrFail();

            // Không cập nhật total_price của tour booking, để tính toán tự động
            // thông qua accessor total_amount_before_discount

            $service->delete();

            DB::commit();

            return redirect()->back()->with('success', 'Dịch vụ đã được xóa thành công!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }
}
