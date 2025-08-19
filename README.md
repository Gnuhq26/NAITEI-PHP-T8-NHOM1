# Furniro E-commerce Platform

Furniro E-commerce Platform is a robust and user-friendly web application built with Laravel, designed to provide a comprehensive solution for online retail. It features a complete e-commerce flow from product browsing and cart management to secure checkout and order tracking, alongside a powerful admin panel for efficient business operations.

## 📦 Introduction

This platform aims to provide a seamless online shopping experience for customers and an intuitive management system for administrators. It is built with modern web technologies, ensuring a responsive and efficient application.

## 👨‍💻 Phát triển bởi

This project was developed by:

- **Nguyen Van Thinh** ([@thinhnv-2059](https://github.com/thinhnv-2059)) - Project Mentor

- **Bui Quang Hung** ([@Gnuhq26](https://github.com/Gnuhq26))
- **Pham Duc Long** ([@DannyP4](https://github.com/DannyP4))
- **Dinh Dinh Hai Viet** ([@20225683-vietddh](https://github.com/20225683-vietddh))



## 🌟 Key Features

### For Customers

* **User Registration & Login**: Secure sign-up, login, password reset, and Google OAuth support.
* **Product Browsing**: Browse products by categories with product counts and detailed product information.
* **Advanced Search & Filtering**: Easily search and filter products to find what you need.
* **Shopping Cart**: Add, update, or remove products from your cart with real-time price calculation.
* **Checkout Process**: Guided and secure checkout flow for placing orders.
* **Order Tracking**: View order history and detailed order status updates.
* **Product Reviews & Feedback**: Submit reviews and feedback for purchased products.

### For Administrators

* **Comprehensive User Authentication**: Secure admin login and management.
* **Product & Category Management**: Full CRUD (Create, Read, Update, Delete) operations for products and categories via an intuitive admin panel.
* **Order Management**: View, track, and update customer orders and their statuses.
* **Customer Management**: Access and manage customer accounts and information.
* **Review & Feedback Moderation**: Manage and moderate customer reviews and feedback.
* **Dashboard & Analytics**: Overview of key business metrics and recent activities.

## 🛠️ Technologies Used

### Backend

*   **PHP**: Version 8.1
*   **Laravel Framework**: Version 10.10
*   **Laravel Sanctum**: API Authentication
*   **Laravel Socialite**: OAuth Authentication (Google)

### Frontend

*   **HTML, CSS, JavaScript**
*   **TailwindCSS**: A utility-first CSS framework for rapid UI development.
*   **Alpine.js**: A rugged, minimal JavaScript framework for composing behavior directly in your markup.
*   **Vite**: A lightning-fast build tool for modern web projects.
*   **Axios**: Promise based HTTP client for the browser and node.js.

### Database

*   **MySQL** (or any other database supported by Laravel, e.g., PostgreSQL, SQLite)

### Tools

*   **Composer**: PHP Dependency Manager
*   **NPM / Yarn**: JavaScript Package Manager

## 📁 Project Structure (Summary)

```
.
├── app/                  # Core application logic (Models, Controllers, Providers, etc.)
├── config/               # Application configuration files
├── database/             # Database migrations, seeders, and factories
├── public/               # Publicly accessible assets (compiled CSS/JS, images)
├── resources/
│   ├── css/              # Tailwind CSS source files
│   ├── js/               # JavaScript (Alpine.js, Bootstrap.js)
│   └── views/            # Blade templates for UI (customer, admin, auth views)
├── routes/               # Application route definitions (web, api, auth)
├── tests/                # Automated tests (Unit and Feature)
├── vendor/               # PHP dependencies managed by Composer
└── node_modules/         # JavaScript dependencies managed by NPM/Yarn
```

## Installation and Setup

Follow these steps to get the app up and running on your local machine.

### Prerequisites

Ensure you have the following installed on your system:

*   PHP >= 8.1
*   Composer
*   Node.js & NPM (or Yarn)
*   A database server (e.g., MySQL)

### Steps

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/awesome-academy/NAITEI-PHP-T8-NHOM1.git
    cd NAITEI-PHP-T8-NHOM1
    ```

2.  **Install PHP Dependencies:**
    ```bash
    composer install
    ```

3.  **Install JavaScript Dependencies:**
    ```bash
    npm install # or yarn install
    ```

4.  **Configure Environment Variables:**
    Copy the `.env.example` file to `.env`:
    ```bash
    cp .env.example .env
    ```
    Open the `.env` file and update your database credentials, `APP_URL`, and any other necessary configurations (e.g., Google OAuth API keys).

    Generate an application key:
    ```bash
    php artisan key:generate
    ```

5.  **Run Database Migrations:**
    ```bash
    php artisan migrate
    ```

6.  **Seed the Database (Optional, for demo data):**
    ```bash
    php artisan db:seed
    ```

7.  **Start the Development Servers:**
    For the Laravel backend:
    ```bash
    php artisan serve
    ```
    For the Vite frontend assets:
    ```bash
    npm run dev # or yarn dev
    ```

    The application should now be accessible at `http://127.0.0.1:8000` (or the `APP_URL` configured in your `.env` file).

## 📝 Usage Guide

### Customer Access

*   **Registration/Login**: Create a new account or log in using existing credentials or Google.
*   **Browse Products**: Explore categories and products.
*   **Shopping Cart**: Add items to cart, update quantities, remove items.
*   **Checkout**: Proceed through the checkout process to place an order.
*   **Order History**: View past orders and their details.
*   **Feedback**: Submit reviews and ratings for purchased products.

### Admin Panel

*   **Login**: Access the admin dashboard with administrator credentials.
*   **Dashboard**: Overview of key metrics and statistics.
*   **User Management**: Add, edit, delete, and search user accounts.
*   **Category Management**: Manage product categories (create, edit, delete).
*   **Product Management**: Add, edit, delete, and search products, including details like stock and images.
*   **Order Management**: View all orders, update order statuses, and view order details.
*   **Feedback Management**: Review and delete customer feedback.

## 📚 References

Below are some useful resources and documentation that were referenced during the development of this project:

- [Laravel Official Documentation](https://laravel.com/docs)
- [Vite Documentation](https://vitejs.dev/guide/)
- [Bootstrap Documentation](https://getbootstrap.com/docs/)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [PHP Official Documentation](https://www.php.net/docs.php)
- [Composer](https://getcomposer.org/doc/)
- [Node.js Documentation](https://nodejs.org/en/docs/)
- [Google OAuth API Documentation](https://developers.google.com/identity/protocols/oauth2)

These references can help you better understand the technologies and tools used in this project.

## 👥 Contributing

We welcome contributions to the this project! If you'd like to contribute, please follow these steps:

1. Fork this repository.
2. Create a new branch for your feature or bug fix (`git checkout -b your-branch-name`).
3. Make your changes, ensuring they follow the project's coding standards.
4. Write relevant tests for your changes.
5. Commit your changes (`git commit -m "Describe your changes"`).
6. Push your branch to your fork (`git push origin your-branch-name`).
7. Open a pull request to the main repository.

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 📧 Contact

If you have any questions, suggestions, or need support, please feel free to reach out:

*   **Mentor's GitHub**: [Nguyen Van Thinh](https://github.com/thinhnv-2059)
*   **Issue**: https://github.com/awesome-academy/NAITEI-PHP-T8-NHOM1/issues
