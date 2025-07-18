# Backend Documentation

This document provides a comprehensive overview of the Laravel CMS backend architecture, components, and implementation details.

## Libraries used

- **spatie/laravel-permission**: Role and permission management  
  Install with:  
  ```bash
  composer require spatie/laravel-permission
  php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
  php artisan migrate
  ```

- **laravel/sanctum**: API authentication  
  Install with:  
  ```bash
  composer require laravel/sanctum
  php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
  php artisan migrate
  ```
## Architecture Overview

The CMS backend is built using Laravel 10 with a clean, scalable architecture following best practices:

- **Repository Pattern** - Data access abstraction
- **Service Layer** - Business logic encapsulation
- **Request Validation** - Input validation and sanitization
- **API Resources** - Response formatting
- **Role-Based Access Control** - User authorization
- **Laravel Sanctum** - API authentication

## Project Structure

```
app/
├── Http/
│   ├── Controllers/          # API Controllers
│   ├── Requests/            # Form Request Validation
│   └── Resources/           # API Response Resources
├── Models/                  # Eloquent Models
├── Repositories/            # Data Access Layer
├── Services/                # Business Logic Layer
├── Providers/               # Service Providers
└── Exceptions/              # Custom Exceptions
```

## Core Components

### 1. Models

#### User Model
```php
// app/Models/User.php
- Authentication with Laravel Sanctum
- Role-based access control
- Password hashing
- Fillable fields validation
```

#### Product Model
```php
// app/Models/Product.php
- Product management with categories
- Stock tracking
- Soft deletes
- Image handling
- Search and filtering capabilities
```

#### Order Model
```php
// app/Models/Order.php
- Order lifecycle management
- Status tracking
- User assignment
- Customer information
- Order items relationship
```

### 2. Repositories

The repository pattern provides a clean abstraction layer for data access:

#### Base Repository
```php
// app/Repositories/BaseRepository.php
- Common CRUD operations
- Pagination support
- Search functionality
- Filtering capabilities
```

#### Product Repository
```php
// app/Repositories/ProductRepository.php
- Product-specific queries
- Category filtering
- Stock management
- Search implementation
```

#### Order Repository
```php
// app/Repositories/OrderRepository.php
- Order-specific queries
- Status filtering
- Assignment queries
- Statistics aggregation
```

### 3. Services

Business logic is encapsulated in service classes:

#### AuthService
```php
// app/Services/AuthService.php
- User registration and login
- Token management
- Password validation
- Role assignment
```

#### ProductService
```php
// app/Services/ProductService.php
- Product CRUD operations
- Stock management
- Category handling
- Image processing
```

#### OrderService
```php
// app/Services/OrderService.php
- Order lifecycle management
- Status transitions
- Assignment logic
- Statistics calculation
```

### 4. Controllers

API controllers handle HTTP requests and responses:

#### AuthController
```php
// app/Http/Controllers/AuthController.php
- POST /api/auth/register
- POST /api/auth/login
- POST /api/auth/logout
- GET /api/auth/account
```

#### ProductController
```php
// app/Http/Controllers/ProductController.php
- GET /api/products (with pagination, search, filtering)
- GET /api/products/{id}
- POST /api/products
- PUT /api/products/{id}
- DELETE /api/products/{id}
- GET /api/products/categories
- GET /api/products/low-stock
- PATCH /api/products/{id}/stock
```

#### OrderController
```php
// app/Http/Controllers/OrderController.php
- GET /api/orders (with pagination, filtering)
- GET /api/orders/{id}
- POST /api/orders
- PUT /api/orders/{id}
- DELETE /api/orders/{id}
- PATCH /api/orders/{id}/status
- PATCH /api/orders/{id}/assign
- GET /api/orders/statistics
- GET /api/orders/statuses
```

### 5. Request Validation

Form request classes handle input validation:

#### Auth Requests
```php
// app/Http/Requests/Auth/RegisterRequest.php
- Email validation
- Password strength requirements
- Name validation
- Role assignment rules

// app/Http/Requests/Auth/LoginRequest.php
- Email format validation
- Password presence
```

#### Product Requests
```php
// app/Http/Requests/Product/StoreProductRequest.php
- Name, description validation
- Price and stock validation
- Category validation
- Image upload rules

// app/Http/Requests/Product/UpdateProductRequest.php
- Conditional validation rules
- Partial updates support
```

#### Order Requests
```php
// app/Http/Requests/Order/StoreOrderRequest.php
- Customer information validation
- Order items validation
- Status validation

// app/Http/Requests/Order/UpdateOrderRequest.php
- Conditional validation
- Status transition rules
```

### 6. API Resources

Response formatting and transformation:

#### ProductResource
```php
// app/Http/Resources/ProductResource.php
- Product data formatting
- Category information
- Stock status
- Image URLs
```

#### OrderResource
```php
// app/Http/Resources/OrderResource.php
- Order data formatting
- Customer information
- Status information
- Assignment details
```

## Authentication & Authorization

### Laravel Sanctum Integration

The application uses Laravel Sanctum for API authentication:

```php
// config/sanctum.php
- Token-based authentication
- Stateful authentication for web routes
- Stateless authentication for API routes
- Token expiration configuration
```

### Role-Based Access Control

User roles and permissions:

```php
// User roles: admin, manager, user
- Admin: Full access to all features
- Manager: Product and order management
- User: Basic access to products and orders
```

### Middleware

Custom middleware for role-based access:

