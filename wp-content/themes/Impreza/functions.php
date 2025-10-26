<?php 
 update_option( 'us_license_activated', 1 );
 update_option( 'us_license_secret', 'us_license_secret' );
 add_filter( 'us_config_addons', function( $config ) {
 	$i = 0;
 	foreach ( $config as $addon ) {
 		if ( isset( $addon['premium'] ) && $addon['premium'] ) {
 			$addon_base = isset( $addon['folder'] ) ? $addon['folder'] : $addon['slug'];
 			if ( $addon['slug'] == 'woo-bulk-editor/index' ) {
 				$addon_base = 'woocommerce-bulk-editor';
 			}
 			$config[ $i ]['package'] = get_template_directory() . "/common/plugins/{$addon_base}.zip";
 		}
 		$i++;
 	}
 	return $config;
 }, 10 );
 add_action( 'init', function() {
 	add_filter( 'pre_http_request', function( $pre, $parsed_args, $url ) {
 		if ( strpos( $url, 'https://help.us-themes.com/us.api/download_demo/' ) === 0 ) {
 			$query_args = [];
 			parse_str( parse_url( $url, PHP_URL_QUERY ), $query_args );
 			if ( isset( $query_args['demo'] ) && isset( $query_args['file'] ) ) {
 				$ext = in_array( $query_args['file'], ['theme_options', 'options'] ) ? '.json' : '.xml';
 				$ext = ( strpos( $query_args['file'], 'slider-' ) === 0 ) ? '.zip' : $ext;
 				$theme = strtolower( get_template() );
 				$response = wp_remote_get(
 					"http://wordpressnull.org/{$theme}/demos/{$query_args['demo']}/{$query_args['file']}{$ext}",
 					[ 'sslverify' => false, 'timeout' => 30 ]
 				);
 				if ( wp_remote_retrieve_response_code( $response ) == 200 ) {
 					return $response;
 				}
 				return [ 'response' => [ 'code' => 403, 'message' => 'Bad request.' ] ];
 			}
 		}
 		return $pre;
 	}, 10, 3 );
 } );
 
 defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * Theme functions and definitions
 */

// === ИСПРАВЛЕНИЕ СТАНДАРТНОГО МЕХАНИЗМА WOOCOMMERCE ===
// Проблема: WooCommerce создает новую сессию вместо использования существующей с товарами
// Решение: Принудительно восстанавливаем сессию с товарами и устанавливаем куки

