<?php

namespace Tests\Feature\Admin\Feedback;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Feedback;
use App\Models\Role;

class FeedbackCrudTest extends TestCase
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
    public function it_can_display_feedback_details()
    {
        $this->withoutMiddleware();
        
        $customerRole = Role::where('name', 'customer')->first();
        $customer = User::factory()->create(['role_id' => $customerRole->role_id]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->category_id]);
        
        $feedback = Feedback::create([
            'user_id' => $customer->id,
            'product_id' => $product->product_id,
            'comment' => 'This is a test feedback',
            'rating' => 5
        ]);

        $this->assertDatabaseHas('feedback', [
            'feedback_id' => $feedback->feedback_id,
            'comment' => 'This is a test feedback'
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->get("/admin/feedbacks/{$feedback->feedback_id}");

        $response->assertStatus(200);
        
        $responseData = $response->json();
        $this->assertArrayHasKey('feedback', $responseData);
        $this->assertArrayHasKey('user', $responseData);  
        $this->assertArrayHasKey('product', $responseData);
    }

    /** @test */
    public function it_can_delete_feedback()
    {
        $this->withoutMiddleware();
        
        $customerRole = Role::where('name', 'customer')->first();
        $customer = User::factory()->create(['role_id' => $customerRole->role_id]);
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->category_id]);
        
        $feedback = Feedback::create([
            'user_id' => $customer->id,
            'product_id' => $product->product_id,
            'comment' => 'This feedback will be deleted',
            'rating' => 4
        ]);

        $this->assertDatabaseHas('feedback', [
            'feedback_id' => $feedback->feedback_id
        ]);

        $response = $this->actingAs($this->adminUser)
                        ->delete("/admin/feedbacks/{$feedback->feedback_id}");

        $response->assertRedirect(route('admin.feedbacks'));
        $response->assertSessionHas('success', 'Feedback deleted successfully!');
    }
}
