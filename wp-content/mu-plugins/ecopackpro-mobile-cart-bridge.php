<?php
/**
 * Plugin Name: EcopackPro Mobile WebView Cart Bridge
 * Description: Обеспечивает работу индикатора корзины в мобильных приложениях (WebView)
 * Version: 1.0.0
 * Author: EcopackPro Dev Team
 */

defined('ABSPATH') || exit;

/**
 * Подключение JavaScript Bridge для WebView
 */
add_action('wp_enqueue_scripts', 'ecopackpro_enqueue_mobile_cart_bridge', 999);
function ecopackpro_enqueue_mobile_cart_bridge() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    // Подключаем основной bridge скрипт
    $script_url = get_stylesheet_directory_uri() . '/mobile-webview-cart-bridge.js';
    
    // Если дочерняя тема неактивна, используем из mu-plugins
    if (!file_exists(get_stylesheet_directory() . '/mobile-webview-cart-bridge.js')) {
        $script_url = content_url('mu-plugins/mobile-webview-cart-bridge.js');
    }
    
    wp_enqueue_script(
        'ecopackpro-mobile-cart-bridge',
        $script_url,
        array('jquery'),
        '1.0.1',
        true
    );
    
    // Подключаем v3.0 - ЗЕЛЁНЫЙ badge с правильной синхронизацией
    wp_enqueue_script(
        'ecopackpro-mobile-cart-badge-v3',
        content_url('mu-plugins/mobile-cart-badge-v3-green.js'),
        array('jquery'),
        '3.0.0',
        true
    );
    
    // Подключаем группировщик навигации
    wp_enqueue_script(
        'ecopackpro-mobile-nav-grouper',
        content_url('mu-plugins/mobile-nav-grouper.js'),
        array(),
        '1.0.0',
        true
    );
    
    // Подключаем КОМПЛЕКСНЫЙ CSS для всех исправлений
    wp_enqueue_style(
        'ecopackpro-fix-all-cart-issues',
        content_url('mu-plugins/fix-all-cart-issues.css'),
        array(),
        '1.0.0'
    );
}

/**
 * Вывод начальных данных корзины в HTML
 */
add_action('wp_body_open', 'ecopackpro_output_cart_count_data');
function ecopackpro_output_cart_count_data() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    ?>
    <script>
    // Инициализация данных корзины для мобильного приложения
    window.initialCartCount = <?php echo intval($cart_count); ?>;
    document.body.setAttribute('data-cart-count', '<?php echo intval($cart_count); ?>');
    document.documentElement.setAttribute('data-cart-count', '<?php echo intval($cart_count); ?>');
    
    // Для localStorage
    try {
        localStorage.setItem('ecopackpro_cart_count', '<?php echo intval($cart_count); ?>');
    } catch(e) {}
    
    console.log('[EcopackPro] Initial cart count set:', <?php echo intval($cart_count); ?>);
    </script>
    <?php
}

/**
 * REST API Endpoints для мобильного приложения
 */
add_action('rest_api_init', 'ecopackpro_register_cart_api');
function ecopackpro_register_cart_api() {
    // Endpoint: /wp-json/ecopackpro/v1/cart/count
    register_rest_route('ecopackpro/v1', '/cart/count', array(
        'methods'  => 'GET',
        'callback' => 'ecopackpro_api_get_cart_count',
        'permission_callback' => '__return_true',
    ));
    
    // Endpoint: /wp-json/ecopackpro/v1/cart/details
    register_rest_route('ecopackpro/v1', '/cart/details', array(
        'methods'  => 'GET',
        'callback' => 'ecopackpro_api_get_cart_details',
        'permission_callback' => '__return_true',
    ));
}

/**
 * API: Получить количество товаров в корзине
 */
function ecopackpro_api_get_cart_count($request) {
    if (!class_exists('WooCommerce')) {
        return new WP_REST_Response(array(
            'count' => 0,
            'error' => 'WooCommerce not active'
        ), 200);
    }
    
    // Инициализируем сессию если нужно
    if (!WC()->session) {
        WC()->session = new WC_Session_Handler();
        WC()->session->init();
    }
    
    // Инициализируем корзину
    if (!WC()->cart) {
        wc_load_cart();
    }
    
    $count = WC()->cart->get_cart_contents_count();
    
    return new WP_REST_Response(array(
        'count' => $count,
        'timestamp' => current_time('timestamp'),
        'session_id' => WC()->session->get_customer_id()
    ), 200);
}

/**
 * API: Получить детальную информацию о корзине
 */
function ecopackpro_api_get_cart_details($request) {
    if (!class_exists('WooCommerce')) {
        return new WP_REST_Response(array(
            'error' => 'WooCommerce not active'
        ), 200);
    }
    
    // Инициализируем сессию и корзину
    if (!WC()->session) {
        WC()->session = new WC_Session_Handler();
        WC()->session->init();
    }
    
    if (!WC()->cart) {
        wc_load_cart();
    }
    
    $items = array();
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $items[] = array(
            'product_id' => $cart_item['product_id'],
            'name' => $product->get_name(),
            'quantity' => $cart_item['quantity'],
            'price' => $product->get_price(),
            'total' => $cart_item['line_total']
        );
    }
    
    return new WP_REST_Response(array(
        'count' => WC()->cart->get_cart_contents_count(),
        'items' => $items,
        'subtotal' => WC()->cart->get_cart_subtotal(),
        'total' => WC()->cart->get_cart_total(),
        'timestamp' => current_time('timestamp')
    ), 200);
}

/**
 * CORS Headers для API запросов
 */
add_action('rest_api_init', function() {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    add_filter('rest_pre_serve_request', function($value) {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
        return $value;
    });
}, 15);

/**
 * Копирование bridge скрипта если его нет
 */
add_action('admin_init', 'ecopackpro_ensure_bridge_script');
function ecopackpro_ensure_bridge_script() {
    $mu_script = WP_CONTENT_DIR . '/mu-plugins/mobile-webview-cart-bridge.js';
    $theme_script = get_stylesheet_directory() . '/mobile-webview-cart-bridge.js';
    
    // Если скрипт есть в теме, копируем в mu-plugins
    if (file_exists($theme_script) && !file_exists($mu_script)) {
        copy($theme_script, $mu_script);
    }
}

