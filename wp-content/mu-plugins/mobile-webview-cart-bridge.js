/**
 * Mobile WebView Cart Bridge - версия 1.0
 * Обеспечивает передачу данных корзины в мобильное приложение через WebView
 * 
 * Поддерживает:
 * - Android WebView JavaScript Bridge
 * - iOS WKWebView postMessage
 * - Глобальные переменные window
 * - HTML data-атрибуты
 * - localStorage/sessionStorage
 */

(function() {
    'use strict';
    
    var CartBridge = {
        
        // Текущее количество товаров
        currentCount: 0,
        
        // Инициализация
        init: function() {
            console.log('=== Mobile WebView Cart Bridge Initialized ===');
            
            // Проверяем среду
            this.detectEnvironment();
            
            // Первоначальное обновление
            this.updateCart();
            
            // Слушаем события WooCommerce
            this.attachWooCommerceEvents();
            
            // Периодическая проверка
            this.startPolling();
            
            // Обновление при изменениях DOM
            this.observeDOM();
        },
        
        // Определение среды выполнения
        detectEnvironment: function() {
            var env = {
                isAndroid: /Android/i.test(navigator.userAgent),
                isIOS: /iPhone|iPad|iPod/i.test(navigator.userAgent),
                isWebView: window.navigator.standalone || 
                          window.matchMedia('(display-mode: standalone)').matches ||
                          document.referrer.includes('android-app://'),
                hasAndroidBridge: typeof Android !== 'undefined',
                hasIOSBridge: typeof webkit !== 'undefined' && 
                            typeof webkit.messageHandlers !== 'undefined' &&
                            typeof webkit.messageHandlers.cartUpdate !== 'undefined'
            };
            
            window.cartBridgeEnvironment = env;
            console.log('Environment:', env);
            
            return env;
        },
        
        // Получение количества товаров всеми возможными способами
        getCartCount: function() {
            var count = 0;
            var sources = [];
            
            // 1. Из WooCommerce cart fragments
            try {
                if (typeof wc_cart_fragments_params !== 'undefined') {
                    var hashKey = wc_cart_fragments_params.cart_hash_key || 'wc_fragments';
                    var fragments = sessionStorage.getItem(hashKey);
                    if (fragments) {
                        var data = JSON.parse(fragments);
                        if (data && data['div.widget_shopping_cart_content']) {
                            var tempDiv = document.createElement('div');
                            tempDiv.innerHTML = data['div.widget_shopping_cart_content'];
                            var quantities = tempDiv.querySelectorAll('.quantity');
                            quantities.forEach(function(q) {
                                var qty = parseInt(q.textContent.replace(/[^\d]/g, '')) || 0;
                                count += qty;
                            });
                            sources.push('wc_fragments');
                        }
                    }
                }
            } catch(e) {
                console.warn('Error reading WC fragments:', e);
            }
            
            // 2. Из элементов на странице
            if (count === 0) {
                var cartQuantityElements = document.querySelectorAll('.w-cart-quantity');
                if (cartQuantityElements.length > 0) {
                    for (var i = 0; i < cartQuantityElements.length; i++) {
                        var text = cartQuantityElements[i].textContent.trim();
                        var qty = parseInt(text) || 0;
                        if (qty > count) count = qty;
                    }
                    if (count > 0) sources.push('.w-cart-quantity');
                }
            }
            
            // 3. Из формы корзины
            if (count === 0) {
                var qtyInputs = document.querySelectorAll('.woocommerce-cart-form .quantity input.qty');
                qtyInputs.forEach(function(input) {
                    count += parseInt(input.value) || 0;
                });
                if (count > 0) sources.push('cart_form');
            }
            
            // 4. Из mini cart widget
            if (count === 0) {
                var miniCartItems = document.querySelectorAll('.widget_shopping_cart .mini_cart_item');
                count = miniCartItems.length;
                if (count > 0) sources.push('mini_cart_widget');
            }
            
            // 5. Из data-атрибута body
            if (count === 0) {
                var bodyCount = document.body.getAttribute('data-cart-count');
                if (bodyCount) {
                    count = parseInt(bodyCount) || 0;
                    if (count > 0) sources.push('body_data_attr');
                }
            }
            
            // 6. Из localStorage
            if (count === 0) {
                try {
                    var storedCount = localStorage.getItem('ecopackpro_cart_count');
                    if (storedCount) {
                        count = parseInt(storedCount) || 0;
                        if (count > 0) sources.push('localStorage');
                    }
                } catch(e) {}
            }
            
            console.log('Cart count:', count, 'Sources:', sources);
            return count;
        },
        
        // Обновление корзины
        updateCart: function() {
            var newCount = this.getCartCount();
            
            // Проверяем изменения
            if (newCount !== this.currentCount) {
                console.log('Cart count changed:', this.currentCount, '→', newCount);
                this.currentCount = newCount;
                
                // Передаем данные во все возможные места
                this.broadcastCartUpdate(newCount);
            }
            
            return newCount;
        },
        
        // Передача данных во все каналы
        broadcastCartUpdate: function(count) {
            var env = window.cartBridgeEnvironment || this.detectEnvironment();
            
            // 1. В глобальную переменную
            window.cartCount = count;
            window.ecopackproCartCount = count;
            
            // 2. В data-атрибут body
            document.body.setAttribute('data-cart-count', count);
            document.documentElement.setAttribute('data-cart-count', count);
            
            // 3. В localStorage
            try {
                localStorage.setItem('ecopackpro_cart_count', count);
                sessionStorage.setItem('ecopackpro_cart_count', count);
            } catch(e) {}
            
            // 4. Android WebView Bridge
            if (env.hasAndroidBridge) {
                try {
                    if (typeof Android.onCartUpdate === 'function') {
                        Android.onCartUpdate(count);
                        console.log('→ Android bridge: onCartUpdate(' + count + ')');
                    }
                    if (typeof Android.updateCartCount === 'function') {
                        Android.updateCartCount(count);
                        console.log('→ Android bridge: updateCartCount(' + count + ')');
                    }
                } catch(e) {
                    console.warn('Android bridge error:', e);
                }
            }
            
            // 5. iOS WKWebView postMessage
            if (env.hasIOSBridge) {
                try {
                    webkit.messageHandlers.cartUpdate.postMessage({count: count});
                    console.log('→ iOS bridge: cartUpdate({count: ' + count + '})');
                } catch(e) {
                    console.warn('iOS bridge error:', e);
                }
            }
            
            // 6. Custom Event для любых слушателей
            try {
                var event = new CustomEvent('ecopackpro:cartUpdate', {
                    detail: {count: count},
                    bubbles: true
                });
                document.dispatchEvent(event);
                console.log('→ CustomEvent: ecopackpro:cartUpdate');
            } catch(e) {}
            
            // 7. postMessage для parent window (если в iframe)
            try {
                if (window.parent !== window) {
                    window.parent.postMessage({
                        type: 'ecopackpro_cart_update',
                        count: count
                    }, '*');
                    console.log('→ postMessage to parent');
                }
            } catch(e) {}
            
            // 8. Обновляем все элементы .w-cart-quantity
            var elements = document.querySelectorAll('.w-cart-quantity');
            elements.forEach(function(el) {
                el.textContent = count;
                if (count > 0) {
                    el.style.display = 'block';
                    el.style.visibility = 'visible';
                    el.style.opacity = '1';
                } else {
                    // Не скрываем, если это единственный источник данных
                    if (elements.length === 1) {
                        el.textContent = '0';
                    }
                }
            });
        },
        
        // Подписка на события WooCommerce
        attachWooCommerceEvents: function() {
            var self = this;
            
            // jQuery events
            if (typeof jQuery !== 'undefined') {
                jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded', function() {
                    console.log('WooCommerce fragments event');
                    setTimeout(function() { self.updateCart(); }, 100);
                });
                
                jQuery(document.body).on('added_to_cart removed_from_cart', function() {
                    console.log('WooCommerce cart change event');
                    setTimeout(function() { self.updateCart(); }, 300);
                });
                
                jQuery(document.body).on('updated_cart_totals', function() {
                    console.log('WooCommerce cart totals updated');
                    setTimeout(function() { self.updateCart(); }, 100);
                });
            }
            
            // Native events
            document.addEventListener('wc_fragments_refreshed', function() {
                setTimeout(function() { self.updateCart(); }, 100);
            });
        },
        
        // Периодическая проверка
        startPolling: function() {
            var self = this;
            
            // Быстрая проверка первые 10 секунд
            var quickChecks = 0;
            var quickInterval = setInterval(function() {
                self.updateCart();
                quickChecks++;
                if (quickChecks >= 10) {
                    clearInterval(quickInterval);
                }
            }, 1000);
            
            // Регулярная проверка каждые 3 секунды
            setInterval(function() {
                self.updateCart();
            }, 3000);
        },
        
        // Наблюдение за изменениями DOM
        observeDOM: function() {
            var self = this;
            
            if (typeof MutationObserver !== 'undefined') {
                var observer = new MutationObserver(function(mutations) {
                    var shouldUpdate = false;
                    
                    mutations.forEach(function(mutation) {
                        // Проверяем изменения в корзине
                        if (mutation.target.classList && 
                            (mutation.target.classList.contains('woocommerce-cart-form') ||
                             mutation.target.classList.contains('widget_shopping_cart') ||
                             mutation.target.classList.contains('w-cart-quantity'))) {
                            shouldUpdate = true;
                        }
                    });
                    
                    if (shouldUpdate) {
                        setTimeout(function() { self.updateCart(); }, 100);
                    }
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['class', 'data-cart-count']
                });
            }
        }
    };
    
    // Инициализация после загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            CartBridge.init();
        });
    } else {
        CartBridge.init();
    }
    
    // Экспорт в глобальную область
    window.EcopackProCartBridge = CartBridge;
    
    // Для отладки
    window.getCartCount = function() {
        return CartBridge.getCartCount();
    };
    
    window.updateCartNow = function() {
        return CartBridge.updateCart();
    };
    
})();

