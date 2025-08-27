<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface CategoryRepository
{
    public function getAllCategories(): Collection;
    public function getCategoryById(int $id): ?Category;
    public function createCategory(array $data): Category;
    public function updateCategory(int $id, array $data): ?Category;
    public function deleteCategory(int $id): bool;
    public function getCategoriesWithProductCount(int $perPage): LengthAwarePaginator;
    public function hasProducts(int $categoryId): bool;
    public function deleteImage(Category $category): void;
    public function count(): int;
} 