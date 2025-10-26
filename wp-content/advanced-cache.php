<?php
/**
 * Advanced Cache Drop-in
 * Простой и эффективный кэш для WordPress
 */

defined('ABSPATH') || exit;

// Настройки кэша
define('CACHE_DIR', WP_CONTENT_DIR . '/cache/');
define('CACHE_TIME', 3600); // 1 час

// Не кэшировать для авторизованных пользователей
if (isset($_COOKIE['wordpress_logged_in'])) {
    return false;
}

// Не кэшировать POST запросы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    return false;
}

// Не кэшировать страницы с параметрами (кроме utm)
$query_string = $_SERVER['QUERY_STRING'] ?? '';
if ($query_string && !preg_match('/^utm_/', $query_string)) {
    return false;
}

// Создаем директорию кэша если её нет
if (!is_dir(CACHE_DIR)) {
    @mkdir(CACHE_DIR, 0755, true);
}

// Генерируем ключ кэша
$cache_key = md5($_SERVER['REQUEST_URI'] . $_SERVER['HTTP_HOST']);
$cache_file = CACHE_DIR . $cache_key . '.html';

// Проверяем существование и актуальность кэша
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < CACHE_TIME) {
    // Отдаем закэшированную страницу
    header('X-Cache-Status: HIT');
    header('Cache-Control: public, max-age=' . CACHE_TIME);
    readfile($cache_file);
    exit;
}

// Начинаем буферизацию вывода
ob_start(function($buffer) use ($cache_file) {
    // Сохраняем страницу в кэш
    if (strlen($buffer) > 0 && strpos($buffer, '<html') !== false) {
        @file_put_contents($cache_file, $buffer);
    }
    return $buffer;
});

// Устанавливаем заголовок MISS
header('X-Cache-Status: MISS');




