<?php
/**
 * Plugin Name: Cart Badge Fix - Server Side Rendering
 * Description: Рендерит badge с правильным значением на сервере для страницы корзины
 * Version: 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * На странице корзины принудительно устанавливаем правильное значение в badge
 */
add_action( 'wp_footer', 'cart_badge_fix_inline_script', 999 );
function cart_badge_fix_inline_script() {
	// Только на странице корзины
	if ( ! is_cart() ) {
		return;
	}
	
	// Получаем количество товаров
	$cart_count = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	
	?>
	<script>
	// Устанавливаем значение badge сразу при загрузке
	(function() {
		function updateCartBadgeOnCartPage() {
			var cartCount = <?php echo esc_js( $cart_count ); ?>;
			
			// Обновляем ВСЕ badge на странице
			jQuery('.w-cart-quantity').html(cartCount > 0 ? cartCount : '0');
			jQuery('.w-cart').toggleClass('empty', cartCount === 0);
			
			console.log('Cart badge updated on cart page:', cartCount);
		}
		
		// Вызываем сразу
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', updateCartBadgeOnCartPage);
		} else {
			updateCartBadgeOnCartPage();
		}
		
		// Дополнительно через 100ms
		setTimeout(updateCartBadgeOnCartPage, 100);
		
		// Дополнительно через 500ms
		setTimeout(updateCartBadgeOnCartPage, 500);
		
		// При событиях WooCommerce
		jQuery(document.body).on('updated_cart_totals updated_wc_div', updateCartBadgeOnCartPage);
	})();
	</script>
	<?php
}

