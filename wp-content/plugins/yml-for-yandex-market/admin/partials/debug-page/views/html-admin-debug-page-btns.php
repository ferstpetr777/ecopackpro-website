<?php
/**
 * Print the Save and Clear logs button for Debug page.
 * 
 * @version    5.0.0 (25-03-2025)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/debug_page/
 * 
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;

?>
<?php wp_nonce_field( 'y4ym_nonce_action', 'y4ym_nonce_field' ); ?>
<input id="button-primary" class="button-primary" type="submit"
	name="y4ym_submit_action_<?php echo esc_attr( $view_arr['tab_name'] ); ?>" value="<?php
		 if ( $view_arr['tab_name'] === 'simulation' ) {
			 printf( '%s', esc_html__( 'Simulation', 'yml-for-yandex-market' ) );
		 } else {
			 printf( '%s', esc_html__( 'Save', 'yml-for-yandex-market' ) );
		 }
		 ?>" />

<?php if ( $view_arr['tab_name'] === 'debug_options' ) : ?>
	<input class="button" type="submit" id="y4ym_submit_action_clear_logs" name="y4ym_submit_action_clear_logs"
		value="<?php esc_html_e( 'Clear logs', 'yml-for-yandex-market' ); ?>" />
<?php endif; ?>