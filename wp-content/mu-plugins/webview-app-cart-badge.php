<?php
/**
 * Plugin Name: WebView App Cart Badge
 * Description: Badge корзины ТОЛЬКО для мобильного ПРИЛОЖЕНИЯ (WebView), НЕ трогает веб-версию
 * Version: 1.0.0
 * Author: EcopackPro Dev Team
 */

defined('ABSPATH') || exit;

/**
 * Подключаем скрипты ТОЛЬКО для WebView приложения
 */
add_action('wp_enqueue_scripts', 'webview_app_enqueue_badge_scripts', 999);
function webview_app_enqueue_badge_scripts() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Подключаем WebView bridge (API для приложения)
    wp_enqueue_script(
        'webview-app-cart-bridge',
        content_url('mu-plugins/mobile-webview-cart-bridge.js'),
        array('jquery', 'wc-cart-fragments'),
        '1.0.2',
        true
    );
    
    // Подключаем badge для приложения
    wp_enqueue_script(
        'webview-app-footer-badge',
        content_url('mu-plugins/webview-app-footer-badge.js'),
        array('jquery', 'wc-cart-fragments'),
        '1.0.0',
        true
    );
    
    // CSS для badge (зелёный, как у избранного)
    wp_enqueue_style(
        'webview-app-badge-css',
        content_url('mu-plugins/webview-app-badge.css'),
        array(),
        '1.0.0'
    );
}

/**
 * Вывод данных корзины для приложения
 */
add_action('wp_body_open', 'webview_app_output_cart_data');
function webview_app_output_cart_data() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    $cart_count = WC()->cart->get_cart_contents_count();
    
    // Выводим в data-атрибут на body
    echo '<script>document.body.setAttribute("data-cart-count", "' . esc_attr($cart_count) . '");</script>';
}

/**
 * REST API endpoints для приложения
 */
add_action('rest_api_init', 'webview_app_register_cart_api');
function webview_app_register_cart_api() {
    // Количество товаров
    register_rest_route('ecopackpro/v1', '/cart/count', array(
        'methods' => 'GET',
        'callback' => 'webview_app_get_cart_count',
        'permission_callback' => '__return_true'
    ));
    
    // Детали корзины
    register_rest_route('ecopackpro/v1', '/cart/details', array(
        'methods' => 'GET',
        'callback' => 'webview_app_get_cart_details',
        'permission_callback' => '__return_true'
    ));
}

function webview_app_get_cart_count() {
    if (!class_exists('WooCommerce')) {
        return new WP_Error('woocommerce_not_active', 'WooCommerce не активен', array('status' => 503));
    }
    
    return array(
        'count' => WC()->cart->get_cart_contents_count(),
        'total' => WC()->cart->get_cart_total()
    );
}

function webview_app_get_cart_details() {
    if (!class_exists('WooCommerce')) {
        return new WP_Error('woocommerce_not_active', 'WooCommerce не активен', array('status' => 503));
    }
    
    $cart_items = array();
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $cart_items[] = array(
            'product_id' => $cart_item['product_id'],
            'quantity' => $cart_item['quantity'],
            'name' => $product->get_name(),
            'price' => $product->get_price()
        );
    }
    
    return array(
        'count' => WC()->cart->get_cart_contents_count(),
        'total' => WC()->cart->get_cart_total(),
        'items' => $cart_items
    );
}

