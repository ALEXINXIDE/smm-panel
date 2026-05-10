<?php

namespace Tests\Feature;

use App\Models\FundAccount;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\TicketMessage;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportAndFundsFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_submit_manual_fund_request_and_see_whatsapp_link(): void
    {
        $user = User::factory()->create(['funds' => 0]);

        $account = FundAccount::create([
            'name' => 'Test Bank',
            'iban' => 'TEST1234567890',
            'notes' => 'Send funds to this account',
            'status' => 'active',
        ]);

        Setting::set('whatsapp_link', 'https://wa.me/1234567890');

        $response = $this->actingAs($user)->post(route('funds.manual'), [
            'fund_account_id' => $account->id,
            'amount' => 1000,
            'reference' => 'TXN12345',
        ]);

        $response->assertRedirect(route('funds.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'deposit',
            'status' => 'pending',
            'reference' => 'TXN12345',
            'gateway' => 'manual',
            'fund_account_id' => $account->id,
        ]);
    }

    public function test_admin_can_approve_manual_fund_request_and_credit_user_balance(): void
    {
        $user = User::factory()->create(['funds' => 0]);
        $admin = User::factory()->create(['is_admin' => true, 'funds' => 0]);

        $account = FundAccount::create([
            'name' => 'Test Bank',
            'iban' => 'TEST1234567890',
            'notes' => 'Send funds to this account',
            'status' => 'active',
        ]);

        $transaction = Transaction::create([
            'user_id' => $user->id,
            'amount' => 10.00,
            'type' => 'deposit',
            'description' => 'Manual deposit request to Test Bank',
            'status' => 'pending',
            'reference' => 'TXN-APPROVE',
            'gateway' => 'manual',
            'fund_account_id' => $account->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.transactions.approve', $transaction->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'completed',
        ]);
        $this->assertEquals(10.00, $user->fresh()->funds);
    }

    public function test_user_can_create_ticket_and_admin_can_reply_and_close_it(): void
    {
        $user = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($user)->post(route('tickets.store'), [
            'subject' => 'Test ticket',
            'message' => 'This is a test support issue.',
            'category' => 'technical',
        ]);

        $ticket = Ticket::first();
        $this->assertNotNull($ticket);
        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => 'This is a test support issue.',
            'is_admin' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.tickets.reply', $ticket->id), [
            'message' => 'Thank you, we are looking into it.',
        ]);
        $response->assertRedirect();

        $this->assertDatabaseHas('ticket_messages', [
            'ticket_id' => $ticket->id,
            'user_id' => $admin->id,
            'message' => 'Thank you, we are looking into it.',
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.tickets.close', $ticket->id));
        $response->assertRedirect();
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'closed',
        ]);
    }
}
