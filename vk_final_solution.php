<?php
/**
 * Финальное решение для полной выгрузки всех товаров в ВКонтакте
 */

// Подключение к WordPress
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-config.php');
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-load.php');

echo "=== ФИНАЛЬНОЕ РЕШЕНИЕ ВЫГРУЗКИ ВКОНТАКТЕ ===\n\n";

global $wpdb;

// Подключаем плагин
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-content/plugins/import-products-to-vk/import-products-to-vk.php');

echo "1. ТЕКУЩИЙ СТАТУС:\n";
echo "==================\n";

$total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'");
$exported_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");

echo "Всего товаров: {$total_products}\n";
echo "Выгружено: {$exported_products}\n";
echo "Осталось: " . ($total_products - $exported_products) . "\n";
echo "Процент: " . round(($exported_products / $total_products) * 100, 2) . "%\n\n";

echo "2. ОПТИМИЗАЦИЯ НАСТРОЕК ДЛЯ БЫСТРОЙ ВЫГРУЗКИ:\n";
echo "===============================================\n";

$settings = get_option('ip2vk_settings_arr');
if ($settings && isset($settings[1])) {
    // Максимально оптимизируем настройки
    $settings[1]['status_sborki'] = '1'; // Начать заново
    $settings[1]['date_sborki'] = time();
    $settings[1]['step_export'] = '1'; // Минимальный шаг
    $settings[1]['status_cron'] = 'enabled';
    $settings[1]['count_products_in_feed'] = '-1'; // Все товары
    $settings[1]['behavior_cats'] = 'upd_off'; // Не обновлять категории
    $settings[1]['image_upload_method'] = 'path';
    $settings[1]['picture_size'] = 'thumbnail'; // Минимальный размер изображений
    $settings[1]['re_import_img'] = 'disabled'; // Не перезагружать изображения
    
    update_option('ip2vk_settings_arr', $settings);
    
    echo "✅ Статус сборки: 1 (начать)\n";
    echo "✅ Шаг экспорта: 1 (максимальная скорость)\n";
    echo "✅ Размер изображений: thumbnail (минимальный)\n";
    echo "✅ Перезагрузка изображений: disabled\n";
    echo "✅ CRON: enabled\n\n";
}

echo "3. ЗАПУСК АГРЕССИВНОЙ ВЫГРУЗКИ:\n";
echo "=================================\n";

if (class_exists('IP2VK')) {
    $ip2vk = new IP2VK();
    echo "✅ Плагин инициализирован\n";
    
    // Запускаем максимальное количество итераций
    $max_iterations = 50;
    $success_count = 0;
    
    for ($i = 1; $i <= $max_iterations; $i++) {
        echo "\n--- Агрессивная итерация {$i} ---\n";
        
        // Проверяем текущий прогресс
        $current_exported = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");
        echo "Выгружено: {$current_exported} из {$total_products}\n";
        
        // Если все выгружено
        if ($current_exported >= $total_products) {
            echo "🎉 ВСЕ ТОВАРЫ ВЫГРУЖЕНЫ!\n";
            break;
        }
        
        // Запускаем процесс
        $ip2vk->do_this_seventy_sec(1);
        
        // Планируем CRON
        wp_schedule_single_event(time(), 'ip2vk_do_this_event');
        
        // Запускаем CRON
        if (function_exists('spawn_cron')) {
            spawn_cron();
        }
        
        // Короткая пауза
        sleep(5);
        
        // Проверяем новый прогресс
        $new_exported = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");
        
        if ($new_exported > $current_exported) {
            $added = $new_exported - $current_exported;
            echo "✅ Добавлено: {$added} товаров\n";
            $success_count++;
        } else {
            echo "⚠️ Прогресс отсутствует\n";
        }
        
        // Если прогресс остановился надолго
        if ($i > 10 && $success_count == 0) {
            echo "🔄 Перезапуск с новыми настройками...\n";
            
            // Меняем настройки для обхода проблем
            $settings = get_option('ip2vk_settings_arr');
            if ($settings && isset($settings[1])) {
                $settings[1]['status_sborki'] = '1';
                $settings[1]['date_sborki'] = time();
                $settings[1]['step_export'] = '2';
                update_option('ip2vk_settings_arr', $settings);
            }
            $success_count = 0;
        }
    }
    
    echo "\n✅ Агрессивная выгрузка завершена\n";
}

