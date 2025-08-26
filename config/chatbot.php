<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chatbot Configuration
    |--------------------------------------------------------------------------
    |
    | Cấu hình cho chatbot AI của khách sạn MARRON
    |
    */

    // Từ khóa liên quan đến khách sạn
    'hotel_keywords' => [
        'khách sạn', 'hotel', 'marron', 'đặt phòng', 'booking', 'reservation',
        'phòng', 'room', 'suite', 'deluxe', 'standard', 'giá', 'price', 'cost',
        'dịch vụ', 'service', 'nhà hàng', 'restaurant', 'bar', 'spa', 'gym',
        'hồ bơi', 'pool', 'đưa đón', 'transfer', 'sân bay', 'airport',
        'địa điểm', 'attraction', 'tham quan', 'visit', 'du lịch', 'tourism',
        'thanh toán', 'payment', 'credit card', 'bank transfer', 'vnd', 'đồng',
        'check-in', 'check-out', 'cancellation', 'hủy', 'đổi phòng', 'change',
        'khuyến mãi', 'promotion', 'discount', 'ưu đãi', 'offer',
        'đánh giá', 'review', 'rating', 'feedback', 'góp ý',
        'liên hệ', 'contact', 'phone', 'email', 'website',
        'địa chỉ', 'address', 'location', 'vị trí', 'position',
        'tiện ích', 'amenity', 'facility', 'wifi', 'internet',
        'giặt ủi', 'laundry', 'dọn phòng', 'housekeeping',
        'phòng họp', 'meeting', 'sự kiện', 'event', 'wedding',
        'tiệc', 'party', 'buffet', 'breakfast', 'lunch', 'dinner',
        'hà nội', 'hanoi', 'việt nam', 'vietnam'
    ],

    // Từ khóa không liên quan (đã giảm tối thiểu để bot có thể trả lời hầu hết các chủ đề)
    'non_hotel_keywords' => [
        // Chỉ chặn những nội dung thực sự có hại
        'khủng bố', 'terrorism', 'bạo lực cực đoan', 'violence extreme'
    ],

    // Cài đặt token và response (tối ưu cho trí thông minh và chất lượng)
    'token_settings' => [
        'max_output_tokens' => 1500,       // Tăng để cho phép phân tích và tư vấn thông minh
        'max_input_tokens' => 15000,       // Tăng để xử lý ngữ cảnh phức tạp
        'temperature' => 0.8,              // Tăng sáng tạo cho các gợi ý thông minh
        'top_k' => 50,                    // Tăng để có nhiều lựa chọn từ ngữ
        'top_p' => 0.9,                   // Tối ưu cho câu trả lời đa dạng
    ],

    // Cài đặt validation
    'validation' => [
        'enable_topic_validation' => false, // Tắt validate chủ đề để bot linh hoạt hơn
        'strict_mode' => false,             // Tắt chế độ nghiêm ngặt
        'fallback_message' => 'Tôi có thể giúp bạn với nhiều chủ đề khác nhau. Bạn cần tôi hỗ trợ gì?',
    ],

    // Cài đặt cache và performance
    'performance' => [
        'enable_response_cache' => true,   // Bật cache câu trả lời
        'cache_ttl' => 3600,              // Thời gian cache (giây)
        'max_conversation_history' => 20, // Tăng lịch sử chat để bot nhớ ngữ cảnh lâu hơn
    ],
];
