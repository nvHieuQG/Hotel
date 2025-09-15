<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'room_type_id',
        'floor',
        'room_number',
        'status',
        'price',
        'capacity',
    ];

    /**
     * Lấy loại phòng
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Lấy tất cả booking của phòng
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }




    
    /**
     * Lấy hình ảnh của phòng
     */
    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }

    /**
     * Lấy ảnh chính của phòng
     */
    public function primaryImage()
    {
        return $this->hasOne(RoomImage::class)->where('is_primary', true);
    }

    /**
     * Lấy ảnh đầu tiên của phòng (fallback)
     */
    public function firstImage()
    {
        return $this->hasOne(RoomImage::class)->orderBy('is_primary', 'desc')->orderBy('id', 'asc');
    }
    
    // /**
    //  * Lấy dịch vụ của phòng
    //  */
    // public function services()
    // {
    //     return $this->belongsToMany(Service::class, 'room_services');
    // }
    
    /**
     * Accessor để lấy tên phòng (kết hợp từ loại phòng và số phòng)
     */
    public function getNameAttribute()
    {
        return $this->roomType->name . ' - ' . $this->room_number;
    }
    
    /**
     * Accessor để lấy giá phòng từ loại phòng
     */
    public function getPriceAttribute($value)
    {
        return $value ?? $this->roomType->price;
    }
    
    /**
     * Accessor để lấy mô tả phòng từ loại phòng
     */
    public function getDescriptionAttribute()
    {
        return $this->roomType->description;
    }
    
    /**
     * Accessor để lấy sức chứa từ loại phòng
     */
    public function getCapacityAttribute($value)
    {
        return $value ?? $this->roomType->capacity;
    }

    
    /**
     * Accessor để lấy tên phòng đầy đủ (Tầng + Số phòng)
     */
    public function getFullNameAttribute()
    {
        return 'Tầng ' . $this->floor . ' - Phòng ' . $this->room_number;
    }

    /**
     * Accessor để lấy tên phòng ngắn gọn
     */
    public function getShortNameAttribute()
    {
        return $this->floor . $this->room_number;
    }

    /**
     * Scope để lọc theo tầng
     */
    public function scopeByFloor($query, $floor)
    {
        return $query->where('floor', $floor);
    }

    /**
     * Scope để lọc theo loại phòng
     */
    public function scopeByType($query, $roomTypeId)
    {
        return $query->where('room_type_id', $roomTypeId);
    }

    /**
     * Scope để lọc theo trạng thái
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Lấy rating trung bình của phòng (từ loại phòng)
     */
    public function getAverageRatingAttribute()
    {
        return $this->roomType->average_rating;
    }

    /**
     * Lấy số lượng reviews của phòng (từ loại phòng)
     */
    public function getReviewsCountAttribute()
    {
        return $this->roomType->reviews_count;
    }

    /**
     * Lấy số sao hiển thị (1-5)
     */
    public function getStarsAttribute()
    {
        return round($this->average_rating);
    }

    /**
     * Lấy phần trăm rating (cho hiển thị progress bar)
     */
    public function getRatingPercentageAttribute()
    {
        return ($this->average_rating / 5) * 100;
    }
    public function getStatusForDisplayAttribute()
    {
        // Hiển thị trạng thái theo ngày hiện tại, có tính đến Tour Booking holds
        return $this->getStatusForDate(now());
    }

    public function getStatusForDate($date)
    {
        $day = \Carbon\Carbon::parse($date)->toDateString();
        
        // 0) Xét hold từ Tour Booking theo room_type
        $holdStatus = $this->getTourHoldStatusForDate($day);
        if ($holdStatus) {
            return $holdStatus;
        }

        // 1) Regular confirmed bookings overlap
        $confirmed = $this->bookings
            ->where('status', 'confirmed')
            ->filter(function($booking) use ($day) {
                $inDate = \Carbon\Carbon::parse($booking->check_in_date)->toDateString();
                $outDate = \Carbon\Carbon::parse($booking->check_out_date)->toDateString();
                return $inDate <= $day && $outDate > $day; // inclusive start, exclusive end
            })
            ->first();

        if ($confirmed) {
            return 'booked';
        }

        // 2) Regular pending bookings overlap (valid <= 30 minutes)
        $pending = $this->bookings
            ->whereIn('status', ['pending', 'pending_payment'])
            ->filter(function($booking) use ($day) {
                $inDate = \Carbon\Carbon::parse($booking->check_in_date)->toDateString();
                $outDate = \Carbon\Carbon::parse($booking->check_out_date)->toDateString();
                return $inDate <= $day && $outDate > $day; // inclusive start, exclusive end
            })
            ->sortByDesc('created_at')
            ->first();

        if ($pending) {
            if ($pending->created_at->diffInMinutes(now()) > 30) {
                return 'available';
            }
            return 'pending';
        }

        // 3) Fallback: nếu không bị giữ bởi booking/tour trong ngày
        // Chỉ tôn trọng trạng thái 'repair' (bảo trì). Các trạng thái 'booked'/'pending'
        // ở cột rooms.status KHÔNG được dùng để đánh dấu kín lịch ngoài phạm vi ngày ở thực tế.
        // Như vậy, nếu hôm nay là 14 và booking là 17-20 thì ngày 14 vẫn hiển thị 'available'.
        if ($this->status === 'repair') {
            return 'repair';
        }
        return 'available';
    }

    /**
     * Xác định trạng thái giữ chỗ theo Tour Booking theo day cho room_type hiện tại
     */
    private function getTourHoldStatusForDate(string $day): ?string
    {
        $allocation = $this->allocateTourHoldsForDay($day);
        $mapped = $allocation['room_to_status'][$this->id] ?? null;
        return $mapped;
    }

    /**
     * Cấp phát giữ chỗ Tour Booking theo phòng (lọc trừ phòng đã bị booking thường giữ),
     * ưu tiên: confirmed trước, sau đó pending (<=30 phút); trong mỗi nhóm sắp xếp theo:
     *  - số đêm giảm dần, check_in tăng dần, created_at tăng dần
     * Trả về: [ 'room_to_status' => [roomId => 'booked'|'pending'], 'room_to_tb' => [roomId => tour_booking_id] ]
     */
    private function allocateTourHoldsForDay(string $day): array
    {
        // 1) Danh sách phòng trong room_type theo id tăng dần (ổn định)
        $orderedRoomIds = self::where('room_type_id', $this->room_type_id)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        // 2) Loại trừ phòng đã bị booking thường giữ hôm đó
        $regularHeldRoomIds = $this->getRegularHeldRoomIdsForDay($day);
        $availableIds = array_values(array_diff($orderedRoomIds, $regularHeldRoomIds));
        if (empty($availableIds)) {
            return ['room_to_status' => [], 'room_to_tb' => []];
        }

        // 3) Lấy các dòng tour_booking_rooms trùng ngày cho room_type này
        $rows = \App\Models\TourBookingRoom::with(['tourBooking' => function($q){
                $q->select('id','booking_id','user_id','status','check_in_date','check_out_date','created_at','tour_name');
            }])
            ->where('room_type_id', $this->room_type_id)
            ->whereHas('tourBooking', function($q) use ($day){
                $q->whereIn('status', ['confirmed','pending','pending_payment'])
                  ->whereDate('check_in_date', '<=', $day)
                  ->whereDate('check_out_date', '>', $day);
            })
            ->get();

        if ($rows->isEmpty()) {
            return ['room_to_status' => [], 'room_to_tb' => []];
        }

        // ƯU TIÊN: Ánh xạ các gán cố định (assigned_room_ids) trước
        $roomToStatus = [];
        $roomToTb = [];

        foreach ($rows as $row) {
            $assigned = is_array($row->assigned_room_ids) ? $row->assigned_room_ids : [];
            if (empty($assigned)) continue;

            $status = ($row->tourBooking && $row->tourBooking->status === 'confirmed') ? 'booked' : 'pending';
            foreach ($assigned as $rid) {
                // Bỏ qua nếu phòng này bị booking thường giữ
                if (in_array($rid, $regularHeldRoomIds, true)) continue;
                $roomToStatus[$rid] = $status;
                $roomToTb[$rid] = $row->tourBooking?->id;
                // Loại trừ khỏi availableIds để không bị cấp phát lần nữa
                $availableIds = array_values(array_diff($availableIds, [$rid]));
            }
        }

        // 4) Chia nhóm theo status và sort theo tiêu chí nights desc, check_in asc, created_at asc cho phần CHƯA gán cố định
        $sorter = function($a, $b) {
            $ta = $a->tourBooking; $tb = $b->tourBooking;
            $na = \Carbon\Carbon::parse($ta->check_in_date)->diffInDays(\Carbon\Carbon::parse($ta->check_out_date));
            $nb = \Carbon\Carbon::parse($tb->check_in_date)->diffInDays(\Carbon\Carbon::parse($tb->check_out_date));
            if ($na === 0) $na = 1; if ($nb === 0) $nb = 1;
            if ($na !== $nb) return $nb <=> $na; // nights desc
            if ($ta->check_in_date != $tb->check_in_date) return strcmp((string)$ta->check_in_date, (string)$tb->check_in_date);
            return strcmp((string)$ta->created_at, (string)$tb->created_at);
        };

        $confirmedRows = $rows->filter(function($r){
            if (!$r->tourBooking) return false;
            if (!empty($r->assigned_room_ids)) return false; // đã gán cố định, bỏ qua ở bước phân bổ động
            return $r->tourBooking->status === 'confirmed';
        })->values();
        $pendingRows = $rows->filter(function($r){
            if (!$r->tourBooking) return false;
            if (!empty($r->assigned_room_ids)) return false; // đã gán cố định, bỏ qua ở bước phân bổ động
            $st = $r->tourBooking->status;
            if (!in_array($st, ['pending','pending_payment'])) return false;
            // chỉ giữ pending trong 30 phút
            return $r->tourBooking->created_at && $r->tourBooking->created_at->diffInMinutes(now()) <= 30;
        })->values();

        $confirmedRows = $confirmedRows->sort($sorter);
        $pendingRows = $pendingRows->sort($sorter);

        // 5) Cấp phát theo thứ tự: confirmed trước, rồi pending
        $cursor = 0;
        $assign = function($collection, $status) use (&$cursor, $availableIds, &$roomToStatus, &$roomToTb) {
            foreach ($collection as $row) {
                $qty = max(0, (int)$row->quantity);
                for ($i = 0; $i < $qty && $cursor < count($availableIds); $i++) {
                    $rid = $availableIds[$cursor++];
                    $roomToStatus[$rid] = ($status === 'confirmed') ? 'booked' : 'pending';
                    $roomToTb[$rid] = $row->tourBooking->id;
                }
                if ($cursor >= count($availableIds)) break;
            }
        };

        $assign($confirmedRows, 'confirmed');
        $assign($pendingRows, 'pending');

        return ['room_to_status' => $roomToStatus, 'room_to_tb' => $roomToTb];
    }

    /**
     * Lấy danh sách room_id bị booking thường giữ trong ngày
     */
    private function getRegularHeldRoomIdsForDay(string $day): array
    {
        // confirmed overlap
        $confirmedIds = Booking::whereDate('check_in_date', '<=', $day)
            ->whereDate('check_out_date', '>', $day)
            ->where('status', 'confirmed')
            ->pluck('room_id')
            ->toArray();

        // pending within 30 minutes
        $pending = Booking::whereDate('check_in_date', '<=', $day)
            ->whereDate('check_out_date', '>', $day)
            ->whereIn('status', ['pending','pending_payment'])
            ->where('created_at', '>=', now()->subMinutes(30))
            ->pluck('room_id')
            ->toArray();

        return array_values(array_unique(array_filter(array_merge($confirmedIds, $pending)))) ;
    }

    /**
     * Kiểm tra nghiêm ngặt: phòng phải trống cho toàn bộ khoảng ngày [checkIn, checkOut)
     */
    public function isStrictlyAvailableForRange(\Carbon\Carbon|string $checkIn, \Carbon\Carbon|string $checkOut): bool
    {
        $in = \Carbon\Carbon::parse($checkIn)->startOfDay();
        $out = \Carbon\Carbon::parse($checkOut)->startOfDay();
        if ($out->lessThanOrEqualTo($in)) {
            $out = (clone $in)->addDay();
        }
        for ($day = $in->copy(); $day->lt($out); $day->addDay()) {
            if ($this->getStatusForDate($day->toDateString()) !== 'available') {
                return false;
            }
        }
        return true;
    }

    /**
     * Kiểm tra phòng trống cho toàn bộ khoảng ngày nhưng BỎ QUA giữ chỗ từ Tour Booking.
     * Chỉ xét: booking thường (confirmed + pending<=30 phút) và trạng thái phòng (repair).
     */
    public function isAvailableForRangeIgnoringTourHolds(\Carbon\Carbon|string $checkIn, \Carbon\Carbon|string $checkOut): bool
    {
        $in = \Carbon\Carbon::parse($checkIn)->startOfDay();
        $out = \Carbon\Carbon::parse($checkOut)->startOfDay();
        if ($out->lessThanOrEqualTo($in)) {
            $out = (clone $in)->addDay();
        }

        if ($this->status === 'repair') {
            return false;
        }

        for ($day = $in->copy(); $day->lt($out); $day->addDay()) {
            $dayStr = $day->toDateString();

            // Bị giữ bởi booking thường?
            $regularBooking = $this->getAssignedRegularBookingForDate($dayStr);
            if ($regularBooking) {
                return false;
            }
        }
        return true;
    }

    /**
     * Lấy booking thường đang giữ phòng này trong ngày chỉ định (ưu tiên confirmed, sau đó pending trong 30 phút)
     */
    public function getAssignedRegularBookingForDate(string $day): ?\App\Models\Booking
    {
        // Confirmed booking overlap
        $confirmed = $this->bookings()
            ->where('status', 'confirmed')
            ->whereDate('check_in_date', '<=', $day)
            ->whereDate('check_out_date', '>', $day)
            ->orderBy('created_at')
            ->first();
        if ($confirmed) {
            return $confirmed;
        }

        // Pending within 30 mins
        $pending = $this->bookings()
            ->whereIn('status', ['pending', 'pending_payment'])
            ->whereDate('check_in_date', '<=', $day)
            ->whereDate('check_out_date', '>', $day)
            ->orderByDesc('created_at')
            ->first();
        if ($pending && $pending->created_at && $pending->created_at->diffInMinutes(now()) <= 30) {
            return $pending;
        }

        return null;
    }

    /**
     * Lấy TourBooking đang giữ phòng này trong ngày chỉ định (mapping theo chỉ số phòng trong room_type)
     */
    public function getAssignedTourBookingForDate(string $day): ?\App\Models\TourBooking
    {
        $allocation = $this->allocateTourHoldsForDay($day);
        $tbId = $allocation['room_to_tb'][$this->id] ?? null;
        if (!$tbId) {
            return null;
        }
        return \App\Models\TourBooking::find($tbId);
    }

} 