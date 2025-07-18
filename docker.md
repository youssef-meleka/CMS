# Docker Setup Guide

This guide provides step-by-step instructions for building and running the Laravel CMS application using Docker. The setup is designed to work on both Windows and Linux platforms.

## Prerequisites

Before you begin, ensure you have the following installed on your system:

- **Docker Desktop** (for Windows/Mac) or **Docker Engine** (for Linux)
- **Docker Compose** (usually included with Docker Desktop)
- **Git** (for cloning the repository)

## Project Structure

The Docker setup consists of the following files:

- `Dockerfile` - Defines the PHP 8.2 Apache container
- `docker-compose.yml` - Orchestrates the application and database services
- `000-default.conf` - Apache virtual host configuration

## Step-by-Step Setup

### 1. Clone the Repository

```bash
git clone <repository-url>
cd cms
```

### 2. Environment Configuration

Create a `.env` file for the Docker environment:

```bash
# Copy the example environment file
cp .env.example .env
```

Update the `.env` file with the following Docker-specific configurations:

```env

# Database Configuration for Docker
DB_CONNECTION=mysql
DB_HOST=cms_mysql
DB_PORT=3306
DB_DATABASE=cms
DB_USERNAME=root
DB_PASSWORD=root

```

### 3. Build and Start the Application

```bash
# Build the Docker images
docker-compose build

# Start the services
docker-compose up -d

# Check if services are running
docker-compose ps
```

### 4. Application Setup

Once the containers are running, you need to set up the Laravel application:

```bash
# Open docker container
docker exec -it cms_backend sh

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed the database with sample data
php artisan db:seed

```

### 5. Verify the Setup

Check if the application is running correctly:

```bash
# Check application logs
docker-compose logs backend

# Test the API endpoint
curl http://localhost:8000/api/products
```

## Service Details

### Backend Service (Laravel + Apache)

- **Container Name**: `cms_backend`
- **Port**: `8000:80` (host:container)
- **Base Image**: `php:8.2-apache`
- **Working Directory**: `/var/www/html`
- **Document Root**: `/var/www/html/public`

**Features**:
- PHP 8.2 with Apache
- Composer for dependency management
- Required PHP extensions: pdo_mysql, zip, gd, calendar
- Apache mod_rewrite enabled
- Proper file permissions for Laravel

### MySQL Service

- **Container Name**: `cms_mysql`
- **Port**: `3308:3306` (host:container)
- **Base Image**: `mysql:8.0`
- **Database**: `cms`
- **Root Password**: `root`

**Features**:
- MySQL 8.0
- Health checks enabled
- Persistent data storage
- Network isolation

## Docker Commands Reference

### Basic Operations

```bash
# Start services
docker-compose up -d

# Stop services
docker-compose down

# View logs
docker-compose logs -f backend
docker-compose logs -f mysql

# Access container shell
docker-compose exec backend bash
docker-compose exec mysql mysql -u root -p

# Rebuild containers
docker-compose build --no-cache
docker-compose up -d
```

### Development Commands

```bash
# Run Laravel commands
docker-compose exec backend php artisan migrate
docker-compose exec backend php artisan db:seed
docker-compose exec backend php artisan cache:clear
docker-compose exec backend php artisan config:clear

# Run tests
docker-compose exec backend php artisan test

# Install Composer dependencies
docker-compose exec backend composer install
docker-compose exec backend composer update
```

### Database Operations

```bash
# Access MySQL
docker-compose exec mysql mysql -u root -p

# Backup database
docker-compose exec mysql mysqldump -u root -proot cms > backup.sql

# Restore database
docker-compose exec -T mysql mysql -u root -proot cms < backup.sql
```

## Troubleshooting

### Common Issues

#### 1. Port Already in Use
If you get a port conflict error:

```bash
# Check what's using the port
netstat -tulpn | grep :8000  # Linux
netstat -an | findstr :8000  # Windows

# Change the port in docker-compose.yml
ports:
  - "8001:80"  # Use a different port
```

#### 2. Permission Issues
If you encounter permission problems:

```bash
# Fix storage permissions
docker-compose exec backend chown -R www-data:www-data storage bootstrap/cache
docker-compose exec backend chmod -R 775 storage bootstrap/cache
```

#### 3. Database Connection Issues
If the application can't connect to the database:

```bash
# Check if MySQL is running
docker-compose ps mysql

# Check MySQL logs
docker-compose logs mysql

# Wait for MySQL to be ready
docker-compose exec backend php artisan migrate --force
```

#### 4. Application Key Issues
If you get encryption key errors:

```bash
# Generate a new application key
docker-compose exec backend php artisan key:generate
```

### Performance Optimization

#### 1. Volume Mounting for Development
For better development experience, the entire project is mounted as a volume. This means changes to your code are immediately reflected without rebuilding the container.

#### 2. Caching
Enable OPcache for better performance in production:

```dockerfile
# Add to Dockerfile
RUN docker-php-ext-install opcache
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini
```

#### 3. Database Optimization
For production, consider using a managed database service or optimizing MySQL configuration.

## Production Deployment

For production deployment, consider the following modifications:

1. **Environment Variables**: Use proper production values
2. **SSL/TLS**: Configure HTTPS
3. **Database**: Use a managed database service
4. **Caching**: Implement Redis for caching
5. **Monitoring**: Add health checks and monitoring
6. **Security**: Review and harden security configurations

## Cleanup

To completely remove the Docker setup:

```bash
# Stop and remove containers
docker-compose down

# Remove volumes (this will delete all data)
docker-compose down -v

# Remove images
docker-compose down --rmi all

# Clean up Docker system
docker system prune -a
```

## Support

If you encounter issues with the Docker setup:

1. Check the troubleshooting section above
2. Review the Docker logs: `docker-compose logs`
3. Ensure all prerequisites are installed correctly
4. Verify your `.env` configuration matches the Docker setup 
