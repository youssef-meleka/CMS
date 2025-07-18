@extends('layouts.dashboard')

@section('title', 'Edit Order')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-pencil-square"></i> Edit Order #{{ $order->id }}</h1>
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
                <form method="POST" action="{{ route('dashboard.orders.update', $order) }}">
                    @csrf
                    @method('PUT')

                    <!-- Customer Information -->
                    <h6 class="mb-3"><i class="bi bi-person"></i> Customer Information</h6>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-floating mb-3">
                                <input type="text"
                                       class="form-control @error('customer_name') is-invalid @enderror"
                                       id="customer_name"
                                       name="customer_name"
                                       value="{{ old('customer_name', $order->customer_name) }}"
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
                                       value="{{ old('customer_email', $order->customer_email) }}"
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
                                       value="{{ old('customer_phone', $order->customer_phone) }}"
                                       placeholder="Customer Phone"
                                       required>
                                <label for="customer_phone">Customer Phone</label>
                                @error('customer_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Order Status and Assignment -->
                    <h6 class="mb-3 mt-4"><i class="bi bi-gear"></i> Order Management</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status"
                                        name="status"
                                        required>
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ old('status', $order->status) === $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="status">Order Status</label>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <select class="form-select @error('assigned_to') is-invalid @enderror"
                                        id="assigned_to"
                                        name="assigned_to">
                                    <option value="">Unassigned</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" {{ old('assigned_to', $order->assigned_to) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} ({{ ucfirst($user->role) }})
                                        </option>
                                    @endforeach
                                </select>
                                <label for="assigned_to">Assign To</label>
                                @error('assigned_to')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Order Items (Read-only) -->
                    <h6 class="mb-3 mt-4"><i class="bi bi-box"></i> Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product->name }}</strong><br>
                                        <small class="text-muted">{{ $item->product->category }}</small>
                                    </td>
                                    <td>${{ number_format($item->unit_price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->subtotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th>${{ number_format($order->total_amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('dashboard.orders.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-x"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> Update Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Order Details -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Order Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Order ID</h6>
                    <p class="text-muted">#{{ $order->id }}</p>
                </div>
                <div class="mb-3">
                    <h6>Current Status</h6>
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'processing' => 'info',
                            'shipped' => 'primary',
                            'delivered' => 'success',
                            'cancelled' => 'danger'
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }} fs-6">
                        {{ ucfirst($order->status) }}
                    </span>
                </div>
                <div class="mb-3">
                    <h6>Total Amount</h6>
                    <p class="text-primary fs-5">${{ number_format($order->total_amount, 2) }}</p>
                </div>
                <div class="mb-3">
                    <h6>Items Count</h6>
                    <p class="text-muted">{{ $order->orderItems->count() }} items</p>
                </div>
                <div class="mb-3">
                    <h6>Created</h6>
                    <p class="text-muted">{{ $order->created_at->format('M j, Y g:i A') }}</p>
                </div>
                <div class="mb-3">
                    <h6>Last Updated</h6>
                    <p class="text-muted">{{ $order->updated_at->format('M j, Y g:i A') }}</p>
                </div>
                @if($order->assignedUser)
                <div class="mb-3">
                    <h6>Currently Assigned To</h6>
                    <p class="text-muted">{{ $order->assignedUser->name }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('dashboard.orders.show', $order) }}" class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> View Order Details
                    </a>
                    <button type="button" class="btn btn-outline-warning" onclick="updateStatus()">
                        <i class="bi bi-arrow-clockwise"></i> Update Status
                    </button>
                    <button type="button" class="btn btn-outline-info" onclick="assignOrder()">
                        <i class="bi bi-person-plus"></i> Assign Order
                    </button>
                    <a href="{{ route('dashboard.orders.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Status Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Status Information</h5>
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
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" {{ $order->status === $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
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
                            @foreach($users as $user)
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
