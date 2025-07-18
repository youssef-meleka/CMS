# Laravel CMS System

A comprehensive Content Management System built with Laravel 10, featuring user authentication, role-based access control, product management, and order processing.

## 📚 Documentation

This project includes comprehensive documentation to help you get started and understand the system:

- **[Backend Documentation](backend.md)** - Detailed explanation of the Laravel backend architecture, components, and implementation
- **[Dashboard Documentation](dashboard.md)** - Complete guide to the CMS dashboard interface and features
- **[Docker Setup Guide](docker.md)** - Step-by-step instructions for running the application with Docker
- **[Testing Guide](testing.md)** - Comprehensive testing documentation and examples

## 🚀 Quick Start

### Option 1: Local Development

```bash
# Clone the repository
git clone <repository-url>
cd cms

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env file

# Run migrations and seeders
php artisan migrate --seed

# Start the development server
php artisan serve
```

### Option 2: Docker Setup

For a complete Docker setup with database, see the **[Docker Setup Guide](docker.md)**.

```bash
# Build and start containers
docker-compose up -d

# Set up the application
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan migrate --seed
```

## 🏗️ System Architecture

The CMS follows Laravel best practices with a clean, scalable architecture:

- **Repository Pattern** - Data access abstraction
- **Service Layer** - Business logic encapsulation  
- **Request Validation** - Input validation and sanitization
- **API Resources** - Response formatting
- **Role-Based Access Control** - User authorization
- **Laravel Sanctum** - API authentication

For detailed architecture information, see **[Backend Documentation](backend.md)**.

## 🔐 Authentication & Authorization

The system uses Laravel Sanctum for API authentication with three user roles:

- **Admin** - Full access to all features including dashboard and user management
- **Manager** - Product and order management with dashboard access
- **User** - Basic access to products and orders (API only)

## 🖥️ Dashboard Interface

The system includes a comprehensive web dashboard for administrators and managers:

- **Modern UI** - Responsive design with Bootstrap 5
- **User Management** - Create, edit, and manage users
- **Product Management** - Full product catalog management
- **Order Management** - Order tracking and status updates
- **Statistics** - Real-time system analytics
- **Role-Based Access** - Secure access control

Access the dashboard at: `http://your-domain.com/dashboard/login`

## 📋 API Endpoints

### Authentication
- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - Login and get token
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/account` - Get authenticated user details

### Products
- `GET /api/products` - List products (with pagination, search, filtering)
- `GET /api/products/{id}` - Get specific product
- `POST /api/products` - Create new product
- `PUT /api/products/{id}` - Update product
- `DELETE /api/products/{id}` - Delete product
- `GET /api/products/categories` - Get product categories
- `GET /api/products/low-stock` - Get low stock products
- `PATCH /api/products/{id}/stock` - Update product stock

### Orders
- `GET /api/orders` - List orders (with pagination, filtering)
- `GET /api/orders/{id}` - Get specific order
- `POST /api/orders` - Create new order
- `PUT /api/orders/{id}` - Update order
- `DELETE /api/orders/{id}` - Delete order
- `PATCH /api/orders/{id}/status` - Update order status
- `PATCH /api/orders/{id}/assign` - Assign order to user
- `GET /api/orders/statistics` - Get order statistics
- `GET /api/orders/statuses` - Get available statuses

## 🧪 Testing

The project includes comprehensive test coverage:

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run with coverage (requires Xdebug)
php artisan test --coverage
```

For detailed testing information, see **[Testing Guide](testing.md)**.

## 📦 Postman Collection

A complete Postman collection is included for API testing:

1. Import `CMS_Postman_Collection.json` into Postman
2. Set up environment variables:
   - `base_url`: `http://127.0.0.1:8000/api`
   - `user_token`: (auto-set after login or register)

## 🔧 Configuration

### Environment Variables

Key configuration options in `.env`:

```env
APP_NAME="CMS API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cms
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS="noreply@example.com"
```

### Database Setup

The system includes migrations for:
- Users (with role-based access)
- Products (with categories and stock tracking)
- Orders (with status tracking and assignment)
- Order Items (for order details)

## 🚀 Deployment

For production deployment:

1. Set `APP_ENV=production` and `APP_DEBUG=false`
2. Configure production database credentials
3. Enable caching: `php artisan config:cache`
4. Set up proper logging and monitoring
5. Review security settings

For Docker deployment, see **[Docker Setup Guide](docker.md)**.

