@extends('layouts.dashboard')

@section('title', $product->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-box"></i> {{ $product->name }}</h1>
    <div>
        <a href="{{ route('dashboard.products.edit', $product) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('dashboard.products.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Products
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Product Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        @if($product->image_url)
                            <img src="{{ asset('storage/' . $product->image_url) }}"
                                 alt="{{ $product->name }}"
                                 class="img-fluid rounded">
                        @else
                            <div class="text-center py-4 bg-light rounded">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">No image available</p>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h4>{{ $product->name }}</h4>
                        <p class="text-muted">{{ $product->description }}</p>

                        <div class="row mt-3">
                            <div class="col-6">
                                <strong>Price:</strong>
                                <span class="text-primary fs-5">${{ number_format($product->price, 2) }}</span>
                            </div>
                            <div class="col-6">
                                <strong>Stock:</strong>
                                @if($product->stock_quantity <= 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($product->stock_quantity < 10)
                                    <span class="badge bg-warning">{{ $product->stock_quantity }} left</span>
                                @else
                                    <span class="badge bg-success">{{ $product->stock_quantity }} in stock</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-6">
                                <strong>Category:</strong>
                                <span class="badge bg-secondary">{{ $product->category }}</span>
                            </div>
                            <div class="col-6">
                                <strong>Status:</strong>
                                @if($product->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </div>
                        </div>

                        @if($product->sku)
                        <div class="row mt-3">
                            <div class="col-6">
                                <strong>SKU:</strong>
                                <code>{{ $product->sku }}</code>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Management -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-box"></i> Stock Management</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('dashboard.products.stock', $product) }}" class="row g-3">
                    @csrf
                    @method('PATCH')
                    <div class="col-md-6">
                        <label for="stock_quantity" class="form-label">Current Stock</label>
                        <input type="number"
                               class="form-control"
                               id="stock_quantity"
                               name="stock_quantity"
                               value="{{ $product->stock_quantity }}"
                               min="0"
                               required>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> Update Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Product Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Product Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Product ID</h6>
                    <p class="text-muted">#{{ $product->id }}</p>
                </div>
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
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('dashboard.products.edit', $product) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit Product
                    </a>
                    <form method="POST"
                          action="{{ route('dashboard.products.destroy', $product) }}"
                          style="display: inline;"
                          onsubmit="return confirm('Are you sure you want to delete this product?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Delete Product
                        </button>
                    </form>
                    <a href="{{ route('dashboard.products.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Stock Alerts -->
        @if($product->stock_quantity <= 0)
        <div class="alert alert-danger mt-4">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Out of Stock!</strong><br>
            This product is currently out of stock.
        </div>
        @elseif($product->stock_quantity < 10)
        <div class="alert alert-warning mt-4">
            <i class="bi bi-exclamation-triangle"></i>
            <strong>Low Stock Alert!</strong><br>
            Only {{ $product->stock_quantity }} items remaining.
        </div>
        @endif

        <!-- Status Toggle -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-toggle-on"></i> Product Status</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('dashboard.products.update', $product) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="name" value="{{ $product->name }}">
                    <input type="hidden" name="description" value="{{ $product->description }}">
                    <input type="hidden" name="price" value="{{ $product->price }}">
                    <input type="hidden" name="stock_quantity" value="{{ $product->stock_quantity }}">
                    <input type="hidden" name="category" value="{{ $product->category }}">
                    <input type="hidden" name="is_active" value="{{ $product->is_active ? '0' : '1' }}">

                    <div class="d-grid">
                        @if($product->is_active)
                            <button type="submit" class="btn btn-outline-warning">
                                <i class="bi bi-pause-circle"></i> Deactivate Product
                            </button>
                        @else
                            <button type="submit" class="btn btn-outline-success">
                                <i class="bi bi-play-circle"></i> Activate Product
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
