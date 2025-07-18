# Laravel CMS Testing Guide

This document provides comprehensive information about the testing suite for the Laravel CMS project.

## Test Structure

The project includes both **Feature Tests** and **Unit Tests** organized as follows:

### Feature Tests (`tests/Feature/`)
- **AuthenticationTest.php** - Tests user authentication flows
- **ProductTest.php** - Tests product management API endpoints
- **OrderTest.php** - Tests order management API endpoints

### Unit Tests (`tests/Unit/`)
- **AuthServiceTest.php** - Tests authentication service logic
- **ProductServiceTest.php** - Tests product service business logic
- **OrderServiceTest.php** - Tests order service business logic

## Test Commands

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suites
```bash
# Feature tests only
php artisan test --testsuite=Feature

# Unit tests only
php artisan test --testsuite=Unit
```

### Run Specific Test Files
```bash
# Authentication tests
php artisan test tests/Feature/AuthenticationTest.php

# Product tests
php artisan test tests/Feature/ProductTest.php

# Order tests
php artisan test tests/Feature/OrderTest.php

# Auth service tests
php artisan test tests/Unit/AuthServiceTest.php

# Product service tests
php artisan test tests/Unit/ProductServiceTest.php

# Order service tests
php artisan test tests/Unit/OrderServiceTest.php
```

### Run Specific Test Methods
```bash
# Run a specific test method
php artisan test --filter test_method_name

# Example: Run only login tests
php artisan test --filter login
```

## Test Categories

### Authentication Tests
Tests user registration, login, logout, and authentication flows:

- ✅ User registration with valid data
- ✅ User registration with invalid data
- ✅ User login with valid credentials
- ✅ User login with invalid credentials
- ✅ User logout
- ✅ Authenticated user access
- ✅ Unauthenticated access denial
- ✅ Password validation rules

### Product Tests
Tests product management functionality:

- ✅ Admin/Manager can create products
- ✅ Employee cannot create products
- ✅ Product listing and pagination
- ✅ Product search and filtering
- ✅ Product updates and deletion
- ✅ Stock management
- ✅ Category management
- ✅ Low stock alerts
- ✅ Product validation rules

### Order Tests
Tests order management functionality:

- ✅ Admin/Manager can create orders
- ✅ Employee cannot create orders
- ✅ Order listing and filtering
- ✅ Order status updates
- ✅ Order assignment to users
- ✅ Order statistics
- ✅ Stock validation during order creation
- ✅ Order validation rules

### Service Tests
Tests business logic in service classes:

- ✅ AuthService: User registration, login, permissions
- ✅ ProductService: CRUD operations, stock management, search
- ✅ OrderService: Order creation, status updates, statistics

## Test Data

### Test Users
The tests create the following test users:

- **Admin User** (`admin@test.com`) - Full permissions
- **Manager User** (`manager@test.com`) - Product and order management
- **Employee User** (`employee@test.com`) - Limited permissions
- **Customer User** (`customer@test.com`) - Order creation only

### Test Products
Sample products are created for testing:

- Electronics products with various prices and stock levels
- Furniture products for category testing
- Products with low stock for inventory testing

### Test Orders
Sample orders are created with various statuses:

- Pending orders
- Processing orders
- Shipped orders
- Delivered orders
- Cancelled orders

### Authentication
- Tests use Laravel Sanctum for API authentication
- Test tokens are generated for each test user
- Authentication state is properly managed

### Validation
- All input validation is tested
- Error responses are verified
- Custom validation rules are tested

## Best Practices

### Test Organization
- Each test method tests one specific functionality
- Tests are named descriptively using `test_` prefix
- Tests are grouped logically within test classes

### Test Data
- Test data is created in `setUp()` methods
- Each test is independent and doesn't rely on other tests
- Test data is cleaned up automatically

### Assertions
- Multiple assertions are used to verify complete functionality
- Database state is verified after operations
- API responses are thoroughly checked

### Error Handling
- Both success and failure scenarios are tested
- Exception handling is verified
- Error messages are validated
