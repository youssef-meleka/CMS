@extends('layouts.dashboard')

@section('title', 'Create Order')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-plus-circle"></i> Create New Order</h1>
    <a href="{{ route('dashboard.orders.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Orders
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('dashboard.orders.store') }}" id="orderForm">
                    @csrf

                    <!-- Customer Information -->
                    <h6 class="mb-3"><i class="bi bi-person"></i> Customer Information</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text"
                                       class="form-control @error('customer_name') is-invalid @enderror"
                                       id="customer_name"
                                       name="customer_name"
                                       value="{{ old('customer_name') }}"
                                       placeholder="Customer Name"
                                       required>
                                <label for="customer_name">Customer Name</label>
                                @error('customer_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="email"
                                       class="form-control @error('customer_email') is-invalid @enderror"
                                       id="customer_email"
                                       name="customer_email"
                                       value="{{ old('customer_email') }}"
                                       placeholder="Customer Email"
                                       required>
                                <label for="customer_email">Customer Email</label>
                                @error('customer_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="tel"
                                       class="form-control @error('customer_phone') is-invalid @enderror"
                                       id="customer_phone"
                                       name="customer_phone"
                                       value="{{ old('customer_phone') }}"
                                       placeholder="Customer Phone"
                                       required>
                                <label for="customer_phone">Customer Phone</label>
                                @error('customer_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <h6 class="mb-3 mt-4"><i class="bi bi-box"></i> Order Items</h6>
                    <div id="orderItems">
                        <div class="order-item border rounded p-3 mb-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="items[0][product_id]" class="form-label">Product</label>
                                    <select class="form-select @error('items.0.product_id') is-invalid @enderror"
                                            name="items[0][product_id]"
                                            required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                    data-price="{{ $product->price }}"
                                                    data-stock="{{ $product->stock_quantity }}">
                                                {{ $product->name }} - ${{ number_format($product->price, 2) }}
                                                ({{ $product->stock_quantity }} in stock)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('items.0.product_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="items[0][quantity]" class="form-label">Quantity</label>
                                    <input type="number"
                                           class="form-control @error('items.0.quantity') is-invalid @enderror"
                                           name="items[0][quantity]"
                                           min="1"
                                           value="1"
                                           required>
                                    @error('items.0.quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Price</label>
                                    <div class="form-control-plaintext item-price">$0.00</div>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="button" class="btn btn-outline-danger btn-sm remove-item" style="display: none;">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary mb-3" id="addItem">
                        <i class="bi bi-plus"></i> Add Another Item
                    </button>

                    <!-- Order Summary -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="mb-3"><i class="bi bi-calculator"></i> Order Summary</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Total Items:</strong> <span id="totalItems">0</span></p>
                                    <p><strong>Total Amount:</strong> <span id="totalAmount" class="text-primary fs-5">$0.00</span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Order Date:</strong> {{ now()->format('M j, Y g:i A') }}</p>
                                    <p><strong>Created By:</strong> {{ auth()->user()->name }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('dashboard.orders.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-x"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> Create Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Order Guidelines</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6><i class="bi bi-person"></i> Customer Information</h6>
                    <ul class="list-unstyled small">
                        <li>• Provide accurate customer details</li>
                        <li>• Email is required for order confirmation</li>
                        <li>• Phone number for delivery coordination</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <h6><i class="bi bi-box"></i> Product Selection</h6>
                    <ul class="list-unstyled small">
                        <li>• Only products with stock are available</li>
                        <li>• Check stock quantities before ordering</li>
                        <li>• Prices are automatically calculated</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <h6><i class="bi bi-calculator"></i> Order Processing</h6>
                    <ul class="list-unstyled small">
                        <li>• Orders start with 'pending' status</li>
                        <li>• Stock is automatically updated</li>
                        <li>• Order can be assigned to staff later</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Validation Rules</h5>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> Customer name is required
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> Valid email address required
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> Phone number is required
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> At least one product required
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> Quantity must be positive
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemCount = 1;

    // Add new item
    document.getElementById('addItem').addEventListener('click', function() {
        const container = document.getElementById('orderItems');
        const newItem = document.querySelector('.order-item').cloneNode(true);

        // Update indices
        newItem.querySelector('select').name = `items[${itemCount}][product_id]`;
        newItem.querySelector('input').name = `items[${itemCount}][quantity]`;
        newItem.querySelector('input').value = '1';
        newItem.querySelector('select').value = '';
        newItem.querySelector('.item-price').textContent = '$0.00';

        // Show remove button
        newItem.querySelector('.remove-item').style.display = 'block';

        container.appendChild(newItem);
        itemCount++;

        // Add event listeners to new item
        addItemEventListeners(newItem);
    });

    // Remove item
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item') || e.target.closest('.remove-item')) {
            const item = e.target.closest('.order-item');
            if (document.querySelectorAll('.order-item').length > 1) {
                item.remove();
                updateOrderSummary();
            }
        }
    });

    // Add event listeners to initial item
    addItemEventListeners(document.querySelector('.order-item'));

    function addItemEventListeners(item) {
        const select = item.querySelector('select');
        const quantity = item.querySelector('input');
        const priceDisplay = item.querySelector('.item-price');

        select.addEventListener('change', function() {
            updateItemPrice(item);
            updateOrderSummary();
        });

        quantity.addEventListener('input', function() {
            updateItemPrice(item);
            updateOrderSummary();
        });
    }

    function updateItemPrice(item) {
        const select = item.querySelector('select');
        const quantity = item.querySelector('input');
        const priceDisplay = item.querySelector('.item-price');

        if (select.value) {
            const option = select.options[select.selectedIndex];
            const price = parseFloat(option.dataset.price);
            const qty = parseInt(quantity.value) || 0;
            const total = price * qty;
            priceDisplay.textContent = `$${total.toFixed(2)}`;
        } else {
            priceDisplay.textContent = '$0.00';
        }
    }

    function updateOrderSummary() {
        let totalItems = 0;
        let totalAmount = 0;

        document.querySelectorAll('.order-item').forEach(item => {
            const select = item.querySelector('select');
            const quantity = item.querySelector('input');

            if (select.value) {
                const option = select.options[select.selectedIndex];
                const price = parseFloat(option.dataset.price);
                const qty = parseInt(quantity.value) || 0;

                totalItems += qty;
                totalAmount += price * qty;
            }
        });

        document.getElementById('totalItems').textContent = totalItems;
        document.getElementById('totalAmount').textContent = `$${totalAmount.toFixed(2)}`;
    }
});
</script>
@endpush
