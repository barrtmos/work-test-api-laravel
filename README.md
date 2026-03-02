# 1. Dependencies
sudo apt update && sudo apt install php-cli composer php-xml php-curl php-zip php-mbstring -y

# 2. Project Creation
composer create-project laravel/laravel work-test-api-laravel
cd work-test-api-laravel

# 3. Environment Setup
cp .env.example .env
php artisan key:generate

# 4. API Activation (creates routes/api.php)
php artisan install:api

# 5. Controller Generation
php artisan make:controller TestController

# 6. Routes (file: routes/api.php)
# Add /test-get and /test-post routes mapped to TestController.

# 7. Logic (file: app/Http/Controllers/TestController.php)
# Implement testGet and testPost methods to return JSON.

# 8. Start Server
php artisan serve

# 9. Verification (GET)
# Browser: http://localhost:8000/api/test-get?name=John&age=25
