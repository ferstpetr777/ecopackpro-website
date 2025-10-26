<?php
// Логируем успех/ошибки wp_mail:
add_action('wp_mail_failed', function($wp_error){
  $line = '[wp_mail_failed] '. $wp_error->get_error_message();
  if ($data = $wp_error->get_error_data()) $line .= ' | data='.print_r($data,true);
  error_log($line);
});
add_action('wp_mail_succeeded', function($result){
  error_log('[wp_mail_succeeded] to='.implode(',', (array)$result['to']).' subject='.$result['subject']);
});
// Лог PHPMailer (SMTP протокол) в debug-mail.log
add_action('phpmailer_init', function($phpmailer){
  $phpmailer->SMTPDebug = 2;
  $phpmailer->Debugoutput = function($str, $level){
    file_put_contents(WP_CONTENT_DIR.'/debug-mail.log',
      '['.date('c')."] [$level] $str\n", FILE_APPEND);
  };
});
