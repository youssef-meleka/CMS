@extends('layouts.dashboard')

@section('title', 'Order Statistics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-graph-up"></i> Order Statistics</h1>
    <a href="{{ route('dashboard.orders.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Orders
    </a>
</div>

<!-- Statistics Overview -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $stats['total_orders'] ?? 0 }}</h4>
                        <p class="mb-0">Total Orders</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-cart-fill" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">${{ number_format($stats['total_revenue'] ?? 0, 2) }}</h4>
                        <p class="mb-0">Total Revenue</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-currency-dollar" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $stats['pending_orders'] ?? 0 }}</h4>
                        <p class="mb-0">Pending Orders</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-clock-fill" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">{{ $stats['avg_order_value'] ?? 0 }}</h4>
                        <p class="mb-0">Avg Order Value</p>
                    </div>
                    <div class="align-self-center">
                        <i class="bi bi-calculator" style="font-size: 2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Order Status Distribution -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Order Status Distribution</h5>
            </div>
            <div class="card-body">
                @if(isset($stats['status_distribution']) && count($stats['status_distribution']) > 0)
                    <div class="row">
                        @foreach($stats['status_distribution'] as $status => $count)
                        <div class="col-md-4 mb-3">
                            <div class="d-flex align-items-center">
                                @php
                                    $statusColors = [
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'shipped' => 'primary',
                                        'delivered' => 'success',
                                        'cancelled' => 'danger'
                                    ];
                                    $percentage = $stats['total_orders'] > 0 ? round(($count / $stats['total_orders']) * 100, 1) : 0;
                                @endphp
                                <div class="status-indicator bg-{{ $statusColors[$status] ?? 'secondary' }} me-3"></div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-capitalize">{{ $status }}</span>
                                        <span class="fw-bold">{{ $count }}</span>
                                    </div>
                                    <div class="progress mt-1" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $statusColors[$status] ?? 'secondary' }}"
                                             style="width: {{ $percentage }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $percentage }}%</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-pie-chart text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No order data available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Monthly Revenue Chart -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> Monthly Revenue Trend</h5>
            </div>
            <div class="card-body">
                @if(isset($stats['monthly_revenue']) && count($stats['monthly_revenue']) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Orders</th>
                                    <th>Revenue</th>
                                    <th>Average</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['monthly_revenue'] as $month => $data)
                                <tr>
                                    <td><strong>{{ $month }}</strong></td>
                                    <td>{{ $data['orders'] }}</td>
                                    <td class="text-success fw-bold">${{ number_format($data['revenue'], 2) }}</td>
                                    <td>${{ number_format($data['average'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-graph-up text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No revenue data available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Top Products -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-star"></i> Top Products</h5>
            </div>
            <div class="card-body">
                @if(isset($stats['top_products']) && count($stats['top_products']) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($stats['top_products'] as $index => $product)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-bold">{{ $product['name'] }}</div>
                                <small class="text-muted">{{ $product['category'] }}</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">{{ $product['orders'] }} orders</div>
                                <small class="text-success">${{ number_format($product['revenue'], 2) }}</small>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-star text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">No product data available</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                @if(isset($stats['recent_orders']) && count($stats['recent_orders']) > 0)
                    <div class="list-group list-group-flush">
                        @foreach($stats['recent_orders'] as $order)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="fw-bold">Order #{{ $order['id'] }}</div>
                                    <small class="text-muted">{{ $order['customer_name'] }}</small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold">${{ number_format($order['total'], 2) }}</div>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            'shipped' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$order['status']] ?? 'secondary' }} badge-sm">
                                        {{ ucfirst($order['status']) }}
                                    </span>
                                </div>
                            </div>
                            <small class="text-muted">{{ $order['created_at'] }}</small>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-clock text-muted" style="font-size: 2rem;"></i>
                        <p class="text-muted mt-2">No recent activity</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-speedometer2"></i> Performance Metrics</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Conversion Rate</h6>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Orders per Day</span>
                        <span class="fw-bold">{{ $stats['orders_per_day'] ?? 0 }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <h6>Customer Retention</h6>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Repeat Customers</span>
                        <span class="fw-bold">{{ $stats['repeat_customers'] ?? 0 }}%</span>
                    </div>
                </div>
                <div class="mb-3">
                    <h6>Order Completion</h6>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Delivered Orders</span>
                        <span class="fw-bold">{{ $stats['delivered_orders'] ?? 0 }}%</span>
                    </div>
                </div>
                <div class="mb-3">
                    <h6>Average Processing Time</h6>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Days to Deliver</span>
                        <span class="fw-bold">{{ $stats['avg_processing_time'] ?? 0 }} days</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Options -->
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-download"></i> Export Statistics</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <a href="#" class="btn btn-outline-primary w-100">
                    <i class="bi bi-file-earmark-excel"></i> Export to Excel
                </a>
            </div>
            <div class="col-md-3">
                <a href="#" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-file-earmark-pdf"></i> Export to PDF
                </a>
            </div>
            <div class="col-md-3">
                <a href="#" class="btn btn-outline-success w-100">
                    <i class="bi bi-file-earmark-csv"></i> Export to CSV
                </a>
            </div>
            <div class="col-md-3">
                <a href="#" class="btn btn-outline-info w-100">
                    <i class="bi bi-printer"></i> Print Report
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.progress {
    background-color: #e9ecef;
}

.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.list-group-item {
    border-left: none;
    border-right: none;
    border-radius: 0 !important;
}

.list-group-item:first-child {
    border-top: none;
}

.list-group-item:last-child {
    border-bottom: none;
}
</style>
@endpush
