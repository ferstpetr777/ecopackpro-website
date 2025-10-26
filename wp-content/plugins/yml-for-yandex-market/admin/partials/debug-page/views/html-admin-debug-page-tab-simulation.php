<?php
/**
 * Display the Simulation tab.
 * 
 * @version    5.0.0 (25-03-2025)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/debug_page/
 * 
 * @param $view_arr['simulated_post_id']
 * @param $view_arr['feed_id']
 * @param $view_arr['simulation_result_report']
 * @param $view_arr['simulation_result']
 */
defined( 'ABSPATH' ) || exit; ?>
<div class="postbox">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="y4ym_simulated_post_id">Product ID</label></th>
				<td class="overalldesc">
					<input type="number" min="1" name="y4ym_simulated_post_id"
						value="<?php echo esc_attr( $view_arr['simulated_post_id'] ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="y4ym_feed_id">Feed ID</label></th>
				<td class="overalldesc">
					<select style="width: 100%" name="y4ym_feed_id" id="y4ym_feed_id">
						<?php
						if ( is_multisite() ) {
							$cur_blog_id = get_current_blog_id();
						} else {
							$cur_blog_id = '0';
						}
						if ( isset( $_POST['y4ym_feed_id'] ) ) {
							$cur_feed_id = sanitize_text_field( $_POST['y4ym_feed_id'] );
						} else {
							$cur_feed_id = '1';
						}
						$y4ym_settings_arr = univ_option_get( 'y4ym_settings_arr' );
						$y4ym_settings_arr_keys_arr = array_keys( $y4ym_settings_arr );
						for ( $i = 0; $i < count( $y4ym_settings_arr_keys_arr ); $i++ ) {
							$feed_id = (string) $y4ym_settings_arr_keys_arr[ $i ];
							if ( $y4ym_settings_arr[ $feed_id ]['y4ym_feed_assignment'] === '' ) {
								$feed_assignment = '';
							} else {
								$feed_assignment = sprintf( ' (%s)',
									$y4ym_settings_arr[ $feed_id ]['y4ym_feed_assignment']
								);
							}
							printf( '<option value="%s" %s>%s %s: feed-xml-%s.xml%s</option>',
								esc_attr( $feed_id ),
								selected( $cur_feed_id, $feed_id, false ),
								esc_html__( 'Feed', 'yml-for-yandex-market' ),
								esc_html( $feed_id ),
								esc_html( $cur_blog_id ),
								esc_html( $feed_assignment )
							);
						}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row" colspan="2">
					<textarea style="width: 100%;" rows="4"><?php
					echo htmlspecialchars( $view_arr['simulation_result_report'] );
					?></textarea>
				</th>
			</tr>
			<tr>
				<th scope="row" colspan="2">
					<textarea rows="16" style="width: 100%;"><?php
					echo htmlspecialchars( $view_arr['simulation_result'] );
					?></textarea>
				</th>
			</tr>
		</tbody>
	</table>
</div>