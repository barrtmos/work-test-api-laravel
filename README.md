# Work Test API Laravel

Короткая инструкция, как развернуть проект на новом ПК после `git clone`.

## 1) Требования
- PHP 8.4+
- Composer
- SQLite (или другая БД, но по умолчанию проект на SQLite)

Для Ubuntu:
```bash
sudo apt update
sudo apt install -y php-cli php-sqlite3 php-mbstring php-xml php-curl php-zip composer sqlite3
```

## 2) Клонирование проекта
```bash
git clone <URL_репозитория>
cd work-test-api-laravel
```
Зачем: получаем код и переходим в папку проекта.

## 3) Установка PHP-зависимостей
```bash
composer install
```
Зачем: скачиваются пакеты Laravel и библиотеки из `composer.lock`.

## 4) Создание `.env` из шаблона
```bash
cp .env.example .env
```
Зачем: создается локальный файл конфигурации приложения.

## 5) Генерация ключа приложения
```bash
php artisan key:generate
```
Зачем: записывает `APP_KEY` в `.env` (нужен Laravel для шифрования/сессий).

## 6) Подготовка SQLite
```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
```
Зачем: создает файл БД, если его нет.

## 7) Настройки в `.env`
Добавь/проверь строки:
```env
DB_CONNECTION=sqlite
APP_URL=http://127.0.0.1:8000
API_KEY=test123
API_BASE_URL=http://127.0.0.1:8000
```
Зачем:
- `API_KEY` нужен для доступа к API (`X-API-KEY`).
- `API_BASE_URL` нужен web-форме, чтобы отправлять лид в API.

## 8) Миграции БД
```bash
php artisan migrate
```
Зачем: создаются таблицы, включая `leads`.

## 9) Запуск проекта
Важно: `POST /lead-form` внутри делает HTTP-запрос на `${API_BASE_URL}/api/lead`.
При `php artisan serve` (1 процесс) запрос "в себя" может дать timeout.

### Вариант A (под текущие настройки `.env`: `API_BASE_URL=http://127.0.0.1:8080`)
Терминал 1 (API):
```bash
php artisan serve --host=127.0.0.1 --port=8080
```
Терминал 2 (форма):
```bash
php artisan serve --host=127.0.0.1 --port=8000
```
Открывай форму: `http://127.0.0.1:8000/lead-form?...`

### Вариант B (один порт)
1. В `.env`:
```env
API_BASE_URL=http://127.0.0.1:8000
PHP_CLI_SERVER_WORKERS=4
```
2. Запуск:
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

## 10) Быстрый тест
Открой:
`http://127.0.0.1:8000/lead-form?utm_source=fb&campaign=test&click_id=123`

Проверка в БД:
```bash
php artisan tinker
App\Models\Lead::latest()->first();
```
Ожидаемо: в записи лида поле `query_params` содержит UTM/click параметры.

## 11) Полезно знать
- Если меняешь порт API, обнови `API_BASE_URL` в `.env`.
- Для текущего `.env` в репозитории (`API_BASE_URL=8080`) нужно 2 процесса (`8080` и `8000`), либо перенастройка на один порт + воркеры.
