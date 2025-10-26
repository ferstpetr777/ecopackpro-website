<?php
/**
 * Settings page.
 * 
 * @version    5.0.0 (25-03-2025)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/settings_page/
 * 
 * @param $view_arr['feed_id']
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit; ?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div id="y4ym_wrap" class="wrap">

	<div id="icon-options-general" class="icon32"></div>
	<h1><?php printf( '%s - %s: %s',
		'YML for Yandex Market',
		esc_html__( 'Feed', 'yml-for-yandex-market' ),
		esc_html( $view_arr['feed_id'] )
	);
	$feed_assignment = common_option_get(
		'y4ym_feed_assignment',
		false,
		$view_arr['feed_id'],
		'y4ym'
	);
	if ( ! empty( $feed_assignment ) ) {
		printf( ' (%s)',
			'YML for Yandex Market',
			esc_html__( $feed_assignment )
		);
	} ?></h1>

	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">
				<?php include_once __DIR__ . '/html-admin-settings-feed-tabs.php'; ?>

				<div class="meta-box-sortables ui-sortable">

					<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post"
						enctype="multipart/form-data">
						<input type="hidden" name="y4ym_feed_id_for_save"
							value="<?php echo esc_attr( $view_arr['feed_id'] ); ?>">
						<?php
						switch ( $view_arr['tab_name'] ) {
							case 'shop_data_tab':
							case 'offer_data_tab':
								include_once __DIR__ . '/html-admin-settings-feed-tab-tags.php';
								break;
							default:
								$html_template = __DIR__ . '/html-admin-settings-feed-tab-standart.php';
								$html_template = apply_filters( 'y4ym_f_html_template_tab',
									$html_template,
									[ 
										'tab_name' => $view_arr['tab_name'],
										'view_arr' => $view_arr
									]
								);
								include_once $html_template;
						}

						include_once __DIR__ . '/html-admin-settings-feed-save-btn.php';
						?>
					</form>

				</div>
				<!-- .meta-box-sortables .ui-sortable -->

			</div>
			<!-- post-body-content -->

			<!-- sidebar -->
			<div id="postbox-container-1" class="postbox-container">

				<div class="meta-box-sortables">

					<?php do_action( 'y4ym_feedback_block' ); ?>

					<!-- .postbox -->

				</div>
				<!-- .meta-box-sortables -->

			</div>
			<!-- #postbox-container-1 .postbox-container -->

		</div>
		<!-- #post-body .metabox-holder .columns-2 -->

		<br class="clear" />
	</div>
	<!-- #poststuff -->

</div> <!-- .wrap -->