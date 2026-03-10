# Бриф проекта (RU)

## 1) Что это за проект
Учебный Laravel-проект с двумя сценариями:
- тестовые API-эндпоинты для проверки валидации и API-ключа;
- lead-flow через web-форму и API с подготовкой browser Pixel + server-side CAPI события.

Проект умеет:
- принимать `GET /api/test-get` и `POST /api/test-post`;
- принимать лид через `POST /api/lead`;
- показывать форму `GET /lead-form` и отправлять её через `POST /lead-form`;
- сохранять query-параметры из URL вместе с лидом;
- связывать browser event и server-side event через общий `event_id`;
- формировать и логировать CAPI payload для события `Lead`.

## 2) Стек
- PHP `^8.4`
- Laravel `^12`
- БД по умолчанию: `sqlite`
- фронт формы: Blade

## 3) Как запустить
1. Установить зависимости:
```bash
composer install
```

2. Подготовить `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

3. Минимально проверить `.env`:
```env
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/project/database/database.sqlite

API_KEY=secret123
API_BASE_URL=http://127.0.0.1:8080

FACEBOOK_PIXEL_ID=123456789012345
FACEBOOK_ACCESS_TOKEN=fake_token_for_learning
FACEBOOK_TEST_EVENT_CODE=TEST12345
```

4. Подготовить SQLite:
```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan migrate
```

## 4) Важный нюанс запуска
`POST /lead-form` внутри делает HTTP-запрос на `${API_BASE_URL}/api/lead`.

Если поднять проект одним `php artisan serve` в один поток и отправлять запрос "в себя", можно получить timeout.

Рабочие варианты:

### Вариант A. Два процесса
Терминал 1:
```bash
php artisan serve --host=127.0.0.1 --port=8080
```

Терминал 2:
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

При этом:
- форма открывается на `http://127.0.0.1:8000/lead-form`
- API вызывается на `http://127.0.0.1:8080/api/lead`

### Вариант B. Один порт
В `.env`:
```env
APP_URL=http://127.0.0.1:8000
API_BASE_URL=http://127.0.0.1:8000
PHP_CLI_SERVER_WORKERS=4
```

Запуск:
```bash
php artisan serve --host=127.0.0.1 --port=8000
```

## 5) Маршруты

### Web
- `GET /lead-form` - показать форму
- `POST /lead-form` - отправить форму, внутри проксирует данные в API

### API
Все API-маршруты защищены middleware `CheckApiKey` и требуют заголовок:

```http
X-API-KEY: <значение API_KEY из .env>
```

Маршруты:
- `GET /api/test-get`
- `POST /api/test-post`
- `POST /api/lead`

## 6) Что делают test API endpoints

### `GET /api/test-get`
Проверяет query-параметры:
- `name` - `nullable|string|min:2|max:50`
- `age` - `nullable|integer|min:18|max:99`

Возвращает JSON:
- `success`
- `method`
- `received`

### `POST /api/test-post`
Проверяет body:
- `email` - `required|email`
- `event` - `required|in:PageView,Lead,Purchase`

Возвращает JSON:
- `success`
- `method`
- `received`

## 7) Основной lead-flow
1. Пользователь открывает `/lead-form` с query-параметрами, например:
`/lead-form?utm_source=fb&campaign=test&click_id=123`
2. `LeadFormController@show` сохраняет query-параметры в сессию.
3. Там же генерируется `event_id` вида `lead_<timestamp>_<random>`.
4. Пользователь отправляет форму.
5. `LeadFormController@submit` собирает данные и отправляет `POST` на `${API_BASE_URL}/api/lead`.
6. В API передаются:
- `first_name`
- `last_name`
- `email`
- `phone_number`
- `ip_address`
- `user_agent`
- `query_params`
- `event_id`
7. `LeadController@store` валидирует входные данные через `StoreLeadRequest`.
8. После успешной валидации создаётся запись `Lead`.
9. Если `event_id` есть, вызывается `FacebookConversionService`.
10. После успешного ответа формы в браузере вызывается `fbq('track', 'Lead', {}, { eventID })`.

## 8) Валидация лида
`StoreLeadRequest` требует:
- `first_name` - `required|string|min:2|max:50`
- `last_name` - `required|string|min:2|max:50`
- `email` - `required|email|max:255`
- `phone_number` - `required|string|min:7|max:20`
- `ip_address` - `required|ip`
- `user_agent` - `required|string|min:5|max:255`
- `query_params` - `nullable|string`
- `event_id` - `nullable|string|max:100`

