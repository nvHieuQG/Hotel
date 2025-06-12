<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\Admin\AdminRoomServiceInterface;
use App\Models\RoomType;
use App\Services\Admin\AdminRoomService;
use Illuminate\Http\Request;

class AdminRoomController extends Controller
{
    protected $roomService;

    public function __construct(AdminRoomServiceInterface $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index(Request $request)
    {
        $status = $request->get('status');
        $rooms = $this->roomService->getAllRooms($status);
        return view('admin.rooms.index', compact('rooms'));
    }

    public function show($id)
    {
        $room = $this->roomService->getRoom($id);
        return view('admin.rooms.show', compact('room'));
    }

    public function create()
    {
        // Lấy dữ liệu cần thiết cho form tạo phòng (nếu cần)
        $roomTypes = RoomType::all();
        return view('admin.rooms.create', compact('roomTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|string|max:20',
            'status' => 'required|in:available,booked,repair',
        ]);
        $this->roomService->createRoom($data);
        return redirect()->route('admin.rooms.index')->with('success', 'Tạo phòng thành công');
    }

    public function edit($id)
    {
        $roomTypes = RoomType::all();
        $room = $this->roomService->getRoom($id);
        return view('admin.rooms.edit', compact('room', 'roomTypes'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|string|max:20',
            'status' => 'required|in:available,booked,repair',
        ]);
        $this->roomService->updateRoom($id, $data);
        return redirect()->route('admin.rooms.index')->with('success', 'Cập nhật phòng thành công');
    }

    public function destroy($id)
    {
        $result = $this->roomService->deleteRoom($id);
        if ($result['success']) {
            return redirect()->route('admin.rooms.index')->with('success', $result['message']);
        }
        
        return redirect()->route('admin.rooms.index')->with('error', $result['message']);
    }
}