<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomChange;
use App\Models\BookingNote;
use App\Mail\RoomChangeSettlementMail;

class ChangeRoomCommand extends Command
{
    protected $signature = 'booking:change-room
        {booking : ID số hoặc mã booking (booking_id) cần đổi phòng}
        {--to-room= : ID phòng đích muốn chuyển tới. Nếu bỏ trống, hệ thống sẽ tự chọn phòng phù hợp}
        {--by= : ID admin thực hiện (bắt buộc vì room_changes.requested_by không cho phép null)}
        {--reason= : Lý do đổi phòng}
        {--old-status=available : Trạng thái phòng cũ sau khi đổi (ví dụ: available, dirty, repair)}
        {--new-status=booked : Trạng thái phòng mới sau khi đổi (thường là booked)}
        {--dry-run : Chỉ kiểm tra điều kiện, không ghi vào DB}';

    protected $description = 'Đổi phòng thủ công cho booking: cùng loại, tầng cao hơn, kiểm tra trống toàn bộ khoảng ở và không bị Tour giữ. Tự động ghi log room_changes với payment_status=not_required.';

    public function handle(): int
    {
        $bookingArg = (string)$this->argument('booking');
        $toRoomId = $this->option('to-room');
        $adminUserId = $this->option('by');
        $reason = $this->option('reason') ?? 'Khách yêu cầu lên tầng cao hơn - cùng loại';
        $isDryRun = (bool)$this->option('dry-run');
        $oldStatus = (string)$this->option('old-status');
        $newStatus = (string)$this->option('new-status');

        if (!$adminUserId) {
            $this->error('Thiếu --by=<admin_user_id>. Trường requested_by không được null.');
            return self::FAILURE;
        }

        // Tìm booking theo ID số (id) hoặc mã code (booking_id)
        if (ctype_digit($bookingArg)) {
            $booking = Booking::with(['room.roomType', 'user'])->find((int)$bookingArg);
        } else {
            $booking = Booking::with(['room.roomType', 'user'])->where('booking_id', $bookingArg)->first();
        }
        if (!$booking) {
            $this->error("Không tìm thấy booking với tham số: {$bookingArg}");
            return self::FAILURE;
        }

        if (in_array($booking->status, ['cancelled','completed'])) {
            $this->error('Booking đã cancelled/completed, không thể đổi phòng.');
            return self::FAILURE;
        }

        if (Carbon::parse($booking->check_out_date)->lte(now())) {
            $this->error('Booking đã qua hoặc trùng ngày check-out, không thể đổi phòng.');
            return self::FAILURE;
        }

        if (!$booking->room) {
            $this->error('Booking chưa gán phòng cụ thể (room_id=null).');
            return self::FAILURE;
        }

        $oldRoom = $booking->room;
        $roomTypeId = $oldRoom->room_type_id;
        $checkIn = $booking->check_in_date;
        $checkOut = $booking->check_out_date;

        $this->info("Booking #{$booking->id}: Phòng hiện tại {$oldRoom->room_number} (tầng {$oldRoom->floor}), loại {$roomTypeId}. Khoảng ở: {$checkIn} -> {$checkOut}");

        // Xác định phòng đích
        $targetRoom = null;
        if ($toRoomId) {
            $targetRoom = Room::find($toRoomId);
            if (!$targetRoom) {
                $this->error("Không tìm thấy phòng đích #{$toRoomId}");
                return self::FAILURE;
            }
            // Ràng buộc cùng loại, tầng cao hơn
            if ((int)$targetRoom->room_type_id !== (int)$roomTypeId) {
                $this->error('Phòng đích không cùng loại phòng.');
                return self::FAILURE;
            }
            if ((int)$targetRoom->floor <= (int)$oldRoom->floor) {
                $this->error('Phòng đích không ở tầng cao hơn.');
                return self::FAILURE;
            }
            if (!$targetRoom->isStrictlyAvailableForRange($checkIn, $checkOut)) {
                $this->error('Phòng đích không trống toàn bộ khoảng ở (có thể bị booking khác hoặc Tour giữ).');
                return self::FAILURE;
            }
        } else {
            // Tự chọn phòng: cùng loại, tầng cao hơn, ưu tiên tầng thấp nhất khả dụng trước
            $candidates = Room::where('room_type_id', $roomTypeId)
                ->where('floor', '>', $oldRoom->floor)
                ->orderBy('floor')
                ->orderBy('room_number')
                ->get();

            foreach ($candidates as $candidate) {
                if ($candidate->isStrictlyAvailableForRange($checkIn, $checkOut)) {
                    $targetRoom = $candidate;
                    break;
                }
            }

            if (!$targetRoom) {
                $this->error('Không tìm thấy phòng phù hợp: cùng loại, tầng cao hơn và trống toàn bộ khoảng ở.');
                return self::FAILURE;
            }
        }

        $this->info("Chọn phòng đích: {$targetRoom->room_number} (tầng {$targetRoom->floor}).");
        $this->line("Phòng cũ sẽ đặt trạng thái: {$oldStatus}; Phòng mới sẽ đặt trạng thái: {$newStatus}");

        if ($isDryRun) {
            $this->info('[Dry-run] Kiểm tra thành công. Không ghi vào DB.');
            return self::SUCCESS;
        }

        try {
            $createdRoomChange = null;
            DB::transaction(function () use ($booking, $oldRoom, $targetRoom, $adminUserId, $reason, $oldStatus, $newStatus, &$createdRoomChange) {
                // Ghi log room_changes: hoàn thành ngay, không cần thanh toán vì cùng loại => chênh lệch 0
                $rc = RoomChange::create([
                    'booking_id' => $booking->id,
                    'old_room_id' => $oldRoom->id,
                    'new_room_id' => $targetRoom->id,
                    'reason' => $reason,
                    'status' => 'completed',
                    'price_difference' => 0,
                    'requested_by' => $adminUserId,
                    'approved_by' => $adminUserId,
                    'admin_note' => 'Đổi phòng thủ công qua Artisan: cùng loại, tầng cao hơn, không chênh lệch.',
                    'customer_note' => null,
                    'approved_at' => now(),
                    'completed_at' => now(),
                    // Các cột payment_status/paid_* có thể đã được thêm bởi migrations mở rộng
                    'payment_status' => 'not_required',
                    'paid_at' => null,
                    'paid_by' => null,
                ]);
                $createdRoomChange = $rc;

                // Cập nhật booking sang phòng mới, không đổi giá vì cùng loại
                $booking->room_id = $targetRoom->id;
                $booking->save();

                // Cập nhật trạng thái phòng
                $oldRoom->update(['status' => $oldStatus]);
                $targetRoom->update(['status' => $newStatus]);

                Log::info('ChangeRoomCommand: Room changed successfully', [
                    'booking_id' => $booking->id,
                    'room_change_id' => $rc->id,
                    'from' => $oldRoom->id,
                    'to' => $targetRoom->id,
                ]);
            });

            // Tạo BookingNote nội bộ ghi nhận thao tác
            try {
                BookingNote::create([
                    'booking_id' => $booking->id,
                    'user_id' => (int)$adminUserId,
                    'content' => sprintf('Đổi phòng thủ công: %s (tầng %s) -> %s (tầng %s). Lý do: %s',
                        $oldRoom->room_number, $oldRoom->floor, $targetRoom->room_number, $targetRoom->floor, $reason
                    ),
                    'type' => 'staff',
                    'visibility' => 'internal',
                    'is_internal' => true,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Failed to create BookingNote after room change', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
            }

            // Gửi email thông báo cho khách
            try {
                if ($booking->user && $booking->user->email) {
                    Mail::to($booking->user->email)->send(new RoomChangeSettlementMail($booking, $createdRoomChange));
                }
            } catch (\Throwable $e) {
                Log::error('Failed to send RoomChangeSettlementMail', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
            }
        } catch (\Throwable $e) {
            Log::error('ChangeRoomCommand failed', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
            $this->error('Đổi phòng thất bại: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Đổi phòng thành công. Đã cập nhật booking và trạng thái phòng, ghi log room_changes (payment_status=not_required).');
        return self::SUCCESS;
    }
}
