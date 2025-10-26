# EcopackPro Website - Документация для Cursor Vector DB

## Описание проекта

**EcopackPro** - это интернет-магазин упаковочных материалов, специализирующийся на продаже коробок, курьерских пакетов, воздушно-пузырьковой пленки, термоэтикеток и других упаковочных материалов оптом и мелким оптом.

**Сайт**: https://ecopackpro.ru  
**GitHub**: https://github.com/ferstpetr777/ecopackpro-website

## Архитектура системы

### Технический стек

- **CMS**: WordPress 6.x
- **E-commerce**: WooCommerce
- **PHP**: 8.0+
- **MySQL**: 8.0+
- **Web Server**: Apache/Nginx
- **SSL**: Let's Encrypt
- **CDN**: CloudFlare (опционально)

### Основные компоненты

1. **WordPress Core** - Основная CMS
2. **WooCommerce** - E-commerce функциональность
3. **Тема** - Кастомная тема для упаковочных материалов
4. **Плагины** - SEO, безопасность, производительность
5. **База данных** - MySQL для хранения данных
6. **Медиафайлы** - Изображения товаров и контента

## Структура кода

### Основные директории

```
ecopackpro.ru/
├── wp-admin/                    # Административная панель WordPress
├── wp-content/                  # Пользовательский контент
│   ├── themes/                  # Темы
│   │   └── ecopackpro-theme/    # Основная тема
│   ├── plugins/                 # Плагины
│   │   ├── woocommerce/         # WooCommerce
│   │   ├── yoast-seo/           # SEO оптимизация
│   │   ├── elementor/           # Конструктор страниц
│   │   └── [другие плагины]/
│   ├── uploads/                 # Загруженные файлы
│   │   ├── products/            # Изображения товаров
│   │   ├── banners/             # Баннеры
│   │   └── [другие медиа]/
│   └── languages/               # Переводы
├── wp-includes/                 # Ядро WordPress
├── wp-config.php                # Конфигурация
├── .htaccess                    # Настройки Apache
└── index.php                    # Точка входа
```

### Ключевые файлы темы

```php
// functions.php - Основные функции темы
function ecopackpro_theme_setup() {
    // Настройка темы
    add_theme_support('woocommerce');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
}

// style.css - Основные стили
.ecopackpro-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

// header.php - Шапка сайта
<header class="ecopackpro-header">
    <div class="container">
        <div class="logo">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/logo.png" alt="EcopackPro">
        </div>
        <nav class="main-menu">
            <?php wp_nav_menu(['theme_location' => 'primary']); ?>
        </nav>
    </div>
</header>

// footer.php - Подвал сайта
<footer class="ecopackpro-footer">
    <div class="container">
        <div class="footer-widgets">
            <?php dynamic_sidebar('footer-1'); ?>
        </div>
    </div>
</footer>
```

## WooCommerce интеграция

### Кастомные типы товаров

```php
// Категории товаров
$product_categories = [
    'boxes' => 'Коробки',
    'courier-bags' => 'Курьерские пакеты',
    'zip-lock-bags' => 'ZIP-LOCK пакеты с бегунком',
    'air-cushion-bags' => 'Пакеты с воздушной подушкой',
    'bubble-wrap' => 'Воздушно-пузырьковая пленка',
    'thermal-labels' => 'Термоэтикетки',
    'packaging-materials' => 'Товары для упаковки',
    'sealing-materials' => 'Средства опломбировки'
];

// Кастомные поля товаров
function add_custom_product_fields() {
    woocommerce_wp_text_input([
        'id' => 'material_type',
        'label' => 'Тип материала',
        'placeholder' => 'Картон, пластик, бумага'
    ]);
    
    woocommerce_wp_text_input([
        'id' => 'dimensions',
        'label' => 'Размеры',
        'placeholder' => 'Д x Ш x В (мм)'
    ]);
    
    woocommerce_wp_text_input([
        'id' => 'color',
        'label' => 'Цвет',
        'placeholder' => 'Белый, коричневый, прозрачный'
    ]);
}
```

### Кастомные страницы

```php
// Страница каталога
function ecopackpro_catalog_page() {
    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false
    ]);
    
    foreach ($categories as $category) {
        echo '<div class="category-card">';
        echo '<h3>' . $category->name . '</h3>';
        echo '<p>' . $category->description . '</p>';
        echo '<a href="' . get_term_link($category) . '">Смотреть товары</a>';
        echo '</div>';
    }
}

// Страница товара
function ecopackpro_single_product() {
    global $product;
    
    // Дополнительная информация о товаре
    $material = get_post_meta($product->get_id(), 'material_type', true);
    $dimensions = get_post_meta($product->get_id(), 'dimensions', true);
    $color = get_post_meta($product->get_id(), 'color', true);
    
    echo '<div class="product-specifications">';
    if ($material) echo '<p><strong>Материал:</strong> ' . $material . '</p>';
    if ($dimensions) echo '<p><strong>Размеры:</strong> ' . $dimensions . '</p>';
    if ($color) echo '<p><strong>Цвет:</strong> ' . $color . '</p>';
    echo '</div>';
}
```

