A backend REST API built with Laravel for the CarlosDelRio697 platform.
This service handles authentication, data management, and API endpoints for the application.

🚀 Tech Stack

PHP

Laravel

MySQL

Composer

REST API

📂 Project Structure
app/
bootstrap/
config/
database/
public/
resources/
routes/
storage/
tests/
⚙️ Installation

1. Clone the repository
   git clone git@github.com:backbencherstudio/carlosdelrio697_backend_L.git
2. Navigate to the project directory
   cd carlosdelrio697_backend_L
3. Install dependencies
   composer install
4. Copy environment file
   cp .env.example .env
5. Generate application key
   php artisan key:generate
6. Configure database

Update your .env file with database credentials.

DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password 7. Run database migrations
php artisan migrate 8. Start the development server
php artisan serve

The application will run at:

http://127.0.0.1:8000
📡 API Base URL
http://127.0.0.1:8000/api
🧪 Running Tests
php artisan test
👨‍💻 Author

Backbencher Studio

📄 License

This project is open-source and available under the MIT License.
