<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiChatbotService
{
    private $apiKeys;
    private $currentApiKeyIndex;
    private $apiEndpoint;
    private $hotelKnowledge;

    public function __construct()
    {
        $this->initializeApiKeys();
        $this->apiEndpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent';
        $this->loadHotelKnowledge();
    }

    /**
     * Khởi tạo danh sách API keys
     */
    private function initializeApiKeys()
    {
        $this->apiKeys = [
            config('services.gemini.api_key'), // API key chính
            config('services.gemini.api_key_2', ''), // API key dự phòng 1
            config('services.gemini.api_key_3', ''), // API key dự phòng 2
            config('services.gemini.api_key_4', ''), // API key dự phòng 3
            config('services.gemini.api_key_5', ''), // API key dự phòng 4
        ];
        
        // Lọc bỏ các API key rỗng
        $this->apiKeys = array_filter($this->apiKeys);
        
        // Kiểm tra xem có API key nào không
        if (empty($this->apiKeys)) {
            Log::error('No valid API keys found in configuration');
            throw new \Exception('Không có API key nào được cấu hình');
        }
        
        // Khởi tạo index API key hiện tại
        $this->currentApiKeyIndex = 0;
        
        // Log số lượng API key có sẵn
        Log::info('GeminiChatbotService initialized with ' . count($this->apiKeys) . ' API keys', [
            'total_keys' => count($this->apiKeys),
            'current_key_index' => $this->currentApiKeyIndex,
            'first_key_preview' => substr($this->apiKeys[0], 0, 10) . '...'
        ]);
    }

    /**
     * Lấy API key hiện tại
     */
    private function getCurrentApiKey()
    {
        if (isset($this->apiKeys[$this->currentApiKeyIndex])) {
            return $this->apiKeys[$this->currentApiKeyIndex];
        }
        return null;
    }

    /**
     * Chuyển sang API key tiếp theo khi gặp lỗi
     */
    private function switchToNextApiKey()
    {
        $oldIndex = $this->currentApiKeyIndex;
        $this->currentApiKeyIndex++;
        
        // Nếu đã hết API key, quay về API key đầu tiên
        if ($this->currentApiKeyIndex >= count($this->apiKeys)) {
            $this->currentApiKeyIndex = 0;
            Log::warning('All API keys have been tried, returning to first API key', [
                'old_index' => $oldIndex,
                'new_index' => $this->currentApiKeyIndex,
                'total_keys' => count($this->apiKeys)
            ]);
        } else {
            Log::info('Switched to next API key', [
                'old_index' => $oldIndex,
                'new_index' => $this->currentApiKeyIndex,
                'total_keys' => count($this->apiKeys)
            ]);
        }
        
        $currentKey = $this->getCurrentApiKey();
        if ($currentKey) {
            Log::info('Current API key info', [
                'index' => $this->currentApiKeyIndex,
                'key_preview' => substr($currentKey, 0, 10) . '...',
                'key_length' => strlen($currentKey)
            ]);
        }
        
        return $currentKey;
    }

    /**
     * Load dữ liệu khách sạn từ file JSON
     */
    private function loadHotelKnowledge()
    {
        try {
            $jsonPath = public_path('data/hotel_knowledge.json');
            if (file_exists($jsonPath)) {
                $this->hotelKnowledge = json_decode(file_get_contents($jsonPath), true);
            } else {
                Log::error('Hotel knowledge file not found');
                $this->hotelKnowledge = [];
            }
        } catch (\Exception $e) {
            Log::error('Error loading hotel knowledge: ' . $e->getMessage());
            $this->hotelKnowledge = [];
        }
    }

    /**
     * Tạo system prompt với thông tin khách sạn
     */
    private function generateSystemPrompt()
    {
        $hotelInfo = $this->hotelKnowledge['hotel_info'] ?? [];
        $roomTypes = $this->hotelKnowledge['room_types'] ?? [];
        $services = $this->hotelKnowledge['services'] ?? [];
        $policies = $this->hotelKnowledge['policies'] ?? [];
        $nearbyAttractions = $this->hotelKnowledge['nearby_attractions'] ?? [];
        $faq = $this->hotelKnowledge['faq'] ?? [];
        $bookingGuide = $this->hotelKnowledge['booking_guide'] ?? [];
        $paymentMethods = $this->hotelKnowledge['payment_methods'] ?? [];
        $detailedFeatures = $this->hotelKnowledge['detailed_features'] ?? [];

        return "Bạn là MARRON AI CONSULTANT - trợ lý AI thông minh và chuyên nghiệp của khách sạn MARRON tại Hà Nội, Việt Nam.

**VAI TRÒ CỦA BẠN:**
🤖 MARRON AI CONSULTANT - Chuyên gia tư vấn du lịch & khách sạn thông minh
🎯 NHIỆM VỤ: Tư vấn, phân tích và đưa ra gợi ý thông minh cho khách hàng
💡 PHONG CÁCH: Thân thiện, chuyên nghiệp, sáng tạo và giàu kinh nghiệm

**KHUNG LÀM VIỆC THÔNG MINH:**
✅ PHÂN TÍCH nhu cầu khách hàng một cách sâu sắc
✅ ĐƯA RA gợi ý phù hợp và sáng tạo  
✅ TƯ VẤN các lựa chọn tối ưu
✅ SALES các dịch vụ một cách tự nhiên và hữu ích
✅ TRẢ LỜI mọi chủ đề nếu có thể giúp được khách hàng

**CHIẾN THUẬT TRẢ LỜI:**
📋 NGẮN GỌN khi cần thiết, CHI TIẾT khi được yêu cầu
🎯 LUÔN có call-to-action hoặc gợi ý tiếp theo
💰 NHẮC ĐẾN ưu đãi/khuyến mãi khi phù hợp
📞 ĐỀ XUẤT liên hệ trực tiếp khi cần

THÔNG TIN KHÁCH SẠN:
- Tên: {$hotelInfo['name']}
- Địa chỉ: {$hotelInfo['address']}
- Điện thoại: {$hotelInfo['phone']}
- Email: {$hotelInfo['email']}
- Website: {$hotelInfo['website']}

LOẠI PHÒNG:
" . $this->formatRoomTypes($roomTypes) . "

DỊCH VỤ:
" . $this->formatServices($services) . "

CHÍNH SÁCH:
" . $this->formatPolicies($policies) . "

ĐỊA ĐIỂM GẦN ĐÓ:
" . $this->formatNearbyAttractions($nearbyAttractions) . "

HƯỚNG DẪN ĐẶT PHÒNG:
" . $this->formatBookingGuide($bookingGuide) . "

PHƯƠNG THỨC THANH TOÁN:
" . $this->formatPaymentMethods($paymentMethods) . "

TÍNH NĂNG CHI TIẾT:
" . $this->formatDetailedFeatures($detailedFeatures) . "

CÂU HỎI THƯỜNG GẶP:
" . $this->formatFAQ($faq) . "

**HƯỚNG DẪN TRẢ LỜI:**
1. Luôn trả lời bằng tiếng Việt, lịch sự và chuyên nghiệp
2. ƯU TIÊN sử dụng thông tin khách sạn MARRON nếu có
3. CÓ THỂ trả lời các chủ đề khác nếu có thể giúp được
4. Nếu không có thông tin, đề xuất liên hệ trực tiếp
5. Sử dụng emoji phù hợp để tạo cảm giác thân thiện
6. Đưa ra lời khuyên hữu ích cho khách hàng

Bạn hãy trả lời câu hỏi của khách hàng một cách linh hoạt và hữu ích.";
    }

    /**
     * Format thông tin loại phòng
     */
    private function formatRoomTypes($roomTypes)
    {
        if (empty($roomTypes)) {
            return "Không có thông tin loại phòng";
        }
        
        $formatted = '';
        foreach ($roomTypes as $room) {
            $formatted .= "- {$room['name']}: {$room['description']} - {$room['price_range']}\n";
        }
        return $formatted;
    }

    /**
     * Format thông tin dịch vụ
     */
    private function formatServices($services)
    {
        if (empty($services)) {
            return "Không có thông tin dịch vụ";
        }
        
        $formatted = '';
        foreach ($services as $category) {
            $formatted .= "{$category['category']}:\n";
            foreach ($category['items'] as $item) {
                $formatted .= "  - {$item['name']}: {$item['description']}\n";
            }
        }
        return $formatted;
    }

    /**
     * Format thông tin chính sách
     */
    private function formatPolicies($policies)
    {
        if (empty($policies)) {
            return "Không có thông tin chính sách";
        }
        
        $formatted = '';
        foreach ($policies as $policy) {
            $formatted .= "{$policy['category']}:\n";
            foreach ($policy['rules'] as $rule) {
                $formatted .= "  - {$rule}\n";
            }
        }
        return $formatted;
    }

    /**
     * Format thông tin địa điểm gần đó
     */
    private function formatNearbyAttractions($attractions)
    {
        if (empty($attractions)) {
            return "Không có thông tin địa điểm gần đó";
        }
        
        $formatted = '';
        foreach ($attractions as $attraction) {
            $formatted .= "- {$attraction['name']} ({$attraction['distance']}): {$attraction['description']}\n";
        }
        return $formatted;
    }

    /**
     * Format thông tin FAQ
     */
    private function formatFAQ($faq)
    {
        if (empty($faq)) {
            return "Không có câu hỏi thường gặp";
        }
        
        $formatted = '';
        foreach ($faq as $item) {
            $formatted .= "Q: {$item['question']}\nA: {$item['answer']}\n\n";
        }
        return $formatted;
    }

    /**
     * Format hướng dẫn đặt phòng
     */
    private function formatBookingGuide($bookingGuide)
    {
        if (empty($bookingGuide)) {
            return "Không có hướng dẫn đặt phòng";
        }
        
        $formatted = '';
        foreach ($bookingGuide as $step) {
            $formatted .= "{$step['step']}: {$step['description']}\n";
        }
        return $formatted;
    }

    /**
     * Format phương thức thanh toán
     */
    private function formatPaymentMethods($paymentMethods)
    {
        if (empty($paymentMethods)) {
            return "Không có thông tin phương thức thanh toán";
        }
        
        $formatted = '';
        foreach ($paymentMethods as $method) {
            $formatted .= "- {$method['method']}: {$method['description']}\n";
        }
        return $formatted;
    }

    /**
     * Format tính năng chi tiết
     */
    private function formatDetailedFeatures($detailedFeatures)
    {
        if (empty($detailedFeatures)) {
            return "Không có thông tin tính năng chi tiết";
        }
        
        $formatted = '';
        foreach ($detailedFeatures as $key => $feature) {
            $formatted .= "{$feature['title']}:\n";
            $formatted .= "{$feature['description']}\n";
            
            if (isset($feature['steps'])) {
                $formatted .= "Các bước thực hiện:\n";
                foreach ($feature['steps'] as $step) {
                    $formatted .= "  {$step}\n";
                }
            }
            
            if (isset($feature['requirements'])) {
                $formatted .= "Yêu cầu:\n";
                foreach ($feature['requirements'] as $requirement) {
                    $formatted .= "  - {$requirement}\n";
                }
            }
            
            if (isset($feature['routes'])) {
                $formatted .= "Đường dẫn:\n";
                foreach ($feature['routes'] as $routeKey => $route) {
                    $formatted .= "  {$routeKey}: {$route}\n";
                }
            }
            
            $formatted .= "\n";
        }
        return $formatted;
    }

    /**
     * Validate xem câu hỏi có liên quan đến khách sạn không
     */
    private function isHotelRelatedTopic($userMessage)
    {
        // Kiểm tra xem có bật validation không
        if (!config('chatbot.validation.enable_topic_validation', true)) {
            return true;
        }

        $userMessage = mb_strtolower($userMessage, 'UTF-8');
        
        // Lấy từ khóa từ config
        $hotelKeywords = config('chatbot.hotel_keywords', []);
        $nonHotelKeywords = config('chatbot.non_hotel_keywords', []);
        
        // Kiểm tra từ khóa khách sạn
        foreach ($hotelKeywords as $keyword) {
            if (mb_strpos($userMessage, $keyword) !== false) {
                return true;
            }
        }
        
        // Kiểm tra từ khóa không liên quan
        foreach ($nonHotelKeywords as $keyword) {
            if (mb_strpos($userMessage, $keyword) !== false) {
                return false;
            }
        }
        
        // Nếu không có từ khóa rõ ràng, kiểm tra ngữ cảnh
        $contextWords = ['ở đâu', 'làm sao', 'thế nào', 'bao nhiêu', 'khi nào', 'có thể'];
        $hasContext = false;
        foreach ($contextWords as $word) {
            if (mb_strpos($userMessage, $word) !== false) {
                $hasContext = true;
                break;
            }
        }
        
        // Nếu có ngữ cảnh câu hỏi, cho phép (có thể là hỏi về khách sạn)
        return $hasContext;
    }

    /**
     * Gọi Gemini API để tạo phản hồi
     */
    public function generateResponse($userMessage, $conversationHistory = [])
    {
        try {
            // Bỏ topic validation để bot linh hoạt hơn
            // if (!$this->isHotelRelatedTopic($userMessage)) {
            //     return config('chatbot.validation.fallback_message', 'Xin lỗi, tôi chỉ hỗ trợ về khách sạn MARRON và dịch vụ du lịch. Vui lòng hỏi về đặt phòng, dịch vụ khách sạn, hoặc địa điểm tham quan gần đây.');
            // }
            
            $systemPrompt = $this->generateSystemPrompt();
            
            // Log để debug
            Log::info('System prompt generated', ['length' => strlen($systemPrompt)]);
            Log::info('User message', ['message' => $userMessage]);
            
            // Xây dựng conversation context với system prompt
            $messages = [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemPrompt . "\n\n" . $userMessage]
                    ]
                ]
            ];
            
            // Validate conversation history trước khi thêm
            if (!empty($conversationHistory)) {
                // Kiểm tra xem conversation history có hợp lệ không
                $validHistory = [];
                foreach ($conversationHistory as $message) {
                    if (isset($message['role']) && isset($message['content']) && 
                        in_array($message['role'], ['user', 'assistant']) &&
                        strlen($message['content']) < 1000) { // Giới hạn độ dài tin nhắn
                        $validHistory[] = $message;
                    }
                }
                
                // Chỉ lấy 3 tin nhắn gần nhất để tránh quá dài
                $recentHistory = array_slice($validHistory, -3);
                
                foreach ($recentHistory as $message) {
                    $messages[] = [
                        'role' => $message['role'],
                        'parts' => [
                            ['text' => $message['content']]
                        ]
                    ];
                }
                
                Log::info('Conversation history added', [
                    'total_history' => count($conversationHistory),
                    'valid_history' => count($validHistory),
                    'recent_history' => count($recentHistory),
                    'total_messages' => count($messages)
                ]);
            }

            $requestData = [
                'contents' => $messages,
                'generationConfig' => [
                    'temperature' => config('chatbot.token_settings.temperature', 0.7),
                    'topK' => config('chatbot.token_settings.top_k', 40),
                    'topP' => config('chatbot.token_settings.top_p', 0.95),
                    'maxOutputTokens' => config('chatbot.token_settings.max_output_tokens', 200),
                ],
                'safetySettings' => [
                    [
                        'category' => 'HARM_CATEGORY_HARASSMENT',
                        'threshold' => 'BLOCK_ONLY_HIGH'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_HATE_SPEECH',
                        'threshold' => 'BLOCK_ONLY_HIGH'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
                        'threshold' => 'BLOCK_ONLY_HIGH'
                    ],
                    [
                        'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
                        'threshold' => 'BLOCK_ONLY_HIGH'
                    ]
                ]
            ];

            $maxRetries = count($this->apiKeys);
            $attempt = 0;
            $response = null;
            
            do {
                $currentApiKey = $this->getCurrentApiKey();
                if (!$currentApiKey) {
                    Log::error('No valid API key available');
                    return 'Xin lỗi, tôi gặp vấn đề kỹ thuật. Vui lòng thử lại sau hoặc liên hệ trực tiếp với chúng tôi.';
                }
                
                Log::info('Sending request to Gemini API', [
                    'attempt' => $attempt + 1,
                    'api_key_index' => $this->currentApiKeyIndex,
                    'api_key_length' => strlen($currentApiKey),
                    'endpoint' => $this->apiEndpoint
                ]);

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->post($this->apiEndpoint . '?key=' . $currentApiKey, $requestData);

                // Nếu thành công, thoát khỏi vòng lặp
                if ($response->successful()) {
                    break;
                }
                
                // Nếu thất bại, log lỗi và chuyển sang API key tiếp theo
                Log::warning('API key ' . ($this->currentApiKeyIndex + 1) . ' failed', [
                    'status' => $response->status(),
                    'error' => $response->body()
                ]);
                
                // Chuyển sang API key tiếp theo
                $this->switchToNextApiKey();
                $attempt++;
                
            } while ($attempt < $maxRetries);

            // Nếu tất cả API keys đều thất bại, log và trả về lỗi
            if ($attempt >= $maxRetries) {
                Log::error('All API keys failed after ' . $maxRetries . ' attempts');
                
                // Kiểm tra response cuối cùng để đưa ra thông báo phù hợp
                if ($response && $response->status() === 400) {
                    return 'Xin lỗi, câu hỏi của bạn quá dài hoặc không phù hợp. Vui lòng thử lại với câu hỏi ngắn gọn hơn.';
                } elseif ($response && $response->status() === 429) {
                    return 'Xin lỗi, tất cả API keys đều đang quá tải. Vui lòng thử lại sau vài phút hoặc liên hệ trực tiếp với chúng tôi.';
                } elseif ($response && (strpos($response->body(), 'quota') !== false || strpos($response->body(), 'QuotaFailure') !== false)) {
                    return 'Xin lỗi, tất cả API keys đều đã hết quota. Vui lòng thử lại sau hoặc liên hệ trực tiếp với chúng tôi để được hỗ trợ.';
                } else {
                    return 'Xin lỗi, tôi gặp vấn đề kỹ thuật với tất cả API keys. Vui lòng thử lại sau hoặc liên hệ trực tiếp với chúng tôi.';
                }
            }

            Log::info('Gemini API response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    return $data['candidates'][0]['content']['parts'][0]['text'];
                }
                
                // Log response structure để debug
                Log::warning('Unexpected API response structure', [
                    'response_keys' => array_keys($data),
                    'candidates_count' => isset($data['candidates']) ? count($data['candidates']) : 0,
                    'response_body' => $response->body()
                ]);
            }

            // Nếu response không thành công nhưng vẫn có data, log để debug
            Log::error('Gemini API error: ' . $response->body());
            
            // Trả về lỗi cụ thể hơn
            if ($response->status() === 400) {
                return 'Xin lỗi, câu hỏi của bạn quá dài hoặc không phù hợp. Vui lòng thử lại với câu hỏi ngắn gọn hơn.';
            } elseif ($response->status() === 429) {
                return 'Xin lỗi, hệ thống đang quá tải. Vui lòng thử lại sau vài giây.';
            } else {
                return 'Xin lỗi, tôi gặp vấn đề kỹ thuật. Vui lòng thử lại sau hoặc liên hệ trực tiếp với chúng tôi.';
            }

        } catch (\Exception $e) {
            Log::error('Error calling Gemini API: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return 'Xin lỗi, tôi gặp vấn đề kỹ thuật. Vui lòng thử lại sau hoặc liên hệ trực tiếp với chúng tôi.';
        }
    }
}
