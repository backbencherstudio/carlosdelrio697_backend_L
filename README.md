# CarlosDelRio697 Backend API

Backend API service built with Laravel for the CarlosDelRio697 platform.

This backend handles application logic, authentication, database operations, and API endpoints for the system.

---

## 🚀 Tech Stack

* PHP
* Laravel
* MySQL
* Composer
* RESTful API

---

## 📂 Project Structure

```
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
tests/
```

---

## ⚙️ Installation

### 1. Clone the repository

```
git clone git@github.com:backbencherstudio/carlosdelrio697_backend_L.git
```

### 2. Navigate to project directory

```
cd carlosdelrio697_backend_L
```

### 3. Install dependencies

```
composer install
```

### 4. Copy environment configuration

```
cp .env.example .env
```

### 5. Generate application key

```
php artisan key:generate
```

### 6. Configure database

Update the `.env` file with your database credentials.

```
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 7. Run database migrations

```
php artisan migrate
```

### 8. Start the development server

```
php artisan serve
```

Application will run at:

```
http://127.0.0.1:8000
```

---

## 📡 API Base URL

```
http://127.0.0.1:8000/api
```

---

## 🔒 Project Status

This repository contains proprietary code and is not intended for public redistribution.

---

## 👨‍💻 Maintained By

Backbencher Studio
