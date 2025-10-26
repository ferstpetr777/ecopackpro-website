# WooCommerce Architecture - EcopackPro

## 🏗️ Общая архитектура WooCommerce

### Основные компоненты WooCommerce на сайте EcopackPro:

```
woocommerce/
├── includes/                    # Основная логика WooCommerce
│   ├── admin/                  # Административная часть
│   │   ├── class-wc-admin.php
│   │   ├── class-wc-admin-menus.php
│   │   ├── class-wc-admin-notices.php
│   │   └── reports/            # Отчеты и аналитика
│   ├── api/                    # REST API
│   │   ├── class-wc-rest-controller.php
│   │   ├── class-wc-rest-products-controller.php
│   │   └── class-wc-rest-orders-controller.php
│   ├── checkout/               # Процесс оформления заказа
│   │   ├── class-wc-checkout.php
│   │   ├── class-wc-cart.php
│   │   └── class-wc-customer.php
│   ├── emails/                 # Email уведомления
│   │   ├── class-wc-email.php
│   │   ├── class-wc-email-new-order.php
│   │   └── class-wc-email-customer-completed-order.php
│   ├── payment-gateways/       # Платежные системы
│   │   ├── class-wc-payment-gateway.php
│   │   ├── bacs/               # Банковский перевод
│   │   ├── cheque/             # Чек
│   │   └── cod/                # Оплата при доставке
│   ├── shipping/               # Доставка
│   │   ├── class-wc-shipping.php
│   │   ├── flat-rate/          # Фиксированная ставка
│   │   └── free-shipping/      # Бесплатная доставка
│   └── shortcodes/             # Шорткоды
│       ├── class-wc-shortcode-products.php
│       └── class-wc-shortcode-cart.php
├── templates/                  # Шаблоны для отображения
│   ├── single-product/         # Страница товара
│   │   ├── product-image.php
│   │   ├── product-title.php
│   │   ├── product-price.php
│   │   └── product-add-to-cart.php
│   ├── loop/                   # Цикл товаров
│   │   ├── product.php
│   │   ├── product-image.php
│   │   └── price.php
│   ├── cart/                   # Корзина
│   │   ├── cart.php
│   │   ├── cart-item.php
│   │   └── mini-cart.php
│   ├── checkout/               # Оформление заказа
│   │   ├── form-checkout.php
│   │   ├── form-billing.php
│   │   └── form-shipping.php
│   └── myaccount/              # Личный кабинет
│       ├── my-account.php
│       ├── orders.php
│       └── addresses.php
├── assets/                     # Статические ресурсы
│   ├── css/                    # Стили
│   │   ├── woocommerce.css
│   │   ├── woocommerce-smallscreen.css
│   │   └── woocommerce-layout.css
│   ├── js/                     # JavaScript
│   │   ├── woocommerce.js
│   │   ├── add-to-cart.js
│   │   └── checkout.js
│   └── images/                 # Изображения
│       ├── placeholder.png
│       └── icons/
└── languages/                  # Переводы
    ├── woocommerce-ru_RU.po
    └── woocommerce-ru_RU.mo
```

## 🛒 Каталог товаров EcopackPro

### Структура товаров:

#### 1. **Коробки** (Boxes)
- Картонные коробки различных размеров
- Почтовые коробки
- Упаковочные коробки

#### 2. **Курьерские пакеты** (Courier Bags)
- Пакеты для доставки
- Различные размеры и материалы
- С карманами и без

#### 3. **ZIP-LOCK пакеты** (ZIP-LOCK Bags)
- Пакеты с застежкой
- Матовые и прозрачные
- Различные размеры

#### 4. **Пакеты с воздушной подушкой** (Bubble Bags)
- Защитная упаковка
- Различные размеры
- С клеевым клапаном

#### 5. **Воздушно-пузырьковая пленка** (Bubble Wrap)
- Защитный материал
- Различная толщина
- Рулоны разных размеров

#### 6. **Термоэтикетки** (Thermal Labels)
- Этикетки для печати
- Различные размеры
- ЭКО серия

#### 7. **Товары для упаковки** (Packaging Supplies)
- Проволока витая
- Пломбираторы
- Вспомогательные материалы

