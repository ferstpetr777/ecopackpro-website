<?php
/**
 * Test Cart Functionality
 * Тестовый скрипт для проверки работы корзины WooCommerce
 */

// Загружаем WordPress
require_once('wp-load.php');

// Проверяем, что WooCommerce активен
if (!class_exists('WooCommerce')) {
    die('WooCommerce не активен!');
}

echo "<h1>Тест функциональности корзины WooCommerce</h1>";

// 1. Проверка AJAX
echo "<h2>1. Проверка AJAX настроек</h2>";
$ajax_enabled = get_option('woocommerce_enable_ajax_add_to_cart');
echo "AJAX добавление в корзину: " . ($ajax_enabled === 'yes' ? '<strong style="color:green;">✓ Включено</strong>' : '<strong style="color:red;">✗ Отключено</strong>') . "<br>";

$redirect_after_add = get_option('woocommerce_cart_redirect_after_add');
echo "Редирект после добавления: " . ($redirect_after_add === 'no' ? '<strong style="color:green;">✓ Отключен (правильно)</strong>' : '<strong style="color:red;">✗ Включен</strong>') . "<br>";

// 2. Проверка сессий
echo "<h2>2. Проверка сессий</h2>";
if (WC()->session) {
    echo "WooCommerce сессии: <strong style='color:green;'>✓ Работают</strong><br>";
    echo "ID сессии: " . WC()->session->get_customer_id() . "<br>";
} else {
    echo "WooCommerce сессии: <strong style='color:red;'>✗ Не работают</strong><br>";
}

// 3. Проверка корзины
echo "<h2>3. Проверка корзины</h2>";
if (WC()->cart) {
    echo "Корзина: <strong style='color:green;'>✓ Инициализирована</strong><br>";
    echo "Товаров в корзине: " . WC()->cart->get_cart_contents_count() . "<br>";
    echo "Сумма корзины: " . WC()->cart->get_cart_total() . "<br>";
} else {
    echo "Корзина: <strong style='color:red;'>✗ Не инициализирована</strong><br>";
}

// 4. Проверка загрузки скриптов
echo "<h2>4. Проверка загрузки скриптов</h2>";
global $wp_scripts;
if (isset($wp_scripts->registered['wc-cart-fragments'])) {
    echo "wc-cart-fragments: <strong style='color:green;'>✓ Зарегистрирован</strong><br>";
} else {
    echo "wc-cart-fragments: <strong style='color:red;'>✗ Не зарегистрирован</strong><br>";
}

if (isset($wp_scripts->registered['wc-add-to-cart'])) {
    echo "wc-add-to-cart: <strong style='color:green;'>✓ Зарегистрирован</strong><br>";
} else {
    echo "wc-add-to-cart: <strong style='color:red;'>✗ Не зарегистрирован</strong><br>";
}

// 5. Тест добавления товара
echo "<h2>5. Тест добавления товара</h2>";
$test_product_id = 7143; // ID товара "Пластиковая номерная пломба Твист-Про"

$product = wc_get_product($test_product_id);
if ($product) {
    echo "Товар найден: <strong style='color:green;'>✓ " . $product->get_name() . "</strong><br>";
    echo "Цена: " . $product->get_price_html() . "<br>";
    echo "В наличии: " . ($product->is_in_stock() ? '<strong style="color:green;">✓ Да</strong>' : '<strong style="color:red;">✗ Нет</strong>') . "<br>";
    echo "Можно купить: " . ($product->is_purchasable() ? '<strong style="color:green;">✓ Да</strong>' : '<strong style="color:red;">✗ Нет</strong>') . "<br>";
    
    // Попробуем добавить в корзину
    if (isset($_GET['add_test'])) {
        $result = WC()->cart->add_to_cart($test_product_id, 1);
        if ($result) {
            echo "<p style='color:green; font-weight:bold;'>✓ Товар успешно добавлен в корзину! Cart Key: $result</p>";
            echo "<p><a href='?'>Обновить страницу</a> | <a href='/cart/'>Перейти в корзину</a></p>";
        } else {
            echo "<p style='color:red; font-weight:bold;'>✗ Ошибка добавления товара в корзину!</p>";
        }
    } else {
        echo "<p><a href='?add_test=1' style='padding:10px 20px; background:#0071a1; color:white; text-decoration:none; border-radius:5px;'>Тестировать добавление в корзину</a></p>";
    }
} else {
    echo "Товар: <strong style='color:red;'>✗ Не найден (ID: $test_product_id)</strong><br>";
}

// 6. Проверка плагинов оптимизации
echo "<h2>6. Проверка плагинов оптимизации</h2>";
$autoptimize_js_exclude = get_option('autoptimize_js_exclude');
if ($autoptimize_js_exclude) {
    echo "Autoptimize JS исключения:<br><pre>" . htmlspecialchars($autoptimize_js_exclude) . "</pre>";
} else {
    echo "Autoptimize: <strong style='color:orange;'>⚠ Нет исключений для JS</strong><br>";
}

// 7. Проверка ошибок PHP
echo "<h2>7. Последние ошибки PHP</h2>";
$debug_log = 'wp-content/debug.log';
if (file_exists($debug_log)) {
    $log_content = file_get_contents($debug_log);
    $log_lines = explode("\n", $log_content);
    $recent_errors = array_slice(array_reverse($log_lines), 0, 10);
    
    echo "<pre style='background:#f5f5f5; padding:10px; max-height:300px; overflow:auto;'>";
    foreach ($recent_errors as $line) {
        if (stripos($line, 'cart') !== false || stripos($line, 'woocommerce') !== false) {
            echo htmlspecialchars($line) . "\n";
        }
    }
    echo "</pre>";
} else {
    echo "<p>Debug.log не найден</p>";
}

echo "<hr>";
echo "<p><strong>Тест завершен!</strong> Время: " . date('Y-m-d H:i:s') . "</p>";
echo "<p><a href='/'>На главную</a> | <a href='/shop/'>В магазин</a> | <a href='/cart/'>В корзину</a></p>";

