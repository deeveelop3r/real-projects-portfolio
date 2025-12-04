# E-Commerce Platform - Setup Guide

## Prerequisites

- PHP 8.1+
- Node.js 16+
- Composer
- MySQL 8.0+ / SQLite
- Git

## Local Development Setup

### 1. Clone Repository

```bash
git clone https://github.com/deeveelop3r/ecommerce-platform.git
cd ecommerce-platform
```

### 2. Backend Setup

```bash
cd backend

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database
# Edit .env and set your database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=

# Run migrations
php artisan migrate

# Seed sample data
php artisan db:seed --class=ProductSeeder

# Configure payment services (if needed)
# Edit config/payments.php with your Stripe/PayPal credentials

# Start Laravel server
php artisan serve
```

### 3. Frontend Setup

```bash
cd ../frontend

# Install dependencies
npm install

# Create .env file
cp .env.example .env

# Configure API endpoint
VITE_API_URL=http://localhost:8000/api

# Start development server
npm run dev
```

The frontend will run on `http://localhost:5173`

## Database Setup

### Create Database

```bash
# MySQL
mysql -u root -p
CREATE DATABASE ecommerce CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce;
```

### Run Migrations

```bash
cd backend
php artisan migrate
```

### Seed Data

```bash
php artisan db:seed
# or specific seeder
php artisan db:seed --class=ProductSeeder
php artisan db:seed --class=UserSeeder
```

## Configuration

### Environment Variables (.env)

```env
APP_NAME="E-Commerce Platform"
APP_ENV=local
APP_KEY=base64:...
APP_URL=http://localhost:8000
QUEUE_CONNECTION=database

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=redis
QUEUE_CONNECTION=redis

STRIPE_PUBLIC_KEY=pk_test_...
STRIPE_SECRET_KEY=sk_test_...

PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=...
PAYPAL_CLIENT_SECRET=...

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=...
MAIL_PASSWORD=...

JWT_SECRET=...
```

## API Documentation

### Authentication

All API endpoints (except public ones) require authentication using JWT tokens.

```bash
# Get token
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}

# Use token in subsequent requests
Authorization: Bearer <token>
```

### Products API

```
GET /api/products                  # List all products
GET /api/products?page=1           # Pagination
GET /api/products?search=keyword   # Search
GET /api/products?category=xyz     # Filter by category
GET /api/products/:id              # Get single product
```

### Cart API

```
GET /api/cart                      # Get cart items
POST /api/cart                     # Add to cart
PUT /api/cart/:id                  # Update quantity
DELETE /api/cart/:id               # Remove from cart
```

### Orders API

```
POST /api/orders                   # Create order
GET /api/orders                    # Get user orders
GET /api/orders/:id                # Get order details
DELETE /api/orders/:id/cancel      # Cancel order
```

### Payments API

```
POST /api/payments/stripe          # Process Stripe payment
POST /api/payments/paypal          # Process PayPal payment
GET /api/payments/:order_id/status # Check payment status
```

## Testing

### Run Tests

```bash
cd backend

# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ProductControllerTest.php

# Run with coverage
php artisan test --coverage
```

## Deployment

See [DEPLOYMENT.md](./DEPLOYMENT.md) for production deployment instructions.

## Troubleshooting

### Database Connection Error

```
SQLSTATE[HY000]: General error: 3 Error writing to file
```

**Solution**: Check database write permissions and ensure MySQL service is running.

### CORS Error

Add to `config/cors.php`:

```php
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
```

### Payment Gateway Issues

- Ensure API keys are correctly configured in `.env`
- Check payment gateway account status
- Review logs in `storage/logs/`

## Support & Contact

- Email: support@ecommerce-platform.com
- GitHub Issues: https://github.com/deeveelop3r/ecommerce-platform/issues
- Documentation: https://docs.ecommerce-platform.com
