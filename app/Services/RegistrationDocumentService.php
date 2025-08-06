<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Interfaces\Services\RegistrationDocumentServiceInterface;
use Barryvdh\DomPDF\Facade\Pdf;

class RegistrationDocumentService implements RegistrationDocumentServiceInterface
{
    /**
     * Tạo giấy đăng ký tạm chú tạm vắng cho booking
     *
     * @param Booking $booking
     * @return string|null
     */
    public function generateRegistrationDocument(Booking $booking): ?string
    {
        try {
            // Xóa file PDF cũ của booking này trước
            $this->deleteOldPdfFiles($booking->booking_id);
            
            // Tạo nội dung HTML cho giấy đăng ký
            $html = $this->generateRegistrationHtml($booking);
            
            // Tạo tên file PDF
            $pdfFilename = 'registration_' . $booking->booking_id . '_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdfFilepath = 'registrations/' . $pdfFilename;
            $pdfPath = storage_path('app/public/' . $pdfFilepath);
            
            // Tạo thư mục nếu chưa có
            Storage::disk('public')->makeDirectory('registrations');
            
            // Tạo file PDF thực sự bằng dompdf
            $this->createPdfFromHtml($html, $pdfPath, $booking->guest_id_number);
            
            // Cập nhật trạng thái booking
            $booking->update([
                'registration_status' => 'generated',
                'registration_generated_at' => now(),
            ]);

            Log::info('Generated registration PDF for booking: ' . $booking->booking_id);

            return 'public/' . $pdfFilepath;
        } catch (\Exception $e) {
            Log::error('Error generating registration PDF: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Tạo nội dung HTML cho giấy đăng ký
     */
    private function generateRegistrationHtml(Booking $booking): string
    {
        $checkInDate = Carbon::parse($booking->check_in_date);
        $checkOutDate = Carbon::parse($booking->check_out_date);
        $nights = $checkInDate->diffInDays($checkOutDate);
        
        $html = '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giấy đăng ký tạm chú tạm vắng</title>
    <style>
        @page { 
            size: A4; 
            margin: 2cm; 
        }
        body { 
            font-family: "DejaVu Sans", "Arial Unicode MS", "Arial", sans-serif; 
            font-size: 12pt; 
            line-height: 1.5; 
            margin: 0; 
            padding: 20px; 
        }
        .form-number {
            text-align: right;
            font-size: 10pt;
            margin-bottom: 10px;
        }
        .header { 
            text-align: center; 
            font-weight: bold; 
            font-size: 16pt; 
            margin: 20px 0; 
        }
        .title { 
            text-align: center; 
            font-weight: bold; 
            font-size: 14pt; 
            margin: 15px 0; 
        }
        .info { 
            font-weight: bold; 
            text-indent: 20px; 
            margin: 10px 0; 
        }
        .normal { 
            text-indent: 20px; 
            margin: 10px 0; 
        }
        .password-info { 
            background-color: #f0f0f0; 
            padding: 10px; 
            margin: 20px 0; 
            border: 1px solid #ccc; 
        }
        .footer { 
            text-align: center; 
            margin-top: 30px; 
            font-size: 10pt; 
            color: #666; 
        }
        .signature-section {
            margin-top: 30px;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 200px;
            display: inline-block;
            margin-top: 50px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .table th, .table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 9pt;
        }
        .table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .dotted-line {
            border-bottom: 1px dotted #000;
            height: 15px;
            margin: 5px 0;
        }
        .footnotes {
            margin-top: 20px;
            font-size: 10pt;
        }
        .footnote {
            margin-bottom: 5px;
            text-indent: 20px;
        }
    </style>
</head>
<body>';

        // Thêm thông tin mật khẩu
        // $html .= '<div class="password-info">
        //     <strong>📋 Thông tin mở file:</strong><br>
        //     Mật khẩu: <strong>' . htmlspecialchars($booking->guest_id_number) . '</strong><br>
        //     Lưu ý: Sử dụng mật khẩu này để mở file PDF
        // </div>';

        // Header chính thức theo mẫu CT01
        $html .= '<div class="form-number">Mẫu CT01 ban hành theo TT số 56/2021/TT-BCA ngày 15/5/2021</div>';
        $html .= '<div class="header">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>';
        $html .= '<div class="header">Độc lập – Tự do – Hạnh phúc</div>';
        $html .= '<div class="title">TỜ KHAI THAY ĐỔI THÔNG TIN CƯ TRÚ</div>';

        // Kính gửi
        $html .= '<div class="info">Kính gửi(1):</div>';
        $html .= '<div class="normal">Công an phường/xã/thị trấn</div>';

        // Thông tin người kê khai
        $html .= '<div class="title">THÔNG TIN NGƯỜI KÊ KHAI</div>';
        $html .= '<div class="info">1. Họ, chữ đệm và tên:</div>';
        $html .= '<div class="normal">' . htmlspecialchars($booking->guest_full_name) . '</div>';
        $html .= '<div class="info">2. Ngày, tháng, năm sinh:</div>';
        $html .= '<div class="normal">' . htmlspecialchars($booking->guest_birth_date) . '</div>';
        $html .= '<div class="info">3. Giới tính:</div>';
        $html .= '<div class="normal">' . htmlspecialchars($booking->guest_gender) . '</div>';
        $html .= '<div class="info">4. Số định danh cá nhân/CMND:</div>';
        $html .= '<div class="normal">' . htmlspecialchars($booking->guest_id_number) . '</div>';
        $html .= '<div class="info">5. Số điện thoại liên hệ:</div>';
        $html .= '<div class="normal">' . htmlspecialchars($booking->guest_phone) . '</div>';
        $html .= '<div class="info">6. Email:</div>';
        $html .= '<div class="normal">' . htmlspecialchars($booking->guest_email) . '</div>';
        $html .= '<div class="info">7. Nơi thường trú:</div>';
        $html .= '<div class="normal">' . htmlspecialchars($booking->guest_permanent_address) . '</div>';
        $html .= '<div class="info">8. Nơi tạm trú:</div>';
        $html .= '<div class="normal">' . htmlspecialchars($booking->guest_current_address ?? 'N/A') . '</div>';
        $html .= '<div class="info">9. Nơi ở hiện tại:</div>';
        $html .= '<div class="normal">' . htmlspecialchars($booking->hotel_name . ', ' . $booking->room_number) . '</div>';
        $html .= '<div class="info">10. Nghề nghiệp, nơi làm việc:</div>';
        $html .= '<div class="normal">' . htmlspecialchars($booking->guest_occupation) . '</div>';
        $html .= '<div class="info">11. Họ, chữ đệm và tên chủ hộ:</div>';
        $html .= '<div class="normal">Nguyễn Văn A</div>';
        $html .= '<div class="info">12. Quan hệ với chủ hộ:</div>';
        $html .= '<div class="normal">Khách lưu trú</div>';
        $html .= '<div class="info">13. Số định danh cá nhân/CMND của chủ hộ:</div>';
        $html .= '<div class="normal">012345678901</div>';

        // Nội dung đề nghị
        $requestText = 'Đăng ký tạm trú tại ' . htmlspecialchars($booking->hotel_name) . ' từ ngày ' . 
                      $checkInDate->format('d/m/Y') . ' đến ngày ' . 
                      $checkOutDate->format('d/m/Y') . '.';
        
        $html .= '<div class="title">NỘI DUNG ĐỀ NGHỊ</div>';
        $html .= '<div class="normal">' . $requestText . '</div>';
        $html .= '<div class="dotted-line"></div>';
        $html .= '<div class="dotted-line"></div>';
        $html .= '<div class="dotted-line"></div>';
        $html .= '<div class="dotted-line"></div>';

        // Bảng thành viên gia đình
        $html .= '<div class="title">15. NHỮNG THÀNH VIÊN TRONG HỘ GIA ĐÌNH CÙNG THAY ĐỔI</div>';
        $html .= '<table class="table">
            <thead>
                <tr>
                    <th>TT</th>
                    <th>Họ, chữ đệm và tên</th>
                    <th>Ngày, tháng, năm sinh</th>
                    <th>Giới tính</th>
                    <th>Số định danh cá nhân/CMND</th>
                    <th>Nghề nghiệp, nơi làm việc</th>
                    <th>Quan hệ với người có thay đổi</th>
                    <th>Quan hệ với chủ hộ</th>
                </tr>
            </thead>
            <tbody>';
        
        for ($i = 1; $i <= 5; $i++) {
            $html .= '
                <tr>
                    <td>' . $i . '</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>';
        }
        
        $html .= '
            </tbody>
        </table>';

        // Phần chữ ký
        $html .= '<table class="table" style="margin-top: 20px;">
            <tr>
                <td>
                    <div class="signature-section">
                        <div class="normal">.., ngày…. tháng ... năm …</div>
                        <div class="info">Ý KIẾN CỦA CHỦ HỘ(3)</div>
                        <div class="normal">(Ghi rõ nội dung, ký ghi rõ họ tên)</div>
                        <div class="signature-line"></div>
                        <div class="normal">Nguyễn Văn A</div>
                        <div class="normal">Giám đốc</div>
                    </div>
                </td>
                <td>
                    <div class="signature-section">
                        <div class="normal">.., ngày…. tháng ... năm …</div>
                        <div class="info">Ý KIẾN CỦA CHỦ SỞ HỮU HOẶC NGƯỜI ĐẠI DIỆN CHỖ Ở HỢP PHÁP(3)</div>
                        <div class="normal">(Ký, ghi rõ họ tên)</div>
                        <div class="signature-line"></div>
                        <div class="normal">Nguyễn Văn A</div>
                        <div class="normal">Giám đốc</div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="signature-section">
                        <div class="normal">.., ngày…. tháng ... năm …</div>
                        <div class="info">Ý KIẾN CỦA CHA, MẸ HOẶC NGƯỜI GIÁM HỘ(4)</div>
                        <div class="normal">(Ký, ghi rõ họ tên)</div>
                        <div class="signature-line"></div>
                        <div class="normal"></div>
                        <div class="normal"></div>
                    </div>
                </td>
                <td>
                    <div class="signature-section">
                        <div class="normal">.., ngày…. tháng ... năm …</div>
                        <div class="info">NGƯỜI KÊ KHAI</div>
                        <div class="normal">(Ký, ghi rõ họ tên)</div>
                        <div class="signature-line"></div>
                        <div class="normal">' . htmlspecialchars($booking->guest_full_name) . '</div>
                        <div class="normal">Khách lưu trú</div>
                    </div>
                </td>
            </tr>
        </table>';

        // Chú thích
        $html .= '<div class="footnotes">
            <div class="title">Chú thích:</div>
            <div class="footnote">(1) Cơ quan đăng ký cư trú</div>
            <div class="footnote">(2) Ghi rõ ràng, cụ thể nội dung đề nghị. Ví dụ: đăng ký thường trú; đăng ký tạm trú; tách hộ; xác nhận thông tin về cư trú...</div>
            <div class="footnote">(3) Áp dụng đối với các trường hợp quy định tại khoản 2, khoản 3, khoản 4, khoản 5, khoản 6 Điều 20; khoản 1 Điều 25 Luật Cư trú</div>
            <div class="footnote">(4) Áp dụng đối với trường hợp người chưa thành niên, người hạn chế hành vi dân sự, người không đủ năng lực hành vi dân sự có thay đổi thông tin về cư trú</div>
        </div>';

        $html .= '</body></html>';
        
        return $html;
    }
    
    /**
     * Tạo file PDF từ HTML bằng dompdf
     */
    private function createPdfFromHtml(string $html, string $outputPath, string $password): void
    {
        try {
            // Tạo PDF bằng dompdf với cấu hình đơn giản
            $pdf = Pdf::loadHTML($html);
            
            // Cấu hình PDF cơ bản với font Unicode
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
                'chroot' => storage_path('app/public'),
                'tempDir' => storage_path('app/temp'),
                'enableFontSubsetting' => false,
                'pdfBackend' => 'CPDF',
                'defaultMediaType' => 'screen',
                'defaultPaperSize' => 'a4',
                'defaultPaperOrientation' => 'portrait',
                'dpi' => 96,
                'fontHeightRatio' => 0.9,
                'enableFontSubsetting' => false,
                'enableCssFloat' => true,
                'enableJavascript' => false,
                'enablePhp' => false,
                'enableRemoteFileAccess' => false,
                'fontCache' => storage_path('app/fonts'),
                'tempDir' => storage_path('app/temp'),
                'logOutputFile' => storage_path('logs/dompdf.log'),
                'defaultFont' => 'DejaVu Sans',
                'fontDir' => storage_path('app/fonts'),
                'fontCache' => storage_path('app/fonts'),
            ]);
            
            // Lưu file PDF
            $pdf->save($outputPath);
            
            // Kiểm tra file đã được tạo
            if (!file_exists($outputPath)) {
                throw new \Exception('PDF file was not created');
            }
            
            // Kiểm tra kích thước file
            $fileSize = filesize($outputPath);
            if ($fileSize < 1000) {
                throw new \Exception('PDF file is too small: ' . $fileSize . ' bytes');
            }
            
        } catch (\Exception $e) {
            Log::error('Error creating PDF with dompdf: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Xóa file PDF cũ của booking
     *
     * @param string $bookingId
     * @return void
     */
    private function deleteOldPdfFiles(string $bookingId): void
    {
        try {
            $files = Storage::disk('public')->files('registrations');
            
            foreach ($files as $file) {
                // Kiểm tra xem file có phải của booking này không và có đuôi .pdf không
                if (strpos($file, 'registration_' . $bookingId . '_') !== false && 
                    strpos($file, '.pdf') !== false) {
                    
                    // Xóa file cũ
                    Storage::disk('public')->delete($file);
                    Log::info('Deleted old PDF file: ' . $file);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error deleting old PDF files: ' . $e->getMessage());
        }
    }
} 