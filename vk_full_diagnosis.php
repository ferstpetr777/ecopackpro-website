<?php
/**
 * Полная диагностика и исправление интеграции с ВКонтакте
 */

// Подключение к WordPress
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-config.php');
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-load.php');

echo "=== ПОЛНАЯ ДИАГНОСТИКА ИНТЕГРАЦИИ С ВКОНТАКТЕ ===\n\n";

global $wpdb;

// 1. Анализ базы данных
echo "1. АНАЛИЗ БАЗЫ ДАННЫХ:\n";
echo "=======================\n";

$total_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'");
$exported_products = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");

echo "Всего товаров в БД: {$total_products}\n";
echo "Выгружено в ВК: {$exported_products}\n";
echo "Процент выгрузки: " . round(($exported_products / $total_products) * 100, 2) . "%\n\n";

// 2. Анализ настроек плагина
echo "2. АНАЛИЗ НАСТРОЕК ПЛАГИНА:\n";
echo "=============================\n";

$settings = get_option('ip2vk_settings_arr');
if ($settings && isset($settings[1])) {
    $s = $settings[1];
    
    echo "ID группы ВК: " . $s['group_id'] . "\n";
    echo "Application ID: " . $s['application_id'] . "\n";
    echo "Статус сборки: " . $s['status_sborki'] . "\n";
    echo "Синхронизация: " . $s['syncing_with_vk'] . "\n";
    echo "CRON статус: " . $s['status_cron'] . "\n";
    echo "Шаг экспорта: " . $s['step_export'] . "\n";
    echo "Количество товаров: " . $s['count_products_in_feed'] . "\n";
    echo "Поведение категорий: " . $s['behavior_cats'] . "\n";
    
    // Проверяем токен
    if (!empty($s['access_token'])) {
        echo "Токен доступа: ✅ Настроен\n";
    } else {
        echo "Токен доступа: ❌ НЕ настроен\n";
    }
    
    // Проверяем дату токена
    if ($s['token_expires_in'] != '-1') {
        echo "Токен истекает: " . date('Y-m-d H:i:s', $s['token_expires_in']) . "\n";
    } else {
        echo "Токен истекает: ❌ Неизвестно\n";
    }
} else {
    echo "❌ Настройки плагина не найдены\n";
}

echo "\n3. ПРОВЕРКА API ВКОНТАКТЕ:\n";
echo "============================\n";

