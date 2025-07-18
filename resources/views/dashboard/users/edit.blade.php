@extends('layouts.dashboard')

@section('title', 'Edit User')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="bi bi-person-gear"></i> Edit User</h1>
    <a href="{{ route('dashboard.users.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Users
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">User Information</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('dashboard.users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $user->name) }}"
                                       placeholder="Full Name"
                                       required>
                                <label for="name">Full Name</label>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $user->email) }}"
                                       placeholder="name@example.com"
                                       required>
                                <label for="email">Email Address</label>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="role" class="form-label">User Role</label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                                            <option value="">Select Role</option>
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="manager" {{ old('role', $user->role) === 'manager' ? 'selected' : '' }}>Manager</option>
                                <option value="employee" {{ old('role', $user->role) === 'employee' ? 'selected' : '' }}>Employee</option>
                                <option value="customer" {{ old('role', $user->role) === 'customer' ? 'selected' : '' }}>Customer</option>
                        </select>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="password"
                                       class="form-control @error('password') is-invalid @enderror"
                                       id="password"
                                       name="password"
                                       placeholder="New Password">
                                <label for="password">New Password (Optional)</label>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Leave blank to keep current password</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input type="password"
                                       class="form-control @error('password_confirmation') is-invalid @enderror"
                                       id="password_confirmation"
                                       name="password_confirmation"
                                       placeholder="Confirm Password">
                                <label for="password_confirmation">Confirm Password</label>
                                @error('password_confirmation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <a href="{{ route('dashboard.users.index') }}" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-x"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check"></i> Update User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
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
                    <h6>Current Role</h6>
                    @php
                        $roleColors = [
                            'admin' => 'danger',
                            'manager' => 'warning',
                            'user' => 'success'
                        ];
                    @endphp
                    <span class="badge bg-{{ $roleColors[$user->role] ?? 'secondary' }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
                <div class="mb-3">
                    <h6>Created</h6>
                    <p class="text-muted">{{ $user->created_at->format('M j, Y g:i A') }}</p>
                </div>
                <div class="mb-3">
                    <h6>Last Updated</h6>
                    <p class="text-muted">{{ $user->updated_at->format('M j, Y g:i A') }}</p>
                </div>
                @if($user->email_verified_at)
                <div class="mb-3">
                    <h6>Email Verified</h6>
                    <p class="text-muted">{{ $user->email_verified_at->format('M j, Y g:i A') }}</p>
                </div>
                @endif
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-shield-check"></i> Role Information</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-danger"><i class="bi bi-shield-fill"></i> Admin</h6>
                    <p class="text-muted small">Full access to all features including user management, product management, and order management.</p>
                </div>
                <div class="mb-3">
                    <h6 class="text-warning"><i class="bi bi-person-gear"></i> Manager</h6>
                    <p class="text-muted small">Can manage products and orders. Has access to dashboard but cannot manage users.</p>
                </div>
                <div class="mb-3">
                    <h6 class="text-success"><i class="bi bi-person"></i> User</h6>
                    <p class="text-muted small">Basic user with limited access. Can only view assigned tasks and basic information.</p>
                </div>
            </div>
        </div>

        @if($user->id === auth()->id())
        <div class="alert alert-info mt-4">
            <i class="bi bi-info-circle"></i>
            <strong>Note:</strong> You are editing your own account. Be careful with role changes.
        </div>
        @endif
    </div>
</div>
@endsection
