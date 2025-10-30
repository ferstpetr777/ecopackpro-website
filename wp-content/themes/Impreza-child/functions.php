<?php
/**
 * Impreza Child Theme Functions
 * 
 * @package Impreza Child
 */

// Подключение скриптов и стилей родительской темы
add_action( 'wp_enqueue_scripts', 'impreza_child_enqueue_styles' );
function impreza_child_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}

/**
 * ИСПРАВЛЕНИЕ: Обновление badge на странице корзины
 * Использует ТОЛЬКО стандартные события WooCommerce: updated_cart_totals
 * Подсчитывает количество товаров из таблицы корзины на странице
 */
add_action( 'wp_footer', 'impreza_child_update_cart_badge_on_cart_page' );
function impreza_child_update_cart_badge_on_cart_page() {
	if ( ! is_cart() ) {
		return;
	}
	?>
	<script>
	jQuery(document).ready(function($) {
		// Функция обновления badge из таблицы корзины
		function updateCartBadgeFromTable() {
			var total = 0;
			
			// Считаем количество товаров из таблицы корзины
			$('.woocommerce-cart-form__cart-item').each(function() {
				var qty = $(this).find('.quantity input.qty').val();
				if (qty) {
					total += parseInt(qty) || 0;
				}
			});
			
			// Обновляем badge в нижнем меню (мобильная версия)
			$('.l-subheader.at_bottom .w-cart-quantity').text(total);
			$('.l-subheader.at_bottom .w-cart').toggleClass('empty', total === 0);
			
			// Обновляем badge в хедере (desktop версия)
			$('.l-header .w-cart-quantity').text(total);
			$('.l-header .w-cart').toggleClass('empty', total === 0);
			
			console.log('Cart badge updated from table:', total);
		}
		
		// Обновляем ПОСЛЕ того как WooCommerce cart-fragments отработает
		$(document.body).on('wc_fragments_loaded wc_fragments_refreshed', function() {
			setTimeout(updateCartBadgeFromTable, 100);
		});
		
		// Обновляем сразу при загрузке (с задержкой, чтобы cart-fragments успел отработать)
		setTimeout(updateCartBadgeFromTable, 500);
		
		// Обновляем при стандартном событии WooCommerce
		$(document.body).on('updated_cart_totals updated_wc_div', updateCartBadgeFromTable);
		
		// Обновляем при изменении количества
		$(document).on('change', '.quantity input.qty', function() {
			setTimeout(updateCartBadgeFromTable, 100);
		});
	});
	</script>
	<?php
}

