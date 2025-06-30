<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\Admin\AdminRoomServiceInterface;
use App\Models\RoomType;
use App\Services\Admin\AdminRoomService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminRoomController extends Controller
{
    protected $roomService;

    public function __construct(AdminRoomServiceInterface $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index(Request $request)
    {
        $filters = [
            'floor' => $request->get('floor'),
            'room_type' => $request->get('room_type'),
            'status' => $request->get('status'),
            'search' => $request->get('search')
        ];
        
        // 1. Lấy tất cả phòng đã lọc (dưới dạng Collection)
        $allRooms = $this->roomService->getAllRoomsWithFilters($filters);

        // 2. Nhóm các phòng theo tầng
        $roomsByFloor = $allRooms->groupBy('floor');

        // 3. Tự tạo phân trang cho các tầng
        $perPage = 5; // Hiển thị 5 tầng trên mỗi trang
        $currentPage = LengthAwarePaginator::resolveCurrentPage('page');
        $currentPageItems = $roomsByFloor->slice(($currentPage - 1) * $perPage, $perPage);

        $paginatedFloors = new LengthAwarePaginator(
            $currentPageItems,
            $roomsByFloor->count(),
            $perPage,
            $currentPage,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );

        $roomTypes = $this->roomService->getRoomTypes();
        $floors = $this->roomService->getAvailableFloors();

        return view('admin.rooms.index', [
            'paginatedFloors' => $paginatedFloors,
            'roomTypes' => $roomTypes,
            'floors' => $floors,
            'filters' => $filters,
            'totalRooms' => $allRooms->count()
        ]);
    }

    public function show($id)
    {
        $room = $this->roomService->getRoom($id);
        return view('admin.rooms.show', compact('room'));
    }

    public function create()
    {
        $roomTypes = $this->roomService->getRoomTypes();
        return view('admin.rooms.create', compact('roomTypes'));
    }

    public function store(Request $request)
    {
        try {
            $this->roomService->createRoom($request->all());
        return redirect()->route('admin.rooms.index')->with('success', 'Tạo phòng thành công');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }
    }

    public function edit($id)
    {
        $roomTypes = $this->roomService->getRoomTypes();
        $room = $this->roomService->getRoom($id);
        return view('admin.rooms.edit', compact('room', 'roomTypes'));
    }

    public function update(Request $request, $id)
    {
        try {
            $this->roomService->updateRoom($id, $request->all());
        return redirect()->route('admin.rooms.index')->with('success', 'Cập nhật phòng thành công');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }
    }

    public function destroy($id)
    {
        $result = $this->roomService->deleteRoom($id);
        if ($result['success']) {
            return redirect()->route('admin.rooms.index')->with('success', $result['message']);
        }
        
        return redirect()->route('admin.rooms.index')->with('error', $result['message']);
    }

    /**
 * Xóa ảnh phòng
 */
public function deleteImage(Request $request, $roomId, $imageId)
{
    $result = $this->roomService->deleteRoomImage($roomId, $imageId);
    
    if ($request->ajax()) {
        return response()->json($result);
    }
    
    return back()->with($result['success'] ? 'success' : 'error', $result['message']);
}

/**
 * Đặt ảnh làm ảnh chính
 */
public function setPrimaryImage(Request $request, $roomId, $imageId)
{
    $result = $this->roomService->setPrimaryImage($roomId, $imageId);
    
    if ($request->ajax()) {
        return response()->json($result);
    }
    
    return back()->with($result['success'] ? 'success' : 'error', $result['message']);
}
}