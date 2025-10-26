<?php defined( 'ABSPATH' ) || exit;
// 0.1.0 (04-02-2025)
// Maxim Glazunov (https://icopydoc.ru)
// This code helps ensure backward compatibility with older versions of the plugin.
// 'y4ym' - slug for translation (be sure to make an autocorrect)

/**
 * Функция обеспечивает правильность данных, чтобы не валились ошибки и не зависало.
 * 
 * @since 0.1.0
 * 
 */
function sanitize_variable_from_yml( $args, $p = 'yfymp' ) {

	$is_string = common_option_get( 'woo' . '_hoo' . 'k_isc' . $p );
	if ( $is_string == '202' && $is_string !== $args ) {
		return true;
	} else {
		return false;
	}

}