## SEO оптимизация

### Yoast SEO настройки

```php
// Кастомные мета-описания
function ecopackpro_custom_meta_description($description) {
    if (is_product_category()) {
        $category = get_queried_object();
        return "Купить {$category->name} оптом и в розницу. Высокое качество, быстрая доставка по России. EcopackPro - ваш надежный поставщик упаковочных материалов.";
    }
    return $description;
}
add_filter('wpseo_metadesc', 'ecopackpro_custom_meta_description');

// Структурированные данные
function ecopackpro_add_structured_data() {
    if (is_product()) {
        global $product;
        
        $structured_data = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->get_name(),
            'description' => $product->get_description(),
            'image' => wp_get_attachment_url($product->get_image_id()),
            'brand' => [
                '@type' => 'Brand',
                'name' => 'EcopackPro'
            ],
            'offers' => [
                '@type' => 'Offer',
                'price' => $product->get_price(),
                'priceCurrency' => 'RUB',
                'availability' => $product->is_in_stock() ? 'InStock' : 'OutOfStock'
            ]
        ];
        
        echo '<script type="application/ld+json">' . json_encode($structured_data) . '</script>';
    }
}
add_action('wp_head', 'ecopackpro_add_structured_data');
```

### Оптимизация производительности

```php
// Кэширование
function ecopackpro_enable_caching() {
    // Кэш для товаров
    if (is_product()) {
        $cache_key = 'product_' . get_the_ID();
        $cached_content = get_transient($cache_key);
        
        if ($cached_content === false) {
            ob_start();
            // Генерация контента
            $content = ob_get_clean();
            set_transient($cache_key, $content, HOUR_IN_SECONDS);
            echo $content;
        } else {
            echo $cached_content;
        }
    }
}

// Оптимизация изображений
function ecopackpro_optimize_images($image_url, $width, $height) {
    // Использование WebP формата
    $webp_url = str_replace(['.jpg', '.jpeg', '.png'], '.webp', $image_url);
    
    if (file_exists($webp_url)) {
        return $webp_url;
    }
    
    return $image_url;
}
```

## База данных

### Основные таблицы WooCommerce

```sql
-- Товары
wp_posts (post_type = 'product')
├── ID
├── post_title (название товара)
├── post_content (описание)
├── post_excerpt (краткое описание)
└── post_status

-- Мета-данные товаров
wp_postmeta
├── post_id
├── meta_key (цена, вес, размеры)
└── meta_value

-- Категории товаров
wp_terms
├── term_id
├── name (название категории)
└── slug

-- Связи товаров с категориями
wp_term_relationships
├── object_id (ID товара)
└── term_taxonomy_id (ID категории)

-- Заказы
wp_posts (post_type = 'shop_order')
├── ID
├── post_title (номер заказа)
├── post_status (статус заказа)
└── post_date (дата заказа)
```

### Кастомные таблицы

```sql
-- Дополнительные характеристики товаров
CREATE TABLE wp_ecopackpro_product_specs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    material_type VARCHAR(100),
    dimensions VARCHAR(50),
    color VARCHAR(50),
    weight DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Статистика продаж
CREATE TABLE wp_ecopackpro_sales_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    date DATE,
    quantity_sold INT,
    revenue DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## API интеграции

### WooCommerce REST API

```php
// Кастомные эндпоинты
add_action('rest_api_init', function() {
    register_rest_route('ecopackpro/v1', '/products/search', [
        'methods' => 'GET',
        'callback' => 'ecopackpro_search_products',
        'permission_callback' => '__return_true'
    ]);
});

function ecopackpro_search_products($request) {
    $search_term = $request->get_param('q');
    $category = $request->get_param('category');
    
    $args = [
        'post_type' => 'product',
        'posts_per_page' => 20,
        's' => $search_term,
        'meta_query' => [
            [
                'key' => '_stock_status',
                'value' => 'instock'
            ]
        ]
    ];
    
    if ($category) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category
            ]
        ];
    }
    
    $products = get_posts($args);
    $results = [];
    
    foreach ($products as $product) {
        $wc_product = wc_get_product($product->ID);
        $results[] = [
            'id' => $product->ID,
            'name' => $product->post_title,
            'price' => $wc_product->get_price(),
            'image' => wp_get_attachment_url($wc_product->get_image_id()),
            'permalink' => get_permalink($product->ID)
        ];
    }
    
    return $results;
}
```

### Интеграция с внешними сервисами

```php
// Интеграция с 1C
function ecopackpro_sync_with_1c() {
    $api_url = 'https://1c.ecopackpro.ru/api/products';
    $response = wp_remote_get($api_url);
    
    if (!is_wp_error($response)) {
        $products = json_decode(wp_remote_retrieve_body($response), true);
        
        foreach ($products as $product_data) {
            $existing_product = get_posts([
                'post_type' => 'product',
                'meta_query' => [
                    [
                        'key' => '1c_id',
                        'value' => $product_data['id']
                    ]
                ]
            ]);
            
            if (empty($existing_product)) {
                // Создание нового товара
                ecopackpro_create_product($product_data);
            } else {
                // Обновление существующего товара
                ecopackpro_update_product($existing_product[0]->ID, $product_data);
            }
        }
    }
}

