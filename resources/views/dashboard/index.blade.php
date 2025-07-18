@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-house-fill"></i> Dashboard</h1>
    <span class="text-muted">Welcome back, {{ auth()->user()->name }}</span>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <h3>{{ $stats['total_users'] }}</h3>
            <p><i class="bi bi-people-fill"></i> Total Users</p>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <h3>{{ $stats['total_products'] }}</h3>
            <p><i class="bi bi-box-fill"></i> Total Products</p>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <h3>{{ $stats['total_orders'] }}</h3>
            <p><i class="bi bi-cart-fill"></i> Total Orders</p>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stats-card">
            <h3>{{ $stats['pending_orders'] }}</h3>
            <p><i class="bi bi-clock-fill"></i> Pending Orders</p>
        </div>
    </div>
</div>

<!-- Alert Cards -->
@if($stats['low_stock_products'] > 0)
<div class="row mb-4">
    <div class="col-12">
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Low Stock Alert:</strong> {{ $stats['low_stock_products'] }} products have low stock levels.
            <a href="{{ route('dashboard.products.index', ['stock_status' => 'low']) }}" class="alert-link">View Products</a>
        </div>
    </div>
</div>
@endif

<!-- Recent Activity -->
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Orders</h5>
            </div>
            <div class="card-body">
                @if($stats['recent_orders']->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['recent_orders'] as $order)
                                <tr>
                                    <td>
                                        <strong>#{{ $order->id }}</strong>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $order->customer->name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $order->customer->email ?? 'N/A' }}</small>
                                        </div>
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
                                        <strong>${{ number_format($order->total_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        <small>{{ $order->created_at->format('M j, Y') }}<br>{{ $order->created_at->format('g:i A') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('dashboard.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('dashboard.orders.index') }}" class="btn btn-primary">
                            <i class="bi bi-arrow-right"></i> View All Orders
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-cart-x text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No recent orders found</p>
                        <a href="{{ route('dashboard.orders.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus"></i> Create New Order
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-speedometer2"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('dashboard.users.create') }}" class="btn btn-outline-primary">
                        <i class="bi bi-person-plus"></i> Add New User
                    </a>
                    <a href="{{ route('dashboard.products.create') }}" class="btn btn-outline-success">
                        <i class="bi bi-box-plus"></i> Add New Product
                    </a>
                    <a href="{{ route('dashboard.orders.create') }}" class="btn btn-outline-info">
                        <i class="bi bi-cart-plus"></i> Create New Order
                    </a>
                    <a href="{{ route('dashboard.orders.statistics') }}" class="btn btn-outline-warning">
                        <i class="bi bi-graph-up"></i> View Statistics
                    </a>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> System Info</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h6 class="text-muted">Your Role</h6>
                            <span class="badge bg-primary">{{ ucfirst(auth()->user()->roles->first()->name ?? 'No Role') }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted">Last Login</h6>
                        <small>{{ now()->format('M j, Y g:i A') }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
