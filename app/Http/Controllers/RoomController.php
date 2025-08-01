<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\Services\RoomServiceInterface;
use App\Interfaces\Services\RoomTypeServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoomController extends Controller
{
    protected $roomService;
    protected $roomTypeService;

    public function __construct(RoomServiceInterface $roomService, RoomTypeServiceInterface $roomTypeService)
    {
        $this->roomService = $roomService;
        $this->roomTypeService = $roomTypeService;
    }

    public function search(Request $request)
    {
        try {
            $filters = $request->only(['keyword', 'price_min', 'price_max', 'type']);

            // Validate và clean filters
            $filters = $this->validateAndCleanFilters($filters);

            $roomTypes = $this->roomTypeService->searchRoomTypes($filters);

            // Tạo thông báo tìm kiếm
            $searchMessage = $this->buildSearchMessage($filters);

            // Truyền searchParams để view có thể sử dụng
            $searchParams = $filters;

            return view('client.rooms.index', compact('roomTypes', 'searchMessage', 'searchParams'));
        } catch (\Exception $e) {
            Log::error('Room search error: ' . $e->getMessage(), [
                'user_id' => Auth::check() ? Auth::id() : null,
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors(['message' => 'Có lỗi xảy ra khi tìm kiếm phòng. Vui lòng thử lại.']);
        }
    }

    /**
     * Tạo thông báo tìm kiếm dựa trên các filter
     */
    private function buildSearchMessage($filters)
    {
        $messages = [];

        if (!empty($filters['keyword'])) {
            $messages[] = "từ khóa: '{$filters['keyword']}'";
        }

        if (!empty($filters['price_min']) || !empty($filters['price_max'])) {
            $priceRange = [];
            if (!empty($filters['price_min'])) {
                $priceRange[] = "từ " . number_format($filters['price_min']) . "đ";
            }
            if (!empty($filters['price_max'])) {
                $priceRange[] = "đến " . number_format($filters['price_max']) . "đ";
            }
            if (!empty($priceRange)) {
                $messages[] = "giá " . implode(' ', $priceRange);
            }
        }

        if (!empty($filters['type'])) {
            $messages[] = "loại phòng: {$filters['type']}";
        }

        if (empty($messages)) {
            return null;
        }

        return "Kết quả tìm kiếm với " . implode(', ', $messages) . " (" . count($filters) . " bộ lọc)";
    }

    /**
     * Validate và clean các filter
     */
    private function validateAndCleanFilters($filters)
    {
        $cleanedFilters = [];

        // Clean keyword
        if (!empty($filters['keyword'])) {
            $cleanedFilters['keyword'] = trim($filters['keyword']);
        }

        // Validate price_min
        if (!empty($filters['price_min']) && is_numeric($filters['price_min'])) {
            $priceMin = (int) $filters['price_min'];
            if ($priceMin >= 0) {
                $cleanedFilters['price_min'] = $priceMin;
            }
        }

        // Validate price_max
        if (!empty($filters['price_max']) && is_numeric($filters['price_max'])) {
            $priceMax = (int) $filters['price_max'];
            if ($priceMax > 0) {
                $cleanedFilters['price_max'] = $priceMax;
            }
        }

        // Validate logic: price_max phải lớn hơn price_min nếu cả hai đều có
        if (isset($cleanedFilters['price_min']) && isset($cleanedFilters['price_max'])) {
            if ($cleanedFilters['price_max'] <= $cleanedFilters['price_min']) {
                unset($cleanedFilters['price_max']); // Loại bỏ price_max nếu không hợp lệ
            }
        }

        // Validate type
        if (!empty($filters['type'])) {
            $cleanedFilters['type'] = trim($filters['type']);
        }

        return $cleanedFilters;
    }
}
