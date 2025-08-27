<?php

namespace Tests\Unit;

use App\Http\Controllers\CustomerController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function categories_method_returns_view_with_categories()
    {
        // Arrange: Create categories
        $categories = Category::factory()->count(3)->create();
        $controller = new CustomerController();

        // Act: Call categories method
        $result = $controller->categories();

        // Assert: Returns view with categories data
        $this->assertInstanceOf(View::class, $result);
        $this->assertEquals('customer.pages.categories', $result->name());
        $this->assertArrayHasKey('categories', $result->getData());
        $this->assertCount(3, $result->getData()['categories']);
    }

    /** @test */
    public function categories_method_includes_products_count()
    {
        // Arrange: Create category with products
        $category = Category::factory()->create();
        Product::factory()->count(5)->create(['category_id' => $category->category_id]);
        
        $controller = new CustomerController();

        // Act: Call categories method
        $result = $controller->categories();

        // Assert: Categories include products count
        $categoriesData = $result->getData()['categories'];
        $this->assertEquals(5, $categoriesData->first()->products_count);
    }

    /** @test */
    public function products_method_returns_view_with_category_and_products()
    {
        // Arrange: Create category with products
        $category = Category::factory()->create();
        $products = Product::factory()->count(3)->create(['category_id' => $category->category_id]);
        
        $controller = new CustomerController();

        // Act: Call products method
        $result = $controller->products($category->category_id);

        // Assert: Returns view with category and products data
        $this->assertInstanceOf(View::class, $result);
        $this->assertEquals('customer.pages.products', $result->name());
        
        $viewData = $result->getData();
        $this->assertArrayHasKey('category', $viewData);
        $this->assertArrayHasKey('products', $viewData);
        $this->assertEquals($category->category_id, $viewData['category']->category_id);
        $this->assertCount(3, $viewData['products']);
    }

    /** @test */
    public function products_method_filters_products_by_category()
    {
        // Arrange: Create categories and products
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        
        $products1 = Product::factory()->count(2)->create(['category_id' => $category1->category_id]);
        $products2 = Product::factory()->count(3)->create(['category_id' => $category2->category_id]);
        
        $controller = new CustomerController();

        // Act: Call products method for category1
        $result = $controller->products($category1->category_id);

        // Assert: Only products from category1 are returned
        $viewData = $result->getData();
        $this->assertCount(2, $viewData['products']);
        
        foreach ($viewData['products'] as $product) {
            $this->assertEquals($category1->category_id, $product->category_id);
        }
    }

    /** @test */
    public function products_method_includes_category_relationship()
    {
        // Arrange: Create category with product
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->category_id]);
        
        $controller = new CustomerController();

        // Act: Call products method
        $result = $controller->products($category->category_id);

        // Assert: Products include category relationship
        $viewData = $result->getData();
        $productWithCategory = $viewData['products']->first();
        
        $this->assertTrue($productWithCategory->relationLoaded('category'));
        $this->assertEquals($category->name, $productWithCategory->category->name);
    }

    /** @test */
    public function products_method_throws_404_for_invalid_category()
    {
        // Arrange: Controller instance
        $controller = new CustomerController();

        // Act & Assert: Should throw ModelNotFoundException (which becomes 404)
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $controller->products(99999);
    }
}