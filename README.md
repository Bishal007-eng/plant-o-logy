# Plant-o-logy

A functional e-commerce website for an online plant shop, built with PHP, MySQL, and Tailwind CSS.

## Features

- Browse plants by category
- Search plants by name
- Product detail pages with light, water, and pot size info
- Session-based shopping cart with AJAX add-to-cart
- Customer registration, login, and logout
- Checkout with order confirmation
- Admin dashboard with full product CRUD and order management
- Login-required modal for unauthenticated actions
- Toast notifications and smooth UI interactions

## Tech Stack

- **Backend:** PHP (PDO, prepared statements)
- **Database:** MySQL
- **Frontend:** HTML5, Tailwind CSS (CDN), vanilla JavaScript
- **Auth:** Bcrypt password hashing, PHP sessions

## Setup

1. Copy the `plantshop/` folder into XAMPP `htdocs/`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Import `sql/database.sql` into MySQL via phpMyAdmin.
4. Visit `http://localhost/plantshop/`.

### Admin Login

- Email: `admin@plantology.com`
- Password: `admin123`

*(Change these in production.)*

## Structure
plantshop/
├── index.php Home page
├── products.php Product listing
├── product.php Product detail
├── cart.php Shopping cart
├── checkout.php Checkout + confirmation
├── auth.php Login / register / logout
├── about.php About page
├── admin.php Admin dashboard
├── includes/ Shared header, footer, db, helpers
├── assets/ Stylesheet + JavaScript
├── images/ Plant photography
└── sql/ Database schema


## License

Personal project — MIT507 Web Design and Development coursework.