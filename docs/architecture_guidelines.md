# Hướng dẫn kiến trúc và luồng làm việc cho hệ thống đặt phòng khách sạn

## 1. Tổng quan kiến trúc

Hệ thống được xây dựng theo mô hình phân lớp với các thành phần chính sau:

```
Client Request → Controller → Service → Repository → Model → Database
```

### Các lớp chính:

- **Models**: Đại diện cho các đối tượng dữ liệu và quan hệ giữa chúng
- **Repositories**: Xử lý truy vấn và tương tác với cơ sở dữ liệu
- **Services**: Chứa logic nghiệp vụ 
- **Controllers**: Xử lý request và response
- **Interfaces**: Định nghĩa các contract mà các lớp cần tuân thủ

## 2. Quy tắc và nguyên tắc

### SOLID Principles
- **Single Responsibility**: Mỗi lớp chỉ có một lý do để thay đổi
- **Open/Closed**: Mở rộng, không sửa đổi
- **Liskov Substitution**: Các lớp con phải thay thế được lớp cha
- **Interface Segregation**: Interface nhỏ, chuyên biệt
- **Dependency Inversion**: Phụ thuộc vào abstraction, không phụ thuộc vào implementation

### Quy tắc đặt tên
- **Interface**: `{Tên}Interface` (ví dụ: `UserRepositoryInterface`)
- **Repository**: `{Tên}Repository` (ví dụ: `UserRepository`)
- **Service**: `{Tên}Service` (ví dụ: `AuthService`)
- **Controller**: `{Tên}Controller` (ví dụ: `HotelController`)

## 3. Luồng làm việc cho các chức năng

### Quy trình phát triển chức năng mới

1. **Tạo Model** (nếu chưa có)
2. **Tạo Interface Repository và Service**
3. **Triển khai Repository và Service**
4. **Tạo Controller** 
5. **Đăng ký Binding trong AppServiceProvider**
6. **Tạo Routes**
7. **Tạo Views**

## 4. Các lưu ý và quy tắc quan trọng

1. **Không làm logic trong Controller**: Controller chỉ nên gọi các phương thức từ Service, không nên chứa logic xử lý.

2. **Service là nơi xử lý logic nghiệp vụ**: Tất cả logic nghiệp vụ nên được đặt trong Service.

3. **Repository chỉ làm việc với dữ liệu**: Repository chỉ nên thực hiện các thao tác CRUD đơn giản, không chứa logic nghiệp vụ.

4. **Sử dụng Interface cho Dependency Injection**: Luôn sử dụng interface thay vì implementation cụ thể trong constructor.

5. **Luôn thêm kiểm tra đầu vào**: Luôn thêm validation cho dữ liệu nhập từ user.

6. **Quản lý lỗi thống nhất**: Sử dụng try-catch và ném exception khi cần thiết.

7. **Tuân thủ nguyên tắc đơn trách nhiệm**: Mỗi class chỉ nên có một lý do để thay đổi.