```php
// app/Http/Middleware/CheckRole.php
- Role verification
- Permission checking
- Access control
```

## Database Design

### Migrations

#### Users Table
```php
// database/migrations/2014_10_12_000000_create_users_table.php
- id (primary key)
- name
- email (unique)
- password
- role (enum: admin, manager, user)
- email_verified_at
- remember_token
- timestamps
```

#### Products Table
```php
// database/migrations/xxxx_create_products_table.php
- id (primary key)
- name
- description
- price (decimal)
- stock_quantity (integer)
- category (string)
- image_path (nullable)
- created_by (foreign key to users)
- timestamps
- deleted_at (soft deletes)
```

#### Orders Table
```php
// database/migrations/xxxx_create_orders_table.php
- id (primary key)
- customer_name
- customer_email
- customer_phone
- status (enum: pending, processing, completed, cancelled)
- total_amount (decimal)
- assigned_to (foreign key to users, nullable)
- created_by (foreign key to users)
- timestamps
- deleted_at (soft deletes)
```

#### Order Items Table
```php
// database/migrations/xxxx_create_order_items_table.php
- id (primary key)
- order_id (foreign key)
- product_id (foreign key)
- quantity (integer)
- unit_price (decimal)
- total_price (decimal)
- timestamps
```

### Relationships

```php
// User Model
- hasMany(Product::class, 'created_by')
- hasMany(Order::class, 'created_by')
- hasMany(Order::class, 'assigned_to')

// Product Model
- belongsTo(User::class, 'created_by')
- hasMany(OrderItem::class)

// Order Model
- belongsTo(User::class, 'created_by')
- belongsTo(User::class, 'assigned_to')
- hasMany(OrderItem::class)

// OrderItem Model
- belongsTo(Order::class)
- belongsTo(Product::class)
```

## API Response Format

### Success Response
```json
{
    "success": true,
    "data": {
        // Resource data
    },
    "message": "Operation completed successfully"
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error message",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

### Paginated Response
```json
{
    "success": true,
    "data": {
        "data": [
            // Resource items
        ],
        "current_page": 1,
        "per_page": 15,
        "total": 100,
        "last_page": 7
    }
}
```

## Error Handling

### Custom Exceptions

```php
// app/Exceptions/
- ProductNotFoundException
- OrderNotFoundException
- InsufficientStockException
- InvalidStatusTransitionException
```

### Global Exception Handler

```php
// app/Exceptions/Handler.php
- API error formatting
- Validation error handling
- Authentication error responses
- Database error handling
```

## Testing

### Test Structure

```
tests/
├── Feature/                 # Feature tests
│   ├── Auth/               # Authentication tests
│   ├── Product/            # Product API tests
│   └── Order/              # Order API tests
└── Unit/                   # Unit tests
    ├── Services/           # Service layer tests
    └── Repositories/       # Repository tests
```

### Test Coverage

- **Authentication**: Registration, login, logout, account access
- **Products**: CRUD operations, search, filtering, stock management
- **Orders**: CRUD operations, status transitions, assignment
- **Services**: Business logic validation
- **Repositories**: Data access layer testing

## Performance Optimization

### Database Optimization

- **Indexes**: Proper indexing on frequently queried columns
- **Eager Loading**: Preventing N+1 query problems
- **Query Optimization**: Efficient database queries

### Caching Strategy

- **Route Caching**: `php artisan route:cache`
- **Config Caching**: `php artisan config:cache`
- **View Caching**: `php artisan view:cache`

### API Optimization

- **Pagination**: Large dataset handling
- **Filtering**: Efficient data filtering
- **Search**: Optimized search functionality

## Security Features

### Input Validation

- **Request Validation**: Comprehensive input validation
- **SQL Injection Prevention**: Eloquent ORM protection
- **XSS Protection**: Output sanitization

### Authentication Security

- **Password Hashing**: Bcrypt encryption
- **Token Security**: Secure token generation
- **Session Security**: Secure session handling

### Authorization

- **Role-Based Access**: User role verification
- **Resource Protection**: Route-level protection
- **Permission Checking**: Granular permission control

## Configuration

### Environment Variables

```env
# Application
APP_NAME="CMS API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cms
DB_USERNAME=root
DB_PASSWORD=root

# Mail
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:8000
SESSION_DOMAIN=localhost
```

### Service Providers

```php
// app/Providers/
- AppServiceProvider: Application bootstrapping
- AuthServiceProvider: Authentication configuration
- RouteServiceProvider: Route configuration
```

## Deployment Considerations

### Production Setup

1. **Environment Configuration**: Set `APP_ENV=production`
2. **Debug Mode**: Disable debug mode (`APP_DEBUG=false`)
3. **Database**: Use production database credentials
4. **Caching**: Enable all caching mechanisms
5. **Logging**: Configure proper logging
6. **Security**: Review security settings

### Performance Monitoring

- **Application Logs**: Monitor application performance
- **Database Logs**: Monitor query performance
- **Error Tracking**: Implement error tracking
- **Health Checks**: API health monitoring

## Maintenance

### Regular Tasks

- **Database Backups**: Regular backup scheduling
- **Log Rotation**: Log file management
- **Cache Clearing**: Periodic cache maintenance
- **Security Updates**: Keep dependencies updated

### Monitoring

- **Application Health**: Regular health checks
- **Performance Metrics**: Monitor response times
- **Error Rates**: Track error occurrences
- **Resource Usage**: Monitor server resources 
