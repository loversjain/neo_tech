# Laravel API Documentation

## Overview

This API is built using **Laravel 10** and utilizes several modern features, including **Laravel Sanctum** for authentication, the **Repository Pattern**, **Traits**, **Eloquent ORM**, **Eloquent Relationships**, and **Enums**. The API provides endpoints for user authentication, order management, and administrative functionalities.

## Table of Contents

- [Installation](#installation)
- [Authentication](#authentication)
- [API Endpoints](#api-endpoints)
  - [Public Routes](#public-routes)
  - [Protected Routes](#protected-routes)
  - [Admin Routes](#admin-routes)
- [Usage](#usage)
- [Database Seeding](#database-seeding)
- [Contributing](#contributing)
- [License](#license)

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/yourusername/yourproject.git
   cd yourproject
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Set up your .env file:**
   ```bash
   cp .env.example .env
   ```

4. **Generate the application key:**
   ```bash
   php artisan key:generate
   ```

5. **Run migrations:**
   ```bash
   php artisan migrate
   ```

6. **Seed the database with sample users and products:**
   ```bash
   php artisan db:seed
   ```

7. **Start the server:**
   ```bash
   php artisan serve
   ```


## Login Credentials
Email | Password| Role|
--- | --- | --- |
check in Users table | password | admin
check in Users table | password | user

## Authentication

### Public Routes

- **Login**
  - **Endpoint:** `POST /api/login`
  - **Request Body:**
    ```json
    {
        "email": "user@example.com",
        "password": "yourpassword"
    }
    ```

### Protected Routes (Requires Authentication)

**User Routes for Order Management**

- **Base URL:** `/api/orders`
  - **Create Order**
    - **Endpoint:** `POST /api/orders`
  - **Update Order**
    - **Endpoint:** `PATCH /api/orders/{id}`
  - **Delete Order**
    - **Endpoint:** `DELETE /api/orders/{id}`
  - **View Orders**
    - **Endpoint:** `GET /api/orders`

### Admin Routes (Requires Admin Privileges)

**Base URL:** `/api/admin`

- **User Management**
  - **Manage User Status**
    - **Endpoint:** `PUT /api/admin/users/{id}/status`
  
- **Order Management**
  - **View All Orders**
    - **Endpoint:** `GET /api/admin/orders`
  - **View Specific Order**
    - **Endpoint:** `GET /api/admin/orders/{id}`
  
- **Product Management**
  - **Show Product**
    - **Endpoint:** `GET /api/admin/products/{id}`
  - **Update Product Stock**
    - **Endpoint:** `PUT /api/admin/products/{id}/stock`

### Refresh Token

- **Endpoint:** `POST /api/refresh-token`
- **Authorization:** Bearer token required.

## Usage

Once the server is running, you can test the API endpoints using tools like Postman or cURL. Make sure to include the Bearer token in the Authorization header for all protected routes.

### Example Request to Create an Order

```bash
curl -X POST http://localhost:8000/api/orders \
-H "Authorization: Bearer YOUR_TOKEN" \
-H "Content-Type: application/json" \
-d '{
    "product_id": 1,
    "quantity": 2
    }'
```

## Database Seeding

The database seeding process includes the creation of sample users and products to facilitate testing. This is done using the `DatabaseSeeder` class, which calls the `UserSeeder` and `ProductSeeder`.

### Seed the Database

To seed the database, run the following command:
```bash
php artisan db:seed
```

## Contributing

If you'd like to contribute to this project, please fork the repository and submit a pull request.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
