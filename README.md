# PM Dairy Farm - Complete Website

> **Pure Milk, Pure Life — Farm Fresh Daily**

A complete, production-ready dairy farm website built with Core PHP, MySQL, jQuery/AJAX, and vanilla JavaScript.

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Frontend** | HTML5, CSS3, jQuery 3.7, Vanilla JavaScript |
| **Backend** | Core PHP 7.4+ |
| **Database** | MySQL 5.7+ / MariaDB 10.3+ |
| **AJAX** | jQuery AJAX for all dynamic operations |
| **Icons** | Font Awesome 6.5 |
| **Fonts** | Google Fonts (Playfair Display + Poppins) |
| **Payment** | Razorpay Integration |

---

## Quick Setup (XAMPP)

### 1. Prerequisites
- XAMPP (PHP 7.4+, MySQL/MariaDB, Apache)
- Web browser

### 2. Installation Steps

```bash
# 1. Clone/Copy project to XAMPP htdocs
# Place the DairyForm folder in: C:\xampp\htdocs\DairyForm

# 2. Start XAMPP
# Start Apache and MySQL from XAMPP Control Panel

# 3. Setup Database
# Option A: Open browser and visit:
http://localhost/DairyForm/database/setup.php

# Option B: Import manually via phpMyAdmin:
# - Open http://localhost/phpmyadmin
# - Create database: pm_dairy
# - Import file: DairyForm/database/schema.sql
```

### 3. Access the Website

| URL | Description |
|-----|-------------|
| `http://localhost/DairyForm/` | Main Website |
| `http://localhost/DairyForm/admin/` | Admin Dashboard |
| `http://localhost/DairyForm/database/setup.php` | DB Setup Script |

### 4. Default Admin Login

```
Phone: 9876543210
Email: admin@pmdairy.com
Password: admin123
```

---

## Configuration

Edit `config/config.php` to update:

```php
// Site Settings
define('SITE_URL', 'http://localhost/DairyForm');
define('SITE_EMAIL', 'info@pmdairy.com');
define('SITE_PHONE', '+91-9876543210');
define('SITE_WHATSAPP', '919876543210');

// Razorpay (for online payments)
define('RAZORPAY_KEY_ID', 'rzp_test_xxxxxxxxxxxx');
define('RAZORPAY_KEY_SECRET', 'your_razorpay_secret');

// JWT Secret (change in production!)
define('JWT_SECRET', 'your_random_secret_key_here');
```

---

## Project Structure

```
DairyForm/
├── index.php                  # Home page
├── .htaccess                  # Apache rewrite rules
├── config/
│   ├── config.php             # App configuration
│   └── database.php           # DB connection (Singleton)
├── includes/
│   ├── functions.php          # Helper functions
│   ├── auth.php               # JWT Auth class
│   ├── header.php             # Site header
│   └── footer.php             # Site footer
├── pages/
│   ├── about.php              # About Us
│   ├── products.php           # Products listing
│   ├── product-detail.php     # Single product
│   ├── cattle.php             # Cattle/Breeds
│   ├── infrastructure.php     # Farm infrastructure
│   ├── gallery.php            # Photo gallery
│   ├── blog.php               # Blog listing
│   ├── blog-detail.php        # Blog post
│   ├── contact.php            # Contact page
│   ├── farm-visit.php         # Book farm visit
│   ├── cart.php               # Shopping cart
│   ├── checkout.php           # Checkout
│   ├── order-confirmation.php # Order success
│   ├── login.php              # Login
│   ├── register.php           # Register
│   ├── profile.php            # User profile
│   ├── my-orders.php          # Order history
│   ├── subscriptions.php      # Subscription plans
│   └── my-subscriptions.php   # My subscriptions
├── api/
│   ├── auth/                  # Auth endpoints
│   │   ├── register.php
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── me.php
│   │   ├── send-otp.php
│   │   └── verify-otp.php
│   ├── products/              # Product endpoints
│   │   ├── index.php
│   │   ├── detail.php
│   │   └── manage.php
│   ├── cart/index.php         # Cart CRUD
│   ├── orders/index.php       # Orders
│   ├── subscriptions/index.php
│   ├── blogs/index.php
│   ├── farm-visits/index.php
│   ├── contact/index.php
│   ├── testimonials/index.php
│   ├── payment/index.php      # Razorpay
│   └── users/index.php
├── admin/
│   ├── index.php              # Dashboard
│   ├── includes/
│   │   ├── header.php
│   │   └── footer.php
│   └── pages/
│       ├── orders.php
│       ├── products.php
│       ├── subscriptions.php
│       ├── blogs.php
│       ├── farm-visits.php
│       ├── testimonials.php
│       ├── users.php
│       └── contacts.php
├── assets/
│   ├── css/
│   │   ├── style.css          # Main stylesheet
│   │   └── admin.css          # Admin stylesheet
│   ├── js/
│   │   └── main.js            # jQuery/JS logic
│   └── images/
├── uploads/                   # User uploads
│   ├── products/
│   ├── blog/
│   ├── testimonials/
│   └── gallery/
└── database/
    ├── schema.sql             # Database schema + seed data
    └── setup.php              # Auto setup script
```

