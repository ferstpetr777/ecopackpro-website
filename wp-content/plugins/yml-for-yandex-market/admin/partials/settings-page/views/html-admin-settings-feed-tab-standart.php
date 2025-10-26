<?php
/**
 * View of the standard tab.
 * 
 * @version    5.0.0 (25-03-2025)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/settings_page/
 * 
 * @param $view_arr['feed_id']
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;

$plugin_date = new Y4YM_Data();
$attr_arr = $plugin_date->get_data_for_tabs( $view_arr['tab_name'] );

$html_header = $view_arr['tab_name'];
$html_body = '';
$html_th = '';
$html_td = '';

for ( $i = 0; $i < count( $attr_arr ); $i++ ) {

	include __DIR__ . '/html-admin-settings-feed-tab-item-loop-body.php';

}

if ( ! empty( $html_body ) ) {
	printf(
		'<div class="y4ym-postbox postbox"><table class="form-table" role="presentation"><tbody>%1$s</tbody></table></div>',
		wp_kses( $html_body, Y4YM_ALLOWED_HTML_ARR )
	);
	$html_body = '';
}
