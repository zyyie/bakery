# Bakery Management System

A complete online bakery management system built with PHP, MySQL, and Bootstrap.

## Features

### Customer Features
- User Registration and Login
- Browse Products by Category
- Shopping Cart
- Checkout and Order Placement
- Order Tracking
- View Order History
- Contact/Enquiry Form
- Newsletter Subscription

### Admin Features
- Admin Dashboard with Statistics
- Category Management (Add/Manage)
- Item/Food Package Management (Add/Edit/Delete)
- Order Management (View, Update Status, Track)
- Enquiry Management
- Subscriber Management
- Registered Users Management
- Sales Reports (Month wise/Year wise)
- Search Orders
- Page Management (About Us, Contact Us)

## Installation

1. **Database Setup**
   - Create a MySQL database named `db_bakery`
   - Import the canonical schema/data: `backend/db_bakery.sql`
   - Ensure `backend/connect.php` points to `db_bakery`

2. **File Structure**
   ```
   bakery shop/
   ├── connect.php
   ├── index.php
   ├── products.php
   ├── login.php
   ├── signup.php
   ├── cart.php
   ├── checkout.php
   ├── my-orders.php
   ├── about.php
   ├── contact.php
   ├── includes/
   │   ├── header.php
   │   └── footer.php
   ├── admin/
   │   ├── login.php
   │   ├── dashboard.php
   │   ├── add-category.php
   │   ├── manage-category.php
   │   ├── add-food-package.php
   │   ├── manage-food-package.php
   │   ├── all-order.php
   │   ├── view-order-detail.php
   │   └── ... (other admin files)
   ├── backend/
   │   └── db_bakery.sql  (canonical database schema and seed data)
   └── uploads/ (for product images)
   ```

3. **Default Admin Credentials**
   - Username: `admin`
   - Password: `admin123`

4. **Create Uploads Directory**
   - Create a folder named `uploads` in the root directory
   - Set proper permissions for image uploads

## Usage

1. Access the website at `http://localhost/bakery/backend/`
2. For admin panel: `http://localhost/bakery/backend/admin/login.php`
3. Register as a user or login with admin credentials
4. Browse products, add to cart, and place orders
5. Admin can manage all aspects of the system from the admin panel

## Technologies Used

- PHP 7.4+
- MySQL
- Bootstrap 5.3.8
- Font Awesome 6.4.0
- JavaScript

## Database Tables

- `categories` - Product categories
- `items` - Food packages/products
- `users` - Registered customers
- `admin` - Admin users
- `orders` - Customer orders
- `order_items` - Order line items
- `enquiries` - Customer enquiries
- `subscribers` - Newsletter subscribers
- `pages` - Static page content

## Notes

- Make sure PHP file uploads are enabled for product images
- Update database connection details in `connect.php`
- The system uses MD5 for password hashing (consider upgrading to bcrypt for production)
- Session management is used for cart and user authentication

