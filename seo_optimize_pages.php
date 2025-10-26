<?php
/**
 * SEO оптимизация страниц и статей - добавление ключевых слов и мета-описаний
 * Дата создания: 13 октября 2025
 */

// Подключаем WordPress
require_once('wp-config.php');
require_once('wp-load.php');

echo "=== SEO ОПТИМИЗАЦИЯ СТРАНИЦ И СТАТЕЙ ===\n";
echo "Дата: " . date('Y-m-d H:i:s') . "\n\n";

// Получаем все опубликованные посты, страницы и товары
$posts = $wpdb->get_results("
    SELECT ID, post_title, post_type, post_content, post_excerpt
    FROM {$wpdb->posts} 
    WHERE post_status = 'publish' 
    AND post_type IN ('post', 'page', 'product')
    ORDER BY post_type, ID ASC
");

$total_posts = count($posts);
echo "Найдено контента для оптимизации: {$total_posts}\n\n";

$processed = 0;
$errors = 0;
$meta_descriptions_added = 0;
$focus_keywords_added = 0;

foreach ($posts as $post) {
    $processed++;
    
    // Определяем фокусное ключевое слово
    $focus_keyword = '';
    $meta_description = '';
    
    // Для главной страницы
    if ($post->post_type == 'page' && $post->ID == get_option('page_on_front')) {
        $focus_keyword = 'упаковка для интернет магазинов';
        $meta_description = 'Упаковка для интернет магазинов - профессиональные упаковочные материалы и решения. Курьерские пакеты, почтовые коробки, конверты с воздушной подушкой.';
    }
    // Для обычных страниц
    elseif ($post->post_type == 'page') {
        $focus_keyword = strtolower($post->post_title);
        $meta_description = $post->post_title . ' - качественные упаковочные материалы от EcopackPro. Профессиональные решения для бизнеса.';
    }
    // Для статей блога
    elseif ($post->post_type == 'post') {
        $focus_keyword = strtolower($post->post_title);
        $meta_description = $post->post_title . ' - подробная информация об упаковочных материалах. Советы по выбору и применению от экспертов.';
    }
    // Для товаров
    elseif ($post->post_type == 'product') {
        $focus_keyword = strtolower($post->post_title);
        $meta_description = $post->post_title . ' - купить упаковочные материалы оптом. Высокое качество, быстрая доставка, выгодные цены.';
    }
    
    // Ограничиваем длину мета-описания до 160 символов
    if (mb_strlen($meta_description) > 160) {
        $meta_description = mb_substr($meta_description, 0, 157) . '...';
    }
    
    // Проверяем, есть ли уже мета-описание
    $existing_meta = $wpdb->get_var($wpdb->prepare("
        SELECT meta_value FROM {$wpdb->postmeta} 
        WHERE post_id = %d AND meta_key = '_yoast_wpseo_metadesc'
    ", $post->ID));
    
    // Добавляем мета-описание, если его нет
    if (empty($existing_meta)) {
        $result1 = $wpdb->insert(
            $wpdb->postmeta,
            array(
                'post_id' => $post->ID,
                'meta_key' => '_yoast_wpseo_metadesc',
                'meta_value' => $meta_description
            ),
            array('%d', '%s', '%s')
        );
        
        if ($result1 !== false) {
            $meta_descriptions_added++;
        }
    }
    
    // Проверяем, есть ли уже фокусное ключевое слово
    $existing_focus = $wpdb->get_var($wpdb->prepare("
        SELECT meta_value FROM {$wpdb->postmeta} 
        WHERE post_id = %d AND meta_key = '_yoast_wpseo_focuskw'
    ", $post->ID));
    
    // Добавляем фокусное ключевое слово, если его нет
    if (empty($existing_focus)) {
        $result2 = $wpdb->insert(
            $wpdb->postmeta,
            array(
                'post_id' => $post->ID,
                'meta_key' => '_yoast_wpseo_focuskw',
                'meta_value' => $focus_keyword
            ),
            array('%d', '%s', '%s')
        );
        
        if ($result2 !== false) {
            $focus_keywords_added++;
        }
    }
    
    // Добавляем SEO заголовок
    $seo_title = $post->post_title . ' | EcopackPro - Упаковочные материалы';
    if (mb_strlen($seo_title) > 60) {
        $seo_title = mb_substr($seo_title, 0, 57) . '...';
    }
    
    $existing_seo_title = $wpdb->get_var($wpdb->prepare("
        SELECT meta_value FROM {$wpdb->postmeta} 
        WHERE post_id = %d AND meta_key = '_yoast_wpseo_title'
    ", $post->ID));
    
    if (empty($existing_seo_title)) {
        $wpdb->insert(
            $wpdb->postmeta,
            array(
                'post_id' => $post->ID,
                'meta_key' => '_yoast_wpseo_title',
                'meta_value' => $seo_title
            ),
            array('%d', '%s', '%s')
        );
    }
    
    echo "[{$processed}/{$total_posts}] ✅ {$post->post_type}: {$post->post_title}\n";
    echo "    Фокусное слово: '{$focus_keyword}'\n";
    echo "    Мета-описание: '{$meta_description}'\n\n";
    
    // Показываем прогресс каждые 20 элементов
    if ($processed % 20 == 0) {
        echo "--- Прогресс: {$processed}/{$total_posts} ---\n\n";
    }
}

echo "\n=== РЕЗУЛЬТАТ ===\n";
echo "Обработано: {$processed} элементов\n";
echo "Добавлено мета-описаний: {$meta_descriptions_added}\n";
echo "Добавлено фокусных ключевых слов: {$focus_keywords_added}\n";
echo "Ошибок: {$errors}\n\n";

// Проверяем результат
$remaining_no_meta = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_yoast_wpseo_metadesc'
    WHERE p.post_status = 'publish' 
    AND p.post_type IN ('post', 'page', 'product')
    AND pm.meta_value IS NULL
");

$remaining_no_focus = $wpdb->get_var("
    SELECT COUNT(*) 
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_yoast_wpseo_focuskw'
    WHERE p.post_status = 'publish' 
    AND p.post_type IN ('post', 'page', 'product')
    AND pm.meta_value IS NULL
");

echo "Осталось без мета-описаний: {$remaining_no_meta}\n";
echo "Осталось без фокусных ключевых слов: {$remaining_no_focus}\n";
echo "SEO оптимизация страниц и статей завершена!\n";
?>