---

## Features

### Frontend (12 Pages)
- Home page with hero, stats, products carousel, story, process, testimonials, CTA
- About page with timeline, founder profile, mission/vision
- Products with category filter, sort, search (AJAX)
- Product detail with gallery, nutrition info, related products
- Cattle breeds with detailed cards
- Farm infrastructure showcase
- Photo gallery with masonry grid, lightbox, category filter
- Blog with categories, sidebar, detail pages
- Contact with form (AJAX), Google Maps, WhatsApp
- Farm visit booking with form
- Shopping cart (AJAX based, real-time updates)
- Checkout with address selection, time slots, COD/Razorpay
- User auth (login/register/OTP)
- User profile, orders, subscriptions management
- Subscription plans (daily/weekly/monthly)

### Backend (API)
- RESTful PHP APIs with JSON responses
- JWT authentication with cookie/session fallback
- OTP-based login system
- CSRF protection
- Rate limiting on all sensitive endpoints
- Input validation & sanitization
- Image upload with resize
- Razorpay payment integration
- Pagination on all list endpoints

### Admin Dashboard
- Dashboard with real-time stats
- Order management with status updates
- Product CRUD with image upload
- Blog post management
- Farm visit booking management
- Testimonial management
- Customer list
- Contact message viewer

### UI/UX
- Mobile-first responsive design
- Dark mode toggle
- Scroll animations
- Toast notifications
- Floating WhatsApp button
- Back to top button
- Page loading animation
- Sticky header with hide-on-scroll
- Product carousel
- Testimonials slider
- Image lightbox

---

## Database Tables (13)

| Table | Description |
|-------|-------------|
| users | Customers & admins |
| user_addresses | Delivery addresses |
| categories | Product categories |
| products | Dairy products |
| orders | Customer orders |
| order_items | Order line items |
| cart_items | Shopping cart |
| subscriptions | Subscription plans |
| subscription_items | Subscription products |
| blogs | Blog posts |
| farm_visits | Visit bookings |
| testimonials | Customer reviews |
| contacts | Contact form messages |
| cattle_breeds | Cattle breed info |
| gallery | Photo gallery |
| site_settings | Dynamic settings |

---

## API Endpoints

### Auth
- `POST /api/auth/register.php` — Register
- `POST /api/auth/login.php` — Login
- `POST /api/auth/logout.php` — Logout
- `GET /api/auth/me.php` — Current user
- `POST /api/auth/send-otp.php` — Send OTP
- `POST /api/auth/verify-otp.php` — Verify OTP

### Products
- `GET /api/products/` — List (with filters)
- `GET /api/products/detail.php?slug=xxx` — Detail
- `POST /api/products/manage.php` — Create (admin)
- `PUT /api/products/manage.php?id=1` — Update (admin)
- `DELETE /api/products/manage.php?id=1` — Delete (admin)

### Cart
- `GET /api/cart/` — Get cart
- `POST /api/cart/?action=add` — Add item
- `POST /api/cart/?action=update` — Update qty
- `POST /api/cart/?action=remove` — Remove item

### Orders
- `POST /api/orders/` — Place order
- `GET /api/orders/` — My orders
- `POST /api/orders/?action=update-status` — Update status (admin)

### + Subscriptions, Blogs, Farm Visits, Contact, Testimonials, Payment, Users

---

## Production Deployment

1. Update `config/config.php` with production values
2. Set `error_reporting(0)` and `display_errors` to `0`
3. Change `JWT_SECRET` to a secure random string
4. Configure Razorpay live keys
5. Setup SSL certificate (HTTPS)
6. Enable `.htaccess` HTTPS redirect
7. Set proper file permissions (755 for dirs, 644 for files)
8. Configure email SMTP settings
9. Setup cron job for subscription auto-orders

---

## License

This project is built for PM Dairy Farm.
