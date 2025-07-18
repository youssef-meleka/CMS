<?php

namespace App\Http\Requests\Order;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'integer', 'exists:users,id'],
            'shipping_address' => ['required', 'string'],
            'billing_address' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
            'status' => ['sometimes', Rule::in(['pending', 'processing', 'shipped', 'delivered', 'cancelled'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $user = Auth::user();
            $isAdmin = $user && $user->hasRole('admin');

            // For admins, customer_id is required
            if ($isAdmin && !$this->input('customer_id')) {
                $validator->errors()->add('customer_id', 'Customer ID is required for admin users.');
            }

            $items = $this->input('items', []);

            foreach ($items as $index => $item) {
                if (isset($item['product_id']) && isset($item['quantity'])) {
                    $product = Product::find($item['product_id']);

                    if ($product && $product->stock_quantity < $item['quantity']) {
                        $validator->errors()->add(
                            "items.{$index}.quantity",
                            "Insufficient stock for product {$product->name}. Available: {$product->stock_quantity}"
                        );
                    }
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_id.required' => 'Customer is required',
            'customer_id.exists' => 'Selected customer does not exist',
            'shipping_address.required' => 'Shipping address is required',
            'billing_address.required' => 'Billing address is required',
            'status.in' => 'Invalid order status',
            'items.required' => 'Order items are required',
            'items.min' => 'Order must have at least one item',
            'items.*.product_id.required' => 'Product is required for each item',
            'items.*.product_id.exists' => 'Selected product does not exist',
            'items.*.quantity.required' => 'Quantity is required for each item',
            'items.*.quantity.min' => 'Quantity must be at least 1',
            'items.*.unit_price.numeric' => 'Unit price must be a number',
            'items.*.unit_price.min' => 'Unit price cannot be negative',
        ];
    }
}
