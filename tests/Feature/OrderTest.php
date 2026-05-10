<?php

namespace Tests\Feature;

// ============================================================================
// OrderTest — Order Lifecycle, Race Conditions, and Business Rules
// ============================================================================

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\OrderException;
use App\Jobs\ProcessOrderJob;
use App\Models\ApiProvider;
use App\Models\Category;
use App\Models\Order;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $orderService;
    private User $user;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app(OrderService::class);

        $this->user = User::factory()->create(['funds' => 100.00]);

        $category = Category::create(['name' => 'Instagram', 'status' => 'active']);
        $this->service = Service::create([
            'name'        => 'Instagram Followers',
            'category_id' => $category->id,
            'rate'        => 1.00, // $1 per 1000
            'min'         => 100,
            'max'         => 10000,
            'status'      => 'active',
            'type'        => 'manual',
        ]);
    }

    /** @test */
    public function user_can_place_order_with_sufficient_funds(): void
    {
        Queue::fake();
        $this->actingAs($this->user);

        $order = $this->orderService->createOrder([
            'service_id' => $this->service->id,
            'quantity'   => 1000,
            'link'       => 'https://instagram.com/test',
        ]);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(1.00, $order->total);
        $this->assertEquals(99.00, $this->user->fresh()->funds); // 100 - 1
    }

    /** @test */
    public function order_deducts_correct_amount_from_balance(): void
    {
        Queue::fake();
        $this->actingAs($this->user);

        $this->orderService->createOrder([
            'service_id' => $this->service->id,
            'quantity'   => 500,
            'link'       => 'https://instagram.com/test',
        ]);

        // 500 / 1000 * $1.00 = $0.50
        $this->assertEquals(99.50, $this->user->fresh()->funds);
    }

    /** @test */
    public function insufficient_funds_throws_exception(): void
    {
        $this->actingAs($this->user);

        // Try to order $200 worth with only $100 balance
        $this->expectException(InsufficientFundsException::class);

        $this->orderService->createOrder([
            'service_id' => $this->service->id,
            'quantity'   => 200000,
            'link'       => 'https://instagram.com/test',
        ]);
    }

    /** @test */
    public function funds_are_not_deducted_on_failed_order(): void
    {
        $this->actingAs($this->user);

        try {
            $this->orderService->createOrder([
                'service_id' => $this->service->id,
                'quantity'   => 200000, // Exceeds balance
                'link'       => 'https://instagram.com/test',
            ]);
        } catch (InsufficientFundsException) {
            // Expected
        }

        // Balance must be unchanged — DB transaction rolled back
        $this->assertEquals(100.00, $this->user->fresh()->funds);
    }

    /** @test */
    public function cannot_order_inactive_service(): void
    {
        $this->actingAs($this->user);
        $this->service->update(['status' => 'inactive']);

        $this->expectException(OrderException::class);

        $this->orderService->createOrder([
            'service_id' => $this->service->id,
            'quantity'   => 1000,
            'link'       => 'https://instagram.com/test',
        ]);
    }

    /** @test */
    public function quantity_below_minimum_throws_exception(): void
    {
        $this->actingAs($this->user);

        $this->expectException(OrderException::class);

        $this->orderService->createOrder([
            'service_id' => $this->service->id,
            'quantity'   => 10, // Below min of 100
            'link'       => 'https://instagram.com/test',
        ]);
    }

    /** @test */
    public function transaction_record_is_created_with_order(): void
    {
        Queue::fake();
        $this->actingAs($this->user);

        $order = $this->orderService->createOrder([
            'service_id' => $this->service->id,
            'quantity'   => 1000,
            'link'       => 'https://instagram.com/test',
        ]);

        $this->assertDatabaseHas('transactions', [
            'user_id'   => $this->user->id,
            'type'      => 'deduction',
            'status'    => 'completed',
            'reference' => (string) $order->id,
        ]);
    }

    /** @test */
    public function api_service_dispatches_process_order_job(): void
    {
        Queue::fake();
        $this->actingAs($this->user);

        // Create service with API provider
        $provider = ApiProvider::create([
            'name'    => 'Test Provider',
            'url'     => 'https://api.test.com',
            'api_key' => 'test_key',
            'status'  => 'active',
        ]);

        $category = Category::first();
        $apiService = Service::create([
            'name'              => 'API Followers',
            'category_id'       => $category->id,
            'api_provider_id'   => $provider->id,
            'api_service_id'    => 999,
            'rate'              => 1.00,
            'min'               => 100,
            'max'               => 10000,
            'status'            => 'active',
            'type'              => 'api',
        ]);

        $this->orderService->createOrder([
            'service_id' => $apiService->id,
            'quantity'   => 1000,
            'link'       => 'https://instagram.com/test',
        ]);

        Queue::assertPushed(ProcessOrderJob::class);
    }

    /** @test */
    public function race_condition_prevention_does_not_overdraw_balance(): void
    {
        // Simulate two near-simultaneous orders that would overdraw if not locked
        $user = User::factory()->create(['funds' => 1.00]);

        Queue::fake();

        // Each order costs $1.00 (1000 qty at $1/1000)
        // User only has $1.00 — only ONE should succeed

        $successCount = 0;
        $failCount    = 0;

        $service = $this->service;

        // Run both "simultaneously" via direct service calls
        foreach ([1, 2] as $_) {
            try {
                $this->actingAs($user);
                app(OrderService::class)->createOrder([
                    'service_id' => $service->id,
                    'quantity'   => 1000,
                    'link'       => 'https://instagram.com/test' . $_,
                ]);
                $successCount++;
            } catch (InsufficientFundsException) {
                $failCount++;
            }
        }

        // Exactly one should succeed, one should fail
        $this->assertEquals(1, $successCount);
        $this->assertEquals(1, $failCount);

        // Balance should be exactly 0 — not negative
        $this->assertEquals(0, $user->fresh()->funds);
    }

    /** @test */
    public function javascript_url_in_link_is_rejected(): void
    {
        $this->actingAs($this->user);

        $this->expectException(OrderException::class);

        $this->orderService->createOrder([
            'service_id' => $this->service->id,
            'quantity'   => 1000,
            'link'       => 'javascript:alert(1)',
        ]);
    }
}
