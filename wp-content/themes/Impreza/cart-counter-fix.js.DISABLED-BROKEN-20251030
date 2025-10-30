jQuery(document).ready(function($) {
    console.log('Загружен скрипт исправления счетчика корзины');
    
    // Функция обновления счетчика
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
    
    // Обновляем сразу
    updateCartCounter();
    
    // Обновляем каждые 2 секунды
    setInterval(updateCartCounter, 2000);
    
    // Обновляем при изменении количества
    $(document).on('change', '.quantity input.qty', updateCartCounter);
    $(document).on('click', '.quantity button', function() {
        setTimeout(updateCartCounter, 100);
    });
});
