# WB Data Loader

Сервис для выгрузки данных из тестового API (wb-api) в базу данных MySQL.

Загружаются четыре сущности:
- Продажи (sales)
- Заказы (orders)
- Склады (stocks)
- Доходы (incomes)

## Быстрый старт (демо-база на Beget)

Если вам нужно только посмотреть результат, без локального запуска:

1. Клонируйте репозиторий:
    ```bash
    git clone https://github.com/IgnatovEI/wb-data-loader
    cd wb-data-loader

2. Установите зависимости:
    ```bash
    composer install

3. Создайте файл .env (из .env.example) и пропишите подключение к удалённой базе:
      ```php
      DB_CONNECTION=mysql
      DB_HOST=v91729h3.beget.tech
      DB_PORT=3306
      DB_DATABASE=v91729h3_bd
      DB_USERNAME=v91729h3_bd
      DB_PASSWORD=jbCyrmvnyA2!

   > Прямое внешнее подключение к MySQL ограничено бесплатным тарифом.

4. Сгенерируйте ключ приложения:
    ```bash
    php artisan key:generate

5. Миграции выполнять НЕ НУЖНО – таблицы уже созданы и заполнены данными.

6. Для просмотра данных откройте phpMyAdmin:
- https://free5.beget.com/phpMyAdmin
- Логин: v91729h3_bd
- Пароль: jbCyrmvnyA2!

   В интерфейсе выберите базу v91729h3_bd и смотрите таблицы sales, orders, stocks, incomes.

## Полное локальное развёртывание

Если хотите запустить проект полностью и загрузить данные самостоятельно:

1. Клонируйте репозиторий.

2. Создайте локальную базу данных MySQL и пропишите её в .env:
    ```php
    DB_DATABASE=wb_db
    DB_USERNAME=root
    DB_PASSWORD=password

3. Выполните миграции:
    ```bash
    php artisan migrate

4. Загрузите данные из API командами:
    ```bash
    php artisan wb:fetch 2025-06-01 2025-06-30
    php artisan wb:fetch 2025-07-01 2025-07-31
    php artisan wb:fetch 2026-05-26

   После этого все таблицы будут наполнены актуальными записями.

## Загрузка данных (консольная команда)

Команда `wb:fetch` принимает дату начала и, опционально, дату окончания.
Примеры:

- php artisan wb:fetch 2025-06-01 2025-06-30   # продажи, заказы, доходы за июнь
- php artisan wb:fetch 2025-07-01 2025-07-31   # доходы за июль
- php artisan wb:fetch 2026-05-26              # склады

Перед вставкой данные за указанный период удаляются, чтобы избежать дубликатов.

## Основные файлы

- app/Services/WbApiService.php – логика работы с API
- app/Console/Commands/WbFetchCommand.php – консольная команда
- app/Models/ – модели Sale, Order, Stock, Income
- database/migrations/ – миграции таблиц

## Контакты

- Telegram: @ignatovei
- GitHub: https://github.com/IgnatovEI