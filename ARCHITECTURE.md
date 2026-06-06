# JEH STORE - Architecture Documentation

## Overview
JEH Store is a modern multi-platform e-commerce system supporting online retail operations through mobile applications, web administration panel, backend APIs, and centralized database management.

## System Components

### 1. Android App (`app/`)
- **Language:** Kotlin with Jetpack Compose
- **Architecture:** MVVM pattern with composable screens
- **Screens:**
  - Login/Register Screen
  - Home Screen (product listing, search, category filter)
  - Product Detail Screen (with reviews, add to cart)
  - Cart Screen (view items, remove, proceed to checkout)
  - Orders Screen (order history)
  - Order Detail Screen (order items, payment processing)
  - Profile Screen (user info, logout)
  - Notifications Screen (order updates)
  - Address Management (list, add addresses)
- **API Service:** `services/ApiService.kt` - Communicates with PHP backend

### 2. Flutter App (`flutter_app/`)
- **Language:** Dart with Flutter
- **Features:**
  - Login/Register with authentication
  - Product browsing with search and category filter
  - Product detail with quantity selector
  - Shopping cart with checkout
  - Order history with status tracking
  - Payment processing (credit card & COD)
  - User profile management
- **API Service:** `services/api_service.dart`

### 3. PHP Backend API (`backend/`)
- **Framework:** Custom PHP with PSR-4 autoloading
- **Authentication:** JWT-based with Bearer tokens
- **Security:** CSRF protection middleware, password hashing
- **Entry Point:** `public/index.php`
- **Database Connection:** PDO with prepared statements

#### API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/auth/register` | Customer registration |
| POST | `/api/auth/login` | Customer login |
| GET | `/api/auth/profile` | Get customer profile |
| POST | `/api/auth/admin/login` | Admin login |
| GET | `/api/products` | List all products |
| GET | `/api/products/{id}` | Get product detail |
| GET | `/api/categories` | List categories |
| GET | `/api/categories/{id}/products` | Products by category |
| GET/POST | `/api/cart` | View/Add to cart |
| POST | `/api/cart/remove` | Remove from cart |
| GET/POST | `/api/orders` | List/Create orders |
| GET | `/api/orders/{id}` | Order detail |
| GET/POST/DELETE | `/api/addresses` | Address CRUD |
| POST | `/api/reviews` | Submit review |
| GET | `/api/products/{id}/reviews` | Get product reviews |
| GET | `/api/notifications` | List notifications |
| POST | `/api/notifications/read` | Mark notification read |
| GET | `/api/search` | Search products |
| GET | `/api/search/suggestions` | Search suggestions |
| POST | `/api/payments` | Process payment |
| GET | `/api/orders/{id}/payment` | Get order payment |
| POST | `/api/admin/products` | Create product |
| PUT | `/api/admin/products/{id}` | Update product |
| DELETE | `/api/admin/products/{id}` | Delete product |
| POST | `/api/admin/products/{id}/images` | Upload product image |
| POST | `/api/admin/categories` | Create category |
| GET | `/api/admin/orders` | List all orders |
| PUT | `/api/admin/orders/{id}/status` | Update order status |
| GET | `/api/admin/dashboard` | Dashboard statistics |
| GET | `/api/admin/payments` | List all payments |
| GET | `/api/admin/activity-logs` | View activity logs |

### 4. Admin Panel (`admin_panel/`)
- **Tech:** PHP + HTML + CSS + JavaScript
- **Pages:**
  - `login.php` - Admin authentication
  - `dashboard.php` - Statistics and quick actions
  - `products.php` - Product CRUD management
  - `categories.php` - Category management
  - `orders.php` - Order management with status updates
  - `customers.php` - Customer overview

### 5. Database (`database/schema.sql`)
- **Engine:** MySQL with InnoDB
- **Tables:** customers, admins, categories, products, product_images, carts, cart_items, orders, order_items, payments, addresses, reviews, notifications, activity_logs
- **Foreign Keys:** Enforce referential integrity with cascading deletes

## Key Features
- ✅ Customer Authentication (register/login with JWT)
- ✅ Product Management (CRUD with categories)
- ✅ Shopping Cart (add/remove/update items)
- ✅ Order Processing (create, track, approve, ship, deliver)
- ✅ Payment Processing (credit card, cash on delivery)
- ✅ Product Reviews & Ratings
- ✅ Search with filtering
- ✅ Notifications System
- ✅ Address Management
- ✅ Admin Dashboard with analytics
- ✅ Image Upload for products
- ✅ Activity Logging
- ✅ CSRF Protection