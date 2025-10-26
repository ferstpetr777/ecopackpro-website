<?php
/**
 * Display a list of feeds and the "Add new feed" button.
 * 
 * @version    5.0.0 (25-03-2025)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/settings_page/
 */
defined( 'ABSPATH' ) || exit;

$feeds_list_table = new Y4YM_Feeds_List_Table();
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div id="y4ym_wrap" class="wrap">

	<div id="icon-options-general" class="icon32"></div>
	<h1>YML for Yandex Market - <?php esc_html_e( 'Feeds list', 'yml-for-yandex-market' ); ?></h1>
	<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post" enctype="multipart/form-data">
		<?php wp_nonce_field( 'y4ym_nonce_action_add_new_feed', 'y4ym_nonce_field_add_new_feed' ); ?>
		<input value="+ <?php esc_html_e( 'Add New Feed', 'yml-for-yandex-market' ); ?>" class="button-primary"
			type="submit" name="y4ym_submit_action_add_new_feed" />
	</form>

	<?php $feeds_list_table->display_html_form(); ?>

	<?php printf( '<p style="text-align: right;"><strong>%s:</strong> %s, <strong>%s:</strong> %s</p>',
		esc_html__( 'Current site time', 'yml-for-yandex-market' ),
		current_time( 'mysql', 0 ),
		esc_html__( 'Current server time', 'yml-for-yandex-market' ),
		current_time( 'mysql', 1 )
	); ?>

	<?php do_action( 'y4ym_print_view_html_icpd_my_plugins_list' ); ?>

</div> <!-- .wrap -->
<?php
