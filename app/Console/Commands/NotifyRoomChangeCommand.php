<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Booking;
use App\Models\RoomChange;
use App\Models\BookingNote;
use App\Mail\RoomChangeSettlementMail;

class NotifyRoomChangeCommand extends Command
{
    protected $signature = 'booking:notify-room-change
        {booking : ID số hoặc mã booking (booking_id)}
        {--by= : ID admin tạo ghi chú}
        {--note-only : Chỉ tạo BookingNote, không gửi email}';

    protected $description = 'Gửi email thông báo đổi phòng và tạo BookingNote nội bộ cho booking đã đổi phòng.';

    public function handle(): int
    {
        $bookingArg = (string)$this->argument('booking');
        $adminUserId = $this->option('by');
        $noteOnly = (bool)$this->option('note-only');

        // Resolve booking by id or code
        $booking = ctype_digit($bookingArg)
            ? Booking::with(['user','room'])->find((int)$bookingArg)
            : Booking::with(['user','room'])->where('booking_id', $bookingArg)->first();

        if (!$booking) {
            $this->error("Không tìm thấy booking: {$bookingArg}");
            return self::FAILURE;
        }

        // Lấy room change gần nhất
        $roomChange = RoomChange::where('booking_id', $booking->id)
            ->orderByDesc('id')
            ->first();
        if (!$roomChange) {
            $this->error('Booking chưa có lịch sử đổi phòng.');
            return self::FAILURE;
        }

        // Tạo ghi chú
        try {
            if ($adminUserId) {
                BookingNote::create([
                    'booking_id' => $booking->id,
                    'user_id' => (int)$adminUserId,
                    'content' => sprintf('Xác nhận thông báo đổi phòng cho khách. Phòng hiện tại: %s (tầng %s).',
                        $booking->room?->room_number ?? 'N/A', $booking->room?->floor ?? 'N/A'
                    ),
                    'type' => 'staff',
                    'visibility' => 'internal',
                    'is_internal' => true,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('NotifyRoomChangeCommand: create note failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }

        if (!$noteOnly) {
            try {
                if ($booking->user && $booking->user->email) {
                    Mail::to($booking->user->email)->send(new RoomChangeSettlementMail($booking, $roomChange));
                }
            } catch (\Throwable $e) {
                Log::error('NotifyRoomChangeCommand: send mail failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
                $this->warn('Gửi email thất bại (xem log).');
            }
        }

        $this->info('Đã tạo ghi chú và gửi email (nếu không tắt bằng --note-only).');
        return self::SUCCESS;
    }
}
