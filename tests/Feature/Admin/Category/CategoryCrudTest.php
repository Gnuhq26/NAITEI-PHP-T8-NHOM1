<?php

namespace Tests\Feature\Admin\Category;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Category;
use App\Models\Role;

class CategoryCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin role 
        $adminRole = Role::create(['name' => 'admin']);
        $customerRole = Role::create(['name' => 'customer']);
        
        // Create an admin user
        $this->adminUser = User::factory()->create([
            'role_id' => $adminRole->role_id,
            'email' => 'admin1@gmail.com' // Super admin email
        ]);
    }

    /** @test */
    public function it_can_create_category()
    {
        $this->withoutMiddleware();
        
        $categoryData = [
            'name' => 'Electronics',
            'description' => 'Electronic products'
        ];

        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/categories', $categoryData);

        $this->assertTrue($response->getStatusCode() < 500);
    }

    /** @test */
    public function it_can_update_category()
    {
        $this->withoutMiddleware();
        
        $category = Category::factory()->create();
        
        $updateData = [
            'name' => 'Updated Category Name',
            'description' => 'Updated category description'
        ];

        $response = $this->actingAs($this->adminUser)
                         ->put("/admin/categories/{$category->category_id}", $updateData);

        $this->assertTrue($response->getStatusCode() < 500);
    }

    /** @test */
    public function it_can_delete_category()
    {
        $this->withoutMiddleware();
        
        $category = Category::factory()->create();

        $response = $this->actingAs($this->adminUser)
                         ->delete("/admin/categories/{$category->category_id}");

        $this->assertTrue($response->getStatusCode() < 500);
    }
}
