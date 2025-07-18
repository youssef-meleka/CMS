@extends('layouts.dashboard')

@section('title', 'Create Product')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-plus-circle"></i> Create New Product</h1>
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
                <form method="POST" action="{{ route('dashboard.products.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name') }}"
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
                                       value="{{ old('sku') }}"
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
                                  required>{{ old('description') }}</textarea>
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
                                       value="{{ old('price') }}"
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
                                       value="{{ old('stock_quantity', 0) }}"
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
                                        <option value="{{ $category }}" {{ old('category') === $category ? 'selected' : '' }}>
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
                                <input type="file"
                                       class="form-control @error('image') is-invalid @enderror"
                                       id="image"
                                       name="image"
                                       accept="image/*">
                                <div class="form-text">Upload a product image (JPEG, PNG, GIF - Max 2MB)</div>
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
                                           {{ old('is_active', true) ? 'checked' : '' }}>
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
                            <i class="bi bi-check"></i> Create Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Product Guidelines</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6><i class="bi bi-tag"></i> Product Information</h6>
                    <ul class="list-unstyled small">
                        <li>• Use descriptive product names</li>
                        <li>• Provide detailed descriptions</li>
                        <li>• Set accurate pricing</li>
                        <li>• Choose appropriate categories</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <h6><i class="bi bi-image"></i> Image Requirements</h6>
                    <ul class="list-unstyled small">
                        <li>• Supported formats: JPEG, PNG, GIF</li>
                        <li>• Maximum size: 2MB</li>
                        <li>• Recommended: Square images</li>
                        <li>• High quality for better display</li>
                    </ul>
                </div>
                <div class="mb-3">
                    <h6><i class="bi bi-box"></i> Stock Management</h6>
                    <ul class="list-unstyled small">
                        <li>• Set accurate stock quantities</li>
                        <li>• Monitor low stock levels</li>
                        <li>• Update stock regularly</li>
                        <li>• Use SKU for tracking</li>
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
                        <i class="bi bi-check-circle text-success"></i> Name is required
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> Description is required
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> Price must be positive
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> Stock quantity must be non-negative
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success"></i> Category is required
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

    // Image preview
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
</script>
@endpush
