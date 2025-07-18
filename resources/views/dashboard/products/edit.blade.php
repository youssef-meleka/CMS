@extends('layouts.dashboard')

@section('title', 'Edit Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil-square"></i> Edit Product</h1>
    <a href="{{ route('dashboard.products.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Products
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Product Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('dashboard.products.update', $product) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $product->name) }}"
                                       placeholder="Product Name"
                                       required>
                                <label for="name">Product Name</label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text"
                                       class="form-control @error('sku') is-invalid @enderror"
                                       id="sku"
                                       name="sku"
                                       value="{{ old('sku', $product->sku) }}"
                                       placeholder="SKU">
                                <label for="sku">SKU (Optional)</label>
                                @error('sku')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control @error('description') is-invalid @enderror"
                                  id="description"
                                  name="description"
                                  rows="4"
                                  placeholder="Enter product description..."
                                  required>{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="number"
                                       class="form-control @error('price') is-invalid @enderror"
                                       id="price"
                                       name="price"
                                       value="{{ old('price', $product->price) }}"
                                       placeholder="0.00"
                                       step="0.01"
                                       min="0"
                                       required>
                                <label for="price">Price ($)</label>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="number"
                                       class="form-control @error('stock_quantity') is-invalid @enderror"
                                       id="stock_quantity"
                                       name="stock_quantity"
                                       value="{{ old('stock_quantity', $product->stock_quantity) }}"
                                       placeholder="0"
                                       min="0"
                                       required>
                                <label for="stock_quantity">Stock Quantity</label>
                                @error('stock_quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <select class="form-select @error('category') is-invalid @enderror"
                                        id="category"
                                        name="category"
                                        required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" {{ old('category', $product->category) === $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                    <option value="new_category" {{ old('category') === 'new_category' ? 'selected' : '' }}>
                                        + Add New Category
                                    </option>
                                </select>
                                <label for="category">Category</label>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="image" class="form-label">Product Image</label>
                                @if($product->image_url)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $product->image_url) }}"
                                             alt="{{ $product->name }}"
                                             class="img-thumbnail"
                                             style="max-width: 200px;">
                                        <div class="form-text">Current image</div>
                                    </div>
                                @endif
                                <input type="file"
                                       class="form-control @error('image') is-invalid @enderror"
                                       id="image"
                                       name="image"
                                       accept="image/*">
                                <div class="form-text">Upload a new image to replace the current one (JPEG, PNG, GIF - Max 2MB)</div>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="is_active"
                                           name="is_active"
                                           value="1"
                                           {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Product
                                    </label>
                                </div>
                                <div class="form-text">Inactive products won't be visible to customers</div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('dashboard.products.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-x"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Product Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Created</h6>
                    <p class="text-muted">{{ $product->created_at->format('M j, Y g:i A') }}</p>
                </div>
                <div class="mb-3">
                    <h6>Last Updated</h6>
                    <p class="text-muted">{{ $product->updated_at->format('M j, Y g:i A') }}</p>
                </div>
                <div class="mb-3">
                    <h6>Created By</h6>
                    <p class="text-muted">{{ $product->creator->name ?? 'N/A' }}</p>
                </div>
                <div class="mb-3">
                    <h6>Product ID</h6>
                    <p class="text-muted">#{{ $product->id }}</p>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-check"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('dashboard.products.show', $product) }}" class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> View Product
                    </a>
                    <button type="button" class="btn btn-outline-warning" onclick="updateStock()">
                        <i class="bi bi-box"></i> Update Stock
                    </button>
                    <a href="{{ route('dashboard.products.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Update Modal -->
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Stock Quantity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('dashboard.products.stock', $product) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="stock_quantity_modal" class="form-label">New Stock Quantity</label>
                        <input type="number"
                               class="form-control"
                               id="stock_quantity_modal"
                               name="stock_quantity"
                               value="{{ $product->stock_quantity }}"
                               min="0"
                               required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const imageInput = document.getElementById('image');

    // Handle new category option
    categorySelect.addEventListener('change', function() {
        if (this.value === 'new_category') {
            const newCategory = prompt('Enter new category name:');
            if (newCategory && newCategory.trim()) {
                const option = new Option(newCategory, newCategory);
                categorySelect.add(option, 1);
                categorySelect.value = newCategory;
            } else {
                categorySelect.value = '';
            }
        }
    });

    // Image validation
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('Image size must be less than 2MB');
                this.value = '';
                return;
            }
        }
    });
});

function updateStock() {
    const modal = new bootstrap.Modal(document.getElementById('stockModal'));
    modal.show();
}
</script>
@endpush
