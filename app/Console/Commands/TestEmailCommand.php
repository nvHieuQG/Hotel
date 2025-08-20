<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmailCommand extends Command
{
    protected $signature = 'email:test';
    protected $description = 'Test gửi email đơn giản';

    public function handle()
    {
        try {
            $this->info('Đang test gửi email...');
            
            Mail::raw('Test email từ Laravel', function($message) {
                $message->to('test@example.com')
                        ->subject('Test Email');
            });
            
            $this->info('✅ Email đã được gửi thành công!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Lỗi gửi email: ' . $e->getMessage());
            return 1;
        }
    }
}
