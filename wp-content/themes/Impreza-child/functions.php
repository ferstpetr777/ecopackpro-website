<?php
/**
 * Impreza Child Theme Functions
 * 
 * @package Impreza Child
 */

// Подключение скриптов и стилей родительской темы
add_action( 'wp_enqueue_scripts', 'impreza_child_enqueue_styles' );
function impreza_child_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}

/**
 * ИСПРАВЛЕНИЕ: Добавляем .w-cart-quantity в WooCommerce cart fragments
 * БЕЗ ЭТОГО badge НЕ ОБНОВЛЯЕТСЯ!
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'impreza_child_add_cart_quantity_fragment' );
function impreza_child_add_cart_quantity_fragment( $fragments ) {
    if ( class_exists( 'WooCommerce' ) && WC()->cart ) {
        $cart_count = WC()->cart->get_cart_contents_count();
        $fragments['.w-cart-quantity'] = '<span class="w-cart-quantity">' . esc_html( $cart_count ) . '</span>';
    }
    return $fragments;
}

/**
 * ОТКЛЮЧЕНО: Подключение Mobile WebView Cart Bridge
 * Используется только стандартный WooCommerce механизм!
 */
// add_action( 'wp_enqueue_scripts', 'ecopackpro_enqueue_mobile_cart_bridge', 999 );
function ecopackpro_enqueue_mobile_cart_bridge() {
    // ОТКЛЮЧЕНО - возвращаемся к стандартному WooCommerce
    /*
    if ( class_exists( 'WooCommerce' ) ) {
        wp_enqueue_script(
            'ecopackpro-mobile-cart-bridge',
            get_stylesheet_directory_uri() . '/mobile-webview-cart-bridge.js',
            array( 'jquery' ),
            '1.0.0',
            true
        );
    }
    */
}

// УДАЛЕНО: Весь мой код для WebView - используем ТОЛЬКО стандартный WooCommerce!

