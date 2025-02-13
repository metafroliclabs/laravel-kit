<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Laravel Starter Boilerplate

This project provides a starter boilerplate for Laravel applications with essential authentication, profile management, and content page routes.

## Installation

1. **Create a new project using Composer:**
   ```bash
   composer create-project mfl/laravel-kit
   ```

2. **Setup environment:**
   - Copy the `.env.example` file to `.env`.
   - Configure database and application settings.

3. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

4. **Run migrations:**
   ```bash
   php artisan migrate
   ```

5. **Install Passport (if required for Sanctum):**
   ```bash
   php artisan passport:install
   ```

6. **Start the application:**
   ```bash
   php artisan serve
   ```

## Available Routes

### Authentication Routes

| Method | Endpoint     | Controller    | Description           |
|--------|--------------|---------------|-----------------------|
| POST   | `/signup`    | AuthController | User signup           |
| POST   | `/login`     | AuthController | User login            |
| POST   | `/logout`    | AuthController | User logout (auth)    |

*Middleware:* `auth:sanctum` is required for `/logout`.

### Password Recovery Routes

| Method | Endpoint         | Controller         | Description                |
|--------|------------------|--------------------|----------------------------|
| POST   | `/forget-password`| ForgetPasswordController | Request password reset  |
| POST   | `/verify-code`    | ForgetPasswordController | Verify reset code       |
| POST   | `/set-password`   | ForgetPasswordController | Reset password          |

### Profile Management (Protected)

| Method | Endpoint                       | Controller       | Description                       |
|--------|--------------------------------|------------------|-----------------------------------|
| GET    | `/get-profile`                 | ProfileController | Get user profile                  |
| POST   | `/edit-profile`                | ProfileController | Edit user profile                 |
| POST   | `/change-password`            | ProfileController | Change password                   |
| GET    | `/notifications/all/list`      | ProfileController | List all notifications            |
| GET    | `/notifications/read/list`     | ProfileController | List read notifications           |
| GET    | `/notifications/unread/list`   | ProfileController | List unread notifications         |
| GET    | `/notifications/unread/count`  | ProfileController | Get unread notification count     |
| POST   | `/mark-notification/{id}`      | ProfileController | Mark a single notification as read/unread|
| POST   | `/mark-all-as-read`            | ProfileController | Mark all notifications as read    |

*All routes require `auth:sanctum` middleware.*

### Page Routes (Protected)

| Method | Endpoint      | Controller    | Description         |
|--------|---------------|---------------|---------------------|
| POST   | `/contact-us` | PageController | Submit contact form |
| GET    | `/content`    | PageController | Get static content  |

*`auth:sanctum` middleware is required.*

## License

This project is licensed under the MIT License.

