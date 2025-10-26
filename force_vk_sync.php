<?php
/**
 * Принудительный запуск синхронизации с ВКонтакте
 */

// Подключение к WordPress
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-config.php');
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-load.php');

echo "=== ПРИНУДИТЕЛЬНАЯ СИНХРОНИЗАЦИЯ С ВКОНТАКТЕ ===\n\n";

// 1. Обновляем настройки для полной выгрузки
$settings = get_option('ip2vk_settings_arr');

if ($settings && isset($settings[1])) {
    echo "1. ОБНОВЛЕНИЕ НАСТРОЕК ПЛАГИНА:\n";
    echo "================================\n";
    
    // Сбрасываем все настройки для полной перевыгрузки
    $settings[1]['status_sborki'] = '1'; // Начать сборку
    $settings[1]['date_sborki'] = time();
    $settings[1]['date_sborki_end'] = '0000000001';
    $settings[1]['count_products_in_feed'] = '-1'; // Все товары
    $settings[1]['status_cron'] = 'enabled'; // Включить CRON
    $settings[1]['step_export'] = '50'; // Увеличиваем шаг экспорта
    
    // Сохраняем настройки
    update_option('ip2vk_settings_arr', $settings);
    
    echo "✅ Статус сборки: 1 (начать)\n";
    echo "✅ Дата сборки: " . date('Y-m-d H:i:s', time()) . "\n";
    echo "✅ Количество товаров: -1 (все)\n";
    echo "✅ CRON: enabled\n";
    echo "✅ Шаг экспорта: 50\n\n";
}

// 2. Очищаем мета-данные предыдущих выгрузок
echo "2. ОЧИСТКА ПРЕДЫДУЩИХ ВЫГРУЗОК:\n";
echo "=================================\n";

global $wpdb;

// Подсчитываем, сколько мета-данных будет очищено
$old_meta_count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE '_ip2vk_%'");
echo "Найдено мета-данных ВКонтакте: {$old_meta_count}\n";

// Очищаем мета-данные (опционально - можно закомментировать)
// $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '_ip2vk_%'");
// echo "✅ Мета-данные очищены\n";

echo "⚠️ Мета-данные НЕ очищены (для сохранения связи с существующими товарами)\n\n";

// 3. Проверяем количество товаров
echo "3. ПРОВЕРКА ТОВАРОВ:\n";
echo "====================\n";

$total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'");
$exported_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");

echo "Всего товаров: {$total_products}\n";
echo "Выгружено: {$exported_products}\n";
echo "Осталось: " . ($total_products - $exported_products) . "\n\n";

// 4. Запускаем WordPress CRON
echo "4. ЗАПУСК CRON ЗАДАЧ:\n";
echo "======================\n";

// Проверяем, есть ли запланированные задачи
$scheduled_hooks = _get_cron_array();
$ip2vk_hooks = array();

if ($scheduled_hooks) {
    foreach ($scheduled_hooks as $timestamp => $hooks) {
        foreach ($hooks as $hook => $events) {
            if (strpos($hook, 'ip2vk') !== false) {
                $ip2vk_hooks[] = $hook;
            }
        }
    }
}

if (!empty($ip2vk_hooks)) {
    echo "Найдены CRON задачи ВКонтакте:\n";
    foreach ($ip2vk_hooks as $hook) {
        echo "- {$hook}\n";
    }
} else {
    echo "⚠️ CRON задачи ВКонтакте не найдены\n";
}

// Принудительно запускаем CRON
if (function_exists('spawn_cron')) {
    spawn_cron();
    echo "✅ WordPress CRON запущен\n";
}

// 5. Создаем событие для немедленного запуска
echo "\n5. СОЗДАНИЕ СОБЫТИЯ ВЫГРУЗКИ:\n";
echo "===============================\n";

// Планируем событие на текущее время
wp_schedule_single_event(time(), 'ip2vk_do_this_event');
echo "✅ Событие выгрузки запланировано на " . date('Y-m-d H:i:s') . "\n";

// 6. Проверяем права доступа
echo "\n6. ПРОВЕРКА ПРАВ ДОСТУПА:\n";
echo "==========================\n";

$upload_dir = wp_upload_dir();
$vk_dir = $upload_dir['basedir'] . '/import-products-to-vk/';

if (is_dir($vk_dir)) {
    echo "✅ Директория ВКонтакте существует\n";
    if (is_writable($vk_dir)) {
        echo "✅ Директория доступна для записи\n";
    } else {
        echo "❌ Директория НЕ доступна для записи\n";
    }
} else {
    echo "⚠️ Директория ВКонтакте не существует, создаем...\n";
    wp_mkdir_p($vk_dir);
    if (is_dir($vk_dir)) {
        echo "✅ Директория создана\n";
    } else {
        echo "❌ Не удалось создать директорию\n";
    }
}

// 7. Создаем файл для мониторинга
echo "\n7. СОЗДАНИЕ ФАЙЛА МОНИТОРИНГА:\n";
echo "================================\n";

$monitor_file = $vk_dir . 'force_sync_' . date('Ymd_His') . '.log';
$log_content = "=== ПРИНУДИТЕЛЬНАЯ СИНХРОНИЗАЦИЯ ВКОНТАКТЕ ===\n";
$log_content .= "Дата: " . date('Y-m-d H:i:s') . "\n";
$log_content .= "Всего товаров: {$total_products}\n";
$log_content .= "Выгружено ранее: {$exported_products}\n";
$log_content .= "Статус: Инициирована полная выгрузка\n";
$log_content .= "=============================================\n";

if (file_put_contents($monitor_file, $log_content)) {
    echo "✅ Файл мониторинга создан: " . basename($monitor_file) . "\n";
} else {
    echo "❌ Не удалось создать файл мониторинга\n";
}

// 8. Дополнительные действия
echo "\n8. ДОПОЛНИТЕЛЬНЫЕ ДЕЙСТВИЯ:\n";
echo "=============================\n";

// Проверяем токен доступа
if (isset($settings[1]['access_token']) && !empty($settings[1]['access_token'])) {
    echo "✅ Токен доступа настроен\n";
} else {
    echo "❌ Токен доступа НЕ настроен\n";
}

// Проверяем ID группы
if (isset($settings[1]['group_id']) && !empty($settings[1]['group_id'])) {
    echo "✅ ID группы настроен: " . $settings[1]['group_id'] . "\n";
} else {
    echo "❌ ID группы НЕ настроен\n";
}

echo "\n=== РЕКОМЕНДАЦИИ ===\n";
echo "1. Проверьте админ-панель WordPress через 5-10 минут\n";
echo "2. Посмотрите логи в: {$vk_dir}\n";
echo "3. Проверьте группу ВКонтакте через 15-30 минут\n";
echo "4. При необходимости запустите скрипт повторно\n";
echo "5. Если проблема сохраняется, проверьте токен доступа\n\n";

echo "=== СИНХРОНИЗАЦИЯ ИНИЦИИРОВАНА ===\n";
?>