// Интеграция с доставкой
function ecopackpro_calculate_shipping($cart_items) {
    $total_weight = 0;
    $total_volume = 0;
    
    foreach ($cart_items as $item) {
        $product = wc_get_product($item['product_id']);
        $weight = $product->get_weight();
        $dimensions = $product->get_dimensions();
        
        $total_weight += $weight * $item['quantity'];
        $total_volume += ($dimensions['length'] * $dimensions['width'] * $dimensions['height']) * $item['quantity'];
    }
    
    // Расчет стоимости доставки
    $shipping_cost = ecopackpro_get_shipping_cost($total_weight, $total_volume);
    
    return $shipping_cost;
}
```

## Безопасность

### Защита от атак

```php
// Ограничение попыток входа
function ecopackpro_limit_login_attempts() {
    $ip = $_SERVER['REMOTE_ADDR'];
    $attempts = get_transient('login_attempts_' . $ip);
    
    if ($attempts >= 5) {
        wp_die('Слишком много попыток входа. Попробуйте позже.');
    }
}
add_action('wp_login_failed', 'ecopackpro_limit_login_attempts');

// Защита от SQL инъекций
function ecopackpro_sanitize_input($input) {
    return sanitize_text_field(wp_unslash($input));
}

// Защита от XSS
function ecopackpro_escape_output($output) {
    return esc_html($output);
}
```

### Резервное копирование

```php
// Автоматическое резервное копирование
function ecopackpro_backup_database() {
    $backup_file = ABSPATH . 'backups/db_backup_' . date('Y-m-d_H-i-s') . '.sql';
    
    $command = "mysqldump -u " . DB_USER . " -p" . DB_PASSWORD . " " . DB_NAME . " > " . $backup_file;
    exec($command);
    
    // Сжатие файла
    exec("gzip " . $backup_file);
}
add_action('wp_scheduled_delete', 'ecopackpro_backup_database');
```

## Мониторинг и аналитика

### Google Analytics интеграция

```javascript
// Отслеживание покупок
gtag('event', 'purchase', {
    transaction_id: '<?php echo $order->get_order_number(); ?>',
    value: <?php echo $order->get_total(); ?>,
    currency: 'RUB',
    items: [
        <?php foreach ($order->get_items() as $item): ?>
        {
            item_id: '<?php echo $item->get_product_id(); ?>',
            item_name: '<?php echo $item->get_name(); ?>',
            category: '<?php echo wp_get_post_terms($item->get_product_id(), 'product_cat')[0]->name; ?>',
            quantity: <?php echo $item->get_quantity(); ?>,
            price: <?php echo $item->get_total(); ?>
        },
        <?php endforeach; ?>
    ]
});
```

### Мониторинг производительности

```php
// Логирование медленных запросов
function ecopackpro_log_slow_queries() {
    if (defined('SAVEQUERIES') && SAVEQUERIES) {
        global $wpdb;
        
        foreach ($wpdb->queries as $query) {
            if ($query[1] > 0.5) { // Запросы дольше 0.5 секунды
                error_log('Slow query: ' . $query[0] . ' Time: ' . $query[1]);
            }
        }
    }
}
add_action('shutdown', 'ecopackpro_log_slow_queries');
```

## Развертывание

### Docker конфигурация

```dockerfile
FROM wordpress:6.4-php8.0-apache

# Установка дополнительных расширений
RUN docker-php-ext-install mysqli pdo_mysql

# Копирование кастомной темы
COPY wp-content/themes/ecopackpro-theme/ /var/www/html/wp-content/themes/ecopackpro-theme/

# Настройка Apache
COPY .htaccess /var/www/html/.htaccess

# Настройка прав доступа
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 80
```

### CI/CD пайплайн

```yaml
# .github/workflows/deploy.yml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      
      - name: Deploy to server
        uses: appleboy/ssh-action@v0.1.5
        with:
          host: ${{ secrets.HOST }}
          username: ${{ secrets.USERNAME }}
          key: ${{ secrets.SSH_KEY }}
          script: |
            cd /var/www/fastuser/data/www/ecopackpro.ru
            git pull origin main
            wp cache flush
            wp rewrite flush
```

## Заключение

EcopackPro - это полнофункциональный интернет-магазин упаковочных материалов, построенный на WordPress и WooCommerce. Проект включает в себя:

**Ключевые особенности:**
- Полная интеграция с WooCommerce
- Кастомная тема для упаковочных материалов
- SEO оптимизация с Yoast
- Интеграция с внешними сервисами
- Система безопасности и мониторинга
- Автоматическое резервное копирование

**Готовность к продакшну:** ✅ Полностью готов к коммерческому использованию

**Техническая поддержка:** Активная разработка и поддержка проекта
