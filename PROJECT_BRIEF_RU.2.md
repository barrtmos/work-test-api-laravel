# Бриф проекта (RU)

## 1) Что это за проект
Учебный Laravel API + веб-форма для сбора лида.

Проект делает две вещи:
- принимает тестовые API-запросы (`/api/test-get`, `/api/test-post`);
- принимает лид через API (`/api/lead`) и через веб-форму (`/lead-form`), сохраняет в БД.

## 2) Технологии и версии
- PHP `^8.4`
- Laravel `^12`
- БД по умолчанию: `sqlite` (файл `database/database.sqlite`)

## 3) Как запустить (с нуля)
1. Установить зависимости:
```bash
composer install
```
2. Подготовить env:
```bash
cp .env.example .env
php artisan key:generate
```
3. Убедиться, что в `.env` есть:
```env
DB_CONNECTION=sqlite
API_KEY=your_secret_key
API_BASE_URL=http://127.0.0.1:8000
```
4. Накатить миграции:
```bash
php artisan migrate
```
5. Запустить сервер:
```bash
php artisan serve
```

По умолчанию Laravel поднимается на `http://127.0.0.1:8000`.

Если нужен другой порт:
```bash
php artisan serve --port=8080
```
И тогда обязательно синхронизировать:
```env
API_BASE_URL=http://127.0.0.1:8080
```

## 3.1) Важный нюанс запуска (чтобы не словить timeout)
`POST /lead-form` внутри вызывает `${API_BASE_URL}/api/lead`.
При `php artisan serve` в 1 процессе запрос "в себя" может зависнуть и вернуть `cURL error 28`.

Рабочие варианты:
- Вариант A (как в текущем `.env`, где `API_BASE_URL=http://127.0.0.1:8080`):
  - процесс 1: `php artisan serve --host=127.0.0.1 --port=8080` (API)
  - процесс 2: `php artisan serve --host=127.0.0.1 --port=8000` (форма)
  - открывать форму на `http://127.0.0.1:8000/lead-form?...`
- Вариант B (один порт):
  - в `.env`: `API_BASE_URL=http://127.0.0.1:8000` и `PHP_CLI_SERVER_WORKERS=4`
  - запуск: `php artisan serve --host=127.0.0.1 --port=8000`

## 4) Маршруты
### Web
- `GET /lead-form` - страница формы
- `POST /lead-form` - отправка формы (внутри делает HTTP-запрос в API)

### API (защищены middleware `CheckApiKey`)
- `GET /api/test-get`
- `POST /api/test-post`
- `POST /api/lead`

Для API обязателен заголовок:
```http
X-API-KEY: <значение API_KEY из .env>
```

## 5) Основной поток данных
1. Пользователь открывает:
`/lead-form?utm_source=fb&campaign=test&click_id=123`
2. Контроллер формы читает query-параметры и сохраняет их в Сессию
3. Форма отправляет:
- поля лида (`first_name`, `last_name`, `email`, `phone_number`);
- технические поля (`ip_address`, `user_agent`);
4. `LeadFormController` достает query_params из сессии отправляет это на `POST /api/lead`.
5. `LeadController` валидирует через `StoreLeadRequest` и сохраняет запись в `leads`.

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
- `app/Http/Controllers/LeadFormController.php` - логика формы и проксирование в API
- `app/Http/Controllers/LeadController.php` - сохранение лида
- `app/Http/Requests/StoreLeadRequest.php` - валидация лида
- `app/Http/Middleware/CheckApiKey.php` - проверка API-ключа
- `resources/views/lead-form.blade.php` - HTML-форма
- `app/Models/Lead.php` - модель Eloquent
- `database/migrations/*leads*` - создание/изменение таблицы `leads`

## 8) Последние изменения (важно)
### 8.1 Сохранение query-параметров URL
Добавлено:
- поле `query_params` в таблицу `leads` (миграция `2026_03_07_120908_add_query_params_to_leads_table.php`);
- включение `query_params` в `fillable` модели `Lead`;
- валидация `query_params` как `nullable|string`;
- query-параметры сохраняются в сессию при открытии страницы, скрытого поля в форме нет.

Итог: UTM/click_id теперь сохраняются в БД в `leads.query_params`.

### 8.2 Facebook Pixel в форме
В `resources/views/lead-form.blade.php` добавлен:
- базовый код Facebook Pixel (событие `PageView`);
- событие `Lead` после успешной отправки формы.

## 9) Быстрая проверка работоспособности
1. Открыть:
`http://127.0.0.1:8000/lead-form?utm_source=fb&campaign=test&click_id=123`
2. Отправить форму.
3. Проверить в tinker:
```php
App\Models\Lead::latest()->first();
```
Ожидаемо: у записи заполнено `query_params`, например
`{"utm_source":"fb","campaign":"test","click_id":"123"}`.

## 10) Важная оговорка по тестам
Шаблонный тест `tests/Feature/ExampleTest.php` проверяет `GET /` на `200`, но роут `/` в проекте не объявлен, поэтому этот тест падает (404). Это не ломает lead-flow.
