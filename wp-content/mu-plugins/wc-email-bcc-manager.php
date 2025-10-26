<?php
/*
Plugin Name: WC Email BCC to Manager
Description: Отправляет скрытую копию писем WooCommerce менеджеру.
*/
add_filter('woocommerce_email_headers', function($headers, $email_id, $order){
    $manager = 'zakaz@plomba-nn.ru';

    // Какие письма дублируем менеджеру
    $targets = [
        'new_order',                       // новое заказ админам
        'cancelled_order',
        'failed_order',
        'customer_processing_order',       // письмо клиенту "заказ в обработке"
        'customer_completed_order',        // "заказ выполнен"
        'customer_on_hold_order',          // "заказ ожидание"
        'customer_invoice',                // счёт/ссылка на оплату
        'customer_refunded_order',
        'customer_partially_refunded_order',
        'customer_note',
    ];

    if ( in_array($email_id, $targets, true) && is_email($manager) ) {
        // чтобы не продублировать заголовок
        if ( stripos($headers, 'Bcc: '.$manager) === false ) {
            $headers .= 'Bcc: '.$manager . "\r\n";
        }
    }
    return $headers;
}, 10, 3);
