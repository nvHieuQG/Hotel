<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    /**
     * Server-Sent Events endpoint for real-time notifications
     */
    public function stream()
    {
        // Disable time limit for SSE
        set_time_limit(0);

        return response()->stream(function () {
            // Set headers for SSE
            echo "data: " . json_encode(['type' => 'connected', 'message' => 'Connected to notification stream']) . "\n\n";
            ob_flush();
            flush();

            // Keep connection alive and check for new messages
            $lastCheck = now()->subSeconds(5); // Start checking from 5 seconds ago

            while (true) {
                // Check for new support messages from users (regardless of read status for real-time detection)
                $newMessages = SupportMessage::where('sender_type', 'user')
                    ->where('created_at', '>', $lastCheck)
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->get();

                if ($newMessages->count() > 0) {
                    foreach ($newMessages as $message) {
                        $data = [
                            'type' => 'new_support_message',
                            'message_id' => $message->id,
                            'conversation_id' => $message->conversation_id,
                            'user_name' => $message->user->name ?? 'Khách',
                            'message' => Str::limit($message->message, 50),
                            'created_at' => $message->created_at->format('H:i d/m/Y'),
                            'url' => route('support.showConversation', $message->conversation_id),
                            'is_read' => $message->is_read
                        ];

                        echo "data: " . json_encode($data) . "\n\n";
                        ob_flush();
                        flush();

                        // Log for debugging
                        Log::info('SSE: New support message detected', [
                            'message_id' => $message->id,
                            'user_name' => $data['user_name'],
                            'created_at' => $message->created_at
                        ]);
                    }

                    // Update last check time to the latest message time
                    $lastCheck = $newMessages->first()->created_at;
                } else {
                    // Send heartbeat to keep connection alive
                    echo "data: " . json_encode(['type' => 'heartbeat', 'timestamp' => now()->format('H:i:s')]) . "\n\n";
                    ob_flush();
                    flush();
                }

                // Sleep for 3 seconds before next check
                sleep(3);

                // Check if connection is still alive
                if (connection_aborted()) {
                    Log::info('SSE: Connection aborted');
                    break;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
        ]);
    }

    /**
     * Get unread support messages count
     */
    public function getUnreadCount()
    {
        $count = SupportMessage::where('sender_type', 'user')
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * Get recent support conversations for dropdown
     */
    public function getRecentConversations()
    {
        $conversations = SupportMessage::select('id', 'conversation_id', 'subject', 'sender_id', 'message', 'created_at')
            ->with('user')
            ->whereIn('id', function($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('support_messages')
                    ->groupBy('conversation_id');
            })
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $conversationsData = [];
        foreach ($conversations as $conversation) {
            $unreadCount = SupportMessage::where('conversation_id', $conversation->conversation_id)
                ->where('sender_type', 'user')
                ->where('is_read', false)
                ->count();

            $conversationsData[] = [
                'message_id' => $conversation->id,
                'conversation_id' => $conversation->conversation_id,
                'user_name' => $conversation->user->name ?? 'Khách',
                'message' => Str::limit($conversation->message, 30),
                'created_at' => $conversation->created_at->diffForHumans(),
                'unread_count' => $unreadCount,
                'url' => route('support.showConversation', $conversation->conversation_id)
            ];
        }

        return response()->json([
            'success' => true,
            'conversations' => $conversationsData
        ]);
    }

    /**
     * Test method to create a test support message for testing realtime
     */
    public function createTestMessage()
    {
        try {
            // Create a test support message
            $testMessage = SupportMessage::create([
                'conversation_id' => 'test_' . time(),
                'subject' => 'Test Message',
                'message' => 'This is a test message for realtime testing - ' . now()->format('H:i:s'),
                'sender_id' => 1, // Assuming user ID 1 exists
                'sender_type' => 'user',
                'is_read' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Test message created successfully',
                'data' => $testMessage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create test message: ' . $e->getMessage()
            ], 500);
        }
    }
}
