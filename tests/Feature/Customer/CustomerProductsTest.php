<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerProductsTest extends TestCase
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
    public function authenticated_customer_can_view_products_in_category()
    {
        // Arrange: Create category with products
        $category = Category::create(['name' => 'Living Room']);
        $products = Product::factory()->count(3)->create([
            'category_id' => $category->category_id
        ]);

        // Act: Visit products page
        $response = $this->actingAs($this->customer)
                         ->get(route('customer.products', $category->category_id));

        // Assert: Page loads successfully with products
        $response->assertStatus(200);
        $response->assertViewIs('customer.pages.products');
        $response->assertViewHas(['category', 'products']);
        
        // Check category name and products are displayed
        $response->assertSee($category->name);
        foreach ($products as $product) {
            $response->assertSee($product->name);
            $response->assertSee($product->description);
        }
    }

    /** @test */
    public function products_page_displays_prices_in_vnd_format()
    {
        // Arrange: Create category and product with specific price
        $category = Category::create(['name' => 'Bedroom']);
        $product = Product::create([
            'name' => 'Test Bed',
            'description' => 'Comfortable bed',
            'price' => 2500000,
            'category_id' => $category->category_id,
        ]);

        // Act: Visit products page
        $response = $this->actingAs($this->customer)
                         ->get(route('customer.products', $category->category_id));

        // Assert: Price is formatted correctly in VND
        $response->assertStatus(200);
        $response->assertSee('2,500,000');
        $response->assertSee('VND');
    }

    /** @test */
    public function products_page_shows_correct_breadcrumb()
    {
        // Arrange: Create category
        $category = Category::create(['name' => 'Kitchen']);

        // Act: Visit products page
        $response = $this->actingAs($this->customer)
                         ->get(route('customer.products', $category->category_id));

        // Assert: Breadcrumb shows correct navigation
        $response->assertStatus(200);
        $response->assertSee('Home');
        $response->assertSee('Shop');
        $response->assertSee($category->name);
    }

    /** @test */
    public function products_page_shows_empty_state_when_no_products()
    {
        // Arrange: Create category without products
        $category = Category::create(['name' => 'Empty Category']);

        // Act: Visit products page
        $response = $this->actingAs($this->customer)
                         ->get(route('customer.products', $category->category_id));

        // Assert: Empty state message is shown
        $response->assertStatus(200);
        $response->assertSee('No products available in this category');
        $response->assertSee('Back to Categories');
    }

    /** @test */
    public function products_page_returns_404_for_invalid_category()
    {
        // Act: Try to visit products page with invalid category ID
        $response = $this->actingAs($this->customer)
                         ->get(route('customer.products', 99999));

        // Assert: Should return 404
        $response->assertStatus(404);
    }

    /** @test */
    public function products_page_has_filter_and_sort_options()
    {
        // Arrange: Create category with products
        $category = Category::create(['name' => 'Office']);
        Product::factory()->count(2)->create(['category_id' => $category->category_id]);

        // Act: Visit products page
        $response = $this->actingAs($this->customer)
                         ->get(route('customer.products', $category->category_id));

        // Assert: Filter and sort elements are present
        $response->assertStatus(200);
        $response->assertSee('Filter');
        $response->assertSee('Sort by');
        $response->assertSee('Show');
        $response->assertSee('Showing');
    }

    /** @test */
    public function products_page_displays_product_actions()
    {
        // Arrange: Create category with product
        $category = Category::create(['name' => 'Outdoor']);
        Product::create([
            'name' => 'Garden Chair',
            'description' => 'Comfortable outdoor chair',
            'price' => 1500000,
            'category_id' => $category->category_id,
        ]);

        // Act: Visit products page
        $response = $this->actingAs($this->customer)
                         ->get(route('customer.products', $category->category_id));

        // Assert: Product action buttons are present
        $response->assertStatus(200);
        $response->assertSee('Add to cart');
        $response->assertSee('Share');
        $response->assertSee('Compare');
        $response->assertSee('Like');
    }
}