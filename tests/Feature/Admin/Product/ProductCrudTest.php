<?php

namespace Tests\Feature\Admin\Product;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Role;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;

    private const TEST_PRODUCT_PRICE = 9999999;

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
    public function it_can_store_product_with_valid_data()
    {
        $this->withoutMiddleware();
        
        $category = Category::factory()->create();
        
        $productData = [
            'name' => 'Test Product',
            'description' => 'This is a test product description',
            'price' => self::TEST_PRODUCT_PRICE,
            'stock' => 10,
            'category_id' => $category->category_id
        ];

        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/products', $productData);

        $response->assertRedirect(route('admin.products'));
        $response->assertSessionHas('success', 'Product added successfully.');
        
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'description' => 'This is a test product description',
            'price' => self::TEST_PRODUCT_PRICE,
            'stock' => 10,
            'category_id' => $category->category_id
        ]);
    }

    /** @test */
    public function it_cannot_store_product_with_invalid_data()
    {
        $this->withoutMiddleware();
        
        $productData = [
            'name' => '',
            'price' => -10,
            'stock' => -5,
            'category_id' => 999
        ];

        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/products', $productData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'price', 'stock', 'category_id']);
    }

    /** @test */
    public function it_can_store_product_with_image()
    {
        $this->withoutMiddleware();
        Storage::fake('public');
        
        $category = Category::factory()->create();
        $image = UploadedFile::fake()->create('product.jpg', 100, 'image/jpeg');
        
        $productData = [
            'name' => 'Product with Image',
            'description' => 'Product with image description',
            'price' => self::TEST_PRODUCT_PRICE,
            'stock' => 5,
            'category_id' => $category->category_id,
            'image' => $image
        ];

        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/products', $productData);

        $response->assertRedirect(route('admin.products'));
        $response->assertSessionHas('success');
        
        // check if product was created
        $product = Product::where('name', 'Product with Image')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($product->image);
    }

    /** @test */
    public function it_cannot_store_product_with_invalid_image()
    {
        $this->withoutMiddleware();
        
        $category = Category::factory()->create();
        $invalidFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'); // use pdf instead of image
        
        $productData = [
            'name' => 'Product with Invalid Image',
            'price' => self::TEST_PRODUCT_PRICE,
            'stock' => 5,
            'category_id' => $category->category_id,
            'image' => $invalidFile
        ];

        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/products', $productData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['image']);
    }

    /** @test */
    public function it_can_update_product_with_valid_data()
    {
        $this->withoutMiddleware();
        
        $category = Category::factory()->create();
        $newCategory = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->category_id,
            'name' => 'Original Product',
            'price' => self::TEST_PRODUCT_PRICE,
            'stock' => 8
        ]);
        
        $updateData = [
            'name' => 'Updated Product Name',
            'description' => 'Updated description',
            'price' => self::TEST_PRODUCT_PRICE + 10000000,
            'stock' => 15,
            'category_id' => $newCategory->category_id
        ];

        $response = $this->actingAs($this->adminUser)
                         ->put("/admin/products/{$product->product_id}", $updateData);

        $response->assertRedirect(route('admin.products'));
        $response->assertSessionHas('success', 'Product updated successfully.');
        
        $product->refresh();
        $this->assertEquals('Updated Product Name', $product->name);
        $this->assertEquals('Updated description', $product->description);
        $this->assertEquals(19999999, $product->price);
        $this->assertEquals(15, $product->stock);
        $this->assertEquals($newCategory->category_id, $product->category_id);
    }

    /** @test */
    public function it_cannot_update_product_with_invalid_data()
    {
        $this->withoutMiddleware();
        
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->category_id]);
        
        $updateData = [
            'name' => '',
            'price' => 'invalid_price',
            'stock' => -10,
            'category_id' => 999999 // non-existent category
        ];

        $response = $this->actingAs($this->adminUser)
                         ->put("/admin/products/{$product->product_id}", $updateData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'price', 'stock', 'category_id']);
    }

    /** @test */
    public function it_can_update_product_image()
    {
        $this->withoutMiddleware();
        Storage::fake('public');
        
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->category_id]);
        $newImage = UploadedFile::fake()->create('new_product.jpg', 100, 'image/jpeg');
        
        $updateData = [
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $product->stock ?? 10,
            'category_id' => $category->category_id,
            'image' => $newImage
        ];

        $response = $this->actingAs($this->adminUser)
                         ->put("/admin/products/{$product->product_id}", $updateData);

        $response->assertRedirect(route('admin.products'));
        $response->assertSessionHas('success');
        
        $product->refresh();
        $this->assertNotNull($product->image);
    }

    /** @test */
    public function it_can_delete_existing_product()
    {
        $this->withoutMiddleware();
        
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->category_id]);
        $productId = $product->product_id;

        $response = $this->actingAs($this->adminUser)
                         ->delete("/admin/products/{$product->product_id}");

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseMissing('products', [
            'product_id' => $productId
        ]);
    }

    /** @test */
    public function it_handles_delete_non_existent_product()
    {
        $this->withoutMiddleware();
        
        $nonExistentId = 999;

        $response = $this->actingAs($this->adminUser)
                         ->delete("/admin/products/{$nonExistentId}");

        $this->assertTrue(in_array($response->getStatusCode(), [404, 200]));
    }

    /** @test */
    public function it_can_search_products_by_name()
    {
        $this->withoutMiddleware();
        
        $category = Category::factory()->create();
        Product::factory()->create([
            'name' => 'Laptop Gaming',
            'category_id' => $category->category_id
        ]);
        Product::factory()->create([
            'name' => 'Smartphone Android',
            'category_id' => $category->category_id
        ]);
        Product::factory()->create([
            'name' => 'Tablet iOS',
            'category_id' => $category->category_id
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/products/search?query=Laptop');

        $response->assertStatus(200);
        $response->assertViewIs('admin.pages.products');
        $response->assertViewHas('products');
        
        $products = $response->viewData('products');
        $this->assertTrue($products->contains('name', 'Laptop Gaming'));
        $this->assertFalse($products->contains('name', 'Smartphone Android'));
    }

    /** @test */
    public function it_can_search_products_by_category()
    {
        $this->withoutMiddleware();
        
        $electronicsCategory = Category::factory()->create(['name' => 'Electronics']);
        $furnitureCategory = Category::factory()->create(['name' => 'Furniture']);
        
        Product::factory()->create([
            'name' => 'Laptop',
            'category_id' => $electronicsCategory->category_id
        ]);
        Product::factory()->create([
            'name' => 'Chair',
            'category_id' => $furnitureCategory->category_id
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->get("/admin/products/search?category_id={$electronicsCategory->category_id}");

        $response->assertStatus(200);
        $response->assertViewHas('products');
        
        $products = $response->viewData('products');
        $this->assertTrue($products->contains('name', 'Laptop'));
        $this->assertFalse($products->contains('name', 'Chair'));
    }

    /** @test */
    public function it_returns_empty_results_for_non_matching_search()
    {
        $this->withoutMiddleware();
        
        $category = Category::factory()->create();
        Product::factory()->create([
            'name' => 'Laptop',
            'category_id' => $category->category_id
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->get('/admin/products/search?query=NonExistentProduct');

        $response->assertStatus(200);
        $response->assertViewHas('products');
        
        $products = $response->viewData('products');
        $this->assertTrue($products->isEmpty());
    }

    /** @test */
    public function it_can_search_products_with_combined_filters()
    {
        $this->withoutMiddleware();
        
        $electronicsCategory = Category::factory()->create(['name' => 'Electronics']);
        $furnitureCategory = Category::factory()->create(['name' => 'Furniture']);
        
        Product::factory()->create([
            'name' => 'Gaming Laptop',
            'category_id' => $electronicsCategory->category_id
        ]);
        Product::factory()->create([
            'name' => 'Office Chair',
            'category_id' => $furnitureCategory->category_id
        ]);
        Product::factory()->create([
            'name' => 'Gaming Chair',
            'category_id' => $furnitureCategory->category_id
        ]);

        $response = $this->actingAs($this->adminUser)
                         ->get("/admin/products/search?query=Gaming&category_id={$electronicsCategory->category_id}");

        $response->assertStatus(200);
        $response->assertViewHas('products');
        
        $products = $response->viewData('products');
        
        $this->assertTrue($products->contains('name', 'Gaming Laptop'));
        $this->assertFalse($products->contains('name', 'Office Chair'));
        $this->assertFalse($products->contains('name', 'Gaming Chair'));
    }

    /** @test */
    public function it_validates_required_fields_for_product_creation()
    {
        $this->withoutMiddleware();
        
        $productData = []; // empty data

        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/products', $productData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['name', 'price', 'stock', 'category_id']);
    }

    /** @test */
    public function it_validates_price_is_non_negative()
    {
        $this->withoutMiddleware();
        
        $category = Category::factory()->create();
        $productData = [
            'name' => 'Test Product',
            'price' => -5000,
            'stock' => 10,
            'category_id' => $category->category_id
        ];

        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/products', $productData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['price']);
    }

    /** @test */
    public function it_validates_stock_is_non_negative_integer()
    {
        $this->withoutMiddleware();
        
        $category = Category::factory()->create();
        $productData = [
            'name' => 'Test Product',
            'price' => self::TEST_PRODUCT_PRICE,
            'stock' => -5, // negative stock
            'category_id' => $category->category_id
        ];

        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/products', $productData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['stock']);
    }

    /** @test */
    public function it_validates_category_exists()
    {
        $this->withoutMiddleware();
        
        $productData = [
            'name' => 'Test Product',
            'price' => self::TEST_PRODUCT_PRICE,
            'stock' => 10,
            'category_id' => 999999 // non-existent category
        ];

        $response = $this->actingAs($this->adminUser)
                         ->post('/admin/products', $productData);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['category_id']);
    }
}
