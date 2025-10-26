<?php
/**
 * The tab items loop body.
 * 
 * @version    5.0.0 (25-03-2025)
 * @package    Y4YM
 * @subpackage Y4YM/admin/partials/settings_page/
 * 
 * @param $view_arr['feed_id']
 */
defined( 'ABSPATH' ) || exit;

$tag_label = $attr_arr[ $i ]['data']['label'];
$opt_value = common_option_get(
	$attr_arr[ $i ]['opt_name'],
	'',
	$view_arr['feed_id'],
	'y4ym'
);

// определим, является ли следующая опция частью текущей
if ( isset( $attr_arr[ $i ]['data']['has_next'] ) ) {
	$has_next = $attr_arr[ $i ]['data']['has_next'];
} else {
	$has_next = false;
}

// зададим вывод информации по колонкам. `th-td` - вывод в два столбца; `td-td` - вывов в одном стоблце (втором)
if ( isset( $attr_arr[ $i ]['data']['table_location'] ) ) {
	$table_location = $attr_arr[ $i ]['data']['table_location'];
} else {
	$table_location = 'th-td';
}

// зададим дескрипшин
if ( isset( $attr_arr[ $i ]['data']['desc'] ) && ! empty( $attr_arr[ $i ]['data']['desc'] ) ) {
	$description = $attr_arr[ $i ]['data']['desc'] . '.';
} else {
	$description = '';
}

// select
if ( $attr_arr[ $i ]['type'] === 'select' ) {

	$multiple = false;
	$tag_attributes_arr = [ 
		'id' => $attr_arr[ $i ]['opt_name'],
		'class' => 'y4ym_select'
	];
	if ( isset( $attr_arr[ $i ]['data']['size'] ) ) {
		$multiple = true;
		$tag_attributes_arr = array_merge(
			$tag_attributes_arr,
			[ 
				'name' => $attr_arr[ $i ]['opt_name'] . '[]',
				'size' => $attr_arr[ $i ]['data']['size'],
				'multiple' => 'multiple'
			]
		);
		// массивы хранятся в отдельных опциях
		$opt_value = maybe_unserialize( univ_option_get(
			$attr_arr[ $i ]['opt_name'] . $view_arr['feed_id'],
			[]
		) );
	} else {
		$tag_attributes_arr = array_merge(
			$tag_attributes_arr,
			[ 
				'name' => $attr_arr[ $i ]['opt_name']
			]
		);
	}

	switch ( $table_location ) {

		case 'th-td':
			$html_th .= sprintf( '%1$s',
				$tag_label
			);
			$html_td .= sprintf( '%1$s<p>%2$s</p>',
				new Y4YM_Get_Paired_Tag(
					'select',
					y4ym_get_html_options( $opt_value, $attr_arr[ $i ]['data'], $multiple ),
					$tag_attributes_arr
				),
				$description
			);
			break;

		default:
			$html_td .= sprintf( '<label class="y4ym_label">%1$s:</label>%2$s<p>%3$s</p>',
				$tag_label,
				new Y4YM_Get_Paired_Tag(
					'select',
					y4ym_get_html_options( $opt_value, $attr_arr[ $i ]['data'], $multiple ),
					$tag_attributes_arr
				),
				$description
			);
			break;

	}
	unset( $tag_attributes_arr );

}

// select2 - place 3 from 5 (with woocommerce serch)
if ( $attr_arr[ $i ]['type'] === 'select2' ) {

	$tag_attributes_arr = [ 
		'id' => $attr_arr[ $i ]['opt_name'],
		'name' => $attr_arr[ $i ]['opt_name'] . '[]',
		'class' => 'y4ym_select2',
		'multiple' => 'multiple',
		'style' => 'width:99%;max-width:25em;'
	];
	// массивы хранятся в отдельных опциях, а select2 точно массив
	$opt_value = maybe_unserialize( univ_option_get(
		$attr_arr[ $i ]['opt_name'] . $view_arr['feed_id'],
		[]
	) );

	switch ( $table_location ) {

		case 'th-td':
			$html_th .= sprintf( '%1$s',
				$tag_label
			);
			$html_td .= sprintf( '%1$s<p>%2$s</p>',
				new Y4YM_Get_Paired_Tag(
					'select',
					y4ym_get_html_options_for_select2( $opt_value ),
					$tag_attributes_arr
				),
				$description
			);
			break;

		default:
			$html_td .= sprintf( '<label class="y4ym_label">%1$s:</label>%2$s<p>%3$s</p>',
				$tag_label,
				new Y4YM_Get_Paired_Tag(
					'select',
					y4ym_get_html_options_for_select2( $opt_value ),
					$tag_attributes_arr
				),
				$description
			);
			break;

	}

}
// end select2 - place 3 from 5 (with woocommerce serch)

