# TeachNest (Slim 4 MVC eCommerce Platform)

A fully functional, MVC-based eCommerce web application built on the [Slim 4 PHP Microframework](https://www.slimframework.com/docs/v4/). This project provides a robust foundation for an online store or educational platform, equipped with user authentication, two-factor authentication (2FA), a shopping cart, wishlists, and a dedicated admin panel for managing the catalog and orders.

## Features

### 🛍️ User & Shopping Experience
- **Authentication:** Secure user registration, login, and dashboard access.
- **Two-Factor Authentication (2FA):** Enhanced security with TOTP-based 2FA and trusted devices support.
- **Product Catalog & Live Search:** Browse products with real-time AJAX search functionality.
- **Shopping Cart:** Add, update, remove items, and clear the cart.
- **Wishlist:** Toggle and manage favorite products.
- **Checkout Flow:** Complete order processing with confirmation pages.
- **Order History:** Users can view their past orders and statuses.

### 🛡️ Admin Panel
- **Dashboard:** Overview of the platform's metrics.
- **Catalog Management:** Full CRUD (Create, Read, Update, Delete) operations for Products and Categories.
- **Order Management:** View all orders and update their statuses.
- **File Uploads:** Secure handling of product image uploads.
- **Reporting:** Export product data directly to PDF.

### ⚙️ Technical Highlights
- **Architecture:** Classic MVC (Model-View-Controller) structure using PHP 8.2+.
- **Database:** Secure PDO wrapper (`BaseModel`) using prepared statements for all SQL queries.
- **Routing:** FastRoute integration for Web and API endpoints.
- **Dependency Injection:** Powered by PHP-DI (PSR-11).
- **Middleware:** Extensively uses PSR-15 middleware for Authentication, Admin Authorization, and 2FA coverage.

---

## 🚀 How to Run the Project Locally

### Prerequisites
1. **PHP 8.2 or higher**
2. **Composer** (Dependency Manager for PHP)
3. **A Web Server** (Apache, Nginx, or PHP's built-in server)
4. **MySQL/MariaDB Database**

### Installation Steps

1. **Clone or Extract the Repository:**
   Ensure the project folder is in your desired location (e.g., your `htdocs` or `www` directory if using XAMPP/WAMP).

2. **Install Dependencies:**
   Open your terminal/command prompt at the root of the project and run:
   ```bash
   composer install
   ```
   *(If you don't have composer globally installed, you can use the included `composer.bat install` on Windows).*

3. **Environment Configuration:**
   Navigate to the `config/` folder. Create a copy of `env.example.php` and rename it to `env.php`.
   ```bash
   cp config/env.example.php config/env.php
   ```

4. **Database Setup:**
   - Create a MySQL database for the project (e.g., `teachnest_db`).
   - Import the project's SQL schema into your new database (if provided in `data/` or `docs/`).
   - Open `config/env.php` and update it with your actual database credentials:
     ```php
     'database' => [
         'host'     => '127.0.0.1',
         'database' => 'teachnest_db',
         'username' => 'root',
         'password' => '',
         // ...
     ]
     ```

5. **Run the Application:**

   **Using PHP's Built-in Server (Recommended for Development):**
   Run the following command from the project root:
   ```bash
   composer start
   ```
   *(This executes `php -S localhost:8080 -t public public/index.php`)*
   
   The application will be accessible at: `http://localhost:8080`

   **Using Apache/Nginx (XAMPP/WAMP):**
   Access the project via your local server's URL, ensuring your virtual host points to the `public/` directory (e.g., `http://localhost/teachnest/public/`).

---

## 📂 Project Structure

```plaintext
teachnest/
├── app/
│   ├── Controllers/    # Application controllers (Web, API, Admin)
│   ├── Domain/         # Business logic: Models and Services
│   ├── Helpers/        # Utilities (SessionManager, PDO Wrapper)
│   ├── Middleware/     # PSR-15 Middleware (Auth, AdminAuth, 2FA)
│   ├── Routes/         # Web and API Route definitions
│   └── Views/          # PHP/HTML View templates
├── config/             # Environment configs, container, and bootstrap
├── data/               # Database files, SQLite DBs (if any), Uploads
├── docs/               # Technical documentation and guides
├── public/             # Document root (index.php, CSS, JS, images)
├── vendor/             # Composer packages
└── var/                # Runtime files (Logs, cache)
```

## 🤝 Contributing
1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
