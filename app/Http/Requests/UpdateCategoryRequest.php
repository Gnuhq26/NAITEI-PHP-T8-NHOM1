<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Category;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $category = $this->route('category');
        $categoryId = $category instanceof Category ? $category->category_id : $category;
        
        return [
            'name' => [
                'required',
                'string', 
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId, 'category_id')
            ],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function bodyParameters(): array
    {
        return [
            'name' => [
                'description' => 'The updated name of the category.',
                'required' => false,
                'type' => 'string',
                'example' => 'Updated Sofa',
            ],
            'image' => [
                'description' => 'The new image file for the category (optional). If not provided, the existing image will be kept.',
                'required' => false,
                'type' => 'file',
                'example' => 'new_category_image.png',
            ],
        ];
    }
}
