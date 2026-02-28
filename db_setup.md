# ТЗ Бөлүк 1: Инфраструктура жана Маалыматтар Базасы (Updated)

## 1. Проекттин Структурасы
Проект PHP 8.2+ (PDO), PostgreSQL жана Docker колдонуу менен "Vanilla PHP" стилинде түзүлөт.

## 2. Маалыматтар Базасынын Схемасы (PostgreSQL)

### Таблица: `admins`
- `id` (SERIAL PRIMARY KEY)
- `username` (VARCHAR(50) UNIQUE)
- `password_hash` (TEXT)
- `created_at` (TIMESTAMP DEFAULT CURRENT_TIMESTAMP)

### Таблица: `periods` (Категориялар)
- `id` (SERIAL PRIMARY KEY)
- `name` (VARCHAR(100))
- `sort_order` (INT DEFAULT 0)

### Таблица: `stages` (Этаптар/Маалыматтар)
- `id` (SERIAL PRIMARY KEY)
- `period_id` (INT REFERENCES periods(id) ON DELETE CASCADE)
- `title` (VARCHAR(255))
- `short_info` (TEXT)
- `youtube_url` (VARCHAR(255))
- `whatsapp_number` (VARCHAR(20))
- `created_at` (TIMESTAMP DEFAULT CURRENT_TIMESTAMP)

### Таблица: `checklists` (Жаңы - Тизмелер)
Бул таблица колдонуучуларга даяр тизмелерди көрсөтүү үчүн керек.
- `id` (SERIAL PRIMARY KEY)
- `title` (VARCHAR(255)) - Мисалы: "Төрөт үйүнө керектелүүчү буюмдар".
- `items` (JSONB) - Тизменин элементтери (массив түрүндө сакталат).
- `is_active` (BOOLEAN DEFAULT TRUE)

## 3. Авто-инициализация (Auto-setup)
1. **Таблицаларды түзүү:** Проект ишке киргенде таблицалар жок болсо автоматтык түзүлөт.
    - Сунуш: PostgreSQL үчүн `CREATE TABLE IF NOT EXISTS ...` колдонуу (кошумча `TABLE EXISTS` текшерүүсүз да болот).
2. **Админди түзүү:** Эгер `admins` таблицасы бош болсо, default админ түзүлөт:
    - **Login:** `admin`
    - **Password:** `admin123` (пароль сөзсүз `password_hash` менен хэш болуп сакталат).
3. **Коопсуздук эскертүүсү (сунуш):**
    - Биринчи киргенден кийин default паролду алмаштыруу талап кылынат (MVP’де эскертүү баннер/билдирүү катары көрсөтсө болот).

## 4. Docker Конфигурациясы
- **App/Web:** PHP-FPM жана Nginx.
- **DB:** PostgreSQL 15.
- **Tailwind:** CDN же Watcher аркылуу стилдерди генерациялоо.

## 5. Коопсуздук Эрежелери
- Бардык сурамдар (Queries) `PDO::prepare()` аркылуу гана аткарылат.
- SQL Injection жана XSS чабуулдарынан коргоо үчүн маалыматтарды фильтрациялоо.