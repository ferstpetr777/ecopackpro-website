<?php
/**
 * Исправление проблем с загрузкой изображений в ВКонтакте
 */

// Подключение к WordPress
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-config.php');
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-load.php');

echo "=== ИСПРАВЛЕНИЕ ПРОБЛЕМ С ИЗОБРАЖЕНИЯМИ ВКОНТАКТЕ ===\n\n";

global $wpdb;

echo "1. АНАЛИЗ ПРОБЛЕМЫ С ИЗОБРАЖЕНИЯМИ:\n";
echo "=====================================\n";

// Анализируем товары с проблемами изображений
$products_with_images = $wpdb->get_results("
    SELECT p.ID, p.post_title, pm.meta_value as image_id
    FROM {$wpdb->posts} p 
    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
    LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_ip2vk_prod_id_on_vk'
    WHERE p.post_type = 'product' 
    AND p.post_status = 'publish'
    AND pm2.meta_value IS NULL
    AND pm.meta_value IS NOT NULL
    LIMIT 10
");

echo "Товары с изображениями, но не выгруженные:\n";
foreach ($products_with_images as $product) {
    echo "- ID: {$product->ID}, Название: {$product->post_title}, Image ID: {$product->image_id}\n";
}

// Проверяем товары без изображений
$products_without_images = $wpdb->get_results("
    SELECT p.ID, p.post_title
    FROM {$wpdb->posts} p 
    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
    LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_ip2vk_prod_id_on_vk'
    WHERE p.post_type = 'product' 
    AND p.post_status = 'publish'
    AND pm2.meta_value IS NULL
    AND pm.meta_value IS NULL
    LIMIT 10
");

echo "\nТовары БЕЗ изображений:\n";
foreach ($products_without_images as $product) {
    echo "- ID: {$product->ID}, Название: {$product->post_title}\n";
}

echo "\n2. НАСТРОЙКА ПАРАМЕТРОВ ЗАГРУЗКИ ИЗОБРАЖЕНИЙ:\n";
echo "================================================\n";

// Обновляем настройки плагина для оптимизации загрузки изображений
$settings = get_option('ip2vk_settings_arr');
if ($settings && isset($settings[1])) {
    // Настройки для изображений
    $settings[1]['image_upload_method'] = 'path'; // Используем путь к файлу
    $settings[1]['picture_size'] = 'medium'; // Уменьшаем размер изображений
    $settings[1]['re_import_img'] = 'enabled'; // Разрешаем перезагрузку изображений
    $settings[1]['step_export'] = '10'; // Уменьшаем шаг для стабильности
    
    // Сбрасываем статус для новой попытки
    $settings[1]['status_sborki'] = '1';
    $settings[1]['date_sborki'] = time();
    
    update_option('ip2vk_settings_arr', $settings);
    
    echo "✅ Метод загрузки изображений: path\n";
    echo "✅ Размер изображений: medium\n";
    echo "✅ Перезагрузка изображений: enabled\n";
    echo "✅ Шаг экспорта: 10\n";
    echo "✅ Статус сброшен\n";
}

echo "\n3. ПРОВЕРКА И ОПТИМИЗАЦИЯ ИЗОБРАЖЕНИЙ:\n";
echo "=========================================\n";

// Функция для проверки и оптимизации изображения
function optimize_image_for_vk($image_id) {
    $upload_dir = wp_upload_dir();
    $image_path = get_attached_file($image_id);
    
    if (!$image_path || !file_exists($image_path)) {
        return false;
    }
    
    // Проверяем размер файла
    $file_size = filesize($image_path);
    echo "   Размер файла: " . round($file_size / 1024, 2) . " KB\n";
    
    // Если файл слишком большой, создаем копию меньшего размера
    if ($file_size > 1024 * 1024) { // Больше 1MB
        echo "   ⚠️ Файл слишком большой, создаем копию...\n";
        
        $image_info = wp_get_image_editor($image_path);
        if (!is_wp_error($image_info)) {
            $image_info->resize(800, 800, true); // Уменьшаем до 800x800
            $optimized_path = str_replace(basename($image_path), 'vk_' . basename($image_path), $image_path);
            $image_info->save($optimized_path);
            
            if (file_exists($optimized_path)) {
                echo "   ✅ Оптимизированное изображение создано\n";
                return $optimized_path;
            }
        }
    }
    
    return $image_path;
}

// Проверяем несколько изображений
echo "Проверка изображений товаров:\n";
$test_products = $wpdb->get_results("
    SELECT p.ID, pm.meta_value as image_id
    FROM {$wpdb->posts} p 
    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
    WHERE p.post_type = 'product' 
    AND p.post_status = 'publish'
    AND pm.meta_value IS NOT NULL
    LIMIT 5
");

foreach ($test_products as $product) {
    echo "Товар ID {$product->ID}:\n";
    optimize_image_for_vk($product->image_id);
}

echo "\n4. ЗАПУСК ОПТИМИЗИРОВАННОЙ ВЫГРУЗКИ:\n";
echo "=======================================\n";

// Подключаем плагин
require_once('/var/www/fastuser/data/www/ecopackpro.ru/wp-content/plugins/import-products-to-vk/import-products-to-vk.php');

if (class_exists('IP2VK')) {
    $ip2vk = new IP2VK();
    echo "✅ Плагин инициализирован\n";
    
    // Запускаем процесс с оптимизированными настройками
    if (method_exists($ip2vk, 'do_this_seventy_sec')) {
        echo "🔄 Запуск оптимизированной выгрузки...\n";
        
        // Запускаем несколько итераций для постепенной выгрузки
        for ($i = 1; $i <= 3; $i++) {
            echo "   Итерация {$i}...\n";
            $ip2vk->do_this_seventy_sec(1);
            
            // Проверяем прогресс
            $current_exported = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");
            echo "   Выгружено товаров: {$current_exported}\n";
            
            sleep(3); // Пауза между итерациями
        }
        
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
}

echo "\n5. МОНИТОРИНГ ПРОЦЕССА:\n";
echo "=========================\n";

// Создаем скрипт для мониторинга
$monitor_script = '/var/www/fastuser/data/www/ecopackpro.ru/vk_monitor.php';
$monitor_content = '<?php
// Скрипт мониторинга выгрузки ВКонтакте
require_once("/var/www/fastuser/data/www/ecopackpro.ru/wp-config.php");
require_once("/var/www/fastuser/data/www/ecopackpro.ru/wp-load.php");

global $wpdb;
$total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = \'product\' AND post_status = \'publish\'");
$exported = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = \'_ip2vk_prod_id_on_vk\'");

echo "=== МОНИТОРИНГ ВКОНТАКТЕ ===\n";
echo "Дата: " . date("Y-m-d H:i:s") . "\n";
echo "Всего товаров: {$total}\n";
echo "Выгружено: {$exported}\n";
echo "Процент: " . round(($exported / $total) * 100, 2) . "%\n";
echo "Осталось: " . ($total - $exported) . "\n";
echo "=============================\n";
?>';

file_put_contents($monitor_script, $monitor_content);
echo "✅ Скрипт мониторинга создан: vk_monitor.php\n";

echo "\n6. СОЗДАНИЕ ОТЧЕТА:\n";
echo "=====================\n";

$log_dir = wp_upload_dir()['basedir'] . '/import-products-to-vk/';
$report_file = $log_dir . 'image_fix_' . date('Ymd_His') . '.log';

$report_content = "=== ОТЧЕТ ИСПРАВЛЕНИЯ ИЗОБРАЖЕНИЙ ===\n";
$report_content .= "Дата: " . date('Y-m-d H:i:s') . "\n";
$report_content .= "Проблема: Ошибки загрузки изображений в ВК\n";
$report_content .= "Решение: Оптимизация настроек и изображений\n";
$report_content .= "Статус: Процесс запущен с оптимизированными настройками\n";
$report_content .= "=============================================\n";

file_put_contents($report_file, $report_content);
echo "✅ Отчет создан: " . basename($report_file) . "\n";

echo "\n7. ИНСТРУКЦИИ ПО МОНИТОРИНГУ:\n";
echo "===============================\n";
echo "1. 🔍 Проверяйте прогресс каждые 10 минут:\n";
echo "   php vk_monitor.php\n\n";
echo "2. 📊 Проверяйте логи в:\n";
echo "   {$log_dir}\n\n";
echo "3. 👥 Проверьте группу ВКонтакте через 30 минут\n\n";
echo "4. 🔄 Если прогресс остановился, запустите:\n";
echo "   php vk_image_fix.php\n\n";

echo "=== ИСПРАВЛЕНИЕ ЗАВЕРШЕНО ===\n";
echo "Процесс выгрузки запущен с оптимизированными настройками\n";
?>
