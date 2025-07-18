@extends('layouts.dashboard')

@section('title', $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person"></i> {{ $user->name }}</h1>
    <div>
        <a href="{{ route('dashboard.users.edit', $user) }}" class="btn btn-warning me-2">
            <i class="bi bi-pencil"></i> Edit
        </a>
        <a href="{{ route('dashboard.users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Users
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <div class="user-avatar mb-3">
                            <i class="bi bi-person-circle" style="font-size: 4rem; color: #6c757d;"></i>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <h4>{{ $user->name }}</h4>
                        <p class="text-muted">{{ $user->email }}</p>

                        <div class="row mt-3">
                            <div class="col-6">
                                <strong>Role:</strong>
                                @php
                                    $roleColors = [
                                        'admin' => 'danger',
                                        'manager' => 'warning',
                                        'user' => 'success'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $roleColors[$user->role] ?? 'secondary' }} fs-6">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </div>
                            <div class="col-6">
                                <strong>Status:</strong>
                                @if($user->email_verified_at)
                                    <span class="badge bg-success">Verified</span>
                                @else
                                    <span class="badge bg-warning">Unverified</span>
                                @endif
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-6">
                                <strong>Member Since:</strong>
                                <p class="text-muted">{{ $user->created_at->format('M j, Y') }}</p>
                            </div>
                            <div class="col-6">
                                <strong>Last Updated:</strong>
                                <p class="text-muted">{{ $user->updated_at->format('M j, Y') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Statistics -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-graph-up"></i> User Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="stat-card">
                            <i class="bi bi-box text-primary" style="font-size: 2rem;"></i>
                            <h4 class="mt-2">{{ $user->products()->count() }}</h4>
                            <p class="text-muted">Products Created</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="stat-card">
                            <i class="bi bi-cart text-success" style="font-size: 2rem;"></i>
                            <h4 class="mt-2">{{ $user->orders()->count() }}</h4>
                            <p class="text-muted">Orders Placed</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="stat-card">
                            <i class="bi bi-calendar text-info" style="font-size: 2rem;"></i>
                            <h4 class="mt-2">{{ $user->created_at->diffInDays(now()) }}</h4>
                            <p class="text-muted">Days Active</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Activity</h5>
            </div>
            <div class="card-body">
                @php
                    $recentProducts = $user->products()->latest()->take(5)->get();
                    $recentOrders = $user->orders()->latest()->take(5)->get();
                @endphp

                @if($recentProducts->count() > 0 || $recentOrders->count() > 0)
                    <div class="row">
                        @if($recentProducts->count() > 0)
                        <div class="col-md-6">
                            <h6><i class="bi bi-box"></i> Recent Products</h6>
                            <div class="list-group list-group-flush">
                                @foreach($recentProducts as $product)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $product->name }}</strong><br>
                                        <small class="text-muted">{{ $product->created_at->diffForHumans() }}</small>
                                    </div>
                                    <span class="badge bg-primary">${{ number_format($product->price, 2) }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        @if($recentOrders->count() > 0)
                        <div class="col-md-6">
                            <h6><i class="bi bi-cart"></i> Recent Orders</h6>
                            <div class="list-group list-group-flush">
                                @foreach($recentOrders as $order)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Order #{{ $order->id }}</strong><br>
                                        <small class="text-muted">{{ $order->created_at->diffForHumans() }}</small>
                                    </div>
                                    <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'pending' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-clock text-muted" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2">No recent activity</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- User Details -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> User Details</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>User ID</h6>
                    <p class="text-muted">#{{ $user->id }}</p>
                </div>
                <div class="mb-3">
                    <h6>Email Address</h6>
                    <p class="text-muted">{{ $user->email }}</p>
                </div>
                <div class="mb-3">
                    <h6>Role</h6>
                    <span class="badge bg-{{ $roleColors[$user->role] ?? 'secondary' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
                <div class="mb-3">
                    <h6>Email Verification</h6>
                    @if($user->email_verified_at)
                        <p class="text-success">
                            <i class="bi bi-check-circle"></i> Verified on {{ $user->email_verified_at->format('M j, Y') }}
                        </p>
                    @else
                        <p class="text-warning">
                            <i class="bi bi-exclamation-triangle"></i> Not verified
                        </p>
                    @endif
                </div>
                <div class="mb-3">
                    <h6>Account Created</h6>
                    <p class="text-muted">{{ $user->created_at->format('M j, Y g:i A') }}</p>
                </div>
                <div class="mb-3">
                    <h6>Last Updated</h6>
                    <p class="text-muted">{{ $user->updated_at->format('M j, Y g:i A') }}</p>
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
                    <a href="{{ route('dashboard.users.edit', $user) }}" class="btn btn-warning">
                        <i class="bi bi-pencil"></i> Edit User
                    </a>
                    @if($user->id !== auth()->id())
                    <form method="POST"
                          action="{{ route('dashboard.users.destroy', $user) }}"
                          style="display: inline;"
                          onsubmit="return confirm('Are you sure you want to delete this user?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-trash"></i> Delete User
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('dashboard.users.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Role Information -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-check"></i> Role Permissions</h5>
            </div>
            <div class="card-body">
                @if($user->role === 'admin')
                    <div class="alert alert-danger">
                        <i class="bi bi-shield-fill"></i>
                        <strong>Admin Access</strong><br>
                        Full system access including user management, product management, and order management.
                    </div>
                @elseif($user->role === 'manager')
                    <div class="alert alert-warning">
                        <i class="bi bi-person-gear"></i>
                        <strong>Manager Access</strong><br>
                        Can manage products and orders. Has dashboard access but cannot manage users.
                    </div>
                @else
                    <div class="alert alert-success">
                        <i class="bi bi-person"></i>
                        <strong>User Access</strong><br>
                        Basic user with limited access. Can view assigned tasks and basic information.
                    </div>
                @endif
            </div>
        </div>

        @if($user->id === auth()->id())
        <div class="alert alert-info mt-4">
            <i class="bi bi-info-circle"></i>
            <strong>Note:</strong> This is your own profile.
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .user-avatar {
        width: 100px;
        height: 100px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 50%;
    }

    .stat-card {
        padding: 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background: #f8f9fa;
    }
</style>
@endpush
