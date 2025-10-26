<?php
/**
 * Display tabs.
 * 
 * @version    5.0.0 (25-03-2025)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/debug_page/
 * 
 * @param $view_arr['tabs_arr']
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit; ?>
<div class="nav-tab-wrapper" style="border-bottom: none; margin: 0; padding: 0;">
	<?php
	foreach ( $view_arr['tabs_arr'] as $tab => $name ) {
		if ( $tab === $view_arr['tab_name'] ) {
			$class = ' nav-tab-active';
		} else {
			$class = '';
		}
		printf(
			'<a class="nav-tab%1$s" href="?page=%2$s&action=edit&current_display=debug_page&tab=%3$s">%4$s</a>',
			esc_attr( $class ),
			'yml-for-yandex-market-debug',
			esc_attr( $tab ),
			esc_html( $name )
		);
	}
	?>
</div>