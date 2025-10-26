<?php
/**
 * Plugin Name: Remove Manager Copy (plomba-nn.ru)
 * Description: Удаляет любые Bcc/Cc на zakaz@plomba-nn.ru и защищает «Новый заказ» от добавления этого адреса.
 * Author: ops
 */

// убираем Bcc/Cc на plomba-nn.ru из всех писем WooCommerce
add_filter('woocommerce_email_headers', function($headers, $email_id, $email_obj){
    return preg_replace('/^(Bcc|Cc):.*\bzakaz@plomba-nn\.ru\b.*\r?\n/im', '', (string)$headers);
}, 999, 3);

// на всякий случай выносим адрес из получателей письма «Новый заказ»
add_filter('woocommerce_email_recipient_new_order', function($recipient, $order, $email){
    $parts = array_map('trim', explode(',', (string)$recipient));
    $parts = array_filter($parts, function($r){ return strcasecmp($r, 'zakaz@plomba-nn.ru') !== 0; });
    return implode(', ', $parts);
}, 999, 3);
