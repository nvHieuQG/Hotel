<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Tờ khai thay đổi thông tin cư trú</title>
    <link rel="stylesheet" href="{{ asset('css/registration-template.css') }}">
</head>
<body>
    <!-- Header chính thức -->
    <div class="header-official">
        <div class="header-top">
            <div class="form-number">Mẫu CT01 ban hành theo TT số 56/2021/TT-BCA ngày 15/5/2021</div>
        </div>
        
        <div class="national-header">
            <div class="country-name">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</div>
            <div class="motto">Độc lập – Tự do – Hạnh phúc</div>
        </div>
        
        <div class="main-title">
            TỜ KHAI THAY ĐỔI THÔNG TIN CƯ TRÚ
        </div>
    </div>

    <!-- Kính gửi -->
    <div class="recipient-section">
        <div class="recipient-line">
            <span class="label">Kính gửi(1):</span>
            <span class="value">Công an phường/xã/thị trấn</span>
        </div>
    </div>

    <!-- Thông tin người kê khai -->
    <div class="section">
        <div class="section-title">THÔNG TIN NGƯỜI KÊ KHAI</div>
        
        <div class="info-grid">
            <div class="info-row">
                <div class="label">1. Họ, chữ đệm và tên:</div>
                <div class="value">{{ $booking->guest_full_name }}</div>
            </div>
            
            <div class="info-row">
                <div class="label">2. Ngày, tháng, năm sinh:</div>
                <div class="value">{{ $booking->guest_birth_date->format('d/m/Y') }}</div>
                <div class="label-inline">3. Giới tính:</div>
                <div class="value-inline">{{ $booking->guest_gender }}</div>
            </div>
            
            <div class="info-row">
                <div class="label">4. Số định danh cá nhân/CMND:</div>
                <div class="value">{{ $booking->guest_id_number }}</div>
            </div>
            
            <div class="info-row">
                <div class="label">5. Số điện thoại liên hệ:</div>
                <div class="value">{{ $booking->guest_phone ?? 'N/A' }}</div>
                <div class="label-inline">6. Email:</div>
                <div class="value-inline">{{ $booking->guest_email ?? 'N/A' }}</div>
            </div>
            
            <div class="info-row">
                <div class="label">7. Nơi thường trú:</div>
                <div class="value">{{ $booking->guest_permanent_address }}</div>
            </div>
            
            <div class="info-row">
                <div class="label">8. Nơi tạm trú:</div>
                <div class="value">{{ $booking->guest_current_address ?? 'N/A' }}</div>
            </div>
            
            <div class="info-row">
                <div class="label">9. Nơi ở hiện tại:</div>
                <div class="value">{{ $hotelInfo['address'] }}</div>
            </div>
            
            <div class="info-row">
                <div class="label">10. Nghề nghiệp, nơi làm việc:</div>
                <div class="value">{{ $booking->guest_purpose_of_stay ?? 'Du lịch' }}</div>
            </div>
            
            <div class="info-row">
                <div class="label">11. Họ, chữ đệm và tên chủ hộ:</div>
                <div class="value">{{ $hotelInfo['representative'] }}</div>
                <div class="label-inline">12. Quan hệ với chủ hộ:</div>
                <div class="value-inline">Khách lưu trú</div>
            </div>
            
            <div class="info-row">
                <div class="label">13. Số định danh cá nhân/CMND của chủ hộ:</div>
                <div class="value">{{ $hotelInfo['representative_id'] ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Nội dung đề nghị -->
    <div class="section">
        <div class="section-title">NỘI DUNG ĐỀ NGHỊ</div>
        <div class="request-content">
            <div class="request-text">
                Đăng ký tạm trú tại {{ $hotelInfo['name'] }} từ ngày {{ $booking->check_in_date->format('d/m/Y') }} đến ngày {{ $booking->check_out_date->format('d/m/Y') }}.
            </div>
            <div class="request-lines">
                <div class="line">................................................................................</div>
                <div class="line">................................................................................</div>
                <div class="line">................................................................................</div>
                <div class="line">................................................................................</div>
            </div>
        </div>
    </div>

    <!-- Thông tin thành viên gia đình -->
    <div class="section">
        <div class="section-title">15. NHỮNG THÀNH VIÊN TRONG HỘ GIA ĐÌNH CÙNG THAY ĐỔI</div>
        <table class="family-table">
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
            <tbody>
                <tr>
                    <td>1</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>2</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>3</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>4</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>5</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Phần chữ ký -->
    <div class="signature-section">
        <div class="signature-grid">
            <!-- Ý kiến chủ hộ -->
            <div class="signature-block">
                <div class="date-line">.., ngày…. tháng ... năm …</div>
                <div class="signature-title">Ý KIẾN CỦA CHỦ HỘ(3)</div>
                <div class="signature-note">(Ghi rõ nội dung, ký ghi rõ họ tên)</div>
                <div class="signature-content">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $hotelInfo['representative'] }}</div>
                    <div class="signature-position">{{ $hotelInfo['representative_position'] }}</div>
                </div>
            </div>

            <!-- Ý kiến chủ sở hữu -->
            <div class="signature-block">
                <div class="date-line">.., ngày…. tháng ... năm …</div>
                <div class="signature-title">Ý KIẾN CỦA CHỦ SỞ HỮU HOẶC NGƯỜI ĐẠI DIỆN CHỖ Ở HỢP PHÁP(3)</div>
                <div class="signature-note">(Ký, ghi rõ họ tên)</div>
                <div class="signature-content">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $hotelInfo['representative'] }}</div>
                    <div class="signature-position">{{ $hotelInfo['representative_position'] }}</div>
                </div>
            </div>

            <!-- Ý kiến cha mẹ -->
            <div class="signature-block">
                <div class="date-line">.., ngày…. tháng ... năm …</div>
                <div class="signature-title">Ý KIẾN CỦA CHA, MẸ HOẶC NGƯỜI GIÁM HỘ(4)</div>
                <div class="signature-note">(Ký, ghi rõ họ tên)</div>
                <div class="signature-content">
                    <div class="signature-line"></div>
                    <div class="signature-name"></div>
                    <div class="signature-position"></div>
                </div>
            </div>

            <!-- Người kê khai -->
            <div class="signature-block">
                <div class="date-line">.., ngày…. tháng ... năm …</div>
                <div class="signature-title">NGƯỜI KÊ KHAI</div>
                <div class="signature-note">(Ký, ghi rõ họ tên)</div>
                <div class="signature-content">
                    <div class="signature-line"></div>
                    <div class="signature-name">{{ $booking->guest_full_name }}</div>
                    <div class="signature-position">Khách lưu trú</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chú thích -->
    <div class="footnotes">
        <div class="footnote-title">Chú thích:</div>
        <div class="footnote-item">(1) Cơ quan đăng ký cư trú</div>
        <div class="footnote-item">(2) Ghi rõ ràng, cụ thể nội dung đề nghị. Ví dụ: đăng ký thường trú; đăng ký tạm trú; tách hộ; xác nhận thông tin về cư trú...</div>
        <div class="footnote-item">(3) Áp dụng đối với các trường hợp quy định tại khoản 2, khoản 3, khoản 4, khoản 5, khoản 6 Điều 20; khoản 1 Điều 25 Luật Cư trú</div>
        <div class="footnote-item">(4) Áp dụng đối với trường hợp người chưa thành niên, người hạn chế hành vi dân sự, người không đủ năng lực hành vi dân sự có thay đổi thông tin về cư trú</div>
    </div>

    <!-- Thông tin tạo -->
    <div class="creation-info">
        <p>Ngày tạo: {{ now()->format('d/m/Y H:i') }}</p>
        <p>Người tạo: {{ auth()->user()->name ?? 'Hệ thống' }}</p>
    </div>
</body>
</html> 