<?php
/**
 * Print the Save button
 * 
 * @version    5.0.0 (25-03-2025)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/settings_page/
 * 
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;

if ( $view_arr['tab_name'] === 'no_submit_tab' ) {
	return;
}
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<?php wp_nonce_field( 'y4ym_nonce_action', 'y4ym_nonce_field' ); ?>
<input id="button-primary" class="button-primary" name="y4ym_submit_action" type="submit" value="<?php
if ( $view_arr['tab_name'] === 'main_tab' ) {
	printf( '%s & %s (ID: %s)',
		esc_html__( 'Save', 'yml-for-yandex-market' ),
		esc_html__( 'Create feed', 'yml-for-yandex-market' ),
		esc_attr( $view_arr['feed_id'] )
	);
} else {
	printf( '%s (ID: %s)',
		esc_html__( 'Save', 'yml-for-yandex-market' ),
		esc_attr( $view_arr['feed_id'] )
	);
}
?>" />