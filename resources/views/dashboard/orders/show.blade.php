@extends('layouts.dashboard')

@section('title', 'Order #' . $order->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-cart"></i> Order #{{ $order->id }}</h1>
    <div>
        <a href="{{ route('dashboard.orders.edit', $order) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('dashboard.orders.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Order Information -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Order Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6><i class="bi bi-person"></i> Customer Information</h6>
                        <p><strong>Name:</strong> {{ $order->customer_name }}</p>
                        <p><strong>Email:</strong> {{ $order->customer_email }}</p>
                        <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6><i class="bi bi-info-circle"></i> Order Details</h6>
                        <p><strong>Order ID:</strong> #{{ $order->id }}</p>
                        <p><strong>Date:</strong> {{ $order->created_at->format('M j, Y g:i A') }}</p>
                        <p><strong>Status:</strong>
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'shipped' => 'primary',
                                    'delivered' => 'success',
                                    'cancelled' => 'danger'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-box"></i> Order Items</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Unit Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->orderItems as $item)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $item->product->name }}</strong>
                                        @if($item->product->image_url)
                                            <br><small class="text-muted">Has image</small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $item->product->category }}</span>
                                </td>
                                <td>${{ number_format($item->unit_price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td><strong>${{ number_format($item->subtotal, 2) }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-primary">
                                <th colspan="4" class="text-end">Total Amount:</th>
                                <th class="fs-5">${{ number_format($order->total_amount, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Order Timeline -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Order Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <h6>Order Created</h6>
                            <p class="text-muted">{{ $order->created_at->format('M j, Y g:i A') }}</p>
                            <small>Order #{{ $order->id }} was created</small>
                        </div>
                    </div>

                    @if($order->assignedUser)
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info"></div>
                        <div class="timeline-content">
                            <h6>Order Assigned</h6>
                            <p class="text-muted">{{ $order->updated_at->format('M j, Y g:i A') }}</p>
                            <small>Assigned to {{ $order->assignedUser->name }}</small>
                        </div>
                    </div>
                    @endif

                    <div class="timeline-item">
                        <div class="timeline-marker bg-{{ $statusColors[$order->status] ?? 'secondary' }}"></div>
                        <div class="timeline-content">
                            <h6>Current Status: {{ ucfirst($order->status) }}</h6>
                            <p class="text-muted">{{ $order->updated_at->format('M j, Y g:i A') }}</p>
                            <small>Last updated</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Order Summary -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-calculator"></i> Order Summary</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Total Items</h6>
                    <p class="text-muted">{{ $order->orderItems->count() }} items</p>
                </div>
                <div class="mb-3">
                    <h6>Total Amount</h6>
                    <p class="text-primary fs-4">${{ number_format($order->total_amount, 2) }}</p>
                </div>
                <div class="mb-3">
                    <h6>Order Status</h6>
                    <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }} fs-6">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <div class="mb-3">
                    <h6>Assigned To</h6>
                    @if($order->assignedUser)
                        <p class="text-muted">{{ $order->assignedUser->name }}</p>
                    @else
                        <p class="text-muted">Unassigned</p>
                    @endif
                </div>
                <div class="mb-3">
                    <h6>Created By</h6>
                    <p class="text-muted">{{ $order->creator->name ?? 'N/A' }}</p>
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
                    <a href="{{ route('dashboard.orders.edit', $order) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit Order
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="updateStatus()">
                        <i class="bi bi-arrow-clockwise"></i> Update Status
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="assignOrder()">
                        <i class="bi bi-person-plus"></i> Assign Order
                    </button>
                    <form method="POST"
                          action="{{ route('dashboard.orders.destroy', $order) }}"
                          style="display: inline;"
                          onsubmit="return confirm('Are you sure you want to delete this order?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Delete Order
                        </button>
                    </form>
                    <a href="{{ route('dashboard.orders.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Status Guide</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <span class="badge bg-warning">Pending</span>
                    <small class="text-muted">Order received, awaiting processing</small>
                </div>
                <div class="mb-2">
                    <span class="badge bg-info">Processing</span>
                    <small class="text-muted">Order is being prepared</small>
                </div>
                <div class="mb-2">
                    <span class="badge bg-primary">Shipped</span>
                    <small class="text-muted">Order has been shipped</small>
                </div>
                <div class="mb-2">
                    <span class="badge bg-success">Delivered</span>
                    <small class="text-muted">Order has been delivered</small>
                </div>
                <div class="mb-2">
                    <span class="badge bg-danger">Cancelled</span>
                    <small class="text-muted">Order has been cancelled</small>
                </div>
            </div>
        </div>

        <!-- Customer Contact -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-lines-fill"></i> Customer Contact</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>{{ $order->customer_name }}</strong>
                </div>
                <div class="mb-2">
                    <i class="bi bi-envelope"></i> {{ $order->customer_email }}
                </div>
                <div class="mb-2">
                    <i class="bi bi-telephone"></i> {{ $order->customer_phone }}
                </div>
                <div class="mt-3">
                    <a href="mailto:{{ $order->customer_email }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-envelope"></i> Send Email
                    </a>
                    <a href="tel:{{ $order->customer_phone }}" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-telephone"></i> Call
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Update Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Order Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('dashboard.orders.status', $order) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status_modal" class="form-label">New Status</label>
                        <select class="form-select" id="status_modal" name="status" required>
                            <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                            <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Order Modal -->
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Assign Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('dashboard.orders.assign', $order) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="assigned_to_modal" class="form-label">Assign To</label>
                        <select class="form-select" id="assigned_to_modal" name="assigned_to" required>
                            <option value="">Select User</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}" {{ $order->assigned_to == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ ucfirst($user->role) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Assign Order</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 0.375rem;
    border-left: 3px solid #007bff;
}

.timeline-content h6 {
    margin-bottom: 5px;
    color: #495057;
}

.timeline-content p {
    margin-bottom: 5px;
}

.timeline-content small {
    color: #6c757d;
}
</style>
@endpush

@push('scripts')
<script>
function updateStatus() {
    const modal = new bootstrap.Modal(document.getElementById('statusModal'));
    modal.show();
}

function assignOrder() {
    const modal = new bootstrap.Modal(document.getElementById('assignModal'));
    modal.show();
}
</script>
@endpush
