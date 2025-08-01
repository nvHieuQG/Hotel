<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BankTransferTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $booking;
    protected $paymentService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'total_booking_price' => 1000000,
            'status' => 'pending'
        ]);

        $this->paymentService = app(PaymentService::class);
    }

    /** @test */
    public function user_can_access_bank_transfer_page()
    {
        $response = $this->actingAs($this->user)
            ->get("/payment/bank-transfer/{$this->booking->id}");

        $response->assertStatus(200);
        $response->assertSee('Thông tin chuyển khoản');
        $response->assertSee('Vietcombank');
        $response->assertSee('BIDV');
        $response->assertSee('Techcombank');
    }

    /** @test */
    public function unauthorized_user_cannot_access_bank_transfer_page()
    {
        $otherUser = User::factory()->create();

        $response = $this->actingAs($otherUser)
            ->get("/payment/bank-transfer/{$this->booking->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function can_create_bank_transfer_payment()
    {
        $payment = $this->paymentService->createBankTransferPayment($this->booking);

        $this->assertEquals('bank_transfer', $payment->payment_method);
        $this->assertEquals('pending', $payment->status);
        $this->assertEquals(1000000, $payment->amount);
        $this->assertEquals('VND', $payment->currency);
        $this->assertEquals('Bank Transfer', $payment->gateway_name);
        $this->assertStringStartsWith('BANK_', $payment->transaction_id);
    }

    /** @test */
    public function can_get_bank_transfer_info()
    {
        $bankInfo = $this->paymentService->getBankTransferInfo();

        $this->assertArrayHasKey('banks', $bankInfo);
        $this->assertArrayHasKey('instructions', $bankInfo);
        $this->assertArrayHasKey('note', $bankInfo);

        $this->assertCount(3, $bankInfo['banks']);

        $vietcombank = collect($bankInfo['banks'])->firstWhere('name', 'Vietcombank');
        $this->assertEquals('1234567890', $vietcombank['account_number']);
        $this->assertEquals('CONG TY TNHH KHACH SAN MARRON', $vietcombank['account_name']);
    }

    /** @test */
    public function can_confirm_bank_transfer()
    {
        $payment = $this->paymentService->createBankTransferPayment($this->booking);

        Storage::fake('public');

        $response = $this->actingAs($this->user)
            ->post("/payment/bank-transfer/{$this->booking->id}/confirm", [
                'transaction_id' => $payment->transaction_id,
                'bank_name' => 'Vietcombank',
                'transfer_amount' => 1000000,
                'transfer_date' => now()->format('Y-m-d'),
                'receipt_image' => UploadedFile::fake()->image('receipt.jpg'),
                'customer_note' => 'Test transfer'
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Kiểm tra payment đã được cập nhật
        $payment->refresh();
        $this->assertEquals('processing', $payment->status);
        $this->assertEquals('BANK_TRANSFER', $payment->gateway_code);
        $this->assertEquals('Đã nhận thông tin chuyển khoản, đang xác nhận', $payment->gateway_message);
    }

    /** @test */
    public function cannot_confirm_with_wrong_amount()
    {
        $payment = $this->paymentService->createBankTransferPayment($this->booking);

        $response = $this->actingAs($this->user)
            ->post("/payment/bank-transfer/{$this->booking->id}/confirm", [
                'transaction_id' => $payment->transaction_id,
                'bank_name' => 'Vietcombank',
                'transfer_amount' => 500000, // Sai số tiền
                'transfer_date' => now()->format('Y-m-d')
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    /** @test */
    public function cannot_confirm_without_required_fields()
    {
        $payment = $this->paymentService->createBankTransferPayment($this->booking);

        $response = $this->actingAs($this->user)
            ->post("/payment/bank-transfer/{$this->booking->id}/confirm", [
                'transaction_id' => $payment->transaction_id,
                // Thiếu các field bắt buộc
            ]);

        $response->assertSessionHasErrors(['bank_name', 'transfer_amount', 'transfer_date']);
    }

    /** @test */
    public function can_upload_receipt_image()
    {
        $payment = $this->paymentService->createBankTransferPayment($this->booking);

        Storage::fake('public');

        $file = UploadedFile::fake()->image('receipt.jpg', 100, 100);

        $response = $this->actingAs($this->user)
            ->post("/payment/bank-transfer/{$this->booking->id}/confirm", [
                'transaction_id' => $payment->transaction_id,
                'bank_name' => 'Vietcombank',
                'transfer_amount' => 1000000,
                'transfer_date' => now()->format('Y-m-d'),
                'receipt_image' => $file
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Kiểm tra file đã được lưu
        Storage::disk('public')->assertExists('receipts/' . $file->hashName());
    }

    /** @test */
    public function cannot_upload_invalid_file_type()
    {
        $payment = $this->paymentService->createBankTransferPayment($this->booking);

        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($this->user)
            ->post("/payment/bank-transfer/{$this->booking->id}/confirm", [
                'transaction_id' => $payment->transaction_id,
                'bank_name' => 'Vietcombank',
                'transfer_amount' => 1000000,
                'transfer_date' => now()->format('Y-m-d'),
                'receipt_image' => $file
            ]);

        $response->assertSessionHasErrors(['receipt_image']);
    }

    /** @test */
    public function bank_transfer_appears_in_payment_methods()
    {
        $paymentMethods = $this->paymentService->getAvailablePaymentMethods();

        $this->assertArrayHasKey('bank_transfer', $paymentMethods);
        $this->assertEquals('Chuyển khoản ngân hàng', $paymentMethods['bank_transfer']['name']);
        $this->assertTrue($paymentMethods['bank_transfer']['enabled']);
    }
}