#### 8. **Средства опломбировки** (Sealing Equipment)
- Пластиковые пломбы
- Пломбираторы для бочек
- Номерные пломбы

## 🔧 Кастомизация WooCommerce для EcopackPro

### Основные изменения:

#### 1. **Кастомные поля товаров**
```php
// Дополнительные поля для упаковочных материалов
- Размеры (длина, ширина, высота)
- Материал
- Цвет
- Количество в упаковке
- Минимальный заказ
```

#### 2. **Система ценообразования**
```php
// Оптовые цены
- Цена от 1 штуки
- Цена от 100 штук
- Цена от 1000 штук
- Цена от 10000 штук
```

#### 3. **Кастомные атрибуты**
```php
// Атрибуты товаров
- Категория упаковки
- Назначение (интернет-магазин, курьерская служба)
- Материал изготовления
- Размерная сетка
```

#### 4. **Система доставки**
```php
// Варианты доставки
- Самовывоз (Нижний Новгород, Казань)
- Доставка по России
- Экспресс доставка
- Бесплатная доставка от суммы
```

## 📊 База данных WooCommerce

### Основные таблицы:

```sql
-- Товары
wp_posts (post_type = 'product')
wp_postmeta (мета-данные товаров)

-- Заказы
wp_posts (post_type = 'shop_order')
wp_postmeta (мета-данные заказов)

-- Клиенты
wp_users
wp_usermeta

-- Корзина
wp_woocommerce_sessions

-- Атрибуты товаров
wp_woocommerce_attribute_taxonomies
wp_terms
wp_term_taxonomy
wp_term_relationships

-- Отчеты
wp_woocommerce_order_items
wp_woocommerce_order_itemmeta
```

## 🎨 Кастомизация темы

### Файлы темы для WooCommerce:

```
themes/ecopackpro/woocommerce/
├── single-product/
│   ├── product-image.php      # Изображения товара
│   ├── product-title.php      # Название товара
│   ├── product-price.php      # Цена товара
│   ├── product-meta.php       # Мета-данные
│   └── product-tabs.php       # Вкладки товара
├── loop/
│   ├── product.php            # Карточка товара в каталоге
│   ├── product-image.php      # Изображение в каталоге
│   └── price.php              # Цена в каталоге
├── cart/
│   ├── cart.php               # Страница корзины
│   ├── cart-item.php          # Элемент корзины
│   └── mini-cart.php          # Мини-корзина
├── checkout/
│   ├── form-checkout.php      # Форма оформления заказа
│   ├── form-billing.php       # Форма биллинга
│   └── form-shipping.php      # Форма доставки
└── myaccount/
    ├── my-account.php         # Личный кабинет
    ├── orders.php             # Заказы
    └── addresses.php          # Адреса
```

## 🔌 Интеграции

### Платежные системы:
- Банковский перевод
- Оплата при доставке
- Онлайн платежи (если подключены)

### Системы доставки:
- СДЭК
- Почта России
- Самовывоз

### CRM интеграции:
- 1C (если подключена)
- Битрикс24 (если подключен)

## 📈 Аналитика и отчеты

### Ключевые метрики:
- Конверсия в заказы
- Средний чек
- Популярные товары
- География заказов
- Время обработки заказов

### Отчеты WooCommerce:
- Отчет по продажам
- Отчет по товарам
- Отчет по клиентам
- Отчет по купонам

## 🚀 Оптимизация производительности

### Кэширование:
- Кэш товаров
- Кэш корзины
- Кэш страниц

### Оптимизация изображений:
- WebP формат
- Ленивая загрузка
- Оптимизация размеров

### Оптимизация базы данных:
- Индексы для быстрого поиска
- Очистка старых данных
- Оптимизация запросов

## 🔒 Безопасность

### Защита данных:
- Шифрование платежных данных
- Защита от SQL-инъекций
- Валидация форм
- CSRF защита

### Резервное копирование:
- Ежедневные бэкапы базы данных
- Резервные копии файлов
- Восстановление из бэкапов

---

**Версия документа:** 1.0  
**Дата создания:** 26 октября 2025  
**Для проекта:** EcopackPro Website
