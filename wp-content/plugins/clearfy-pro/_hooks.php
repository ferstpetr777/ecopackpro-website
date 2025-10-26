<?php

// Файл с примерами использования хуков для нашей документации

/**
 * [en] Enable 404 error instead of redirect
 * [ru] Включаем 404 ошибку вместо перенаправления
 */
add_filter( 'clearfy/disable_feeds/is_redirect', '__return_false' );


/**
 * [en] Enable RSS feeds for any post types
 * [ru] Включаем для записей любых типов постов работу RSS-фидов
 */
add_filter( 'clearfy/disable_feeds/is_feed', function ( $is_feed ) {
    // Вы можете использовать любые проверки здесь, например, is_category(), is_tag(), is_tax() и т.д.
    if ( is_singular() ) {
        return false;
    }

    return $is_feed;
} );


/**
 * [en] Disable content protection for specific pages
 * [ru] Отключаем защиту контента для определенных страниц
 *
 * @param bool $enable
 * @param string $type
 */
add_filter( 'clearfy/content_protection/enable', function ( $enable, $type ) {

    // Пример для одной конкретной страницы c ID 437
    if ( is_page( 437 ) ) {
        return false;
    }

    // Пример для всех записей в рубрике с ID 7
    if ( in_category( 7 ) ) {
        return false;
    }

    // Дополнительно можно отключить только одну функцию защиты от копирования, например, выделение текста
    // $type может быть: context_menu, text_selection, disable_hotkeys, source_link
    if ( 'text_selection' === $type && is_single( 219 ) ) {
        return false;
    }

    return $enable;
}, 10, 2 );
