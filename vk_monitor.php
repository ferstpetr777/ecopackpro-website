<?php
// Скрипт мониторинга выгрузки ВКонтакте
require_once("/var/www/fastuser/data/www/ecopackpro.ru/wp-config.php");
require_once("/var/www/fastuser/data/www/ecopackpro.ru/wp-load.php");

global $wpdb;
$total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'");
$exported = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_ip2vk_prod_id_on_vk'");

echo "=== МОНИТОРИНГ ВКОНТАКТЕ ===\n";
echo "Дата: " . date("Y-m-d H:i:s") . "\n";
echo "Всего товаров: {$total}\n";
echo "Выгружено: {$exported}\n";
echo "Процент: " . round(($exported / $total) * 100, 2) . "%\n";
echo "Осталось: " . ($total - $exported) . "\n";
echo "=============================\n";
?>