// Исправление для мобильного индикатора корзины
jQuery(document).ready(function($) {
    // Функция для обновления индикатора корзины
    function updateCartIndicator() {
        // Получаем количество товаров из корзины
        var cartTotal = 0;
        $('.woocommerce-cart-form .quantity input.qty').each(function() {
            var quantity = parseInt($(this).val()) || 0;
            cartTotal += quantity;
        });
        
        // Обновляем индикатор корзины
        var $cartQuantity = $('.w-cart-quantity');
        var $cartElement = $('.w-cart');
        
        if (cartTotal > 0) {
            $cartQuantity.text(cartTotal);
            $cartElement.removeClass('empty');
            $cartQuantity.show();
            $cartQuantity.css({
                'display': 'block',
                'visibility': 'visible',
                'opacity': '1'
            });
        } else {
            $cartQuantity.text('0');
            $cartElement.addClass('empty');
            $cartQuantity.hide();
        }
    }
    
    // Принудительное обновление при загрузке страницы
    updateCartIndicator();
    
    // Обновляем индикатор при изменении количества товаров
    $(document).on('change', '.woocommerce-cart-form .quantity input.qty', function() {
        setTimeout(updateCartIndicator, 100);
    });
    
    // Обновляем индикатор при клике на кнопки + и -
    $(document).on('click', '.woocommerce-cart-form .quantity button', function() {
        setTimeout(updateCartIndicator, 200);
    });
    
    // Обновляем индикатор при удалении товаров
    $(document).on('click', '.remove_from_cart_button', function() {
        setTimeout(updateCartIndicator, 500);
    });
    
    // Обновляем индикатор при добавлении товаров
    $(document).on('added_to_cart', function() {
        setTimeout(updateCartIndicator, 100);
    });
    
    // Обновляем индикатор при обновлении корзины
    $(document).on('wc_fragments_loaded wc_fragments_refreshed', function() {
        setTimeout(updateCartIndicator, 100);
    });
    
    // Обновляем индикатор при обновлении корзины через AJAX
    $(document).on('updated_cart_totals', function() {
        setTimeout(updateCartIndicator, 100);
    });
    
    // Обновляем индикатор при изменении любого элемента в корзине
    $(document).on('input change click', '.woocommerce-cart-form', function() {
        setTimeout(updateCartIndicator, 100);
    });
    
    // Дополнительная проверка каждые 500ms для надежности
    setInterval(updateCartIndicator, 500);
    
    // Принудительное обновление через 1, 2, 3 секунды после загрузки
    setTimeout(updateCartIndicator, 1000);
    setTimeout(updateCartIndicator, 2000);
    setTimeout(updateCartIndicator, 3000);
    
    // Принудительное обновление при любом изменении DOM
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' || mutation.type === 'attributes') {
                setTimeout(updateCartIndicator, 100);
            }
        });
    });
        
    observer.observe(document.body, {
        childList: true,
        subtree: true,
        attributes: true,
        attributeFilter: ['class', 'value']
    });
    
    // Дополнительная проверка при изменении размера окна
    $(window).on('resize', function() {
        setTimeout(updateCartIndicator, 100);
    });
    
    // Дополнительная проверка при фокусе на странице
    $(window).on('focus', function() {
        setTimeout(updateCartIndicator, 100);
    });
});
