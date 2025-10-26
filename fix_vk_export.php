<?php
/**
 * Скрипт для исправления выгрузки товаров в ВКонтакте
 * Цель: Выгрузить все 193 товара вместо 76
 */

// Подключение к WordPress
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-config.php');
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-load.php');

echo "=== СКРИПТ ИСПРАВЛЕНИЯ ВЫГРУЗКИ ТОВАРОВ В ВКОНТАКТЕ ===\n\n";

// 1. Проверяем текущее состояние
global $wpdb;

echo "1. АНАЛИЗ ТЕКУЩЕГО СОСТОЯНИЯ:\n";
echo "================================\n";

// Общее количество товаров
$total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'");
echo "Всего товаров в базе данных: {$total_products}\n";

// Количество выгруженных товаров
$exported_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");
echo "Выгружено в ВКонтакте: {$exported_products}\n";

$not_exported = $total_products - $exported_products;
echo "НЕ выгружено: {$not_exported}\n\n";

// 2. Проверяем настройки плагина
echo "2. ПРОВЕРКА НАСТРОЕК ПЛАГИНА:\n";
echo "==============================\n";

$settings = get_option('ip2vk_settings_arr');
if ($settings && isset($settings[1])) {
    $group_settings = $settings[1];
    
    echo "ID группы ВКонтакте: " . ($group_settings['group_id'] ?? 'не установлен') . "\n";
    echo "Синхронизация: " . ($group_settings['syncing_with_vk'] ?? 'не установлена') . "\n";
    echo "Шаг экспорта: " . ($group_settings['step_export'] ?? 'не установлен') . "\n";
    echo "Статус сборки: " . ($group_settings['status_sborki'] ?? 'не установлен') . "\n";
    echo "Количество товаров в фиде: " . ($group_settings['count_products_in_feed'] ?? 'не установлено') . "\n";
    echo "CRON статус: " . ($group_settings['status_cron'] ?? 'не установлен') . "\n\n";
}

// 3. Сбрасываем статус сборки для полной перевыгрузки
echo "3. СБРОС СТАТУСА ДЛЯ ПОЛНОЙ ПЕРЕВЫГРУЗКИ:\n";
echo "==========================================\n";

if ($settings && isset($settings[1])) {
    // Сбрасываем статус сборки
    $settings[1]['status_sborki'] = '1'; // 1 = начать сборку
    $settings[1]['date_sborki'] = time();
    $settings[1]['date_sborki_end'] = '0000000001';
    $settings[1]['count_products_in_feed'] = '-1'; // -1 = все товары
    $settings[1]['status_cron'] = 'enabled'; // включаем CRON
    
    // Сохраняем настройки
    update_option('ip2vk_settings_arr', $settings);
    echo "✅ Статус сборки сброшен\n";
    echo "✅ Дата сборки обновлена: " . date('Y-m-d H:i:s') . "\n";
    echo "✅ CRON включен\n";
    echo "✅ Установлено выгружать все товары\n\n";
}

// 4. Проверяем товары без выгрузки
echo "4. АНАЛИЗ НЕВЫГРУЖЕННЫХ ТОВАРОВ:\n";
echo "=================================\n";

$not_exported_query = "
    SELECT p.ID, p.post_title, p.post_status 
    FROM {$wpdb->posts} p 
    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_ip2vk_prod_id_on_vk'
    WHERE p.post_type = 'product' 
    AND p.post_status = 'publish'
    AND pm.meta_value IS NULL
    LIMIT 10
";

$not_exported_products = $wpdb->get_results($not_exported_query);

echo "Примеры невыгруженных товаров:\n";
foreach ($not_exported_products as $product) {
    echo "- ID: {$product->ID}, Название: {$product->post_title}\n";
}

if (count($not_exported_products) > 10) {
    echo "... и еще " . ($not_exported - 10) . " товаров\n";
}

echo "\n";

// 5. Запускаем процесс выгрузки
echo "5. ЗАПУСК ПРОЦЕССА ВЫГРУЗКИ:\n";
echo "=============================\n";

// Проверяем, активен ли плагин
if (!function_exists('ip2vk_do_this_event')) {
    echo "❌ Плагин Import Products to VK не активен или функция недоступна\n";
    exit;
}

// Инициируем выгрузку
echo "🔄 Инициируем выгрузку товаров...\n";

// Вызываем функцию плагина для запуска процесса
if (function_exists('ip2vk_do_this_event')) {
    // Создаем событие для запуска выгрузки
    wp_schedule_single_event(time(), 'ip2vk_do_this_event');
    echo "✅ Событие выгрузки запланировано\n";
}

// 6. Дополнительные настройки для улучшения выгрузки
echo "\n6. ДОПОЛНИТЕЛЬНЫЕ НАСТРОЙКИ:\n";
echo "==============================\n";

// Увеличиваем лимиты памяти и времени
ini_set('memory_limit', '512M');
set_time_limit(0);

// Проверяем права доступа к файлам
$upload_dir = wp_upload_dir();
$vk_upload_dir = $upload_dir['basedir'] . '/import-products-to-vk/';

if (!is_dir($vk_upload_dir)) {
    wp_mkdir_p($vk_upload_dir);
    echo "✅ Создана директория для логов ВКонтакте\n";
}

if (is_writable($vk_upload_dir)) {
    echo "✅ Директория для логов доступна для записи\n";
} else {
    echo "⚠️ Директория для логов недоступна для записи\n";
}

// 7. Мониторинг процесса
echo "\n7. РЕКОМЕНДАЦИИ ПО МОНИТОРИНГУ:\n";
echo "==================================\n";
echo "1. Проверьте логи в: {$vk_upload_dir}\n";
echo "2. Мониторьте процесс в админ-панели WordPress\n";
echo "3. Проверьте группу ВКонтакте через 15-30 минут\n";
echo "4. При необходимости запустите скрипт повторно\n\n";

// 8. Создаем файл мониторинга
$monitor_file = $vk_upload_dir . 'monitor_' . date('Ymd_His') . '.log';
$monitor_content = "=== МОНИТОРИНГ ВЫГРУЗКИ В ВКОНТАКТЕ ===\n";
$monitor_content .= "Дата запуска: " . date('Y-m-d H:i:s') . "\n";
$monitor_content .= "Всего товаров в БД: {$total_products}\n";
$monitor_content .= "Выгружено ранее: {$exported_products}\n";
$monitor_content .= "Требуется выгрузить: {$not_exported}\n";
$monitor_content .= "Статус: Инициирована полная выгрузка\n";
$monitor_content .= "=====================================\n\n";

file_put_contents($monitor_file, $monitor_content);
echo "✅ Создан файл мониторинга: " . basename($monitor_file) . "\n";

echo "\n=== СКРИПТ ЗАВЕРШЕН ===\n";
echo "Проверьте результат через 15-30 минут\n";
?>
