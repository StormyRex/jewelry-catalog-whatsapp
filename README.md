# TP Jewellery — Digital Catalog with WhatsApp Ordering

A full-stack web application for a home-based imitation jewellery business. Customers browse products and place orders directly via WhatsApp. Built without a payment gateway — by design, since the business operates on manual order confirmation.

## Live Demo
> Run locally via XAMPP — see setup instructions below.

![TP Jewellery Catalog](images/TP_Jewellery.jpg)

---

## The Problem This Solves

Home-based jewellery businesses in India typically take orders through Instagram DMs or word of mouth — no organized catalog, no structured ordering process. This project replaces that with a professional digital storefront that requires zero technical knowledge from the business owner to operate.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8 (procedural) |
| Database | MySQL via MySQLi |
| Frontend | HTML5, CSS3 (Glassmorphism) |
| Server | Apache (XAMPP) |
| Ordering | WhatsApp Business API (`wa.me`) |

**No JavaScript. No frameworks. No payment gateway.**  
Server-side rendering only — every interaction is a PHP form submission.

---

## Features

### Customer Side
- 🔍 **Search + Filter** — by name and category, combined in one query
- 🗂️ **Sort** — by newest, price low to high, price high to low
- 🛒 **Session Cart** — add multiple items, send one WhatsApp message with full order
- 📱 **Product Detail Page** — full description, large image, related products
- 💬 **WhatsApp Integration** — dynamically generated `wa.me` links with pre-filled order text
- 📵 **Out of Stock** — items marked unavailable show badge, disable cart and order buttons
- 📱 **Mobile Responsive** — CSS Grid with `auto-fill` + `minmax`

### Admin Side
- 🔐 **Session-based login** — password protected, no database users table needed
- ➕ **Add Product** — image upload with type/size validation, unique filename generation
- ✏️ **Edit Product** — update all fields, replace image, toggle stock status
- 🗑️ **Delete Product** — removes DB record + image file from disk
- 📊 **Dashboard** — total products, in stock, out of stock, categories at a glance
- 🚫 **Bulk Out of Stock** — mark entire category unavailable in one click

---

## Project Structure
```
jewelry_catalog/
├── index.php            # Customer catalog
├── product.php          # Product detail page
├── cart.php             # Session cart + WhatsApp order
├── 404.php              # Custom not found page
├── db_connect.php       # Database connection
├── admin_upload.php     # Admin login + add product
├── admin_products.php   # Manage + delete products
├── admin_edit.php       # Edit existing product
├── admin_dashboard.php  # Admin overview
└── images/              # Uploaded product images
```

---

## Database Schema
```sql
CREATE TABLE products (
    id          INT(11)      NOT NULL AUTO_INCREMENT,
    name        VARCHAR(255) NOT NULL,
    price       INT(11)      NOT NULL,
    category    VARCHAR(100) NOT NULL,
    image_file  VARCHAR(255) NOT NULL,
    description TEXT         NOT NULL,
    in_stock    TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);
```

---

## Local Setup

**Requirements:** XAMPP (Apache + MySQL)
```bash
# 1. Clone the repo
git clone https://github.com/YOURUSERNAME/jewelry-catalog-whatsapp.git

# 2. Move to htdocs
# Place the folder inside C:\xampp\htdocs\

# 3. Start Apache + MySQL in XAMPP Control Panel

# 4. Create database
# Open http://localhost/phpmyadmin
# Run the SQL from schema above

# 5. Visit the catalog
http://localhost/jewelry_catalog/index.php

# 6. Admin panel
http://localhost/jewelry_catalog/admin_upload.php
# Default password: admin123
```

---

## WhatsApp Ordering Flow
```
Customer browses catalog
        ↓
Clicks "Order on WhatsApp" OR adds to cart
        ↓
wa.me link opens WhatsApp with pre-filled message:
"Hi! I am interested in [Product Name] priced at Rs.[Price]. Is it available?"
        ↓
Business owner confirms manually and arranges delivery
```

This model works because:
- No GST registration required
- No payment gateway integration needed  
- Owner maintains full control over order confirmation
- Zero transaction fees

---

## Security Measures

- `mysqli_real_escape_string()` on all user inputs before DB queries
- File upload validation — type checking (`image/jpeg`, `image/png`, `image/webp`) and size limit (2MB)
- Unique filename generation on upload — prevents overwriting and path traversal
- Session-based admin authentication — protected routes redirect to login
- `(int)` casting on all ID parameters from GET requests

---

## Developed By

**Shiven**  
Diploma in Information Technology — Semester 4  
LJ Polytechnic, Ahmedabad  
Enrollment No: 24012250410072

*Built as a proof-of-work project for summer internship applications — May 2026*