// Исправление для индикатора корзины - версия 2.0
// Использует WooCommerce cart fragments для корректного отображения
jQuery(document).ready(function($) {
    
    // Функция для получения количества товаров из WooCommerce
    function getCartCount() {
        // Пробуем получить из WooCommerce cart fragments
        if (typeof wc_cart_fragments_params !== 'undefined') {
            var fragments = JSON.parse(sessionStorage.getItem(wc_cart_fragments_params.cart_hash_key));
            if (fragments && fragments['div.widget_shopping_cart_content']) {
                var $content = $(fragments['div.widget_shopping_cart_content']);
                var count = 0;
                $content.find('.quantity').each(function() {
                    var qty = parseInt($(this).text().replace(/[^\d]/g, '')) || 0;
                    count += qty;
                });
                return count;
            }
        }
        
        // Если есть элемент корзины с количеством
        var $cartQuantity = $('.w-cart-quantity');
        if ($cartQuantity.length && $cartQuantity.is(':visible')) {
            var currentCount = parseInt($cartQuantity.text()) || 0;
            if (currentCount > 0) {
                return currentCount;
            }
        }
        
        // Получаем из виджета корзины
        var $widgetCart = $('.widget_shopping_cart .mini_cart_item');
        if ($widgetCart.length > 0) {
            var count = 0;
            $widgetCart.find('.quantity').each(function() {
                var qty = parseInt($(this).text().replace(/[^\d]/g, '')) || 1;
                count += qty;
            });
            return count;
        }
        
        // Если мы на странице корзины - подсчитываем из формы
        var $cartForm = $('.woocommerce-cart-form');
        if ($cartForm.length > 0) {
            var cartTotal = 0;
            $cartForm.find('.quantity input.qty').each(function() {
                var quantity = parseInt($(this).val()) || 0;
                cartTotal += quantity;
            });
            return cartTotal;
        }
        
        return 0;
    }
    
    // Функция для обновления индикатора корзины
    function updateCartIndicator() {
        var cartCount = getCartCount();
        
        var $cartQuantity = $('.w-cart-quantity');
        var $cartElement = $('.w-cart');
        
        if (cartCount > 0) {
            $cartQuantity.text(cartCount);
            $cartElement.removeClass('empty');
            $cartQuantity.show();
            $cartQuantity.css({
                'display': 'block',
                'visibility': 'visible',
                'opacity': '1'
            });
            console.log('Cart updated: ' + cartCount + ' items');
        } else {
            // НЕ скрываем полностью, если данные недоступны
            var hasFragments = sessionStorage.getItem('wc_fragments');
            if (!hasFragments && $cartQuantity.text() !== '' && parseInt($cartQuantity.text()) > 0) {
                // Оставляем текущее значение, если фрагменты еще не загружены
                console.log('Cart fragments not loaded yet, keeping current value');
                return;
            }
            
            $cartQuantity.text('0');
            $cartElement.addClass('empty');
            $cartQuantity.hide();
            console.log('Cart is empty');
        }
    }
    
    // Обновляем при загрузке WooCommerce fragments
    $(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function() {
        console.log('WooCommerce fragments refreshed/loaded');
        setTimeout(updateCartIndicator, 100);
    });
    
    // Обновляем при добавлении товара
    $(document.body).on('added_to_cart', function(e, fragments, cart_hash) {
        console.log('Product added to cart');
        setTimeout(updateCartIndicator, 300);
    });
    
    // Обновляем при удалении товара
    $(document.body).on('removed_from_cart', function() {
        console.log('Product removed from cart');
        setTimeout(updateCartIndicator, 300);
    });
    
    // Обновляем на странице корзины
    $(document.body).on('updated_cart_totals', function() {
        console.log('Cart totals updated');
        setTimeout(updateCartIndicator, 100);
    });
    
    // Обновляем при изменении количества на странице корзины
    $(document).on('change', '.woocommerce-cart-form .quantity input.qty', function() {
        setTimeout(updateCartIndicator, 100);
    });
    
    // Первоначальное обновление - ТОЛЬКО ОДИН РАЗ после загрузки fragments
    var initialUpdateDone = false;
    function doInitialUpdate() {
        if (!initialUpdateDone && sessionStorage.getItem('wc_fragments')) {
            updateCartIndicator();
            initialUpdateDone = true;
            console.log('Initial cart update done');
        }
    }
    
    // Ждем загрузки фрагментов (максимум 2 секунды)
    var checkInterval = setInterval(function() {
        if (sessionStorage.getItem('wc_fragments')) {
            doInitialUpdate();
            clearInterval(checkInterval);
        }
    }, 100);
    
    // Таймаут на случай, если фрагменты не загрузятся
    setTimeout(function() {
        clearInterval(checkInterval);
        if (!initialUpdateDone) {
            updateCartIndicator();
            console.log('Initial cart update done (timeout)');
        }
    }, 2000);
});