// text 
if ( $attr_arr[ $i ]['type'] === 'text' || $attr_arr[ $i ]['type'] === 'number' ) {

	$tag_attributes_arr = [ 
		'id' => esc_attr( $attr_arr[ $i ]['opt_name'] ),
		'name' => esc_attr( $attr_arr[ $i ]['opt_name'] ),
		'class' => esc_attr( 'y4ym_input' ),
		'value' => esc_attr( $opt_value ),
		'placeholder' => esc_attr( $attr_arr[ $i ]['data']['placeholder'] )
	];

	switch ( $table_location ) {

		case 'th-td':
			$html_th .= sprintf( '%1$s',
				$tag_label
			);
			$html_td .= sprintf( '%1$s <p>%2$s</p>',
				new Y4YM_Get_Open_Tag(
					'input',
					$tag_attributes_arr,
					true
				),
				$description
			);
			break;

		default:
			$html_td .= sprintf( '<label class="y4ym_label">%1$s:</label>%2$s<p>%3$s</p>',
				$tag_label,
				new Y4YM_Get_Open_Tag(
					'input',
					$tag_attributes_arr,
					true
				),
				$description
			);

	}

}

// Color Picker - place 4 from 4
if ( $attr_arr[ $i ]['type'] === 'color_picker' ) {

	$tag_attributes_arr = [ 
		'id' => esc_attr( $attr_arr[ $i ]['opt_name'] ),
		'name' => esc_attr( $attr_arr[ $i ]['opt_name'] ),
		'class' => esc_attr( 'iris_color' ),
		'type' => 'text',
		'value' => esc_attr( $opt_value )
	];

	switch ( $table_location ) {

		case 'th-td':
			$html_th .= sprintf( '%1$s',
				$tag_label
			);
			$html_td .= sprintf( '%1$s <p>%2$s</p>',
				new Y4YM_Get_Open_Tag(
					'input',
					$tag_attributes_arr,
					true
				),
				$description
			);
			break;

		default:
			$html_td .= sprintf( '<label class="y4ym_label">%1$s:</label>%2$s<p>%3$s</p>',
				$tag_label,
				new Y4YM_Get_Open_Tag(
					'input',
					$tag_attributes_arr,
					true
				),
				$description
			);

	}

}

// textarea 
if ( $attr_arr[ $i ]['type'] === 'textarea' ) {

	$tag_attributes_arr = [ 
		'id' => esc_attr( $attr_arr[ $i ]['opt_name'] ),
		'name' => esc_attr( $attr_arr[ $i ]['opt_name'] ),
		'class' => esc_attr( 'y4ym_textarea' ),
		'value' => esc_attr( $opt_value ),
		'placeholder' => esc_attr( $attr_arr[ $i ]['data']['placeholder'] )
	];

	switch ( $table_location ) {

		case 'th-td':
			$html_th .= sprintf( '%1$s',
				$tag_label
			);
			$html_td .= sprintf( '%1$s <p>%2$s</p>',
				new Y4YM_Get_Paired_Tag(
					'textarea',
					$opt_value,
					$tag_attributes_arr
				),
				$description
			);
			break;

		default:
			$html_td .= sprintf( '<label class="y4ym_label">%1$s:</label>%2$s<p>%3$s</p>',
				$tag_label,
				new Y4YM_Get_Paired_Tag(
					'textarea',
					$opt_value,
					$tag_attributes_arr
				),
				$description
			);

	}
}

if ( false === $has_next ) {
	$html_body .= sprintf( '<tr><th class="y4ym_th">%1$s</th><td class="y4ym_td overalldesc">%2$s</td></tr>',
		wp_kses( $html_th, Y4YM_ALLOWED_HTML_ARR ),
		wp_kses( $html_td, Y4YM_ALLOWED_HTML_ARR )
	);
	$html_th = '';
	$html_td = '';
}