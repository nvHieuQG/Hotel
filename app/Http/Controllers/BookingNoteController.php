<?php

namespace App\Http\Controllers;

use App\Services\BookingNoteService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookingNoteController extends Controller
{
    protected $bookingNoteService;

    public function __construct(BookingNoteService $bookingNoteService)
    {
        $this->bookingNoteService = $bookingNoteService;
    }

    /**
     * Lấy danh sách ghi chú của một booking
     */
    public function index(Request $request, $bookingId): JsonResponse
    {
        try {
            $notes = $this->bookingNoteService->getVisibleNotes($bookingId);
            
            return response()->json([
                'success' => true,
                'data' => $notes,
                'message' => 'Lấy danh sách ghi chú thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Tạo ghi chú mới
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'content' => 'required|string|max:1000',
                'type' => 'required|in:customer,staff,admin',
                'visibility' => 'required|in:public,private,internal',
                'is_internal' => 'boolean'
            ]);

            $note = $this->bookingNoteService->createNote($validatedData);
            
            return response()->json([
                'success' => true,
                'data' => $note->load('user'),
                'message' => 'Tạo ghi chú thành công'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Cập nhật ghi chú
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'content' => 'required|string|max:1000',
                'visibility' => 'sometimes|in:public,private,internal'
            ]);

            $success = $this->bookingNoteService->updateNote($id, $validatedData);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật ghi chú thành công'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể cập nhật ghi chú'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Xóa ghi chú
     */
    public function destroy($id): JsonResponse
    {
        try {
            $success = $this->bookingNoteService->deleteNote($id);
            
            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'Xóa ghi chú thành công'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa ghi chú'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Tạo ghi chú qua AJAX (cho form inline)
     */
    public function storeAjax(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'content' => 'required|string|max:1000',
                'type' => 'required|in:customer,staff,admin',
                'visibility' => 'required|in:public,private,internal',
                'is_internal' => 'boolean'
            ]);

            $note = $this->bookingNoteService->createNote($validatedData);
            
            return response()->json([
                'success' => true,
                'data' => $note->load('user'),
                'message' => 'Thêm ghi chú thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
