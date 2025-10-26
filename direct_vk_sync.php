<?php
/**
 * Прямой запуск синхронизации с ВКонтакте
 */

// Подключение к WordPress
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-config.php');
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-load.php');

echo "=== ПРЯМОЙ ЗАПУСК СИНХРОНИЗАЦИИ ВКОНТАКТЕ ===\n\n";

// Подключаем файлы плагина
$plugin_path = '/var/www/fastuser/data/www/ecopackpro.ru/wp-content/plugins/import-products-to-vk/';
require_once($plugin_path . 'classes/system/class-ip2vk.php');
require_once($plugin_path . 'classes/generation/class-ip2vk-generation-xml.php');

echo "1. ИНИЦИАЛИЗАЦИЯ ПЛАГИНА:\n";
echo "==========================\n";

// Инициализируем плагин
if (class_exists('IP2VK')) {
    echo "✅ Класс IP2VK найден\n";
    
    // Создаем экземпляр
    $ip2vk = new IP2VK();
    echo "✅ Экземпляр плагина создан\n";
} else {
    echo "❌ Класс IP2VK не найден\n";
    exit;
}

// Проверяем настройки
$settings = get_option('ip2vk_settings_arr');
if ($settings && isset($settings[1])) {
    echo "✅ Настройки плагина загружены\n";
    
    $group_settings = $settings[1];
    echo "ID группы: " . $group_settings['group_id'] . "\n";
    echo "Статус сборки: " . $group_settings['status_sborki'] . "\n";
    echo "Синхронизация: " . $group_settings['syncing_with_vk'] . "\n";
} else {
    echo "❌ Настройки плагина не найдены\n";
    exit;
}

echo "\n2. ЗАПУСК ПРОЦЕССА СБОРКИ:\n";
echo "============================\n";

// Обновляем статус сборки
$settings[1]['status_sborki'] = '1';
$settings[1]['date_sborki'] = time();
$settings[1]['count_products_in_feed'] = '-1';
update_option('ip2vk_settings_arr', $settings);

echo "✅ Статус сборки обновлен\n";

// Проверяем количество товаров
global $wpdb;
$total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'");
echo "Всего товаров для выгрузки: {$total_products}\n";

// Запускаем CRON задачу вручную
if (function_exists('ip2vk_do_this_seventy_sec')) {
    echo "🔄 Запуск функции ip2vk_do_this_seventy_sec...\n";
    
    // Вызываем функцию напрямую
    ip2vk_do_this_seventy_sec();
    
    echo "✅ Функция выполнена\n";
} else {
    echo "⚠️ Функция ip2vk_do_this_seventy_sec не найдена\n";
    
    // Пробуем альтернативные способы
    if (function_exists('ip2vk_do_this_event')) {
        echo "🔄 Запуск функции ip2vk_do_this_event...\n";
        ip2vk_do_this_event();
        echo "✅ Функция ip2vk_do_this_event выполнена\n";
    } else {
        echo "❌ Функции плагина недоступны\n";
    }
}

echo "\n3. ПРОВЕРКА РЕЗУЛЬТАТА:\n";
echo "=========================\n";

// Ждем немного и проверяем результат
sleep(5);

$exported_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");
echo "Выгружено товаров: {$exported_products}\n";

if ($exported_products > 76) {
    echo "✅ Выгрузка работает! Добавлено: " . ($exported_products - 76) . " товаров\n";
} else {
    echo "⚠️ Количество выгруженных товаров не изменилось\n";
}

echo "\n4. МОНИТОРИНГ ЛОГОВ:\n";
echo "=====================\n";

$log_dir = wp_upload_dir()['basedir'] . '/import-products-to-vk/';
$log_files = glob($log_dir . '*.log');

echo "Файлы логов:\n";
foreach ($log_files as $log_file) {
    echo "- " . basename($log_file) . " (" . date('Y-m-d H:i:s', filemtime($log_file)) . ")\n";
}

echo "\n=== РЕКОМЕНДАЦИИ ===\n";
echo "1. Проверьте логи плагина для детальной информации\n";
echo "2. Мониторьте процесс в админ-панели WordPress\n";
echo "3. Проверьте группу ВКонтакте через 10-15 минут\n";
echo "4. При необходимости повторите запуск\n\n";

echo "=== ПРЯМОЙ ЗАПУСК ЗАВЕРШЕН ===\n";
?>
