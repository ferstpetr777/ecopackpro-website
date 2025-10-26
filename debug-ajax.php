<?php
/**
 * Подробное логирование AJAX запросов WooCommerce
 * Добавить в functions.php или подключить как плагин
 */

// Логирование всех AJAX запросов
add_action('wp_ajax_nopriv_woocommerce_add_to_cart', 'log_ajax_add_to_cart', 1);
add_action('wp_ajax_woocommerce_add_to_cart', 'log_ajax_add_to_cart', 1);
add_action('wp_ajax_nopriv_woocommerce_get_refreshed_fragments', 'log_ajax_fragments', 1);
add_action('wp_ajax_woocommerce_get_refreshed_fragments', 'log_ajax_fragments', 1);

function log_ajax_add_to_cart() {
    $log_data = [
        'timestamp' => current_time('Y-m-d H:i:s'),
        'action' => 'add_to_cart',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'referer' => $_SERVER['HTTP_REFERER'] ?? 'unknown',
        'request_data' => $_POST,
        'cart_before' => WC()->cart ? WC()->cart->get_cart_contents_count() : 'no_cart',
        'session_id' => WC()->session ? WC()->session->get_customer_id() : 'no_session'
    ];
    
    error_log('=== AJAX ADD TO CART START ===');
    error_log('AJAX Data: ' . print_r($log_data, true));
    error_log('POST Data: ' . print_r($_POST, true));
    error_log('=== AJAX ADD TO CART END ===');
}

function log_ajax_fragments() {
    $log_data = [
        'timestamp' => current_time('Y-m-d H:i:s'),
        'action' => 'get_fragments',
        'cart_count' => WC()->cart ? WC()->cart->get_cart_contents_count() : 'no_cart',
        'session_id' => WC()->session ? WC()->session->get_customer_id() : 'no_session'
    ];
    
    error_log('=== AJAX FRAGMENTS ===');
    error_log('Fragments Data: ' . print_r($log_data, true));
}

// Логирование JavaScript ошибок
add_action('wp_footer', 'add_js_error_logging');
function add_js_error_logging() {
    if (is_woocommerce() || is_cart() || is_checkout()) {
        ?>
        <script>
        // Логирование всех JavaScript ошибок
        window.addEventListener('error', function(e) {
            var errorData = {
                message: e.message,
                filename: e.filename,
                lineno: e.lineno,
                colno: e.colno,
                stack: e.error ? e.error.stack : 'No stack'
            };
            
            // Отправляем ошибку на сервер для логирования
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=log_js_error&error_data=' + encodeURIComponent(JSON.stringify(errorData))
            });
        });
        
        // Логирование AJAX ошибок
        var originalFetch = window.fetch;
        window.fetch = function() {
            return originalFetch.apply(this, arguments)
                .then(function(response) {
                    if (!response.ok) {
                        console.error('AJAX Error:', response.status, response.statusText);
                        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'action=log_ajax_error&status=' + response.status + '&statusText=' + encodeURIComponent(response.statusText) + '&url=' + encodeURIComponent(arguments[0])
                        });
                    }
                    return response;
                })
                .catch(function(error) {
                    console.error('Fetch Error:', error);
                    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=log_ajax_error&error=' + encodeURIComponent(error.message) + '&url=' + encodeURIComponent(arguments[0])
                    });
                    throw error;
                });
        };
        </script>
        <?php
    }
}

// Обработчики для логирования ошибок
add_action('wp_ajax_log_js_error', 'handle_js_error_logging');
add_action('wp_ajax_nopriv_log_js_error', 'handle_js_error_logging');
add_action('wp_ajax_log_ajax_error', 'handle_ajax_error_logging');
add_action('wp_ajax_nopriv_log_ajax_error', 'handle_ajax_error_logging');

function handle_js_error_logging() {
    $error_data = json_decode(stripslashes($_POST['error_data']), true);
    error_log('=== JS ERROR ===');
    error_log('JS Error: ' . print_r($error_data, true));
    error_log('=== END JS ERROR ===');
    wp_die();
}

function handle_ajax_error_logging() {
    $log_data = $_POST;
    error_log('=== AJAX ERROR ===');
    error_log('AJAX Error: ' . print_r($log_data, true));
    error_log('=== END AJAX ERROR ===');
    wp_die();
}

// Логирование WooCommerce событий
add_action('woocommerce_add_to_cart', 'log_wc_add_to_cart', 10, 6);
function log_wc_add_to_cart($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data) {
    error_log('=== WC ADD TO CART ===');
    error_log('Product ID: ' . $product_id);
    error_log('Variation ID: ' . $variation_id);
    error_log('Quantity: ' . $quantity);
    error_log('Cart Key: ' . $cart_item_key);
    error_log('=== END WC ADD TO CART ===');
}

// Логирование ошибок WooCommerce
add_action('woocommerce_cart_item_removed', 'log_wc_cart_removed');
function log_wc_cart_removed($cart_item_key) {
    error_log('=== WC CART ITEM REMOVED ===');
    error_log('Removed Cart Key: ' . $cart_item_key);
    error_log('=== END WC CART ITEM REMOVED ===');
}



