<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\Services\RoomServiceInterface;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomServiceInterface $roomService)
    {
        $this->roomService = $roomService;
    }

    public function search(Request $request)
    {
        $filters = $request->only(['keyword', 'capacity', 'price_min', 'price_max', 'type']);
        $rooms = $this->roomService->searchRooms($filters);
        return view('client.rooms', compact('rooms'));
    }
}
