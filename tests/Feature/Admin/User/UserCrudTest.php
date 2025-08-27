<?php

namespace Tests\Feature\Admin\User;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;

class UserCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $superAdminUser;
    protected $customerUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $adminRole = Role::firstOrCreate(
            ['role_id' => 1], 
            ['name' => 'Admin']
        );
        $customerRole = Role::firstOrCreate(
            ['role_id' => 2], 
            ['name' => 'Customer']
        );
        
        $this->superAdminUser = User::factory()->create([
            'email' => 'admin1@gmail.com',
            'role_id' => $adminRole->role_id,
            'is_activate' => true,
        ]);
        
        $this->customerUser = User::factory()->create([
            'role_id' => $customerRole->role_id,
            'is_activate' => false,
        ]);
    }

    /** @test */
    public function super_admin_can_delete_inactive_user()
    {
        $this->actingAs($this->superAdminUser);

        $response = $this->delete(route('admin.users.delete', $this->customerUser));

        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('users', [
            'id' => $this->customerUser->id,
        ]);
    }

    /** @test */
    public function super_admin_can_toggle_user_activation()
    {
        $adminRole = Role::firstOrCreate(['role_id' => 1], ['name' => 'Admin']);
        $customerRole = Role::firstOrCreate(['role_id' => 2], ['name' => 'Customer']);

        $testUser = User::factory()->create([
            'role_id' => $customerRole->role_id,
            'is_activate' => false,
        ]);

        $this->actingAs($this->superAdminUser);

        $response = $this->post(route('admin.users.toggle-activation', $testUser));

        $response->assertJson([
            'success' => true,
            'message' => 'User has been activated successfully.',
            'is_activate' => true
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $testUser->id,
            'is_activate' => true,
        ]);

        $response = $this->post(route('admin.users.toggle-activation', $testUser));

        $response->assertJson([
            'success' => true,
            'message' => 'User has been deactivated successfully.',
            'is_activate' => false
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $testUser->id,
            'is_activate' => false,
        ]);
    }
}
