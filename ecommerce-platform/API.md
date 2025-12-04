# E-Commerce Platform - API Documentation

**Base URL**: `https://api.ecommerce-platform.com`

**API Version**: `v1`

## Authentication

### JWT Authentication

All protected endpoints require a Bearer token in the Authorization header:

```
Authorization: Bearer your_jwt_token_here
```

### Register

```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "token": "eyJhbGc..."
  }
}
```

### Login

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "eyJhbGc..."
  }
}
```

### Logout

```http
POST /api/auth/logout
Authorization: Bearer your_token
```

## Products

### Get All Products

```http
GET /api/products?page=1&per_page=12&sort_by=created_at&sort_order=desc
```

**Query Parameters:**
- `page` (integer, default: 1) - Page number
- `per_page` (integer, default: 12) - Items per page
- `search` (string) - Search in name and description
- `category` (string) - Filter by category
- `min_price` (decimal) - Minimum price
- `max_price` (decimal) - Maximum price
- `sort_by` (string, default: created_at) - Sort field
- `sort_order` (string, default: desc) - Sort direction (asc/desc)

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "name": "Laptop Pro",
        "description": "High-performance laptop",
        "price": "999.99",
        "stock": 50,
        "category": "Electronics",
        "image_url": "https://...",
        "rating": 4.5,
        "reviews_count": 150,
        "created_at": "2024-01-15T10:30:00Z"
      }
    ],
    "pagination": {
      "total": 245,
      "count": 12,
      "per_page": 12,
      "current_page": 1,
      "last_page": 21
    }
  }
}
```

### Get Single Product

```http
GET /api/products/{product_id}
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Laptop Pro",
    "description": "High-performance laptop",
    "long_description": "A powerful laptop with...",
    "price": "999.99",
    "stock": 50,
    "category": "Electronics",
    "sku": "LP-001",
    "image_url": "https://...",
    "images": ["https://...", "https://..."],
    "specifications": {
      "processor": "Intel i9",
      "ram": "16GB",
      "storage": "512GB SSD"
    },
    "rating": 4.5,
    "reviews_count": 150,
    "is_active": true,
    "is_featured": true,
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-20T15:45:00Z"
  }
}
```

### Get Product Categories

```http
GET /api/products/categories
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    "Electronics",
    "Clothing",
    "Books",
    "Home & Garden"
  ]
}
```

### Get Featured Products

```http
GET /api/products/featured
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": [
    { "id": 1, "name": "Laptop Pro", "price": "999.99", ... },
    { "id": 2, "name": "Smartphone X", "price": "799.99", ... }
  ]
}
```

## Cart

### Get Cart Items

```http
GET /api/cart
Authorization: Bearer your_token
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "items": [
      {
        "id": 1,
        "product_id": 5,
        "product": {
          "id": 5,
          "name": "Laptop Pro",
          "price": "999.99"
        },
        "quantity": 1,
        "subtotal": "999.99"
      }
    ],
    "total_items": 1,
    "total_price": "999.99"
  }
}
```

### Add to Cart

```http
POST /api/cart
Authorization: Bearer your_token
Content-Type: application/json

{
  "product_id": 5,
  "quantity": 1
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Product added to cart",
  "data": {
    "id": 1,
    "product_id": 5,
    "quantity": 1
  }
}
```

### Update Cart Item

```http
PUT /api/cart/{cart_item_id}
Authorization: Bearer your_token
Content-Type: application/json

{
  "quantity": 2
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Cart item updated",
  "data": {
    "id": 1,
    "quantity": 2,
    "subtotal": "1999.98"
  }
}
```

### Remove from Cart

```http
DELETE /api/cart/{cart_item_id}
Authorization: Bearer your_token
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Item removed from cart"
}
```

### Clear Cart

```http
DELETE /api/cart
Authorization: Bearer your_token
```

## Orders

### Create Order

```http
POST /api/orders
Authorization: Bearer your_token
Content-Type: application/json

{
  "shipping_address": {
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "address": "123 Main Street",
    "city": "New York",
    "country": "United States",
    "postal_code": "10001"
  },
  "notes": "Please leave at door"
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 1,
    "user_id": 1,
    "total_amount": "1999.98",
    "status": "pending",
    "payment_status": "pending",
    "shipping_address": { ... },
    "items": [
      {
        "id": 1,
        "product_id": 5,
        "quantity": 2,
        "price": "999.99",
        "subtotal": "1999.98"
      }
    ],
    "created_at": "2024-01-25T10:30:00Z"
  }
}
```

### Get All Orders

```http
GET /api/orders?page=1&per_page=10
Authorization: Bearer your_token
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "total_amount": "1999.98",
        "status": "pending",
        "payment_status": "pending",
        "created_at": "2024-01-25T10:30:00Z"
      }
    ],
    "pagination": { ... }
  }
}
```

### Get Single Order

```http
GET /api/orders/{order_id}
Authorization: Bearer your_token
```

### Cancel Order

```http
DELETE /api/orders/{order_id}/cancel
Authorization: Bearer your_token
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Order cancelled successfully"
}
```

## Payments

### Process Stripe Payment

```http
POST /api/payments/stripe
Authorization: Bearer your_token
Content-Type: application/json

{
  "order_id": 1,
  "token": "tok_visa",
  "payment_method": "credit_card"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Payment processed successfully",
  "data": {
    "order_id": 1,
    "transaction_id": "stripe_123456789",
    "amount": "1999.98"
  }
}
```

### Process PayPal Payment

```http
POST /api/payments/paypal
Authorization: Bearer your_token
Content-Type: application/json

{
  "order_id": 1,
  "paypal_token": "token_from_paypal_sdk"
}
```

### Get Payment Status

```http
GET /api/payments/{order_id}/status
Authorization: Bearer your_token
```

**Response (200 OK):**
```json
{
  "success": true,
  "data": {
    "status": "completed",
    "amount": "1999.98",
    "transaction_id": "stripe_123456789",
    "payment_method": "stripe",
    "created_at": "2024-01-25T10:35:00Z"
  }
}
```

## Error Responses

### 400 Bad Request

```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "email": ["The email field is required"]
  }
}
```

### 401 Unauthorized

```json
{
  "success": false,
  "message": "Unauthorized access"
}
```

### 404 Not Found

```json
{
  "success": false,
  "message": "Resource not found"
}
```

### 429 Too Many Requests

```json
{
  "success": false,
  "message": "Too many requests"
}
```

### 500 Server Error

```json
{
  "success": false,
  "message": "Internal server error"
}
```

## Rate Limiting

- Public endpoints: 60 requests per minute
- Authenticated endpoints: 300 requests per minute
- Admin endpoints: 1000 requests per minute

Rate limit info is included in response headers:
```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1234567890
```

## Pagination

Paginated endpoints return data in this format:

```json
{
  "data": [...],
  "pagination": {
    "total": 245,
    "count": 12,
    "per_page": 12,
    "current_page": 1,
    "last_page": 21,
    "from": 1,
    "to": 12,
    "links": {
      "first": "https://api.../products?page=1",
      "last": "https://api.../products?page=21",
      "next": "https://api.../products?page=2",
      "prev": null
    }
  }
}
```

## Timestamps

All timestamp fields use ISO 8601 format:
```
2024-01-25T10:30:00Z
```

---

For more information, visit: https://docs.ecommerce-platform.com
