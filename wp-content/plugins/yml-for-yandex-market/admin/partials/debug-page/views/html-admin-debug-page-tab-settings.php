<?php
/**
 * Display the Settings tab.
 * 
 * @version    5.0.0 (25-03-2025)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/debug_page/
 * 
 * @param $view_arr['keeplogs']
 * @param $view_arr['plugin_notifications']
 */
defined( 'ABSPATH' ) || exit; ?>
<div class="y4ym-postbox postbox">

	<table class="form-table">
		<tbody>
			<tr>
				<th class="y4ym_th" scope="row"><?php esc_html_e( 'Keep logs', 'yml-for-yandex-market' ); ?></th>
				<td class="y4ym_td overalldesc">
					<select id="y4ym_keeplogs" class="y4ym_select" name="y4ym_keeplogs">
						<option value="disabled" <?php selected( $view_arr['keeplogs'], 'disabled' ); ?>>
							<?php esc_html_e( 'Disabled', 'yml-for-yandex-market' ); ?>
						</option>
						<option value="enabled" <?php selected( $view_arr['keeplogs'], 'enabled' ); ?>>
							<?php esc_html_e( 'Enabled', 'yml-for-yandex-market' ); ?>
						</option>
					</select>
				</td>
			</tr>
			<?php if ( $view_arr['keeplogs'] === 'enabled' ) : ?>
				<tr>
					<th class="y4ym_th" scope="row"><?php esc_html_e( 'Link to the log file', 'yml-for-yandex-market' ); ?>
					</th>
					<td class="y4ym_td overalldesc">
						<?php
						printf( '<p><a href="%1$s%2$s">%1$s%2$s</a><br/><strong>%4$s:</strong> %3$s%2$s (<a href="%1$s%2$s" download>%5$s</a>).</p>',
							esc_attr( Y4YM_PLUGIN_UPLOADS_DIR_URL ),
							'/yml-for-yandex-market.log',
							esc_html( Y4YM_PLUGIN_UPLOADS_DIR_PATH ),
							esc_html__( 'Location on your server', 'yml-for-yandex-market' ),
							esc_html__( 'Download', 'yml-for-yandex-market' )
						);
						?>
					</td>
				</tr>
			<?php endif; ?>

			<tr>
				<th class="y4ym_th" scope="row"><?php esc_html_e( 'Plugin notifications', 'yml-for-yandex-market' ); ?>
				</th>
				<td class="y4ym_td overalldesc">
					<select id="y4ym_plugin_notifications" class="y4ym_select" name="y4ym_plugin_notifications">
						<option value="enabled" <?php selected( $view_arr['plugin_notifications'], 'enabled' ); ?>>
							<?php esc_html_e( 'Enabled', 'yml-for-yandex-market' ); ?>
						</option>
						<option value="disabled" <?php selected( $view_arr['plugin_notifications'], 'disabled' ); ?>>
							<?php esc_html_e( 'Disabled', 'yml-for-yandex-market' ); ?>
						</option>
					</select>
					<p><?php esc_html_e( 'Disables most of the plugin notifications', 'yml-for-yandex-market' ); ?>.</p>
				</td>
			</tr>
		</tbody>
	</table>

</div>