<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerCategoriesTest extends TestCase
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

        // Create a customer user
        $this->customer = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => Hash::make('password'),
            'role_id' => 2,
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function authenticated_customer_can_view_categories_page()
    {
        // Arrange: Create some categories
        $categories = Category::factory()->count(3)->create();

        // Act: Login and visit categories page
        $response = $this->actingAs($this->customer)
                         ->get('/customer/categories');

        // Assert: Page loads successfully
        $response->assertStatus(200);
        $response->assertViewIs('customer.pages.categories');
        $response->assertViewHas('categories');
        
        // Check that categories are displayed
        foreach ($categories as $category) {
            $response->assertSee($category->name);
        }
    }

    /** @test */
    public function categories_page_displays_products_count()
    {
        // Arrange: Create category with products
        $category = Category::create(['name' => 'Living Room']);
        Product::factory()->count(5)->create(['category_id' => $category->category_id]);

        // Act: Visit categories page
        $response = $this->actingAs($this->customer)
                         ->get('/customer/categories');

        // Assert: Products count is displayed
        $response->assertStatus(200);
        $response->assertSee('5 products');
    }

    /** @test */
    public function categories_page_shows_empty_state_when_no_categories()
    {
        // Act: Visit categories page with no categories
        $response = $this->actingAs($this->customer)
                         ->get('/customer/categories');

        // Assert: Empty state message is shown
        $response->assertStatus(200);
        $response->assertSee('No categories available');
    }

    /** @test */
    public function categories_page_has_proper_layout_elements()
    {
        // Arrange: Create a category
        Category::create(['name' => 'Bedroom']);

        // Act: Visit categories page
        $response = $this->actingAs($this->customer)
                         ->get('/customer/categories');

        // Assert: Check for layout elements
        $response->assertStatus(200);
        $response->assertSee('Furniro'); // Logo
        $response->assertSee('Shop'); // Page title
        $response->assertSee('Filter'); // Filter button
        $response->assertSee('Sort by'); // Sort options
    }

    /** @test */
    public function category_cards_have_view_products_links()
    {
        // Arrange: Create a category
        $category = Category::create(['name' => 'Office']);

        // Act: Visit categories page
        $response = $this->actingAs($this->customer)
                         ->get('/customer/categories');

        // Assert: View products link exists
        $response->assertStatus(200);
        $response->assertSee('View Products');
        $response->assertSee(route('customer.products', $category->category_id));
    }
}