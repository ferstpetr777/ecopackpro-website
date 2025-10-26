<?php
// Принудительно задаём адрес и имя отправителя
add_filter('wp_mail_from', function(){ return 'zakaz1@ecopackpro.ru'; });
add_filter('wp_mail_from_name', function(){ return 'EcopackPro'; });

// Самое главное: конвертный отправитель (Return-Path / MAIL FROM) для SPF
add_action('phpmailer_init', function($phpmailer){
  $phpmailer->Sender = 'zakaz1@ecopackpro.ru';               // Return-Path
  $phpmailer->setFrom('zakaz1@ecopackpro.ru','EcopackPro',false);
});
