<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Booking;
use Illuminate\Support\Collection;
use App\Models\BookingNote;
use App\Models\RoomTypeReview;

class AdminNotificationService
{
    /**
     * Tạo thông báo đặt phòng mới
     */
    public function notifyBookingCreated(Booking $booking): AdminNotification
    {
        return AdminNotification::createNotification(
            'booking_created',
            'Đặt phòng mới',
            "Khách hàng {$booking->user->name} vừa đặt phòng {$booking->room->name}",
            [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'room_id' => $booking->room_id,
                'booking_code' => $booking->booking_id
            ],
            'high',
            'fas fa-calendar-plus',
            'primary'
        );
    }

    /**
     * Tạo thông báo thay đổi trạng thái booking
     */
    public function notifyBookingStatusChanged(Booking $booking, string $oldStatus, string $newStatus): AdminNotification
    {
        $statusText = match($newStatus) {
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'checked_in' => 'Đã nhận phòng',
            'checked_out' => 'Đã trả phòng',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'no_show' => 'Khách không đến',
            default => $newStatus
        };

        $priority = in_array($newStatus, ['cancelled', 'no_show']) ? 'high' : 'normal';
        $color = in_array($newStatus, ['cancelled', 'no_show']) ? 'danger' : 'info';

        return AdminNotification::createNotification(
            'booking_status_changed',
            'Thay đổi trạng thái đặt phòng',
            "Đặt phòng #{$booking->booking_id} đã chuyển sang trạng thái: {$statusText}",
            [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'booking_code' => $booking->booking_id
            ],
            $priority,
            'fas fa-exchange-alt',
            $color
        );
    }

    /**
     * Tạo thông báo hủy đặt phòng
     */
    public function notifyBookingCancelled(Booking $booking, string $reason = ''): AdminNotification
    {
        $message = "Đặt phòng #{$booking->booking_id} đã bị hủy";
        if ($reason) {
            $message .= " - Lý do: {$reason}";
        }

        return AdminNotification::createNotification(
            'booking_cancelled',
            'Đặt phòng bị hủy',
            $message,
            [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'reason' => $reason,
                'booking_code' => $booking->booking_id
            ],
            'high',
            'fas fa-times-circle',
            'danger'
        );
    }

    /**
     * Tạo thông báo thanh toán
     */
    public function notifyPaymentReceived(Booking $booking, float $amount): AdminNotification
    {
        return AdminNotification::createNotification(
            'payment_received',
            'Thanh toán thành công',
            "Đã nhận thanh toán {$amount} VND cho đặt phòng #{$booking->booking_id}",
            [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'amount' => $amount,
                'booking_code' => $booking->booking_id
            ],
            'normal',
            'fas fa-credit-card',
            'success'
        );
    }

    /**
     * Tạo thông báo thanh toán thất bại
     */
    public function notifyPaymentFailed(Booking $booking, string $error = ''): AdminNotification
    {
        $message = "Thanh toán thất bại cho đặt phòng #{$booking->booking_id}";
        if ($error) {
            $message .= " - Lỗi: {$error}";
        }

        return AdminNotification::createNotification(
            'payment_failed',
            'Thanh toán thất bại',
            $message,
            [
                'booking_id' => $booking->id,
                'user_id' => $booking->user_id,
                'error' => $error,
                'booking_code' => $booking->booking_id
            ],
            'high',
            'fas fa-exclamation-triangle',
            'danger'
        );
    }

    /**
     * Tạo thông báo ticket hỗ trợ mới
     */
    public function notifySupportTicket($ticket): AdminNotification
    {
        return AdminNotification::createNotification(
            'support_ticket',
            'Ticket hỗ trợ mới',
            "Ticket #{$ticket->id} từ {$ticket->user->name}: {$ticket->subject}",
            [
                'ticket_id' => $ticket->id,
                'user_id' => $ticket->user_id,
                'subject' => $ticket->subject
            ],
            'normal',
            'fas fa-headset',
            'warning'
        );
    }

