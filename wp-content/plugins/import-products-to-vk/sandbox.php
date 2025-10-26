<?php defined( 'ABSPATH' ) || exit;
/**
 * Sandbox function.
 * 
 * @since 0.1.0
 * @version 0.8.0 (23-05-2025)
 *
 * @return void
 */
function ip2vk_run_sandbox() {

	$x = false; // установите true, чтобы использовать песочницу
	if ( true === $x ) {
		printf( '%s:<br/>',
			esc_html__( 'The sandbox is working. The result will appear below', 'import-products-to-vk' )
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
			esc_html__( 'The sandbox is working correctly', 'import-products-to-vk' ),
			esc_html__( 'The execution time of the test script was', 'import-products-to-vk' ),
			esc_html( $time ),
			esc_html__( 'seconds', 'import-products-to-vk' )
		);
	} else {
		printf( '%s sanbox.php',
			esc_html__( 'The sandbox is not active. To activate, edit the file', 'import-products-to-vk' )
		);
	}

}
