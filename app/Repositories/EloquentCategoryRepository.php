<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class EloquentCategoryRepository implements CategoryRepository
{
    private const CATEGORY_IMAGE_DIR = 'images/categories';
    private const ALLOWED_IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/svg+xml'];
    private const MAX_IMAGE_SIZE = 2048 * 1024; // 2MB in bytes

    public function getAllCategories(): Collection
    {
        return Category::all();
    }

    public function getCategoryById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function createCategory(array $data): Category
    {
        if (isset($data['image']) && $data['image']->isValid()) {
            $data['image'] = $this->handleImageUpload($data['image']);
        }

        return Category::create($data);
    }

    public function updateCategory(int $id, array $data): ?Category
    {
        $category = $this->getCategoryById($id);

        if (!$category) {
            return null;
        }

        if (isset($data['image']) && $data['image']->isValid()) {
            $this->deleteImage($category);
            $data['image'] = $this->handleImageUpload($data['image']);
        }

        $category->update($data);

        return $category;
    }

    public function deleteCategory(int $id): bool
    {
        $category = $this->getCategoryById($id);

        if (!$category) {
            return false;
        }

        if ($this->hasProducts($id)) {
            return false;
        }

        $this->deleteImage($category);

        return $category->delete();
    }

    public function getCategoriesWithProductCount(int $perPage): LengthAwarePaginator
    {
        return Category::withCount('products')->orderBy('updated_at', 'desc')->paginate($perPage);
    }

    public function hasProducts(int $categoryId): bool
    {
        $category = $this->getCategoryById($categoryId);
        return $category ? $category->products()->count() > 0 : false;
    }

    public function deleteImage(Category $category): void
    {
        if ($category->image && File::exists(public_path($category->image))) {
            File::delete(public_path($category->image));
        }
    }

    public function count(): int
    {
        return Category::count();
    }

    private function handleImageUpload($image): ?string
    {
        $mimeType = $image->getMimeType();
        $size = $image->getSize();

        if (!in_array($mimeType, self::ALLOWED_IMAGE_MIME_TYPES) || $size > self::MAX_IMAGE_SIZE) {
            throw new \InvalidArgumentException('Invalid image file type or size.');
        }

        $imageName = Str::uuid() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path(self::CATEGORY_IMAGE_DIR), $imageName);
        return self::CATEGORY_IMAGE_DIR . '/' . $imageName;
    }
} 