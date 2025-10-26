<?php
/**
 * Скрипт для продолжения выгрузки товаров в ВКонтакте
 */

// Подключение к WordPress
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-config.php');
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-load.php');

echo "=== ПРОДОЛЖЕНИЕ ВЫГРУЗКИ ВКОНТАКТЕ ===\n\n";

// Подключаем плагин
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-content/plugins/import-products-to-vk/import-products-to-vk.php');

echo "1. АНАЛИЗ ТЕКУЩЕГО СОСТОЯНИЯ:\n";
echo "===============================\n";

global $wpdb;

// Проверяем количество товаров
$total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'");
$exported_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");

echo "Всего товаров: {$total_products}\n";
echo "Выгружено: {$exported_products}\n";
echo "Осталось: " . ($total_products - $exported_products) . "\n\n";

echo "2. ОБНОВЛЕНИЕ НАСТРОЕК ДЛЯ ПРОДОЛЖЕНИЯ:\n";
echo "=========================================\n";

$settings = get_option('ip2vk_settings_arr');
if ($settings && isset($settings[1])) {
    // Переводим в режим выгрузки товаров (пропускаем категории)
    $settings[1]['status_sborki'] = '2'; // Этап выгрузки товаров
    $settings[1]['date_sborki'] = time();
    $settings[1]['behavior_cats'] = 'upd_off'; // Не обновлять категории
    $settings[1]['step_export'] = '50'; // Уменьшаем шаг для стабильности
    $settings[1]['count_products_in_feed'] = '-1'; // Все товары
    
    update_option('ip2vk_settings_arr', $settings);
    
    echo "✅ Статус сборки: 2 (выгрузка товаров)\n";
    echo "✅ Категории: не обновлять\n";
    echo "✅ Шаг экспорта: 50\n";
    echo "✅ Дата обновлена: " . date('Y-m-d H:i:s') . "\n\n";
}

echo "3. ЗАПУСК ПРОЦЕССА ВЫГРУЗКИ ТОВАРОВ:\n";
echo "======================================\n";

// Создаем экземпляр плагина
if (class_exists('IP2VK')) {
    $ip2vk = new IP2VK();
    echo "✅ Экземпляр плагина создан\n";
    
    // Запускаем процесс выгрузки товаров
    if (method_exists($ip2vk, 'do_this_seventy_sec')) {
        echo "🔄 Запуск выгрузки товаров...\n";
        $ip2vk->do_this_seventy_sec(1);
        echo "✅ Процесс запущен\n";
    } else {
        echo "❌ Метод не найден\n";
    }
} else {
    echo "❌ Класс плагина не найден\n";
}

echo "\n4. МНОЖЕСТВЕННЫЙ ЗАПУСК ДЛЯ УСКОРЕНИЯ:\n";
echo "========================================\n";

// Запускаем несколько раз для ускорения процесса
for ($i = 1; $i <= 5; $i++) {
    echo "🔄 Запуск #{$i}...\n";
    
    if (method_exists($ip2vk, 'do_this_seventy_sec')) {
        $ip2vk->do_this_seventy_sec(1);
    }
    
    // Проверяем прогресс
    $current_exported = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");
    echo "   Выгружено товаров: {$current_exported}\n";
    
    if ($current_exported > $exported_products) {
        $added = $current_exported - $exported_products;
        echo "   ✅ Добавлено: {$added} товаров\n";
        $exported_products = $current_exported;
    }
    
    sleep(2); // Небольшая пауза между запусками
}

echo "\n5. ФИНАЛЬНАЯ ПРОВЕРКА:\n";
echo "========================\n";

$final_exported = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");
$total_added = $final_exported - $exported_products;

echo "Итоговое количество выгруженных товаров: {$final_exported}\n";
echo "Добавлено в этой сессии: {$total_added}\n";
echo "Осталось выгрузить: " . ($total_products - $final_exported) . "\n";

if ($total_added > 0) {
    echo "✅ ПРОГРЕСС ЕСТЬ! Процесс работает\n";
} else {
    echo "⚠️ Прогресс отсутствует. Возможные причины:\n";
    echo "   - Токен доступа истек\n";
    echo "   - Превышены лимиты API ВКонтакте\n";
    echo "   - Проблемы с подключением\n";
}

echo "\n6. СОЗДАНИЕ ОТЧЕТА:\n";
echo "=====================\n";

$log_dir = wp_upload_dir()['basedir'] . '/import-products-to-vk/';
$report_file = $log_dir . 'continue_report_' . date('Ymd_His') . '.log';

$report_content = "=== ОТЧЕТ О ПРОДОЛЖЕНИИ ВЫГРУЗКИ ===\n";
$report_content .= "Дата: " . date('Y-m-d H:i:s') . "\n";
$report_content .= "Всего товаров в БД: {$total_products}\n";
$report_content .= "Выгружено до запуска: {$exported_products}\n";
$report_content .= "Выгружено после запуска: {$final_exported}\n";
$report_content .= "Добавлено в сессии: {$total_added}\n";
$report_content .= "Осталось выгрузить: " . ($total_products - $final_exported) . "\n";
$report_content .= "Статус: " . ($total_added > 0 ? "УСПЕШНО" : "ТРЕБУЕТ ВНИМАНИЯ") . "\n";
$report_content .= "====================================\n";

file_put_contents($report_file, $report_content);
echo "✅ Отчет создан: " . basename($report_file) . "\n";

echo "\n=== РЕКОМЕНДАЦИИ ===\n";
if ($total_added > 0) {
    echo "1. ✅ Процесс работает! Повторите запуск через 10-15 минут\n";
    echo "2. Мониторьте прогресс через команду:\n";
    echo "   mysql -u m1shqamai2_worp6 -p'9nUQkM*Q2cnvy379' m1shqamai2_worp6 -e \"SELECT COUNT(*) FROM wp_postmeta WHERE meta_key = '_ip2vk_prod_id_on_vk';\"\n";
    echo "3. Проверьте группу ВКонтакте через 30 минут\n";
} else {
    echo "1. ❌ Проверьте токен доступа в настройках плагина\n";
    echo "2. ❌ Убедитесь, что группа ВКонтакте доступна\n";
    echo "3. ❌ Проверьте логи плагина на ошибки\n";
    echo "4. ❌ Возможно, превышены лимиты API ВКонтакте\n";
}

echo "\n=== СКРИПТ ЗАВЕРШЕН ===\n";
?>
