# Work Test API Laravel

Короткая инструкция, как развернуть проект и проверить текущий lead-flow с Pixel + server-side CAPI-подготовкой.

## 1) Требования
- PHP 8.4+
- Composer
- SQLite (или другая БД; по умолчанию проект на SQLite)

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

## 3) Установка зависимостей
```bash
composer install
```

## 4) Подготовка `.env`
```bash
cp .env.example .env
php artisan key:generate
```

Минимальные настройки:
```env
DB_CONNECTION=sqlite
APP_URL=http://127.0.0.1:8000
API_KEY=test123
API_BASE_URL=http://127.0.0.1:8080

FACEBOOK_PIXEL_ID=123456789012345
FACEBOOK_ACCESS_TOKEN=fake_token_for_learning
FACEBOOK_TEST_EVENT_CODE=TEST12345
```

Пояснение:
- `API_KEY` - обязателен для API (`X-API-KEY`).
- `API_BASE_URL` - куда web-форма отправляет `POST /api/lead`.
- `FACEBOOK_*` - настройки server-side CAPI payload.

## 5) Подготовка SQLite
```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
```

## 6) Запуск проекта
Важно: `POST /lead-form` внутри делает HTTP-запрос на `${API_BASE_URL}/api/lead`.
При `php artisan serve` в 1 процессе запрос "в себя" может дать timeout.

### Вариант A (два процесса)
Терминал 1 (API):
```bash
php artisan serve --host=127.0.0.1 --port=8080
```

Терминал 2 (форма):
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

### Вариант B (один порт)
В `.env`:
```env
API_BASE_URL=http://127.0.0.1:8000
PHP_CLI_SERVER_WORKERS=4
```
Запуск:
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

## 7) Что реализовано сейчас
- Web-форма: `GET /lead-form`, `POST /lead-form`.
- API: `POST /api/lead` (через `X-API-KEY`).
- Query-параметры (`utm_*`, `click_id` и т.п.) сохраняются в `leads.query_params`.
- В форме подключен Facebook Pixel base code (`PageView`).
- После успешной отправки формы в браузере вызывается `fbq('track', 'Lead', ..., { eventID })`.
- На сервере после успешного сохранения лида формируется CAPI payload `Lead` с тем же `event_id`.
- CAPI payload логируется в `storage/logs/laravel.log` (режим подготовки/тестовой интеграции).

## 8) Быстрая проверка
Открой:
`http://127.0.0.1:8000/lead-form?utm_source=fb&campaign=test&click_id=123`

Отправь форму и проверь:

1. Запись в БД:
```bash
php artisan tinker
App\Models\Lead::latest()->first();
```

2. Логи CAPI payload:
```bash
tail -f storage/logs/laravel.log
```
Ожидаемо в логе появится `Facebook CAPI - Lead Event` с `event_id`, `user_data`, `test_event_code`.

## 9) Полезно знать
- Если меняешь порт API, обнови `API_BASE_URL`.
- Шаблонный `tests/Feature/ExampleTest.php` падает на `GET /` (404), так как `/` не объявлен в маршрутах.