## 9) Что хранится в таблице `leads`
- `id`
- `first_name`
- `last_name`
- `email`
- `phone_number`
- `ip_address`
- `user_agent`
- `query_params`
- `created_at`
- `updated_at`

`query_params` хранится как JSON-строка.

## 10) Facebook / Pixel / CAPI

### Что есть сейчас
- в форме подключён Facebook Pixel base code;
- при открытии формы вызывается `fbq('track', 'PageView')`;
- после успешной отправки вызывается browser event `Lead` с `eventID`;
- на сервере собирается CAPI payload для `Lead` с тем же `event_id`.

### Что делает сервер
`FacebookConversionService`:
- собирает payload;
- хэширует `email`, `phone`, `first_name`, `last_name`;
- добавляет `client_ip_address` и `client_user_agent`;
- добавляет `test_event_code`, если он есть в `.env`;
- логирует payload в `storage/logs/laravel.log`.

### Важное ограничение текущей реализации
Сейчас сервис не отправляет событие в Meta API реально. Он только пишет лог с подготовленным payload и URL endpoint.

То есть текущий статус такой:
- browser Pixel event вызывается в шаблоне;
- server-side CAPI payload только формируется и логируется;
- реального `Http::post()` в Meta пока нет.

### Важное замечание по `FACEBOOK_PIXEL_ID`
В `.env` уже есть `FACEBOOK_PIXEL_ID`, но в текущем шаблоне формы Pixel ID захардкожен прямо во view.

Это значит:
- серверная часть берёт Pixel ID из конфига;
- браузерный Pixel на странице пока использует вручную прописанное значение.

Если приводить проект к консистентному состоянию, Pixel ID в Blade тоже нужно брать из `.env`.

## 11) Ключевые файлы
- `routes/web.php` - web-маршруты формы
- `routes/api.php` - API-маршруты
- `app/Http/Controllers/LeadFormController.php` - показ формы, сохранение query-параметров, отправка данных в API
- `app/Http/Controllers/LeadController.php` - сохранение лида и вызов server-side шага
- `app/Http/Controllers/TestController.php` - тестовые API-эндпоинты
- `app/Http/Requests/StoreLeadRequest.php` - валидация лида
- `app/Http/Requests/TestGetRequest.php` - валидация `GET /api/test-get`
- `app/Http/Requests/TestPostRequest.php` - валидация `POST /api/test-post`
- `app/Http/Middleware/CheckApiKey.php` - проверка заголовка `X-API-KEY`
- `app/Services/FacebookConversionService.php` - подготовка и логирование CAPI payload
- `app/Models/Lead.php` - модель лида
- `resources/views/lead-form.blade.php` - HTML-форма и Pixel-скрипт
- `database/migrations/*leads*` - миграции таблицы лидов

## 12) Что проверять при ручном тесте
1. Открыть:
`http://127.0.0.1:8000/lead-form?utm_source=fb&campaign=test&click_id=123`

2. Отправить форму валидными данными.

3. Проверить, что в БД появилась запись лида.

4. Проверить, что в `leads.query_params` сохранились query-параметры.

5. Проверить лог:
```bash
tail -f storage/logs/laravel.log
```

Ожидаемо появится запись:
- `Facebook CAPI - Lead Event`
- с `event_id`
- с `payload`
- с `test_event_code`
- с URL вида `https://graph.facebook.com/v21.0/<PIXEL_ID>/events`

## 13) Что нужно заполнить в `.env` для теста

### Минимум для локального прогона
- `APP_URL`
- `DB_CONNECTION=sqlite`
- `DB_DATABASE`
- `API_KEY`
- `API_BASE_URL`

### Для тестовой Meta-интеграции
- `FACEBOOK_PIXEL_ID`
- `FACEBOOK_ACCESS_TOKEN`
- `FACEBOOK_TEST_EVENT_CODE`

Если нужен только локальный прогон с логами, можно оставить тестовые значения `FACEBOOK_*`.
Если нужна реальная интеграция с Meta, нужно подставить настоящие значения.

## 14) Известные ограничения
- `tests/Feature/ExampleTest.php` ожидает `GET /` со статусом `200`, но роут `/` не объявлен, поэтому тест падает с `404`.
- `FACEBOOK_PIXEL_ID` пока не протянут из `.env` в Blade-шаблон формы.
- server-side CAPI сейчас не отправляет запрос в Meta, а только логирует payload.
