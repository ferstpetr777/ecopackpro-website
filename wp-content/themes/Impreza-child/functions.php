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
 * Подключение Mobile WebView Cart Bridge
 * Для поддержки мобильных приложений с WebView
 */
add_action( 'wp_enqueue_scripts', 'ecopackpro_enqueue_mobile_cart_bridge', 999 );
function ecopackpro_enqueue_mobile_cart_bridge() {
    // Подключаем только если WooCommerce активен
    if ( class_exists( 'WooCommerce' ) ) {
        wp_enqueue_script(
            'ecopackpro-mobile-cart-bridge',
            get_stylesheet_directory_uri() . '/mobile-webview-cart-bridge.js',
            array( 'jquery' ),
            '1.0.0',
            true
        );
    }
}

/**
 * Добавление data-атрибута cart-count в body
 * Для чтения мобильным приложением
 */
add_filter( 'body_class', 'ecopackpro_add_cart_count_to_body' );
function ecopackpro_add_cart_count_to_body( $classes ) {
    if ( class_exists( 'WooCommerce' ) ) {
        // Получаем количество товаров
        $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
        
        // Добавляем как атрибут (будет обработано JavaScript)
        add_filter( 'body_class', function( $classes ) use ( $cart_count ) {
            // Сохраняем в глобальной переменной для JavaScript
            global $ecopackpro_cart_count;
            $ecopackpro_cart_count = $cart_count;
            return $classes;
        });
    }
    
    return $classes;
}

/**
 * Вывод data-атрибута в body tag
 */
add_action( 'wp_body_open', 'ecopackpro_output_cart_count_data' );
function ecopackpro_output_cart_count_data() {
    if ( class_exists( 'WooCommerce' ) ) {
        $cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
        ?>
        <script>
        // Инициализация данных корзины для мобильного приложения
        window.initialCartCount = <?php echo intval( $cart_count ); ?>;
        document.body.setAttribute('data-cart-count', <?php echo intval( $cart_count ); ?>);
        document.documentElement.setAttribute('data-cart-count', <?php echo intval( $cart_count ); ?>);
        
        // Для localStorage
        try {
            localStorage.setItem('ecopackpro_cart_count', '<?php echo intval( $cart_count ); ?>');
        } catch(e) {}
        
        console.log('Initial cart count set:', <?php echo intval( $cart_count ); ?>);
        </script>
        <?php
    }
}

/**
 * REST API Endpoint для получения количества товаров в корзине
 * Используется мобильным приложением
 */
add_action( 'rest_api_init', 'ecopackpro_register_cart_api' );
function ecopackpro_register_cart_api() {
    register_rest_route( 'ecopackpro/v1', '/cart/count', array(
        'methods'  => 'GET',
        'callback' => 'ecopackpro_get_cart_count',
        'permission_callback' => '__return_true', // Публичный доступ
    ));
    
    register_rest_route( 'ecopackpro/v1', '/cart/details', array(
        'methods'  => 'GET',
        'callback' => 'ecopackpro_get_cart_details',
        'permission_callback' => '__return_true',
    ));
}

/**
 * Получить количество товаров в корзине через API
 */
function ecopackpro_get_cart_count( $request ) {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return new WP_REST_Response( array(
            'count' => 0,
            'error' => 'WooCommerce not active'
        ), 200 );
    }
    
    // Инициализируем сессию если нужно
    if ( ! WC()->session ) {
        WC()->session = new WC_Session_Handler();
        WC()->session->init();
    }
    
    // Инициализируем корзину если нужно
    if ( ! WC()->cart ) {
        WC()->cart = new WC_Cart();
    }
    
    $count = WC()->cart->get_cart_contents_count();
    
    return new WP_REST_Response( array(
        'count' => $count,
        'timestamp' => current_time( 'timestamp' ),
        'session_id' => WC()->session->get_customer_id()
    ), 200 );
}

/**
 * Получить детальную информацию о корзине через API
 */
function ecopackpro_get_cart_details( $request ) {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return new WP_REST_Response( array(
            'error' => 'WooCommerce not active'
        ), 200 );
    }
    
    // Инициализируем сессию и корзину
    if ( ! WC()->session ) {
        WC()->session = new WC_Session_Handler();
        WC()->session->init();
    }
    
    if ( ! WC()->cart ) {
        WC()->cart = new WC_Cart();
    }
    
    $items = array();
    foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
        $product = $cart_item['data'];
        $items[] = array(
            'product_id' => $cart_item['product_id'],
            'name' => $product->get_name(),
            'quantity' => $cart_item['quantity'],
            'price' => $product->get_price(),
            'total' => $cart_item['line_total']
        );
    }
    
    return new WP_REST_Response( array(
        'count' => WC()->cart->get_cart_contents_count(),
        'items' => $items,
        'subtotal' => WC()->cart->get_cart_subtotal(),
        'total' => WC()->cart->get_cart_total(),
        'timestamp' => current_time( 'timestamp' )
    ), 200 );
}

/**
 * Добавление CORS headers для API запросов из мобильного приложения
 */
add_action( 'rest_api_init', function() {
    remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );
    add_filter( 'rest_pre_serve_request', function( $value ) {
        header( 'Access-Control-Allow-Origin: *' );
        header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS' );
        header( 'Access-Control-Allow-Credentials: true' );
        header( 'Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With' );
        return $value;
    });
}, 15 );

