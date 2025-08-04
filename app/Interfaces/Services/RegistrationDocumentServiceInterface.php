<?php

namespace App\Interfaces\Services;

use App\Models\Booking;

interface RegistrationDocumentServiceInterface
{
    /**
     * Tạo giấy đăng ký tạm chú tạm vắng cho booking
     *
     * @param Booking $booking
     * @return string|null
     */
    public function generateRegistrationDocument(Booking $booking): ?string;
} 