    /**
     * Tạo thông báo đánh giá mới
     */
    public function notifyReviewSubmitted($review): AdminNotification
    {
        return AdminNotification::createNotification(
            'review_submitted',
            'Đánh giá mới',
            "Đánh giá {$review->rating}/5 sao cho {$review->roomType->name}",
            [
                'review_id' => $review->id,
                'user_id' => $review->user_id,
                'room_type_id' => $review->room_type_id,
                'rating' => $review->rating
            ],
            'low',
            'fas fa-star',
            'info'
        );
    }

    /**
     * Tạo thông báo ghi chú mới
     */
    public function notifyBookingNoteCreated(BookingNote $bookingNote): AdminNotification
    {
        $typeText = match($bookingNote->type) {
            'customer' => 'Khách hàng',
            'staff' => 'Nhân viên',
            'admin' => 'Quản lý',
            default => 'Không xác định'
        };

        $priority = $bookingNote->is_internal ? 'high' : 'normal';
        $color = $bookingNote->is_internal ? 'warning' : 'info';

        return AdminNotification::createNotification(
            'booking_note_created',
            'Ghi chú mới',
            "Ghi chú mới từ {$typeText} cho đặt phòng #{$bookingNote->booking->booking_id}",
            [
                'note_id' => $bookingNote->id,
                'booking_id' => $bookingNote->booking_id,
                'user_id' => $bookingNote->user_id,
                'type' => $bookingNote->type,
                'visibility' => $bookingNote->visibility,
                'is_internal' => $bookingNote->is_internal,
                'booking_code' => $bookingNote->booking->booking_id
            ],
            $priority,
            'fas fa-sticky-note',
            $color
        );
    }

    /**
     * Tạo thông báo ghi chú được cập nhật
     */
    public function notifyBookingNoteUpdated(BookingNote $bookingNote): AdminNotification
    {
        return AdminNotification::createNotification(
            'booking_note_updated',
            'Ghi chú được cập nhật',
            "Ghi chú đã được cập nhật cho đặt phòng #{$bookingNote->booking->booking_id}",
            [
                'note_id' => $bookingNote->id,
                'booking_id' => $bookingNote->booking_id,
                'user_id' => $bookingNote->user_id,
                'type' => $bookingNote->type,
                'booking_code' => $bookingNote->booking->booking_id
            ],
            'low',
            'fas fa-edit',
            'info'
        );
    }

    /**
     * Tạo thông báo ghi chú bị xóa
     */
    public function notifyBookingNoteDeleted(BookingNote $bookingNote): AdminNotification
    {
        return AdminNotification::createNotification(
            'booking_note_deleted',
            'Ghi chú bị xóa',
            "Ghi chú đã bị xóa khỏi đặt phòng #{$bookingNote->booking->booking_id}",
            [
                'booking_id' => $bookingNote->booking_id,
                'user_id' => $bookingNote->user_id,
                'type' => $bookingNote->type,
                'booking_code' => $bookingNote->booking->booking_id
            ],
            'normal',
            'fas fa-trash',
            'warning'
        );
    }

    /**
     * Tạo thông báo ghi chú được khôi phục
     */
    public function notifyBookingNoteRestored(BookingNote $bookingNote): AdminNotification
    {
        return AdminNotification::createNotification(
            'booking_note_restored',
            'Ghi chú được khôi phục',
            "Ghi chú đã được khôi phục cho đặt phòng #{$bookingNote->booking->booking_id}",
            [
                'note_id' => $bookingNote->id,
                'booking_id' => $bookingNote->booking_id,
                'user_id' => $bookingNote->user_id,
                'type' => $bookingNote->type,
                'booking_code' => $bookingNote->booking->booking_id
            ],
            'low',
            'fas fa-undo',
            'success'
        );
    }

