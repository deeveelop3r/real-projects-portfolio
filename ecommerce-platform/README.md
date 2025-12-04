# E-Commerce Platform

A fully functional e-commerce website with shopping cart and payment integration.

## Features

### Customer Features
- ✅ User registration and authentication
- ✅ Product browsing and search
- ✅ Shopping cart management
- ✅ Checkout process
- ✅ Payment gateway integration (Stripe/PayPal)
- ✅ Order tracking
- ✅ User reviews and ratings
- ✅ Wishlist functionality

### Admin Features
- ✅ Product management (CRUD)
- ✅ Order management
- ✅ Inventory management
- ✅ Customer management
- ✅ Sales reports and analytics
- ✅ Payment settlement
- ✅ Discount/Coupon management

## Tech Stack

- **Backend**: Laravel 10
- **Database**: MySQL/SQLite
- **Frontend**: Vue.js 3 / Bootstrap 5
- **Payment**: Stripe API / PayPal API
- **Authentication**: Laravel Sanctum
- **Real-time**: Laravel Echo + WebSocket

## Project Structure

```
ecommerce-platform/
├── backend/                 # Laravel backend
│   ├── app/
│   │   ├── Models/
│   │   │   ├── Product.php
│   │   │   ├── Order.php
│   │   │   ├── OrderItem.php
│   │   │   ├── User.php
│   │   │   └── Payment.php
│   │   ├── Http/Controllers/
│   │   │   ├── ProductController.php
│   │   │   ├── CartController.php
│   │   │   ├── OrderController.php
│   │   │   ├── PaymentController.php
│   │   │   └── Admin/ProductAdminController.php
│   │   └── Services/
│   │       ├── PaymentService.php
│   │       ├── CartService.php
│   │       └── OrderService.php
│   ├── routes/
│   │   ├── api.php          # API routes
│   │   └── web.php          # Web routes
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   └── config/
│       └── payments.php
├── frontend/                # Vue.js frontend
│   ├── src/
│   │   ├── components/
│   │   ├── pages/
│   │   ├── stores/          # Pinia state management
│   │   └── services/        # API services
│   └── package.json
└── docs/
    ├── API.md
    ├── SETUP.md
    └── DEPLOYMENT.md
```

## Installation

### Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan seed
php artisan serve
```

### Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

## Database Schema

### Products Table
- id, name, description, price, stock, category, image_url, created_at, updated_at

### Orders Table
- id, user_id, total_amount, status, payment_status, created_at, updated_at

### Order Items Table
- id, order_id, product_id, quantity, price, subtotal

### Cart Items Table
- id, user_id, product_id, quantity, created_at, updated_at

### Payments Table
- id, order_id, amount, payment_method, transaction_id, status, created_at, updated_at

## API Endpoints

### Products
- `GET /api/products` - Get all products
- `GET /api/products/:id` - Get product details
- `GET /api/products/search?q=keyword` - Search products

### Cart
- `GET /api/cart` - Get cart items
- `POST /api/cart` - Add to cart
- `PUT /api/cart/:id` - Update quantity
- `DELETE /api/cart/:id` - Remove from cart

### Orders
- `POST /api/orders` - Create order
- `GET /api/orders/:id` - Get order details
- `GET /api/orders` - Get user orders

### Payments
- `POST /api/payments/process` - Process payment
- `POST /api/payments/webhook` - Payment webhook

## Security Features

- ✅ CSRF Protection
- ✅ Rate Limiting
- ✅ Input Validation
- ✅ SQL Injection Prevention (ORM)
- ✅ XSS Protection
- ✅ HTTPS/SSL Required
- ✅ Secure Payment Processing
- ✅ JWT Token Authentication

## Deployment

### Production Checklist
- [ ] Environment variables configured
- [ ] Database migrations run
- [ ] SSL certificate installed
- [ ] Payment gateway configured
- [ ] Email service configured
- [ ] Redis cache configured
- [ ] CDN for images configured
- [ ] Backup strategy set up
- [ ] Monitoring and logging configured
- [ ] Load balancer configured (if needed)

## Performance Optimization

- Database indexing on frequently queried columns
- Product image optimization and lazy loading
- API response caching
- Database query optimization
- Frontend bundle optimization
- CDN for static assets

## License

MIT License - See LICENSE file for details

## Support

For issues and support, please visit: https://github.com/deeveelop3r/ecommerce-platform
