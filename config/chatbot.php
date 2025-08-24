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
        'tiệc', 'party', 'buffet', 'breakfast', 'lunch', 'dinner'
    ],

    // Từ khóa không liên quan đến khách sạn
    'non_hotel_keywords' => [
        'chính trị', 'politics', 'bầu cử', 'election', 'chính phủ', 'government',
        'thể thao', 'sport', 'bóng đá', 'football', 'tennis', 'basketball',
        'giải trí', 'entertainment', 'phim', 'movie', 'nhạc', 'music', 'ca sĩ', 'singer',
        'tin tức', 'news', 'thời sự', 'current events', 'thế giới', 'world',
        'kinh tế', 'economy', 'chứng khoán', 'stock', 'bitcoin', 'crypto',
        'y tế', 'health', 'bệnh viện', 'hospital', 'bác sĩ', 'doctor',
        'giáo dục', 'education', 'trường học', 'school', 'đại học', 'university'
    ],

    // Cài đặt token và response
    'token_settings' => [
        'max_output_tokens' => 200,        // Giới hạn token output
        'max_input_tokens' => 4000,        // Giới hạn token input
        'temperature' => 0.7,              // Độ sáng tạo của AI
        'top_k' => 40,                    // Top K sampling
        'top_p' => 0.95,                  // Top P sampling
    ],

    // Cài đặt validation
    'validation' => [
        'enable_topic_validation' => true, // Bật/tắt validate chủ đề
        'strict_mode' => true,             // Chế độ nghiêm ngặt
        'fallback_message' => 'Xin lỗi, tôi chỉ hỗ trợ về khách sạn MARRON và dịch vụ du lịch. Vui lòng hỏi về đặt phòng, dịch vụ khách sạn, hoặc địa điểm tham quan gần đây.',
    ],

    // Cài đặt cache và performance
    'performance' => [
        'enable_response_cache' => true,   // Bật cache câu trả lời
        'cache_ttl' => 3600,              // Thời gian cache (giây)
        'max_conversation_history' => 10, // Số tin nhắn tối đa trong lịch sử
    ],
];