    /**
     * Tạo thông báo ghi chú bị xóa vĩnh viễn
     */
    public function notifyBookingNoteForceDeleted(BookingNote $bookingNote): AdminNotification
    {
        return AdminNotification::createNotification(
            'booking_note_force_deleted',
            'Ghi chú bị xóa vĩnh viễn',
            "Ghi chú đã bị xóa vĩnh viễn khỏi đặt phòng #{$bookingNote->booking->booking_id}",
            [
                'booking_id' => $bookingNote->booking_id,
                'user_id' => $bookingNote->user_id,
                'type' => $bookingNote->type,
                'booking_code' => $bookingNote->booking->booking_id
            ],
            'high',
            'fas fa-trash-alt',
            'danger'
        );
    }

    /**
     * Tạo thông báo đánh giá mới
     */
    public function notifyRoomTypeReviewCreated($review): AdminNotification
    {
        return AdminNotification::createNotification(
            'room_type_review_created',
            'Đánh giá phòng mới',
            "Đánh giá {$review->rating}/5 sao cho {$review->roomType->name} từ {$review->user->name}",
            [
                'review_id' => $review->id,
                'user_id' => $review->user_id,
                'room_type_id' => $review->room_type_id,
                'rating' => $review->rating,
                'room_type_name' => $review->roomType->name
            ],
            'normal',
            'fas fa-star',
            'info'
        );
    }

    /**
     * Tạo thông báo đánh giá được cập nhật
     */
    public function notifyRoomTypeReviewUpdated($review): AdminNotification
    {
        return AdminNotification::createNotification(
            'room_type_review_updated',
            'Đánh giá phòng được cập nhật',
            "Đánh giá cho {$review->roomType->name} đã được cập nhật thành {$review->rating}/5 sao",
            [
                'review_id' => $review->id,
                'user_id' => $review->user_id,
                'room_type_id' => $review->room_type_id,
                'rating' => $review->rating,
                'room_type_name' => $review->roomType->name
            ],
            'low',
            'fas fa-edit',
            'info'
        );
    }

    /**
     * Tạo thông báo đánh giá bị xóa
     */
    public function notifyRoomTypeReviewDeleted($review): AdminNotification
    {
        return AdminNotification::createNotification(
            'room_type_review_deleted',
            'Đánh giá phòng bị xóa',
            "Đánh giá cho {$review->roomType->name} đã bị xóa",
            [
                'user_id' => $review->user_id,
                'room_type_id' => $review->room_type_id,
                'rating' => $review->rating,
                'room_type_name' => $review->roomType->name
            ],
            'normal',
            'fas fa-trash',
            'warning'
        );
    }

    /**
     * Lấy danh sách thông báo chưa đọc
     */
    public function getUnreadNotifications(int $limit = 10): Collection
    {
        return AdminNotification::unread()
            ->orderBy('priority', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy số lượng thông báo chưa đọc
     */
    public function getUnreadCount(): int
    {
        return AdminNotification::unread()->count();
    }

    /**
     * Lấy số lượng thông báo theo độ ưu tiên
     */
    public function getUnreadCountByPriority(): array
    {
        return [
            'urgent' => AdminNotification::unread()->ofPriority('urgent')->count(),
            'high' => AdminNotification::unread()->ofPriority('high')->count(),
            'normal' => AdminNotification::unread()->ofPriority('normal')->count(),
            'low' => AdminNotification::unread()->ofPriority('low')->count(),
        ];
    }

    /**
     * Đánh dấu thông báo đã đọc
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = AdminNotification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        return false;
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     */
    public function markAllAsRead(): int
    {
        return AdminNotification::unread()->update([
            'is_read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Xóa thông báo cũ (quá 30 ngày)
     */
    public function deleteOldNotifications(): int
    {
        return AdminNotification::where('created_at', '<', now()->subDays(30))->delete();
    }

    /**
     * Lấy thông báo theo loại
     */
    public function getNotificationsByType(string $type, int $limit = 10): Collection
    {
        return AdminNotification::ofType($type)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
} 