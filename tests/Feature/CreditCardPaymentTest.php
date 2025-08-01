<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreditCardPaymentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $booking;
    protected $payment;

    protected function setUp(): void
    {
        parent::setUp();

        // Tạo user
        $this->user = User::factory()->create();

        // Tạo room type
        $roomType = RoomType::factory()->create([
            'name' => 'Phòng Deluxe',
            'price' => 1000000,
            'capacity' => 2
        ]);

        // Tạo room
        $room = Room::factory()->create([
            'room_type_id' => $roomType->id,
            'name' => 'Phòng 101'
        ]);

        // Tạo booking
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'room_id' => $room->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => now()->addDays(1),
            'check_out_date' => now()->addDays(3),
            'total_booking_price' => 2000000,
            'status' => 'pending'
        ]);
    }

    /** @test */
    public function user_can_access_credit_card_payment_page()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('payment.credit-card', $this->booking->id));

        $response->assertStatus(200);
        $response->assertViewIs('client.payment.credit-card');
        $response->assertViewHas('booking');
        $response->assertViewHas('payment');
        $response->assertViewHas('creditCardInfo');
    }

    /** @test */
    public function user_cannot_access_credit_card_payment_page_for_other_user_booking()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->get(route('payment.credit-card', $this->booking->id));

        $response->assertStatus(403);
    }

    /** @test */
    public function credit_card_payment_with_valid_test_card_succeeds()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('payment.credit-card.confirm', $this->booking->id), [
            'card_number' => '4111111111111111',
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cvv' => '123',
            'cardholder_name' => 'Test User',
            'transaction_id' => 'CC_' . $this->booking->booking_id . '_' . time()
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);

        // Kiểm tra payment được tạo
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'status' => 'completed'
        ]);
    }

    /** @test */
    public function credit_card_payment_with_invalid_card_fails()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('payment.credit-card.confirm', $this->booking->id), [
            'card_number' => '4000000000000002',
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cvv' => '123',
            'cardholder_name' => 'Test User',
            'transaction_id' => 'CC_' . $this->booking->booking_id . '_' . time()
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => false
        ]);

        // Kiểm tra payment được tạo với status failed
        $this->assertDatabaseHas('payments', [
            'booking_id' => $this->booking->id,
            'payment_method' => 'credit_card',
            'status' => 'failed'
        ]);
    }

    /** @test */
    public function credit_card_payment_validates_required_fields()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('payment.credit-card.confirm', $this->booking->id), [
            'card_number' => '',
            'expiry_month' => '',
            'expiry_year' => '',
            'cvv' => '',
            'cardholder_name' => '',
            'transaction_id' => ''
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function credit_card_payment_validates_card_number_format()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('payment.credit-card.confirm', $this->booking->id), [
            'card_number' => '123456789012345', // 15 digits instead of 16
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cvv' => '123',
            'cardholder_name' => 'Test User',
            'transaction_id' => 'CC_' . $this->booking->booking_id . '_' . time()
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function credit_card_payment_validates_cvv_format()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('payment.credit-card.confirm', $this->booking->id), [
            'card_number' => '4111111111111111',
            'expiry_month' => 12,
            'expiry_year' => 2025,
            'cvv' => '12', // 2 digits instead of 3-4
            'cardholder_name' => 'Test User',
            'transaction_id' => 'CC_' . $this->booking->booking_id . '_' . time()
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function credit_card_payment_validates_expiry_year()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('payment.credit-card.confirm', $this->booking->id), [
            'card_number' => '4111111111111111',
            'expiry_month' => 12,
            'expiry_year' => 2020, // Past year
            'cvv' => '123',
            'cardholder_name' => 'Test User',
            'transaction_id' => 'CC_' . $this->booking->booking_id . '_' . time()
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function payment_service_identifies_card_brands_correctly()
    {
        $paymentService = app(\App\Interfaces\Services\PaymentServiceInterface::class);

        // Test Visa
        $this->assertEquals('Visa', $this->invokeMethod($paymentService, 'getCardBrand', ['4111111111111111']));

        // Test Mastercard
        $this->assertEquals('Mastercard', $this->invokeMethod($paymentService, 'getCardBrand', ['5555555555554444']));

        // Test American Express
        $this->assertEquals('American Express', $this->invokeMethod($paymentService, 'getCardBrand', ['378282246310005']));
    }

    /** @test */
    public function payment_service_identifies_test_cards_correctly()
    {
        $paymentService = app(\App\Interfaces\Services\PaymentServiceInterface::class);

        // Test valid test cards
        $this->assertTrue($this->invokeMethod($paymentService, 'isTestCard', ['4111111111111111']));
        $this->assertTrue($this->invokeMethod($paymentService, 'isTestCard', ['5555555555554444']));
        $this->assertTrue($this->invokeMethod($paymentService, 'isTestCard', ['378282246310005']));

        // Test invalid cards
        $this->assertFalse($this->invokeMethod($paymentService, 'isTestCard', ['4000000000000002']));
        $this->assertFalse($this->invokeMethod($paymentService, 'isTestCard', ['1234567890123456']));
    }

    /** @test */
    public function payment_service_returns_credit_card_test_info()
    {
        $paymentService = app(\App\Interfaces\Services\PaymentServiceInterface::class);

        $testInfo = $paymentService->getCreditCardTestInfo();

        $this->assertArrayHasKey('test_cards', $testInfo);
        $this->assertArrayHasKey('instructions', $testInfo);
        $this->assertArrayHasKey('security_note', $testInfo);

        $this->assertCount(4, $testInfo['test_cards']);
        $this->assertCount(5, $testInfo['instructions']);
    }

    /**
     * Helper method to invoke private methods for testing
     */
    private function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
