<?php
/**
 * Debug page.
 * 
 * @version    5.0.0 (25-03-2025)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/debug_page/
 * 
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit; ?>
<div id="y4ym_wrap" class="wrap">

	<div id="icon-options-general" class="icon32"></div>
	<h1><?php printf( '%s - %s (v. %s)',
		'YML for Yandex Market',
		esc_html__( 'Debug page', 'yml-for-yandex-market' ),
		esc_html( univ_option_get( 'y4ym_version' ) )
	); ?></h1>

	<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

			<!-- main content -->
			<div id="post-body-content">
				<?php include_once __DIR__ . '/html-admin-debug-page-tabs.php'; ?>

				<div class="meta-box-sortables ui-sortable">
					<?php
					switch ( $view_arr['tab_name'] ) {
						case 'debug_options': ?>
							<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post"
								enctype="multipart/form-data">
								<?php
								include_once __DIR__ . '/html-admin-debug-page-tab-settings.php';
								include_once __DIR__ . '/html-admin-debug-page-btns.php';
								?>
							</form><?php
							break;
						case 'simulation': ?>
							<form action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" method="post"
								enctype="multipart/form-data">
								<?php
								include_once __DIR__ . '/html-admin-debug-page-tab-simulation.php';
								include_once __DIR__ . '/html-admin-debug-page-btns.php';
								?>
							</form><?php
							break;
						case 'sandbox':
							include_once __DIR__ . '/html-admin-debug-page-tab-sandbox.php';
							break;
						case 'premium':
							include_once __DIR__ . '/html-admin-debug-page-tab-premium.php';
							break;
						case 'status':
							include_once __DIR__ . '/html-admin-debug-page-tab-status.php';
							break;
					}
					?>
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