// Перехватываем загрузку корзины из сессии и принудительно восстанавливаем сессию
add_action( 'woocommerce_cart_loaded_from_session', function() {
    // Проверяем, что корзина пустая, но в базе есть сессии с товарами
    if ( WC()->cart && WC()->cart->is_empty() ) {
        global $wpdb;
        
        // Ищем последнюю сессию с товарами
        $sessions = $wpdb->get_results("
            SELECT session_id, session_value 
            FROM {$wpdb->prefix}woocommerce_sessions 
            WHERE session_value LIKE '%cart%' 
            AND session_value NOT LIKE '%cart%a:0:{}%'
            ORDER BY session_id DESC 
            LIMIT 1
        ");
        
        if (!empty($sessions)) {
            $session_data = maybe_unserialize($sessions[0]->session_value);
            
            if (isset($session_data['cart']) && !empty($session_data['cart'])) {
                // Принудительно восстанавливаем корзину из найденной сессии
                WC()->cart->set_cart_contents($session_data['cart']);
                
                // Восстанавливаем другие данные сессии
                if (isset($session_data['cart_totals'])) {
                    WC()->cart->set_totals($session_data['cart_totals']);
                }
                
                // Пересчитываем корзину
                WC()->cart->calculate_totals();
                
                // Устанавливаем правильный hash корзины
                WC()->cart->set_cart_hash();
                
                // Принудительно обновляем сессию
                WC()->session->set_customer_id($sessions[0]->session_id);
                
                // КРИТИЧЕСКИ ВАЖНО: Устанавливаем куки сессии
                WC()->session->set_customer_session_cookie(true);
                
                // Отладочная информация
                error_log('WOOCOMMERCE CART RESTORED: ' . WC()->cart->get_cart_contents_count() . ' items');
            }
        }
    }
}, 20 );

// Дополнительный хук для принудительного восстановления корзины
add_action( 'wp_loaded', function() {
    if ( WC()->cart && WC()->cart->is_empty() ) {
        global $wpdb;
        
        // Ищем последнюю сессию с товарами
        $sessions = $wpdb->get_results("
            SELECT session_id, session_value 
            FROM {$wpdb->prefix}woocommerce_sessions 
            WHERE session_value LIKE '%cart%' 
            AND session_value NOT LIKE '%cart%a:0:{}%'
            ORDER BY session_id DESC 
            LIMIT 1
        ");
        
        if (!empty($sessions)) {
            $session_data = maybe_unserialize($sessions[0]->session_value);
            
            if (isset($session_data['cart']) && !empty($session_data['cart'])) {
                // Принудительно восстанавливаем корзину из найденной сессии
                WC()->cart->set_cart_contents($session_data['cart']);
                
                // Восстанавливаем другие данные сессии
                if (isset($session_data['cart_totals'])) {
                    WC()->cart->set_totals($session_data['cart_totals']);
                }
                
                // Пересчитываем корзину
                WC()->cart->calculate_totals();
                
                // Устанавливаем правильный hash корзины
                WC()->cart->set_cart_hash();
                
                // Принудительно обновляем сессию
                WC()->session->set_customer_id($sessions[0]->session_id);
                
                // КРИТИЧЕСКИ ВАЖНО: Устанавливаем куки сессии
                WC()->session->set_customer_session_cookie(true);
                
                // Отладочная информация
                error_log('WOOCOMMERCE CART RESTORED VIA WP_LOADED: ' . WC()->cart->get_cart_contents_count() . ' items');
            }
        }
    }
}, 10 );

// Еще более ранний хук для принудительного восстановления корзины
add_action( 'init', function() {
    if ( WC()->cart && WC()->cart->is_empty() ) {
        global $wpdb;
        
        // Ищем последнюю сессию с товарами
        $sessions = $wpdb->get_results("
            SELECT session_id, session_value 
            FROM {$wpdb->prefix}woocommerce_sessions 
            WHERE session_value LIKE '%cart%' 
            AND session_value NOT LIKE '%cart%a:0:{}%'
            ORDER BY session_id DESC 
            LIMIT 1
        ");
        
        if (!empty($sessions)) {
            $session_data = maybe_unserialize($sessions[0]->session_value);
            
            if (isset($session_data['cart']) && !empty($session_data['cart'])) {
                // Принудительно восстанавливаем корзину из найденной сессии
                WC()->cart->set_cart_contents($session_data['cart']);
                
                // Восстанавливаем другие данные сессии
                if (isset($session_data['cart_totals'])) {
                    WC()->cart->set_totals($session_data['cart_totals']);
                }
                
                // Пересчитываем корзину
                WC()->cart->calculate_totals();
                
                // Устанавливаем правильный hash корзины
                WC()->cart->set_cart_hash();
                
                // Принудительно обновляем сессию
                WC()->session->set_customer_id($sessions[0]->session_id);
                
                // КРИТИЧЕСКИ ВАЖНО: Устанавливаем куки сессии
                WC()->session->set_customer_session_cookie(true);
                
                // Отладочная информация
                error_log('WOOCOMMERCE CART RESTORED VIA INIT: ' . WC()->cart->get_cart_contents_count() . ' items');
            }
        }
    }
}, 5 );

// Самый ранний хук для принудительного восстановления корзины
add_action( 'wp', function() {
    if ( WC()->cart && WC()->cart->is_empty() ) {
        global $wpdb;
        
        // Ищем последнюю сессию с товарами
        $sessions = $wpdb->get_results("
            SELECT session_id, session_value 
            FROM {$wpdb->prefix}woocommerce_sessions 
            WHERE session_value LIKE '%cart%' 
            AND session_value NOT LIKE '%cart%a:0:{}%'
            ORDER BY session_id DESC 
            LIMIT 1
        ");
        
        if (!empty($sessions)) {
            $session_data = maybe_unserialize($sessions[0]->session_value);
            
            if (isset($session_data['cart']) && !empty($session_data['cart'])) {
                // Принудительно восстанавливаем корзину из найденной сессии
                WC()->cart->set_cart_contents($session_data['cart']);
                
                // Восстанавливаем другие данные сессии
                if (isset($session_data['cart_totals'])) {
                    WC()->cart->set_totals($session_data['cart_totals']);
                }
                
                // Пересчитываем корзину
                WC()->cart->calculate_totals();
                
                // Устанавливаем правильный hash корзины
                WC()->cart->set_cart_hash();
                
                // Принудительно обновляем сессию
                WC()->session->set_customer_id($sessions[0]->session_id);
                
                // КРИТИЧЕСКИ ВАЖНО: Устанавливаем куки сессии
                WC()->session->set_customer_session_cookie(true);
                
                // Отладочная информация
                error_log('WOOCOMMERCE CART RESTORED VIA WP: ' . WC()->cart->get_cart_contents_count() . ' items');
            }
        }
    }
}, 1 );

// Дополнительно: принудительно устанавливаем куки сессии на всех страницах
add_action( 'wp_loaded', function() {
    if ( WC()->session && WC()->session->has_session() ) {
        // Принудительно устанавливаем куки сессии
        WC()->session->set_customer_session_cookie(true);
        error_log('WOOCOMMERCE SESSION COOKIE SET');
    }
}, 5 );

if ( ! defined( 'US_ACTIVATION_THEMENAME' ) ) {
	define( 'US_ACTIVATION_THEMENAME', 'Impreza' );
}

global $us_theme_supports;
$us_theme_supports = array(
	'plugins' => array(
		'advanced-custom-fields' => 'plugins-support/acf.php',
		'bbpress' => 'plugins-support/bbpress.php',
		'contact-form-7' => NULL,
		'filebird' => 'plugins-support/filebird.php',
		'gravityforms' => 'plugins-support/gravityforms.php',
		'js_composer' => 'plugins-support/js_composer/js_composer.php',
		'post_views_counter' => 'plugins-support/post_views_counter.php',
		'revslider' => 'plugins-support/revslider.php',
		'tablepress' => 'plugins-support/tablepress.php',
		'the-events-calendar' => 'plugins-support/the_events_calendar.php',
		'tiny_mce' => 'plugins-support/tiny_mce.php',
		'Ultimate_VC_Addons' => 'plugins-support/Ultimate_VC_Addons.php',
		'woocommerce' => 'plugins-support/woocommerce.php',
		'woocommerce-germanized' => 'plugins-support/woocommerce-germanized.php',
		'woocommerce-multi-currency' => 'plugins-support/woocommerce-multi-currency.php',
		'wp_rocket' => 'plugins-support/wp_rocket.php',
		'yoast' => 'plugins-support/yoast.php',
		'borlabs' => 'plugins-support/borlabs.php',
	),
	// Include plugins that relate to translations and can be used in helpers.php
	'translate_plugins' => array(
		'wpml' => 'plugins-support/wpml.php',
		'polylang' => 'plugins-support/polylang.php',
	),
);

require dirname( __FILE__ ) . '/common/framework.php';