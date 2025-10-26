<?php
/**
 * Автоматический скрипт для непрерывной выгрузки товаров в ВКонтакте
 */

// Подключение к WordPress
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-config.php');
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-load.php');

echo "=== АВТОМАТИЧЕСКАЯ ВЫГРУЗКА ВКОНТАКТЕ ===\n\n";

global $wpdb;

// Подключаем плагин
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-content/plugins/import-products-to-vk/import-products-to-vk.php');

if (!class_exists('IP2VK')) {
    echo "❌ Плагин ВКонтакте не найден\n";
    exit;
}

$ip2vk = new IP2VK();

echo "1. ИНИЦИАЛИЗАЦИЯ АВТОМАТИЧЕСКОГО ПРОЦЕССА:\n";
echo "===========================================\n";

$total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'");
echo "Всего товаров для выгрузки: {$total_products}\n";

// Настройки для автоматической выгрузки
$settings = get_option('ip2vk_settings_arr');
if ($settings && isset($settings[1])) {
    $settings[1]['status_sborki'] = '1';
    $settings[1]['date_sborki'] = time();
    $settings[1]['step_export'] = '5'; // Очень маленький шаг для стабильности
    $settings[1]['status_cron'] = 'enabled';
    $settings[1]['count_products_in_feed'] = '-1';
    $settings[1]['image_upload_method'] = 'path';
    $settings[1]['picture_size'] = 'medium';
    
    update_option('ip2vk_settings_arr', $settings);
    echo "✅ Настройки оптимизированы для автоматической выгрузки\n";
}

echo "\n2. ЗАПУСК НЕПРЕРЫВНОЙ ВЫГРУЗКИ:\n";
echo "===================================\n";

$max_iterations = 20; // Максимум 20 итераций
$iteration = 0;
$last_exported = 0;
$stagnation_count = 0;

while ($iteration < $max_iterations) {
    $iteration++;
    echo "\n--- Итерация {$iteration} ---\n";
    
    // Проверяем текущее количество выгруженных товаров
    $current_exported = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");
    echo "Выгружено товаров: {$current_exported} из {$total_products}\n";
    echo "Процент: " . round(($current_exported / $total_products) * 100, 2) . "%\n";
    
    // Если все товары выгружены
    if ($current_exported >= $total_products) {
        echo "✅ ВСЕ ТОВАРЫ ВЫГРУЖЕНЫ!\n";
        break;
    }
    
    // Проверяем прогресс
    if ($current_exported > $last_exported) {
        $added = $current_exported - $last_exported;
        echo "✅ Добавлено: {$added} товаров\n";
        $stagnation_count = 0;
        $last_exported = $current_exported;
    } else {
        $stagnation_count++;
        echo "⚠️ Прогресс отсутствует (попытка {$stagnation_count})\n";
        
        // Если прогресс остановился на 3 итерациях подряд
        if ($stagnation_count >= 3) {
            echo "🔄 Перезапуск процесса...\n";
            
            // Сбрасываем настройки
            $settings = get_option('ip2vk_settings_arr');
            if ($settings && isset($settings[1])) {
                $settings[1]['status_sborki'] = '1';
                $settings[1]['date_sborki'] = time();
                update_option('ip2vk_settings_arr', $settings);
            }
            
            $stagnation_count = 0;
        }
    }
    
    // Запускаем процесс выгрузки
    echo "🔄 Запуск процесса выгрузки...\n";
    $ip2vk->do_this_seventy_sec(1);
    
    // Планируем CRON задачу
    wp_schedule_single_event(time(), 'ip2vk_do_this_event');
    
    // Запускаем CRON
    if (function_exists('spawn_cron')) {
        spawn_cron();
    }
    
    // Пауза между итерациями
    echo "⏳ Ожидание 15 секунд...\n";
    sleep(15);
}

echo "\n3. ФИНАЛЬНЫЙ ОТЧЕТ:\n";
echo "=====================\n";

$final_exported = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");
$total_added = $final_exported - $last_exported;

echo "Итоговое количество выгруженных товаров: {$final_exported}\n";
echo "Добавлено в этой сессии: {$total_added}\n";
echo "Процент выгрузки: " . round(($final_exported / $total_products) * 100, 2) . "%\n";
echo "Осталось выгрузить: " . ($total_products - $final_exported) . "\n";

if ($final_exported >= $total_products) {
    echo "🎉 УСПЕХ! Все товары выгружены!\n";
} elseif ($total_added > 0) {
    echo "✅ ПРОГРЕСС! Добавлено {$total_added} товаров\n";
    echo "💡 Рекомендация: Запустите скрипт повторно для продолжения\n";
} else {
    echo "⚠️ Прогресс отсутствует. Проверьте:\n";
    echo "   - Токен доступа ВКонтакте\n";
    echo "   - Права группы ВКонтакте\n";
    echo "   - Лимиты API ВКонтакте\n";
}

echo "\n4. СОЗДАНИЕ ОТЧЕТА:\n";
echo "=====================\n";

$log_dir = wp_upload_dir()['basedir'] . '/import-products-to-vk/';
$report_file = $log_dir . 'auto_sync_' . date('Ymd_His') . '.log';

$report_content = "=== ОТЧЕТ АВТОМАТИЧЕСКОЙ ВЫГРУЗКИ ===\n";
$report_content .= "Дата: " . date('Y-m-d H:i:s') . "\n";
$report_content .= "Всего товаров: {$total_products}\n";
$report_content .= "Выгружено: {$final_exported}\n";
$report_content .= "Добавлено в сессии: {$total_added}\n";
$report_content .= "Процент: " . round(($final_exported / $total_products) * 100, 2) . "%\n";
$report_content .= "Итераций выполнено: {$iteration}\n";
$report_content .= "Статус: " . ($final_exported >= $total_products ? "ЗАВЕРШЕНО" : "В ПРОЦЕССЕ") . "\n";
$report_content .= "=====================================\n";

file_put_contents($report_file, $report_content);
echo "✅ Отчет создан: " . basename($report_file) . "\n";

echo "\n=== АВТОМАТИЧЕСКАЯ ВЫГРУЗКА ЗАВЕРШЕНА ===\n";
?>
