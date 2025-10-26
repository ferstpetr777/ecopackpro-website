<?php defined( 'ABSPATH' ) || exit;
/**
 * Sandbox function.
 * 
 * @since 0.1.0
 * @version 5.0.0 (25-03-2025)
 *
 * @return void
 */
function y4ym_run_sandbox() {

	$x = false; // установите true, чтобы использовать песочницу
	if ( true === $x ) {
		printf( '%s:<br/>',
			esc_html__( 'The sandbox is working. The result will appear below', 'yml-for-yandex-market' )
		);
		$time_start = microtime( true );
		/* вставьте ваш код ниже */
		// Example:
		// $product = wc_get_product(8303);
		// echo $product->get_price(); 

		/* дальше не редактируем */
		$time_end = microtime( true );
		$time = $time_end - $time_start;
		printf( '<br/>%s<br/>%s %d %s',
			esc_html__( 'The sandbox is working correctly', 'yml-for-yandex-market' ),
			esc_html__( 'The execution time of the test script was', 'yml-for-yandex-market' ),
			esc_html( $time ),
			esc_html__( 'seconds', 'yml-for-yandex-market' )
		);
	} else {
		printf( '%s sanbox.php',
			esc_html__( 'The sandbox is not active. To activate, edit the file', 'yml-for-yandex-market' )
		);
	}

}
