<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );

/**
 * The template for displaying pages footers
 *
 * Do not overload this file directly. Instead have a look at templates/footer.php file in us-core plugin folder:
 * you should find all the needed hooks there.
 */

if ( function_exists( 'us_load_template' ) ) {

	us_load_template( 'templates/footer' );

} else {
	?>
		</div>
		<footer	class="l-footer">
			<section class="l-section color_footer-top">
				<div class="l-section-h i-cf align_center">
					<span><?php bloginfo( 'name' ); ?></span>
				</div>
			</section>
		</footer>
		<!-- === ПРИНУДИТЕЛЬНОЕ ИСПРАВЛЕНИЕ СЧЕТЧИКА КОРЗИНЫ === -->
		<script>
		jQuery(document).ready(function($) {
			console.log('Загружен скрипт исправления счетчика корзины');
			function updateCartCounter() {
				let total = 0;
				$('.quantity input.qty').each(function() {
					total += parseInt($(this).val()) || 0;
				});
				$('.w-cart-quantity').html(total > 0 ? total : '0');
				$('.w-cart').each(function() {
					$(this)[total > 0 ? 'removeClass' : 'addClass']('empty');
				});
				console.log('Счетчик корзины обновлен:', total);
			}
			updateCartCounter();
			setInterval(updateCartCounter, 2000);
			$(document).on('change', '.quantity input.qty', updateCartCounter);
			$(document).on('click', '.quantity button', function() {
				setTimeout(updateCartCounter, 100);
			});
		});
		</script>
		<?php wp_footer(); ?>
	</body>
	</html>
	<?php
}
