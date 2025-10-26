<?php
/**
 * Финальный скрипт для запуска полной выгрузки товаров в ВКонтакте
 */

// Подключение к WordPress
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-config.php');
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-load.php');

echo "=== ФИНАЛЬНЫЙ ЗАПУСК ВЫГРУЗКИ ВКОНТАКТЕ ===\n\n";

// Подключаем плагин
$plugin_file = '/var/www/fastuser/data/www/ecopackpro.ru/wp-content/plugins/import-products-to-vk/import-products-to-vk.php';
if (file_exists($plugin_file)) {
    require_once($plugin_file);
    echo "✅ Плагин подключен\n";
} else {
    echo "❌ Плагин не найден\n";
    exit;
}

// Инициализируем плагин
if (class_exists('IP2VK')) {
    $ip2vk = new IP2VK();
    echo "✅ Экземпляр плагина создан\n";
} else {
    echo "❌ Класс IP2VK не найден\n";
    exit;
}

echo "\n1. ПРОВЕРКА ТЕКУЩЕГО СОСТОЯНИЯ:\n";
echo "==================================\n";

global $wpdb;
$total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'");
$exported_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");

echo "Всего товаров в базе: {$total_products}\n";
echo "Выгружено в ВКонтакте: {$exported_products}\n";
echo "Осталось выгрузить: " . ($total_products - $exported_products) . "\n";

echo "\n2. ОБНОВЛЕНИЕ НАСТРОЕК ДЛЯ ПОЛНОЙ ВЫГРУЗКИ:\n";
echo "=============================================\n";

$settings = get_option('ip2vk_settings_arr');
if ($settings && isset($settings[1])) {
    // Сбрасываем все настройки
    $settings[1]['status_sborki'] = '1'; // Начать сборку
    $settings[1]['date_sborki'] = time();
    $settings[1]['date_sborki_end'] = '0000000001';
    $settings[1]['count_products_in_feed'] = '-1'; // Все товары
    $settings[1]['status_cron'] = 'enabled';
    $settings[1]['step_export'] = '100'; // Увеличиваем шаг
    
    update_option('ip2vk_settings_arr', $settings);
    
    echo "✅ Статус сборки: 1 (начать)\n";
    echo "✅ Дата сборки: " . date('Y-m-d H:i:s') . "\n";
    echo "✅ Количество товаров: -1 (все)\n";
    echo "✅ CRON: enabled\n";
    echo "✅ Шаг экспорта: 100\n";
}

echo "\n3. ЗАПУСК ВЫГРУЗКИ ЧЕРЕЗ МЕТОДЫ ПЛАГИНА:\n";
echo "==========================================\n";

// Вызываем метод do_this_event напрямую
if (method_exists($ip2vk, 'do_this_event')) {
    echo "🔄 Запуск do_this_event(1)...\n";
    $ip2vk->do_this_event(1);
    echo "✅ do_this_event выполнен\n";
} else {
    echo "❌ Метод do_this_event не найден\n";
}

// Также вызываем do_this_seventy_sec
if (method_exists($ip2vk, 'do_this_seventy_sec')) {
    echo "🔄 Запуск do_this_seventy_sec(1)...\n";
    $ip2vk->do_this_seventy_sec(1);
    echo "✅ do_this_seventy_sec выполнен\n";
} else {
    echo "❌ Метод do_this_seventy_sec не найден\n";
}

echo "\n4. ПРИНУДИТЕЛЬНЫЙ ЗАПУСК CRON:\n";
echo "================================\n";

// Планируем событие
wp_schedule_single_event(time(), 'ip2vk_do_this_event');
echo "✅ Событие запланировано\n";

// Запускаем CRON
if (function_exists('spawn_cron')) {
    spawn_cron();
    echo "✅ WordPress CRON запущен\n";
}

echo "\n5. ПРОВЕРКА РЕЗУЛЬТАТА ЧЕРЕЗ 10 СЕКУНД:\n";
echo "=========================================\n";

echo "Ожидание 10 секунд...\n";
sleep(10);

$new_exported = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");
echo "Выгружено товаров после запуска: {$new_exported}\n";

if ($new_exported > $exported_products) {
    echo "✅ УСПЕХ! Добавлено: " . ($new_exported - $exported_products) . " товаров\n";
} else {
    echo "⚠️ Количество не изменилось. Возможно, процесс еще идет...\n";
}

echo "\n6. СОЗДАНИЕ ФАЙЛА МОНИТОРИНГА:\n";
echo "================================\n";

$log_dir = wp_upload_dir()['basedir'] . '/import-products-to-vk/';
$monitor_file = $log_dir . 'final_sync_' . date('Ymd_His') . '.log';

$log_content = "=== ФИНАЛЬНЫЙ ЗАПУСК ВЫГРУЗКИ ВКОНТАКТЕ ===\n";
$log_content .= "Дата: " . date('Y-m-d H:i:s') . "\n";
$log_content .= "Всего товаров: {$total_products}\n";
$log_content .= "Выгружено до запуска: {$exported_products}\n";
$log_content .= "Выгружено после запуска: {$new_exported}\n";
$log_content .= "Добавлено: " . ($new_exported - $exported_products) . "\n";
$log_content .= "Статус: Процесс запущен\n";
$log_content .= "===========================================\n";

file_put_contents($monitor_file, $log_content);
echo "✅ Файл мониторинга создан: " . basename($monitor_file) . "\n";

echo "\n7. ИНСТРУКЦИИ ПО МОНИТОРИНГУ:\n";
echo "===============================\n";
echo "1. Проверьте админ-панель WordPress через 5-10 минут\n";
echo "2. Посмотрите логи в: {$log_dir}\n";
echo "3. Проверьте группу ВКонтакте (ID: 185841914) через 15-30 минут\n";
echo "4. Если нужно, запустите скрипт повторно\n";
echo "5. Для проверки прогресса выполните:\n";
echo "   mysql -u m1shqamai2_worp6 -p'9nUQkM*Q2cnvy379' m1shqamai2_worp6 -e \"SELECT COUNT(*) FROM wp_postmeta WHERE meta_key = '_ip2vk_prod_id_on_vk';\"\n\n";

echo "=== ВЫГРУЗКА ИНИЦИИРОВАНА ===\n";
echo "Ожидайте завершения процесса...\n";
?>
