<?php
/**
 * SEO оптимизация изображений - добавление alt-тегов
 * Дата создания: 13 октября 2025
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "=== SEO ОПТИМИЗАЦИЯ ИЗОБРАЖЕНИЙ ===\n";
echo "Дата: " . date('Y-m-d H:i:s') . "\n\n";

// Получаем все изображения без alt-тегов
$images_without_alt = $wpdb->get_results("
    SELECT ID, post_title, post_excerpt, guid 
    FROM {$wpdb->posts} 
    WHERE post_type = 'attachment' 
    AND post_mime_type LIKE 'image%' 
    AND (post_excerpt = '' OR post_excerpt IS NULL)
    ORDER BY ID ASC
");

$total_images = count($images_without_alt);
echo "Найдено изображений без alt-тегов: {$total_images}\n\n";

$processed = 0;
$errors = 0;

foreach ($images_without_alt as $image) {
    $processed++;
    
    // Получаем имя файла без расширения
    $filename = basename($image->guid);
    $filename_without_ext = pathinfo($filename, PATHINFO_FILENAME);
    
    // Очищаем имя файла от цифр и спецсимволов
    $clean_filename = preg_replace('/[0-9_-]+/', ' ', $filename_without_ext);
    $clean_filename = trim($clean_filename);
    
    // Если имя файла пустое, используем заголовок
    if (empty($clean_filename)) {
        $clean_filename = !empty($image->post_title) ? $image->post_title : 'Изображение';
    }
    
    // Создаем alt-текст
    $alt_text = ucfirst(strtolower($clean_filename));
    
    // Обновляем post_excerpt (alt-текст)
    $result = $wpdb->update(
        $wpdb->posts,
        array('post_excerpt' => $alt_text),
        array('ID' => $image->ID),
        array('%s'),
        array('%d')
    );
    
    if ($result !== false) {
        echo "[{$processed}/{$total_images}] ✅ {$filename} → '{$alt_text}'\n";
    } else {
        echo "[{$processed}/{$total_images}] ❌ Ошибка для {$filename}\n";
        $errors++;
    }
    
    // Показываем прогресс каждые 50 изображений
    if ($processed % 50 == 0) {
        echo "--- Прогресс: {$processed}/{$total_images} ---\n";
    }
}

echo "\n=== РЕЗУЛЬТАТ ===\n";
echo "Обработано: {$processed} изображений\n";
echo "Ошибок: {$errors}\n";
echo "Успешно: " . ($processed - $errors) . "\n\n";

// Проверяем результат
$remaining = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$wpdb->posts} 
    WHERE post_type = 'attachment' 
    AND post_mime_type LIKE 'image%' 
    AND (post_excerpt = '' OR post_excerpt IS NULL)
");

echo "Осталось без alt-тегов: {$remaining}\n";
echo "SEO оптимизация изображений завершена!\n";
?>





