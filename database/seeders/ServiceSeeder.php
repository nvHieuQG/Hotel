<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tạo các danh mục dịch vụ
        $categories = [
            [
                'name' => 'Thông tin phòng',
                'services' => [
                    ['name' => 'Diện tích 20m²', 'description' => 'Phòng diện tích 20 mét vuông', 'price' => 0],
                    ['name' => 'Diện tích 25m²', 'description' => 'Phòng diện tích 25 mét vuông', 'price' => 0],
                    ['name' => 'Diện tích 30–35m²', 'description' => 'Phòng diện tích từ 30 đến 35 mét vuông', 'price' => 0],
                    ['name' => 'Diện tích 35–50m²', 'description' => 'Phòng diện tích từ 35 đến 50 mét vuông', 'price' => 0],
                    ['name' => 'Diện tích 45–60m²', 'description' => 'Phòng diện tích từ 45 đến 60 mét vuông', 'price' => 0],
                    ['name' => '1 người', 'description' => 'Sức chứa tối đa 1 người', 'price' => 0],
                    ['name' => '2 người', 'description' => 'Sức chứa tối đa 2 người', 'price' => 0],
                    ['name' => '3–5 người', 'description' => 'Sức chứa 3 đến 5 người (phù hợp cho gia đình)', 'price' => 0],
                    ['name' => '1 giường đơn', 'description' => 'Phòng có 1 giường đơn', 'price' => 0],
                    ['name' => '1 giường đôi', 'description' => 'Phòng có 1 giường đôi', 'price' => 0],
                    ['name' => '2 giường đơn', 'description' => 'Phòng có 2 giường đơn', 'price' => 0],
                    ['name' => '1 giường đôi lớn (King/Queen)', 'description' => 'Giường lớn cao cấp cho trải nghiệm tốt hơn', 'price' => 0],
                    ['name' => '1 giường đôi + 1 giường đơn', 'description' => 'Phòng có 1 giường đôi và 1 giường đơn', 'price' => 0],
                    ['name' => '2 giường đôi', 'description' => 'Phòng có 2 giường đôi rộng rãi', 'price' => 0],
                ]
            ],
            [
                'name' => 'Tính năng nổi bật',
                'services' => [
                    ['name' => 'Ban công riêng', 'description' => 'Ban công riêng với view đẹp', 'price' => 0],
                    ['name' => 'Máy sưởi', 'description' => 'Hệ thống sưởi ấm', 'price' => 0],
                    ['name' => 'Điều hòa không khí', 'description' => 'Điều hòa nhiệt độ', 'price' => 0],
                    ['name' => 'Cửa sổ thoáng mát', 'description' => 'Cửa sổ lớn đón ánh sáng tự nhiên', 'price' => 0],
                    ['name' => 'Cách âm tốt', 'description' => 'Hệ thống cách âm cao cấp', 'price' => 0],
                    ['name' => 'View đẹp', 'description' => 'View hướng thành phố, biển, vườn, hồ', 'price' => 0],
                    ['name' => 'Hệ thống đèn trang trí cao cấp', 'description' => 'Đèn trang trí và chiếu sáng thông minh', 'price' => 0],
                    ['name' => 'Thiết kế căn hộ mini', 'description' => 'Thiết kế dạng căn hộ mini với khu vực tiếp khách riêng', 'price' => 0],
                    ['name' => 'Ban công lớn', 'description' => 'Ban công lớn với view toàn cảnh', 'price' => 0],
                    ['name' => 'Hệ thống cách âm tuyệt đối', 'description' => 'Cách âm tuyệt đối', 'price' => 0],
                    ['name' => 'Điều hòa trung tâm', 'description' => 'Hệ thống điều hòa trung tâm', 'price' => 0],
                    ['name' => 'Nội thất cao cấp', 'description' => 'Nội thất cao cấp và sang trọng', 'price' => 0],
                    ['name' => 'Không gian rộng rãi', 'description' => 'Không gian rộng rãi cho cả gia đình', 'price' => 0],
                    ['name' => 'Khu vực ngồi chung', 'description' => 'Khu vực ngồi chung cho gia đình', 'price' => 0],
                ]
            ],
            [
                'name' => 'Tiện nghi trong phòng',
                'services' => [
                    ['name' => 'Giường ngủ thoải mái', 'description' => 'Giường ngủ chất lượng cao', 'price' => 0],
                    ['name' => 'Tủ quần áo', 'description' => 'Tủ quần áo rộng rãi', 'price' => 0],
                    ['name' => 'Tủ lạnh mini', 'description' => 'Tủ lạnh mini trong phòng', 'price' => 0],
                    ['name' => 'Wifi miễn phí', 'description' => 'Kết nối wifi miễn phí tốc độ cao', 'price' => 0],
                    ['name' => 'Nước uống miễn phí', 'description' => 'Nước uống miễn phí hàng ngày', 'price' => 0],
                    ['name' => 'Ấm siêu tốc', 'description' => 'Ấm siêu tốc để pha trà, cà phê', 'price' => 0],
                    ['name' => 'Đồ ăn nhẹ miễn phí', 'description' => 'Mì ly và đồ ăn nhẹ miễn phí', 'price' => 0],
                    ['name' => 'Bàn làm việc', 'description' => 'Bàn làm việc tiện nghi', 'price' => 0],
                    ['name' => 'Trà & cà phê', 'description' => 'Trà và cà phê miễn phí', 'price' => 0],
                    ['name' => 'Tivi màn hình phẳng', 'description' => 'Tivi màn hình phẳng', 'price' => 0],
                    ['name' => 'Sofa thư giãn', 'description' => 'Sofa thư giãn thoải mái', 'price' => 0],
                    ['name' => 'Minibar', 'description' => 'Minibar đầy đủ', 'price' => 0],
                    ['name' => 'Smart TV', 'description' => 'Smart TV với nhiều kênh', 'price' => 0],
                    ['name' => 'Bàn làm việc lớn', 'description' => 'Bàn làm việc lớn và tiện nghi', 'price' => 0],
                    ['name' => 'Két an toàn', 'description' => 'Két an toàn để cất giữ đồ quý', 'price' => 0],
                    ['name' => 'Ly tách, cốc sứ', 'description' => 'Ly tách, cốc sứ cao cấp', 'price' => 0],
                    ['name' => 'Khu vực tiếp khách', 'description' => 'Khu vực tiếp khách với sofa', 'price' => 0],
                    ['name' => 'Tivi 2 khu vực', 'description' => 'Tivi ở 2 khu vực riêng biệt', 'price' => 0],
                    ['name' => 'Bàn làm việc riêng', 'description' => 'Bàn làm việc riêng biệt', 'price' => 0],
                    ['name' => 'Minibar đầy đủ', 'description' => 'Minibar với đầy đủ đồ uống', 'price' => 0],
                    ['name' => 'Máy pha cà phê', 'description' => 'Máy pha cà phê tự động', 'price' => 0],
                    ['name' => 'Điện thoại', 'description' => 'Điện thoại trong phòng', 'price' => 0],
                    ['name' => 'Đèn ngủ', 'description' => 'Đèn ngủ tiện nghi', 'price' => 0],
                    ['name' => 'Đèn đọc sách', 'description' => 'Đèn đọc sách chuyên dụng', 'price' => 0],
                    ['name' => 'Hệ thống chiếu sáng thông minh', 'description' => 'Hệ thống chiếu sáng thông minh', 'price' => 0],
                    ['name' => 'Bàn ăn nhỏ', 'description' => 'Bàn ăn nhỏ hoặc sofa', 'price' => 0],
                    ['name' => 'Tivi màn hình lớn', 'description' => 'Tivi màn hình lớn cho gia đình', 'price' => 0],
                    ['name' => 'Tủ quần áo lớn', 'description' => 'Tủ quần áo lớn cho gia đình', 'price' => 0],
                ]
            ],
            [
                'name' => 'Tiện nghi phòng tắm',
                'services' => [
                    ['name' => 'Vòi sen và bồn tắm', 'description' => 'Vòi sen và bồn tắm tiện nghi', 'price' => 0],
                    ['name' => 'Khăn tắm sạch', 'description' => 'Khăn tắm sạch sẽ', 'price' => 0],
                    ['name' => 'Áo choàng tắm', 'description' => 'Áo choàng tắm cao cấp', 'price' => 0],
                    ['name' => 'Máy sấy tóc', 'description' => 'Máy sấy tóc tiện nghi', 'price' => 0],
                    ['name' => 'Hệ thống nước nóng lạnh', 'description' => 'Hệ thống nước nóng lạnh', 'price' => 0],
                    ['name' => 'Bộ đồ vệ sinh cá nhân', 'description' => 'Bộ đồ vệ sinh cá nhân miễn phí (xà phòng, dầu gội, kem đánh răng)', 'price' => 0],
                    ['name' => 'Vòi sen hoặc bồn tắm', 'description' => 'Vòi sen hoặc bồn tắm', 'price' => 0],
                    ['name' => 'Bồn tắm đứng', 'description' => 'Bồn tắm đứng hoặc bồn tắm nằm', 'price' => 0],
                    ['name' => 'Vòi sen đa chế độ', 'description' => 'Vòi sen đa chế độ', 'price' => 0],
                    ['name' => 'Dép đi trong phòng', 'description' => 'Dép đi trong phòng', 'price' => 0],
                    ['name' => 'Gương trang điểm', 'description' => 'Gương trang điểm', 'price' => 0],
                    ['name' => 'Bộ đồ vệ sinh cao cấp', 'description' => 'Bộ đồ vệ sinh cá nhân cao cấp', 'price' => 0],
                    ['name' => 'Phòng tắm riêng biệt', 'description' => 'Phòng tắm riêng biệt (vòi sen & bồn tắm riêng)', 'price' => 0],
                    ['name' => 'Bồn tắm sục', 'description' => 'Bồn tắm sục (Jacuzzi)', 'price' => 0],
                    ['name' => 'Khăn lông dày', 'description' => 'Khăn lông dày, áo choàng, dép đi trong nhà', 'price' => 0],
                    ['name' => 'Máy sấy tóc công suất lớn', 'description' => 'Máy sấy tóc công suất lớn', 'price' => 0],
                    ['name' => 'Bồn tắm lớn', 'description' => 'Bồn tắm lớn hoặc vòi sen', 'price' => 0],
                    ['name' => 'Khăn tắm đầy đủ', 'description' => 'Khăn tắm đầy đủ cho cả gia đình', 'price' => 0],
                ]
            ]
        ];

        // Tạo danh mục và dịch vụ
        foreach ($categories as $categoryData) {
            $category = ServiceCategory::create([
                'name' => $categoryData['name']
            ]);

            foreach ($categoryData['services'] as $serviceData) {
                Service::create([
                    'name' => $serviceData['name'],
                    'description' => $serviceData['description'],
                    'price' => $serviceData['price'],
                    'service_category_id' => $category->id
                ]);
            }
        }

        $this->command->info('Đã tạo thành công ' . count($categories) . ' danh mục dịch vụ và các dịch vụ tương ứng!');
    }
}
