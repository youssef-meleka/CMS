<?php

namespace App\Http\Requests\Product;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = Auth::user();
        return $user && $user->canManageProducts();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'category' => ['sometimes', 'string', 'max:255'],
            'stock_quantity' => ['sometimes', 'integer', 'min:0'],
            'image_url' => ['nullable', 'string', 'url'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.max' => 'Product name cannot exceed 255 characters',
            'price.numeric' => 'Product price must be a number',
            'price.min' => 'Product price cannot be negative',
            'category.max' => 'Product category cannot exceed 255 characters',
            'stock_quantity.integer' => 'Stock quantity must be an integer',
            'stock_quantity.min' => 'Stock quantity cannot be negative',
            'image_url.url' => 'Please provide a valid URL for the image',
            'is_active.boolean' => 'Active status must be true or false',
        ];
    }
}
