// Диагностический скрипт для тестирования счетчика корзины
console.log('=== ДИАГНОСТИЧЕСКИЙ СКРИПТ ЗАГРУЖЕН ===');

// Проверяем доступность jQuery
if (typeof jQuery === 'undefined') {
    console.error('jQuery не загружен!');
} else {
    console.log('jQuery версия:', jQuery.fn.jquery);
}

// Проверяем элементы корзины
jQuery(document).ready(function($) {
    console.log('=== НАЧАЛО ДИАГНОСТИКИ ===');
    
    // Проверяем наличие элементов
    const cartQuantity = $('.w-cart-quantity');
    const cartElements = $('.w-cart');
    const quantityInputs = $('.quantity input.qty');
    
    console.log('Найдено элементов .w-cart-quantity:', cartQuantity.length);
    console.log('Найдено элементов .w-cart:', cartElements.length);
    console.log('Найдено элементов .quantity input.qty:', quantityInputs.length);
    
    // Проверяем User Agent
    console.log('User Agent:', navigator.userAgent);
    console.log('Платформа:', navigator.platform);
    console.log('Размер экрана:', screen.width + 'x' + screen.height);
    console.log('Размер окна:', window.innerWidth + 'x' + window.innerHeight);
    
    // Проверяем, является ли это мобильным устройством
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    console.log('Мобильное устройство:', isMobile);
    
    // Проверяем, является ли это WebView
    const isWebView = /wv|WebView/i.test(navigator.userAgent);
    console.log('WebView:', isWebView);
    
    // Функция обновления счетчика
    function updateCartCounter() {
        let total = 0;
        $('.quantity input.qty').each(function() {
            const val = parseInt($(this).val()) || 0;
            total += val;
        });
        
        $('.w-cart-quantity').html(total > 0 ? total : '0');
        $('.w-cart').each(function() {
            $(this)[total > 0 ? 'removeClass' : 'addClass']('empty');
        });
        
        console.log('Счетчик обновлен:', total);
        return total;
    }
    
    // Первоначальное обновление
    updateCartCounter();
    
    // Обновление каждые 1 секунду для диагностики
    setInterval(function() {
        console.log('Диагностическое обновление счетчика');
        updateCartCounter();
    }, 1000);
    
    // Проверяем работу через 5 секунд
    setTimeout(function() {
        console.log('=== ПРОВЕРКА ЧЕРЕЗ 5 СЕКУНД ===');
        console.log('Текущее значение счетчика:', $('.w-cart-quantity').html());
        console.log('Классы .w-cart:', $('.w-cart').attr('class'));
    }, 5000);
    
    console.log('=== ДИАГНОСТИКА НАСТРОЕНА ===');
});
