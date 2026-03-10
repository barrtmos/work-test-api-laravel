# Бриф проекта (RU)

## 1) Что это за проект
Учебный Laravel API + веб-форма для сбора лида.

Проект делает:
- принимает тестовые API-запросы (`/api/test-get`, `/api/test-post`);
- принимает лид через API (`/api/lead`) и через веб-форму (`/lead-form`), сохраняет в БД;
- связывает browser Pixel событие и server-side CAPI payload через общий `event_id`.

## 2) Технологии и версии
- PHP `^8.4`
- Laravel `^12`
- БД по умолчанию: `sqlite` (`database/database.sqlite`)

## 3) Как запустить
1. Установить зависимости:
```bash
composer install
```
2. Подготовить env:
```bash
cp .env.example .env
php artisan key:generate
```
3. Проверить `.env`:
```env
DB_CONNECTION=sqlite
API_KEY=your_secret_key
API_BASE_URL=http://127.0.0.1:8080

FACEBOOK_PIXEL_ID=123456789012345
FACEBOOK_ACCESS_TOKEN=fake_token_for_learning
FACEBOOK_TEST_EVENT_CODE=TEST12345
```
4. Подготовить БД:
```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
```

## 3.1) Важный нюанс запуска (чтобы не словить timeout)
`POST /lead-form` внутри вызывает `${API_BASE_URL}/api/lead`.
При `php artisan serve` в 1 процессе запрос "в себя" может зависнуть и вернуть `cURL error 28`.

Рабочие варианты:
- Вариант A:
  - `php artisan serve --host=127.0.0.1 --port=8080` (API)
  - `php artisan serve --host=127.0.0.1 --port=8000` (форма)
- Вариант B:
  - в `.env`: `API_BASE_URL=http://127.0.0.1:8000`, `PHP_CLI_SERVER_WORKERS=4`
  - запуск: `php artisan serve --host=127.0.0.1 --port=8000`

## 4) Маршруты
### Web
- `GET /lead-form` - страница формы
- `POST /lead-form` - отправка формы (внутри HTTP-запрос в API)

### API (защищены `CheckApiKey`)
- `GET /api/test-get`
- `POST /api/test-post`
- `POST /api/lead`

Для API обязателен заголовок:
```http
X-API-KEY: <значение API_KEY из .env>
```

## 5) Основной поток данных
1. Пользователь открывает `/lead-form?utm_source=fb&campaign=test&click_id=123`.
2. `LeadFormController@show` сохраняет query-параметры в сессию и генерирует `event_id`.
3. Форма отправляет лид на `POST /lead-form`.
4. `LeadFormController@submit` отправляет в API (`POST /api/lead`) поля лида, `query_params`, `event_id`.
5. `LeadController@store` валидирует и сохраняет запись в `leads`.
6. Если лид сохранен успешно, вызывается `FacebookConversionService`.
7. В браузере при успешной отправке триггерится `fbq('track', 'Lead', ..., { eventID })`.

## 6) Что хранится в таблице `leads`
- `first_name`
- `last_name`
- `email`
- `phone_number`
- `ip_address`
- `user_agent`
- `query_params` (`TEXT NULL`, JSON-строка)
- `created_at`, `updated_at`

## 7) Структура ключевых файлов
- `routes/web.php` - web маршруты
- `routes/api.php` - API маршруты
- `app/Http/Controllers/LeadFormController.php` - форма и проксирование в API
- `app/Http/Controllers/LeadController.php` - сохранение лида и запуск server-side CAPI шага
- `app/Http/Requests/StoreLeadRequest.php` - валидация лида
- `app/Http/Middleware/CheckApiKey.php` - проверка API-ключа
- `app/Services/FacebookConversionService.php` - сборка/логирование CAPI payload
- `resources/views/lead-form.blade.php` - HTML-форма + Pixel
- `app/Models/Lead.php` - модель Eloquent
- `database/migrations/*leads*` - миграции таблицы лидов

## 8) Последние изменения (актуально)
### 8.1 Сохранение query-параметров URL
Добавлено:
- поле `query_params` в таблицу `leads`;
- `query_params` в `Lead::$fillable`;
- валидация `query_params` как `nullable|string`;
- сохранение query-параметров через сессию при открытии формы.

### 8.2 Facebook Pixel в форме
В `resources/views/lead-form.blade.php`:
- добавлен base code Pixel (`PageView`);
- после успешной отправки формы вызывается `Lead` с `eventID`.

### 8.3 Server-side CAPI шаг
В `LeadController` после успешного `Lead::create(...)`:
- вызывается `FacebookConversionService`;
- формируется payload события `Lead` (`event_id`, `action_source=website`, `user_data`, `test_event_code`);
- payload логируется в `storage/logs/laravel.log` (тестовый/подготовительный режим).

## 9) Быстрая проверка
1. Открыть:
`http://127.0.0.1:8000/lead-form?utm_source=fb&campaign=test&click_id=123`
2. Отправить форму.
3. Проверить БД:
```php
App\Models\Lead::latest()->first();
```
4. Проверить логи:
```bash
tail -f storage/logs/laravel.log
```
Ожидаемо появляется запись `Facebook CAPI - Lead Event` с корректным `event_id`.

## 10) Важная оговорка по тестам
Шаблонный тест `tests/Feature/ExampleTest.php` проверяет `GET /` на `200`, но роут `/` не объявлен, поэтому тест падает (404). Это не ломает lead-flow.
