<?php

namespace App\Services;

use App\Models\TourBooking;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class TourVatInvoiceService
{
    public function generateVatInvoice(TourBooking $tourBooking): ?string
    {
        try {
            // Xóa file VAT cũ của tour booking
            $this->deleteOldPdfFiles($tourBooking->id);

            // Tính toán giá trị
            $nights = $tourBooking->check_in_date && $tourBooking->check_out_date 
                ? $tourBooking->check_in_date->copy()->startOfDay()->diffInDays($tourBooking->check_out_date->copy()->startOfDay()) 
                : 0;
            
            $roomCost = $tourBooking->total_rooms_amount ?? 0;
            $services = $tourBooking->total_services_amount ?? 0;
            $discount = $tourBooking->promotion_discount ?? 0;
            
            // Tổng cộng đã bao gồm VAT 10%
            $grandTotal = $roomCost + $services - $discount;
            
            // Tính ngược lại: giá trước VAT = tổng cộng / (1 + VAT rate)
            $vatRate = 0.1;
            $subtotal = round($grandTotal / (1 + $vatRate));
            $vatAmount = $grandTotal - $subtotal;

            // Chuẩn bị thông tin công ty
            $info = [
                'companyName' => $tourBooking->company_name ?? 'N/A',
                'taxCode' => $tourBooking->company_tax_code ?? 'N/A',
                'companyAddress' => $tourBooking->company_address ?? 'N/A',
                'receiverEmail' => $tourBooking->company_email ?? $tourBooking->user->email ?? 'N/A',
                'receiverName' => $tourBooking->company_name ?? $tourBooking->user->name ?? 'N/A',
            ];

            $html = view('emails.tour-vat-invoice-template', compact(
                'tourBooking', 'info', 'nights', 'roomCost', 'discount', 
                'services', 'subtotal', 'vatRate', 'vatAmount', 'grandTotal'
            ))->render();

            // Lưu PDF với cấu hình giống luồng booking thường
            $filename = 'tour_vat_invoice_' . $tourBooking->id . '_' . date('Y-m-d_H-i-s') . '.pdf';
            $pathRel = 'vat_invoices/' . $filename;
            $pathAbs = storage_path('app/public/' . $pathRel);
            Storage::disk('public')->makeDirectory('vat_invoices');
            $this->createPdfFromHtml($html, $pathAbs);

            // Cập nhật file path vào database
            $tourBooking->update([
                'vat_invoice_file_path' => 'public/' . $pathRel,
                'vat_invoice_generated_at' => now(),
            ]);

            Log::info('Tour VAT invoice PDF created successfully', [
                'tour_booking_id' => $tourBooking->id,
                'file_path' => 'public/' . $pathRel,
                'full_path' => $pathAbs,
                'file_exists' => file_exists($pathAbs),
                'file_size' => file_exists($pathAbs) ? filesize($pathAbs) : 0
            ]);

            return 'public/' . $pathRel;
        } catch (\Throwable $e) {
            Log::error('Tour VAT invoice generation failed: '.$e->getMessage());
            return null;
        }
    }

    public function sendVatInvoiceEmail(TourBooking $tourBooking): bool
    {
        try {
            if (empty($tourBooking->vat_invoice_file_path)) {
                $filePath = $this->generateVatInvoice($tourBooking);
                if (!$filePath) {
                    Log::error('Failed to generate VAT invoice for email', [
                        'tour_booking_id' => $tourBooking->id
                    ]);
                    return false;
                }
                $tourBooking->refresh(); // Refresh để lấy file path mới
            }
            
            if (empty($tourBooking->vat_invoice_file_path)) {
                Log::error('VAT invoice file path is empty after generation', [
                    'tour_booking_id' => $tourBooking->id
                ]);
                return false;
            }

            $info = [
                'receiverEmail' => $tourBooking->company_email ?? $tourBooking->user->email,
                'receiverName' => $tourBooking->company_name ?? $tourBooking->user->name,
            ];

            Log::info('Attempting to send VAT invoice email', [
                'tour_booking_id' => $tourBooking->id,
                'receiver_email' => $info['receiverEmail'],
                'file_path' => $tourBooking->vat_invoice_file_path
            ]);

            Mail::send('emails.tour-vat-invoice', [
                'tourBooking' => $tourBooking,
                'info' => $info,
            ], function ($message) use ($info, $tourBooking) {
                $message->to($info['receiverEmail'], $info['receiverName'])
                    ->subject('Hóa đơn VAT cho Tour Booking ' . $tourBooking->booking_code);
                $attachment = storage_path('app/' . $tourBooking->vat_invoice_file_path);
                if (is_file($attachment)) {
                    $message->attach($attachment);
                    Log::info('VAT invoice attachment added to email', [
                        'tour_booking_id' => $tourBooking->id,
                        'attachment_path' => $attachment,
                        'attachment_size' => filesize($attachment)
                    ]);
                } else {
                    Log::warning('VAT invoice attachment file not found', [
                        'tour_booking_id' => $tourBooking->id,
                        'attachment_path' => $attachment
                    ]);
                }
            });

            $tourBooking->update([
                'vat_invoice_sent_at' => now(),
            ]);
            
            Log::info('VAT invoice email sent successfully', [
                'tour_booking_id' => $tourBooking->id,
                'receiver_email' => $info['receiverEmail']
            ]);
            
            return true;
        } catch (\Throwable $e) {
            Log::error('Send Tour VAT invoice failed: '.$e->getMessage(), [
                'tour_booking_id' => $tourBooking->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    private function deleteOldPdfFiles(int $tourBookingId): void
    {
        try {
            $files = Storage::disk('public')->files('vat_invoices');
            foreach ($files as $file) {
                if (str_contains($file, 'tour_vat_invoice_' . $tourBookingId . '_') && str_ends_with($file, '.pdf')) {
                    Storage::disk('public')->delete($file);
                    Log::info('Deleted old Tour VAT invoice file: ' . $file);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Cannot delete old Tour VAT invoice files: '.$e->getMessage());
        }
    }

    private function createPdfFromHtml(string $html, string $outputPath): void
    {
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 96,
        ]);
        $pdf->save($outputPath);
        if (!file_exists($outputPath)) {
            throw new \RuntimeException('Tour VAT PDF was not created');
        }
    }
}