echo "\n4. ФИНАЛЬНАЯ ПРОВЕРКА:\n";
echo "=======================\n";

$final_exported = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");
$total_added = $final_exported - $exported_products;

echo "Итоговое количество: {$final_exported}\n";
echo "Добавлено в сессии: {$total_added}\n";
echo "Процент выгрузки: " . round(($final_exported / $total_products) * 100, 2) . "%\n";
echo "Осталось: " . ($total_products - $final_exported) . "\n";

if ($final_exported >= $total_products) {
    echo "🎉 УСПЕХ! Все товары выгружены в ВКонтакте!\n";
} elseif ($total_added > 0) {
    echo "✅ ПРОГРЕСС! Добавлено {$total_added} товаров\n";
} else {
    echo "⚠️ Прогресс отсутствует\n";
}

echo "\n5. СОЗДАНИЕ ИТОГОВОГО ОТЧЕТА:\n";
echo "===============================\n";

$log_dir = wp_upload_dir()['basedir'] . '/import-products-to-vk/';
$final_report = $log_dir . 'final_report_' . date('Ymd_His') . '.log';

$report_content = "=== ИТОГОВЫЙ ОТЧЕТ ВЫГРУЗКИ ВКОНТАКТЕ ===\n";
$report_content .= "Дата: " . date('Y-m-d H:i:s') . "\n";
$report_content .= "Всего товаров в БД: {$total_products}\n";
$report_content .= "Выгружено в ВКонтакте: {$final_exported}\n";
$report_content .= "Процент выгрузки: " . round(($final_exported / $total_products) * 100, 2) . "%\n";
$report_content .= "Добавлено в финальной сессии: {$total_added}\n";
$report_content .= "Осталось выгрузить: " . ($total_products - $final_exported) . "\n";
$report_content .= "Статус: " . ($final_exported >= $total_products ? "ЗАВЕРШЕНО" : "ЧАСТИЧНО") . "\n";
$report_content .= "ID группы ВКонтакте: 185841914\n";
$report_content .= "=============================================\n";

file_put_contents($final_report, $report_content);
echo "✅ Итоговый отчет создан: " . basename($final_report) . "\n";

echo "\n6. РЕКОМЕНДАЦИИ:\n";
echo "==================\n";

if ($final_exported >= $total_products) {
    echo "🎉 ОТЛИЧНО! Все товары успешно выгружены!\n";
    echo "✅ Интеграция с ВКонтакте настроена корректно\n";
    echo "✅ Группа ВКонтакте обновлена\n";
    echo "💡 Рекомендации:\n";
    echo "   - Настройте автоматическую синхронизацию\n";
    echo "   - Регулярно проверяйте обновления товаров\n";
    echo "   - Мониторьте логи плагина\n";
} elseif ($total_added > 0) {
    echo "✅ ЕСТЬ ПРОГРЕСС! Добавлено {$total_added} товаров\n";
    echo "💡 Рекомендации:\n";
    echo "   - Запустите скрипт повторно через 30 минут\n";
    echo "   - Проверьте лимиты API ВКонтакте\n";
    echo "   - Мониторьте процесс в админ-панели\n";
} else {
    echo "⚠️ ПРОГРЕСС ОТСУТСТВУЕТ\n";
    echo "💡 Рекомендации:\n";
    echo "   - Проверьте токен доступа ВКонтакте\n";
    echo "   - Убедитесь в правах группы ВКонтакте\n";
    echo "   - Проверьте лимиты API\n";
    echo "   - Обратитесь к разработчику плагина\n";
}

echo "\n=== ФИНАЛЬНОЕ РЕШЕНИЕ ЗАВЕРШЕНО ===\n";
?>
