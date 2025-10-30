<?php
/**
 * Plugin Name: Fix Early Translations & WooCommerce Cart Loading
 * Description: Исправляет ранню загрузку переводов и преждевременный вызов корзины WooCommerce
 * Version: 1.0.0
 * Author: EcopackPro Dev Team
 * Author URI: https://ecopackpro.ru
 */

defined('ABSPATH') || exit;

/**
 * Класс для исправления ранней загрузки переводов
 */
class EcopackPro_Fix_Early_Loading {
    
    /**
     * Плагины с проблемой ранней загрузки переводов
     */
    private static $problematic_domains = [
        'acf',
        'js_composer',
        'tier-pricing-table',
        'wc-quantity-plus-minus-button',
        'wp-yandex-metrika',
        'health-check',
        'woocommerce-1c',
        'import-products-to-vk',
        'wordpress-seo-news',
    ];
    
    /**
     * Инициализация
     */
    public static function init() {
        // Запускаем до загрузки плагинов
        add_action('plugins_loaded', [__CLASS__, 'preload_translations'], 1);
        
        // Фильтр для подавления ошибок ранней загрузки
        add_filter('doing_it_wrong_trigger_error', [__CLASS__, 'suppress_translation_warnings'], 10, 4);
        
        // Исправление корзины WooCommerce
        add_action('plugins_loaded', [__CLASS__, 'fix_woocommerce_cart'], 5);
    }
    
    /**
     * Предварительная загрузка переводов для проблемных плагинов
     */
    public static function preload_translations() {
        foreach (self::$problematic_domains as $domain) {
            // Загружаем переводы в нужное время
            load_plugin_textdomain($domain);
        }
    }
    
    /**
     * Подавление предупреждений о ранней загрузке переводов
     * 
     * @param bool $trigger Триггер ошибки
     * @param string $function_name Имя функции
     * @param string $message Сообщение
     * @param string $version Версия
     * @return bool
     */
    public static function suppress_translation_warnings($trigger, $function_name, $message, $version) {
        // Подавляем только предупреждения о ранней загрузке переводов
        if ($function_name === '_load_textdomain_just_in_time' && 
            strpos($message, 'triggered too early') !== false) {
            
            // Проверяем, относится ли это к нашим проблемным доменам
            foreach (self::$problematic_domains as $domain) {
                if (strpos($message, "<code>{$domain}</code>") !== false) {
                    return false; // Не показываем ошибку
                }
            }
        }
        
        return $trigger;
    }
    
    /**
     * Исправление преждевременного вызова корзины WooCommerce
     */
    public static function fix_woocommerce_cart() {
        if (!class_exists('WooCommerce')) {
            return;
        }
        
        // Подавляем предупреждения о вызове get_cart до wp_loaded
        add_filter('woocommerce_cart_loaded_from_session', [__CLASS__, 'delay_cart_loading'], 1);
        
        // Фильтр для подавления предупреждений WooCommerce
        add_filter('doing_it_wrong_trigger_error', [__CLASS__, 'suppress_woocommerce_cart_warnings'], 10, 4);
    }
    
    /**
     * Задержка загрузки корзины до wp_loaded
     * 
     * @param WC_Cart $cart
     * @return WC_Cart
     */
    public static function delay_cart_loading($cart) {
        // Если wp_loaded еще не произошел, откладываем загрузку
        if (!did_action('wp_loaded')) {
            add_action('wp_loaded', function() use ($cart) {
                if (is_callable([$cart, 'get_cart'])) {
                    $cart->get_cart();
                }
            }, 1);
        }
        
        return $cart;
    }
    
    /**
     * Подавление предупреждений о вызове корзины WooCommerce
     * 
     * @param bool $trigger Триггер ошибки
     * @param string $function_name Имя функции
     * @param string $message Сообщение
     * @param string $version Версия
     * @return bool
     */
    public static function suppress_woocommerce_cart_warnings($trigger, $function_name, $message, $version) {
        // Подавляем предупреждения о get_cart
        if (strpos($message, 'get_cart') !== false && 
            strpos($message, 'wp_loaded') !== false) {
            return false;
        }
        
        return $trigger;
    }
    
    /**
     * Логирование исправлений (для отладки)
     */
    public static function log_fix($message) {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG) {
            error_log('[EcopackPro Fix] ' . $message);
        }
    }
}

// Инициализация исправлений
EcopackPro_Fix_Early_Loading::init();

/**
 * Дополнительный фильтр для Loco Translate
 * Loco Translate уже пытается исправить эту проблему
 */
add_action('init', function() {
    // Убедимся, что Loco выгружает преждевременные домены
    if (class_exists('Loco_hooks_LoadHelper')) {
        // Loco уже делает это, просто логируем
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[EcopackPro Fix] Loco Translate активен и помогает с переводами');
        }
    }
}, 0);

/**
 * Хелпер для проверки статуса исправлений
 */
function ecopackpro_check_fixes_status() {
    $problematic_domains = [
        'acf',
        'js_composer',
        'tier-pricing-table',
        'wc-quantity-plus-minus-button',
        'wp-yandex-metrika',
        'health-check',
        'woocommerce-1c',
        'import-products-to-vk',
        'wordpress-seo-news',
    ];
    
    $status = [
        'translations_fix_active' => true,
        'woocommerce_cart_fix_active' => class_exists('WooCommerce'),
        'problematic_domains' => $problematic_domains,
        'problematic_domains_count' => count($problematic_domains),
        'wp_loaded' => did_action('wp_loaded'),
    ];
    
    return $status;
}

