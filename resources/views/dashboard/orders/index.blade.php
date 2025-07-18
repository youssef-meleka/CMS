@extends('layouts.dashboard')

@section('title', 'Orders')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-cart-fill"></i> Orders</h1>
    <div>
        <a href="{{ route('dashboard.orders.statistics') }}" class="btn btn-outline-info me-2">
            <i class="bi bi-graph-up"></i> View Statistics
        </a>
        <a href="{{ route('dashboard.orders.create') }}" class="btn btn-primary">
            <i class="bi bi-plus"></i> Create New Order
        </a>
    </div>
</div>

<!-- Search and Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('dashboard.orders.index') }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text"
                           class="form-control"
                           id="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Search by customer name, email, or order ID...">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="assigned_to" class="form-label">Assigned To</label>
                    <select class="form-select" id="assigned_to" name="assigned_to">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date"
                           class="form-control"
                           id="date_from"
                           name="date_from"
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date"
                           class="form-control"
                           id="date_to"
                           name="date_to"
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <a href="{{ route('dashboard.orders.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-clockwise"></i> Reset Filters
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Orders Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Orders List ({{ $orders->total() }} total)</h5>
    </div>
    <div class="card-body">
        @if($orders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                        <tr>
                            <td>
                                <strong>#{{ $order->id }}</strong>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $order->customer_name }}</strong><br>
                                    <small class="text-muted">{{ $order->customer_email }}</small><br>
                                    <small class="text-muted">{{ $order->customer_phone }}</small>
                                </div>
                            </td>
                            <td>
                                <small>
                                    {{ $order->orderItems->count() }} item(s)<br>
                                    @foreach($order->orderItems->take(2) as $item)
                                        {{ $item->product->name }} ({{ $item->quantity }})<br>
                                    @endforeach
                                    @if($order->orderItems->count() > 2)
                                        <span class="text-muted">+{{ $order->orderItems->count() - 2 }} more</span>
                                    @endif
                                </small>
                            </td>
                            <td>
                                <strong>${{ number_format($order->total_amount, 2) }}</strong>
                            </td>
                            <td>
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
                            </td>
                            <td>
                                @if($order->assignedUser)
                                    <small>{{ $order->assignedUser->name }}</small>
                                @else
                                    <span class="text-muted">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                <small>
                                    {{ $order->created_at->format('M j, Y') }}<br>
                                    <span class="text-muted">{{ $order->created_at->format('g:i A') }}</span>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('dashboard.orders.show', $order) }}"
                                       class="btn btn-sm btn-outline-primary"
                                       title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('dashboard.orders.edit', $order) }}"
                                       class="btn btn-sm btn-outline-warning"
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST"
                                          action="{{ route('dashboard.orders.destroy', $order) }}"
                                          style="display: inline;"
                                          onsubmit="return confirm('Are you sure you want to delete this order?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $orders->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-4">
                <i class="bi bi-cart text-muted" style="font-size: 3rem;"></i>
                <p class="text-muted mt-2">No orders found</p>
                @if(request()->hasAny(['search', 'status', 'assigned_to', 'date_from', 'date_to']))
                    <p class="text-muted">Try adjusting your search criteria.</p>
                    <a href="{{ route('dashboard.orders.index') }}" class="btn btn-outline-primary">
                        <i class="bi bi-arrow-clockwise"></i> Reset Filters
                    </a>
                @else
                    <a href="{{ route('dashboard.orders.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Create First Order
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
