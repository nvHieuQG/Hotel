<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateQRCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate QR codes for bank transfer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating QR codes for bank transfer...');

        $banks = [
            'vietcombank' => [
                'name' => 'Vietcombank',
                'account_number' => '1234567890',
                'account_name' => 'CONG TY TNHH KHACH SAN MARRON',
                'transfer_content' => 'Thanh toan dat phong'
            ],
            'bidv' => [
                'name' => 'BIDV',
                'account_number' => '9876543210',
                'account_name' => 'CONG TY TNHH KHACH SAN MARRON',
                'transfer_content' => 'Thanh toan dat phong'
            ],
            'techcombank' => [
                'name' => 'Techcombank',
                'account_number' => '1122334455',
                'account_name' => 'CONG TY TNHH KHACH SAN MARRON',
                'transfer_content' => 'Thanh toan dat phong'
            ]
        ];

        foreach ($banks as $bankKey => $bank) {
            $this->info("Creating QR code placeholder for {$bank['name']}...");

            // Tạo file placeholder cho QR code
            $filename = "public/images/qr-{$bankKey}.png";
            $this->createQRPlaceholder($filename, $bank);

            $this->info("QR code placeholder created: {$filename}");
        }

        $this->info('All QR code placeholders created successfully!');
        $this->info('Note: In production, you should generate actual QR codes using a QR code library.');
    }

    /**
     * Create QR code placeholder
     */
    private function createQRPlaceholder(string $filename, array $bank): void
    {
        // Tạo một file PNG đơn giản với thông tin ngân hàng
        $width = 300;
        $height = 300;

        // Tạo image resource
        $image = imagecreate($width, $height);

        // Đặt màu
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $gray = imagecolorallocate($image, 128, 128, 128);

        // Vẽ background trắng
        imagefill($image, 0, 0, $white);

        // Vẽ khung
        imagerectangle($image, 10, 10, $width - 10, $height - 10, $black);

        // Vẽ text
        $fontSize = 3;
        $text = "QR Code\n{$bank['name']}\n{$bank['account_number']}";
        $lines = explode("\n", $text);
        $y = 50;

        foreach ($lines as $line) {
            $x = ($width - strlen($line) * imagefontwidth($fontSize)) / 2;
            imagestring($image, $fontSize, $x, $y, $line, $black);
            $y += imagefontheight($fontSize) + 5;
        }

        // Vẽ thông tin bổ sung
        $infoText = "Placeholder QR Code";
        $x = ($width - strlen($infoText) * imagefontwidth(2)) / 2;
        imagestring($image, 2, $x, $height - 40, $infoText, $gray);

        // Lưu file
        imagepng($image, $filename);
        imagedestroy($image);
    }
}
