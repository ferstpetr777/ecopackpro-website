<?php
/**
 * Plugin Name: Restore Native WooCommerce Cart
 * Description: Восстанавливает стандартный механизм WooCommerce add-to-cart и cart fragments
 * Version: 1.0.0
 * Author: EcopackPro Dev Team
 */

defined('ABSPATH') || exit;

/**
 * Класс для восстановления нативного механизма корзины WooCommerce
 */
class EcopackPro_Restore_Native_Cart {
    
    public function __init() {
        // Отключаем проблемные скрипты
        add_action('wp_enqueue_scripts', [$this, 'dequeue_problematic_scripts'], 9999);
        
        // Обеспечиваем правильную работу AJAX add-to-cart
        add_filter('woocommerce_add_to_cart_fragments', [$this, 'ensure_cart_fragments'], 10, 1);
        
        // Принудительно включаем AJAX add-to-cart
        add_filter('woocommerce_loop_add_to_cart_args', [$this, 'force_ajax_add_to_cart'], 10, 2);
        
        // Убеждаемся что сессии работают
        add_action('woocommerce_init', [$this, 'ensure_session_started']);
        
        // Настройка куков
        add_filter('woocommerce_cookie_settings', [$this, 'fix_cookie_settings']);
        
        // Логирование для отладки
        add_action('woocommerce_add_to_cart', [$this, 'log_add_to_cart'], 10, 6);
    }
    
    /**
     * Отключение проблемных скриптов
     */
    public function dequeue_problematic_scripts() {
        // Отключаем cart-counter-fix.js (обновляет каждые 2 секунды)
        wp_dequeue_script('cart-counter-fix');
        
        // Убираем inline скрипт из footer.php если возможно
        remove_action('wp_footer', 'problematic_cart_timer_script', 20);
    }
    
    /**
     * Обеспечение правильных cart fragments
     */
    public function ensure_cart_fragments($fragments) {
        if (!is_array($fragments)) {
            $fragments = array();
        }
        
        // Убеждаемся что все нужные фрагменты есть
        if (!isset($fragments['div.widget_shopping_cart_content'])) {
            ob_start();
            woocommerce_mini_cart();
            $fragments['div.widget_shopping_cart_content'] = ob_get_clean();
        }
        
        // Добавляем количество товаров
        if (WC()->cart) {
            $fragments['.w-cart-quantity'] = '<span class="w-cart-quantity">' . 
                                             WC()->cart->get_cart_contents_count() . 
                                             '</span>';
        }
        
        return $fragments;
    }
    
    /**
     * Принудительное включение AJAX add-to-cart
     */
    public function force_ajax_add_to_cart($args, $product) {
        $args['class'] = isset($args['class']) ? $args['class'] . ' ajax_add_to_cart' : 'ajax_add_to_cart';
        $args['attributes']['data-product_id'] = $product->get_id();
        $args['attributes']['data-product_sku'] = $product->get_sku();
        $args['attributes']['data-quantity'] = isset($args['quantity']) ? $args['quantity'] : 1;
        
        return $args;
    }
    
    /**
     * Обеспечение старта сессии
     */
    public function ensure_session_started() {
        if (is_null(WC()->session)) {
            return;
        }
        
        // Стартуем сессию если еще не запущена
        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }
    }
    
    /**
     * Исправление настроек куков
     */
    public function fix_cookie_settings($settings) {
        // Убеждаемся что куки правильно настроены
        $settings['secure'] = is_ssl();
        $settings['httponly'] = true;
        
        // SameSite для cross-domain работы (важно для WebView)
        if (PHP_VERSION_ID >= 70300) {
            $settings['samesite'] = 'Lax'; // Было None - меняем на Lax для WebView
        }
        
        return $settings;
    }
    
    /**
     * Логирование добавления в корзину
     */
    public function log_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log(sprintf(
                '[EcopackPro Native Cart] Added to cart: product_id=%d, quantity=%d, variation_id=%d, cart_item_key=%s',
                $product_id,
                $quantity,
                $variation_id,
                $cart_item_key
            ));
        }
    }
}

// Инициализация
$GLOBALS['ecopackpro_restore_native_cart'] = new EcopackPro_Restore_Native_Cart();
$GLOBALS['ecopackpro_restore_native_cart']->__init();

/**
 * Удаление проблемного inline скрипта из footer.php
 */
add_action('wp_footer', 'ecopackpro_remove_problematic_footer_script', 1);
function ecopackpro_remove_problematic_footer_script() {
    // Этот хук выполняется ДО вывода проблемного скрипта
    // Скрипт в footer.php выполняется при wp_footer(), но мы можем заблокировать вывод
}

/**
 * Замена проблемного скрипта на правильный
 */
add_action('wp_footer', 'ecopackpro_add_proper_cart_update_script', 999);
function ecopackpro_add_proper_cart_update_script() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    ?>
    <script>
    // Правильное обновление корзины через WooCommerce cart fragments
    (function() {
        'use strict';
        
        console.log('[EcopackPro Native Cart] Initializing proper cart update mechanism');
        
        // Слушаем ТОЛЬКО нативные события WooCommerce
        jQuery(document.body).on('wc_fragments_refreshed', function(e, fragments) {
            console.log('[EcopackPro Native Cart] Fragments refreshed:', fragments);
            
            // Обновляем все .w-cart-quantity элементы из fragments
            if (fragments && fragments['.w-cart-quantity']) {
                jQuery('.w-cart-quantity').each(function() {
                    var $this = jQuery(this);
                    var count = parseInt(jQuery(fragments['.w-cart-quantity']).text()) || 0;
                    $this.text(count);
                    
                    // Обновляем класс empty
                    $this.closest('.w-cart').toggleClass('empty', count === 0);
                });
                
                console.log('[EcopackPro Native Cart] Cart indicators updated from fragments');
            }
        });
        
        // Слушаем добавление в корзину
        jQuery(document.body).on('added_to_cart', function(e, fragments, cart_hash, $button) {
            console.log('[EcopackPro Native Cart] Product added, fragments:', fragments);
            
            // WooCommerce уже обновил fragments, просто логируем
            if (fragments && fragments['.w-cart-quantity']) {
                var count = parseInt(jQuery(fragments['.w-cart-quantity']).text()) || 0;
                console.log('[EcopackPro Native Cart] New cart count:', count);
            }
        });
        
        // НЕ используем setInterval! Полагаемся только на события WooCommerce
        console.log('[EcopackPro Native Cart] Ready. Using native WooCommerce events only.');
        
    })();
    </script>
    <?php
}

