<?php
/**
 * The Another page tab
 * 
 * @version    5.0.5 (07-04-2025)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/settings_page/
 * 
 * @param $view_arr['feed_id']
 * @param $view_arr['tab_name']
 */
defined( 'ABSPATH' ) || exit;

// придерживаться правил. 1 из 2
$plugin_date = new Y4YM_Data();
$attr_arr = $plugin_date->get_options( [ 'y4ym_yml_rules' ] );
if ( $view_arr['tab_name'] === 'offer_data_tab' || $view_arr['tab_name'] === 'shop_data_tab' ) {
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
}
// end придерживаться правил. 1 из 2

$attr_arr = $plugin_date->get_data_for_tabs( $view_arr['tab_name'] );
$html_header = '';
$html_body = '';
$html_th = '';
$html_td = '';

// придерживаться правил. 2 из 2
$yml_rules = common_option_get( 'y4ym_yml_rules', false, $view_arr['feed_id'], 'y4ym' );
if ( $yml_rules === 'yandex_market' ) { // TODO: 5.0.0 (25-03-2025) - удалить со временем это условие
	$yml_rules = 'yandex_market_assortment';
}
$rules_obj = new Y4YM_Rules_List();
$rules_arr = $rules_obj->get_rules_arr();
$attr_arr_new = [];
for ( $i = 0; $i < count( $attr_arr ); $i++ ) {
	$tag_name = $attr_arr[ $i ]['data']['tag_name'];
	if ( ( isset( $rules_arr[ $yml_rules ] ) && in_array( $tag_name, $rules_arr[ $yml_rules ] ) )
		|| $tag_name === 'always' ) { // тег есть в этих правилах		
		$attr_arr_new[] = $attr_arr[ $i ];
	}
}
if ( ! empty( $attr_arr_new ) ) {
	unset( $attr_arr );
	$attr_arr = $attr_arr_new;
	unset( $attr_arr_new );
}
// end придерживаться правил. 2 из 2

for ( $i = 0; $i < count( $attr_arr ); $i++ ) {

	include __DIR__ . '/html-admin-settings-feed-tab-item-loop-body.php';

	// --- уникальная часть ---

	// зададим название для раскрывающегося блока
	if ( isset( $attr_arr[ $i ]['data']['tag_name_for_desc'] ) ) {
		$tag_name_for_desc = $attr_arr[ $i ]['data']['tag_name_for_desc'];
	} else {
		$tag_name_for_desc = $attr_arr[ $i ]['data']['tag_name'];
	}

	// пристыкуем к названию блока название тега
	if ( empty( $html_header ) ) {
		if ( isset( $attr_arr[ $i ]['data']['div_header'] ) ) {
			$html_header = sprintf( '%s &lt;%s&gt;', $attr_arr[ $i ]['data']['div_header'], $tag_name_for_desc );
		} else {
			$html_header = sprintf( '%s &lt;%s&gt;', $tag_label, $tag_name_for_desc );
		}
	}

	if ( ! isset( $attr_arr[ $i + 1 ]['data']['tag_name'] )
		|| $attr_arr[ $i + 1 ]['data']['tag_name'] === 'always'
		|| ( $attr_arr[ $i ]['data']['tag_name'] !== $attr_arr[ $i + 1 ]['data']['tag_name'] )
	) : ?>
		<div class="y4ym-postbox postbox closed">
			<div class="postbox-header">
				<h2 class="hndle ui-sortable-handle"><?php echo wp_kses( $html_header, Y4YM_ALLOWED_HTML_ARR ); ?></h2>
			</div>
			<div class="inside" style="padding: 0 !important;">
				<table class="form-table" role="presentation">
					<tbody>
						<?php echo wp_kses( $html_body, Y4YM_ALLOWED_HTML_ARR ); ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
		$html_header = '';
		$html_body = '';
	endif;

}
