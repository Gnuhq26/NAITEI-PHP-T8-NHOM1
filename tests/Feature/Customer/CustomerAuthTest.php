<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles with specific IDs
        Role::insert([
            ['role_id' => 1, 'name' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['role_id' => 2, 'name' => 'Customer', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /** @test */
    public function customer_can_login_and_redirected_to_categories_page()
    {
        // Arrange: Create a customer
        $customer = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => Hash::make('password'),
            'role_id' => 2, // Customer role
            'email_verified_at' => now(),
        ]);

        // Act: Login with customer credentials
        $response = $this->post('/login', [
            'email' => 'customer@test.com',
            'password' => 'password',
        ]);

        // Assert: Should redirect to customer categories page
        $response->assertRedirect('/customer/categories');
        $this->assertAuthenticatedAs($customer);
    }

    /** @test */
    public function admin_login_redirected_to_admin_dashboard()
    {
        // Arrange: Create an admin
        $admin = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role_id' => 1, // Admin role
            'email_verified_at' => now(),
        ]);

        // Act: Login with admin credentials
        $response = $this->post('/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        // Assert: Should redirect to admin dashboard
        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    /** @test */
    public function invalid_login_credentials_returns_error()
    {
        // Act: Try to login with invalid credentials
        $response = $this->post('/login', [
            'email' => 'invalid@test.com',
            'password' => 'wrongpassword',
        ]);

        // Assert: Should return to login page with errors
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /** @test */
    public function guest_cannot_access_customer_categories()
    {
        // Act: Try to access customer categories without login
        $response = $this->get('/customer/categories');

        // Assert: Should redirect to login page
        $response->assertRedirect('/login');
    }

    /** @test */
    public function guest_cannot_access_customer_products()
    {
        // Act: Try to access customer products without login
        $response = $this->get('/customer/products/1');

        // Assert: Should redirect to login page
        $response->assertRedirect('/login');
    }
}