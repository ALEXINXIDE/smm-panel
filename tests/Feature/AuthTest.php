<?php

namespace Tests\Feature;

// ============================================================================
// AuthTest — Authentication, Admin Access, Rate Limiting, Brute Force
// ============================================================================

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => Hash::make('password123!'),
            'status'   => 'active',
        ]);

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => 'password123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function invalid_credentials_return_error(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => 'wrong_password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function login_is_case_insensitive_for_email(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => Hash::make('password123!'),
            'status'   => 'active',
        ]);

        $response = $this->post('/login', [
            'email'    => 'TEST@EXAMPLE.COM',
            'password' => 'password123!',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function repeated_invalid_logins_lock_the_account(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@example.com',
            'password' => Hash::make('password123!'),
            'status'   => 'active',
        ]);

        foreach (range(1, 5) as $attempt) {
            $this->post('/login', [
                'email'    => 'test@example.com',
                'password' => 'wrong_password',
            ]);
        }

        $user->refresh();
        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->isLocked());

        $response = $this->post('/login', [
            'email'    => 'test@example.com',
            'password' => 'password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function banned_user_cannot_login(): void
    {
        User::factory()->create([
            'email'    => 'banned@example.com',
            'password' => Hash::make('password123!'),
            'status'   => 'banned',
        ]);

        $response = $this->post('/login', [
            'email'    => 'banned@example.com',
            'password' => 'password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** @test */
    public function unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /** @test */
    public function non_admin_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')
             ->assertForbidden();
    }

    /** @test */
    public function admin_user_can_access_admin_routes(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')
             ->assertOk();
    }

    /** @test */
    public function is_admin_cannot_be_set_via_registration(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Hacker',
            'email'                 => 'hacker@example.com',
            'password'              => 'password123!',
            'password_confirmation' => 'password123!',
            'is_admin'              => true,        // Attempted mass-assignment
            'funds'                 => 99999,        // Attempted mass-assignment
        ]);

        $user = User::where('email', 'hacker@example.com')->first();

        if ($user) {
            $this->assertFalse($user->is_admin);
            $this->assertEquals(0, $user->funds);
        }
    }

    /** @test */
    public function admin_dashboard_shows_stats(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
             ->get('/admin')
             ->assertOk()
             ->assertViewIs('admin.dashboard');
    }

    /** @test */
    public function logout_invalidates_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->post('/logout');

        $this->assertGuest();
    }

    /** @test */
    public function secure_headers_are_present(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Content-Security-Policy');
    }

    /** @test */
    public function password_reset_link_can_be_requested(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $response = $this->post('/password/email', [
            'email' => 'user@example.com',
        ]);

        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function admin_add_funds_requires_reason(): void
    {
        $admin  = User::factory()->create(['is_admin' => true]);
        $target = User::factory()->create(['funds' => 10]);

        $response = $this->actingAs($admin)
            ->post("/admin/users/{$target->id}/add-funds", [
                'amount' => 50,
                // Missing reason
            ]);

        $response->assertSessionHasErrors('reason');
        $this->assertEquals(10, $target->fresh()->funds); // Unchanged
    }

    /** @test */
    public function admin_add_funds_is_logged_to_audit_trail(): void
    {
        $admin  = User::factory()->create(['is_admin' => true]);
        $target = User::factory()->create(['funds' => 10]);

        $this->actingAs($admin)->post("/admin/users/{$target->id}/add-funds", [
            'amount' => 50,
            'reason' => 'Test credit for promotional campaign',
        ]);

        $this->assertDatabaseHas('admin_action_logs', [
            'admin_id'    => $admin->id,
            'action'      => 'add_funds',
            'target_type' => 'User',
            'target_id'   => $target->id,
        ]);
    }

    /** @test */
    public function admin_cannot_ban_another_admin(): void
    {
        $admin1 = User::factory()->create(['is_admin' => true]);
        $admin2 = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin1)->post("/admin/users/{$admin2->id}/ban", [
            'reason' => 'Test ban attempt',
        ]);

        $response->assertSessionHasErrors('error');
        $this->assertEquals('active', $admin2->fresh()->status);
    }
}
