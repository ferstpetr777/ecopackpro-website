<?php
// Логируем каждый вызов wp_mail
add_filter('wp_mail', function($a){
  $line = sprintf("[%s] TO=%s SUBJECT=%s\n", date('c'),
    is_array($a['to'])? implode(',', $a['to']) : $a['to'], $a['subject']);
  file_put_contents(WP_CONTENT_DIR.'/debug-mail.log', $line, FILE_APPEND);
  return $a;
});
// Логируем сам факт срабатывания email-классов Woo
add_action('woocommerce_email_after_order_table', function($order,$sent_to_admin,$plain,$email){
  error_log('WC email fired: '.get_class($email).' order#'.$order->get_id());
},10,4);