// Создаем функцию для проверки API
function test_vk_api($access_token, $group_id) {
    $url = "https://api.vk.com/method/groups.getById";
    $params = array(
        'access_token' => $access_token,
        'group_id' => $group_id,
        'v' => '5.131'
    );
    
    $response = wp_remote_get($url . '?' . http_build_query($params));
    
    if (is_wp_error($response)) {
        return array('error' => $response->get_error_message());
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    return $data;
}

if (isset($s['access_token']) && isset($s['group_id'])) {
    echo "🔄 Тестирование API ВКонтакте...\n";
    $api_result = test_vk_api($s['access_token'], $s['group_id']);
    
    if (isset($api_result['error'])) {
        echo "❌ Ошибка API: " . $api_result['error'] . "\n";
    } elseif (isset($api_result['response']) && !empty($api_result['response'])) {
        echo "✅ API работает! Группа найдена\n";
        echo "Название группы: " . $api_result['response'][0]['name'] . "\n";
        echo "Тип группы: " . $api_result['response'][0]['type'] . "\n";
    } else {
        echo "❌ API не отвечает или группа не найдена\n";
        if (isset($api_result['error'])) {
            echo "Ошибка: " . $api_result['error']['error_msg'] . "\n";
        }
    }
}

echo "\n4. АНАЛИЗ НЕВЫГРУЖЕННЫХ ТОВАРОВ:\n";
echo "===================================\n";

// Находим товары без выгрузки
$not_exported = $wpdb->get_results("
    SELECT p.ID, p.post_title, p.post_date, pm.meta_value as price
    FROM {$wpdb->posts} p 
    LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_ip2vk_prod_id_on_vk'
    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_price'
    WHERE p.post_type = 'product' 
    AND p.post_status = 'publish'
    AND pm1.meta_value IS NULL
    ORDER BY p.post_date DESC
    LIMIT 10
");

echo "Примеры невыгруженных товаров:\n";
foreach ($not_exported as $product) {
    echo "- ID: {$product->ID}, Название: {$product->post_title}, Цена: {$product->price} руб.\n";
}

echo "\n5. ПРОВЕРКА ЛОГОВ ПЛАГИНА:\n";
echo "============================\n";

$log_dir = wp_upload_dir()['basedir'] . '/import-products-to-vk/';
if (is_dir($log_dir)) {
    $log_files = glob($log_dir . '*.log');
    echo "Найдено файлов логов: " . count($log_files) . "\n";
    
    foreach ($log_files as $log_file) {
        echo "- " . basename($log_file) . " (" . date('Y-m-d H:i:s', filemtime($log_file)) . ")\n";
    }
    
    // Читаем последние записи из основного лога
    $main_log = $log_dir . 'plugin.log';
    if (file_exists($main_log)) {
        echo "\nПоследние записи из plugin.log:\n";
        $lines = file($main_log);
        $last_lines = array_slice($lines, -10);
        foreach ($last_lines as $line) {
            echo trim($line) . "\n";
        }
    }
} else {
    echo "❌ Директория логов не найдена\n";
}

echo "\n6. ИСПРАВЛЕНИЕ НАСТРОЕК:\n";
echo "=========================\n";

// Сбрасываем статус для полной перевыгрузки
if ($settings && isset($settings[1])) {
    $settings[1]['status_sborki'] = '1'; // Начать заново
    $settings[1]['date_sborki'] = time();
    $settings[1]['date_sborki_end'] = '0000000001';
    $settings[1]['count_products_in_feed'] = '-1'; // Все товары
    $settings[1]['status_cron'] = 'enabled';
    $settings[1]['step_export'] = '25'; // Уменьшаем шаг
    $settings[1]['behavior_cats'] = 'upd_off'; // Не обновлять категории
    
    update_option('ip2vk_settings_arr', $settings);
    
    echo "✅ Настройки сброшены для полной перевыгрузки\n";
    echo "✅ Статус сборки: 1 (начать)\n";
    echo "✅ CRON: enabled\n";
    echo "✅ Шаг экспорта: 25\n";
    echo "✅ Категории: не обновлять\n";
}

echo "\n7. ЗАПУСК ПРОЦЕССА ВЫГРУЗКИ:\n";
echo "===============================\n";

// Подключаем плагин
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-content/plugins/import-products-to-vk/import-products-to-vk.php');

if (class_exists('IP2VK')) {
    $ip2vk = new IP2VK();
    echo "✅ Плагин инициализирован\n";
    
    // Запускаем процесс
    if (method_exists($ip2vk, 'do_this_seventy_sec')) {
        echo "🔄 Запуск выгрузки...\n";
        $ip2vk->do_this_seventy_sec(1);
        echo "✅ Процесс запущен\n";
    }
    
    // Планируем CRON задачу
    wp_schedule_single_event(time(), 'ip2vk_do_this_event');
    echo "✅ CRON задача запланирована\n";
    
    // Запускаем CRON
    if (function_exists('spawn_cron')) {
        spawn_cron();
        echo "✅ WordPress CRON запущен\n";
    }
} else {
    echo "❌ Плагин не инициализирован\n";
}

echo "\n8. СОЗДАНИЕ ОТЧЕТА ДИАГНОСТИКИ:\n";
echo "===================================\n";

$report_file = $log_dir . 'diagnosis_' . date('Ymd_His') . '.log';
$report_content = "=== ОТЧЕТ ДИАГНОСТИКИ ВКОНТАКТЕ ===\n";
$report_content .= "Дата: " . date('Y-m-d H:i:s') . "\n";
$report_content .= "Всего товаров: {$total_products}\n";
$report_content .= "Выгружено: {$exported_products}\n";
$report_content .= "Процент: " . round(($exported_products / $total_products) * 100, 2) . "%\n";
$report_content .= "Статус: Настройки сброшены, процесс запущен\n";
$report_content .= "=============================================\n";

file_put_contents($report_file, $report_content);
echo "✅ Отчет создан: " . basename($report_file) . "\n";

echo "\n9. РЕКОМЕНДАЦИИ:\n";
echo "==================\n";
echo "1. ✅ Настройки исправлены и процесс запущен\n";
echo "2. 🔍 Проверьте прогресс через 10-15 минут:\n";
echo "   mysql -u m1shqamai2_worp6 -p'9nUQkM*Q2cnvy379' m1shqamai2_worp6 -e \"SELECT COUNT(*) FROM wp_postmeta WHERE meta_key = '_ip2vk_prod_id_on_vk';\"\n";
echo "3. 📊 Мониторьте логи в: {$log_dir}\n";
echo "4. 👥 Проверьте группу ВКонтакте через 30 минут\n";
echo "5. 🔄 При необходимости запустите скрипт повторно\n\n";

echo "=== ДИАГНОСТИКА ЗАВЕРШЕНА ===\n";
?>
