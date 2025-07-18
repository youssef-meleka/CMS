# CMS API

This is a Laravel-based Content Management System API that provides endpoints for managing users, products, and orders.

## API Endpoints

### Authentication

- `POST /api/auth/register` - Register a new user
- `POST /api/auth/login` - Login and get token
- `POST /api/auth/logout` - Logout user (requires authentication)
- `GET /api/auth/account` - Get authenticated user details (requires authentication)

### Products

- `GET /api/products` - List all products (supports pagination, search, category filtering)
- `GET /api/products/{id}` - Get a specific product
- `POST /api/products` - Create a new product
- `PUT /api/products/{id}` - Update a product
- `DELETE /api/products/{id}` - Delete a product
- `GET /api/products/categories` - Get list of product categories
- `GET /api/products/low-stock` - Get products with low stock
- `PATCH /api/products/{id}/stock` - Update product stock quantity

### Orders

- `GET /api/orders` - List all orders (supports pagination, status/customer/assignment filtering)
- `GET /api/orders/{id}` - Get a specific order
- `POST /api/orders` - Create a new order
- `PUT /api/orders/{id}` - Update an order
- `DELETE /api/orders/{id}` - Delete an order
- `PATCH /api/orders/{id}/status` - Update order status
- `PATCH /api/orders/{id}/assign` - Assign order to a user
- `GET /api/orders/statistics` - Get order statistics
- `GET /api/orders/statuses` - Get available order statuses

## Postman Collection

A complete Postman collection is included in the project. To use it:

1. Import the `CMS_Postman_Collection.json` file into Postman
2. Set up an environment with the following variables:
   - `base_url`: `http://127.0.0.1:8000/api`
   - `user_token`: (this will be automatically set after login)

The collection is organized into folders:
- Authentication
- Products
- Orders

Each request includes the necessary headers, body parameters, and authorization settings.

## Authentication

The API uses Laravel Sanctum for authentication. To access protected endpoints:

1. Register or login to get a token
2. Include the token in the Authorization header as a Bearer token

## Running the API

```bash
# Install dependencies
composer install

# Run migrations and seeders
php artisan migrate --seed

# Start the server
php artisan serve
```

You can then access the API at `http://127.0.0.1:8000/api`.
