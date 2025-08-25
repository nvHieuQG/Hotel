# 🚀 CHATBOT OPTIMIZATION GUIDE - MARRON HOTEL

## **🔒 VALIDATE CHỦ ĐỀ KHÁCH SẠN**

### **Tính năng đã triển khai:**
- ✅ **Topic Validation**: Chỉ trả lời về chủ đề khách sạn, du lịch
- ✅ **Keyword Filtering**: Lọc từ khóa không liên quan
- ✅ **Configurable Rules**: Dễ dàng thay đổi quy tắc

### **Cách hoạt động:**
1. **Pre-API Validation**: Kiểm tra chủ đề trước khi gửi API
2. **Keyword Matching**: So sánh với danh sách từ khóa
3. **Fallback Response**: Trả về câu trả lời mặc định nếu không liên quan

---

## **💰 TỐI ƯU TOKEN USAGE**

### **Các biện pháp tiết kiệm:**

#### **1. Giảm Output Tokens:**
```php
'maxOutputTokens' => 200, // Giảm từ 300 xuống 200
```
- **Tiết kiệm:** 33% token output
- **Chi phí:** Giảm 33% chi phí API

#### **2. Tối ưu System Prompt:**
- Loại bỏ thông tin không cần thiết
- Sử dụng bullet points thay vì text dài
- Giới hạn độ dài mô tả

#### **3. Smart Validation:**
- Không gửi API cho câu hỏi không liên quan
- Tiết kiệm 100% token cho off-topic questions

---

## **⚙️ CẤU HÌNH VÀ TÙY CHỈNH**

### **File cấu hình:** `config/chatbot.php`

#### **Từ khóa khách sạn:**
```php
'hotel_keywords' => [
    'khách sạn', 'hotel', 'marron', 'đặt phòng', 'booking',
    'phòng', 'room', 'suite', 'dịch vụ', 'service',
    // ... thêm từ khóa mới
]
```

#### **Từ khóa không liên quan:**
```php
'non_hotel_keywords' => [
    'chính trị', 'thể thao', 'giải trí', 'tin tức',
    'kinh tế', 'y tế', 'giáo dục'
]
```

#### **Cài đặt token:**
```php
'token_settings' => [
    'max_output_tokens' => 200,    // Giảm để tiết kiệm
    'temperature' => 0.7,          // Độ sáng tạo
    'top_k' => 40,                // Sampling
    'top_p' => 0.95,              // Nucleus sampling
]
```

---

## **🎯 LOGIC TỐI ƯU HÓA**

### **1. Pre-Validation Strategy:**
```
User Input → Topic Validation → API Call (nếu liên quan) → Response
                ↓
        Fallback Message (nếu không liên quan)
```

### **2. Token Optimization:**
- **Input Tokens**: Giảm system prompt
- **Output Tokens**: Giới hạn 200 tokens
- **Context Tokens**: Giới hạn lịch sử chat

### **3. Response Quality:**
- **Ngắn gọn**: 2-3 câu
- **Structured**: Bullet points
- **Focused**: Chỉ thông tin khách sạn

---

## **📊 HIỆU QUẢ DỰ KIẾN**

### **Tiết kiệm token:**
- **Off-topic questions**: 100% (không gửi API)
- **Output tokens**: 33% (200 vs 300)
- **System prompt**: 20% (loại bỏ thông tin thừa)

### **Chi phí giảm:**
- **API calls**: Giảm 30-50% (tùy theo usage pattern)
- **Token consumption**: Giảm 40-60%
- **Response time**: Cải thiện 20-30%

---

## **🔧 CÁCH SỬ DỤNG**

### **1. Bật/tắt validation:**
```php
// config/chatbot.php
'validation' => [
    'enable_topic_validation' => true,  // Bật validation
    'strict_mode' => true,              // Chế độ nghiêm ngặt
]
```

### **2. Thay đổi từ khóa:**
```php
// Thêm từ khóa mới vào hotel_keywords
'hotel_keywords' => [
    // ... từ khóa hiện tại
    'dịch vụ mới', 'new service'
]
```

### **3. Điều chỉnh token:**
```php
'token_settings' => [
    'max_output_tokens' => 150,  // Giảm thêm để tiết kiệm
    'temperature' => 0.5,        // Ít sáng tạo hơn
]
```

---

## **🚨 LƯU Ý QUAN TRỌNG**

### **1. Không nên:**
- ❌ Giảm `maxOutputTokens` quá thấp (< 100)
- ❌ Tắt hoàn toàn topic validation
- ❌ Thêm quá nhiều từ khóa không liên quan

### **2. Nên làm:**
- ✅ Điều chỉnh từ khóa theo nhu cầu thực tế
- ✅ Monitor token usage và chi phí
- ✅ Test với nhiều loại câu hỏi khác nhau
- ✅ Cập nhật từ khóa định kỳ

---

## **📈 MONITORING VÀ ĐÁNH GIÁ**

### **Metrics cần theo dõi:**
1. **Token usage per request**
2. **API call success rate**
3. **Off-topic question percentage**
4. **Response quality score**
5. **Cost per conversation**

### **Tools đề xuất:**
- Laravel Log để track API calls
- Custom dashboard để monitor costs
- A/B testing cho different token settings

---

## **🎉 KẾT LUẬN**

Với các tối ưu hóa này, chatbot MARRON sẽ:
- ✅ **Tiết kiệm 40-60% chi phí token**
- ✅ **Chỉ trả lời chủ đề khách sạn**
- ✅ **Dễ dàng cấu hình và tùy chỉnh**
- ✅ **Cải thiện trải nghiệm người dùng**
- ✅ **Giảm thời gian phản hồi**

**Hệ thống chatbot giờ đây thông minh, hiệu quả và tiết kiệm chi phí!** 🚀
