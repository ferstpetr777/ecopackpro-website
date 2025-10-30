/**
 * Mobile Footer Cart Badge - для нижней навигации
 * Создает и обновляет badge для элемента "Корзина" в нижней мобильной навигации
 */

(function() {
    'use strict';
    
    var MobileFooterCartBadge = {
        
        badgeElement: null,
        cartLinkElement: null,
        currentCount: 0,
        
        // Инициализация
        init: function() {
            console.log('[Mobile Footer Badge] Initializing...');
            
            // Находим элемент корзины в нижней навигации
            this.findCartElement();
            
            // Создаем badge если его нет
            this.createBadge();
            
            // Первоначальное обновление
            this.updateBadge();
            
            // Подписываемся на события
            this.attachEvents();
            
            // Периодическое обновление
            this.startPolling();
            
            console.log('[Mobile Footer Badge] Initialized successfully');
        },
        
        // Поиск элемента корзины в нижней навигации
        findCartElement: function() {
            // Вариант 1: ush_vwrapper_5 (из header конфигурации)
            var wrapper = document.querySelector('.ush_vwrapper_5');
            if (wrapper) {
                this.cartLinkElement = wrapper.querySelector('a[href*="/cart"]') || 
                                      wrapper.querySelector('.w-text-h');
                console.log('[Mobile Footer Badge] Found cart wrapper: ush_vwrapper_5');
            }
            
            // Вариант 2: Поиск по тексту "Корзина"
            if (!this.cartLinkElement) {
                var allLinks = document.querySelectorAll('.hidden_for_laptops a');
                for (var i = 0; i < allLinks.length; i++) {
                    if (allLinks[i].textContent.trim() === 'Корзина' || 
                        allLinks[i].href.includes('/cart')) {
                        this.cartLinkElement = allLinks[i];
                        console.log('[Mobile Footer Badge] Found cart link by text/href');
                        break;
                    }
                }
            }
            
            // Вариант 3: Все ссылки на /cart/ в футере
            if (!this.cartLinkElement) {
                var cartLinks = document.querySelectorAll('a[href*="/cart"]');
                for (var j = 0; j < cartLinks.length; j++) {
                    var parent = cartLinks[j].closest('.hidden_for_laptops, .w-vwrapper, footer');
                    if (parent) {
                        this.cartLinkElement = cartLinks[j];
                        console.log('[Mobile Footer Badge] Found cart link in footer area');
                        break;
                    }
                }
            }
            
            if (!this.cartLinkElement) {
                console.warn('[Mobile Footer Badge] Cart link not found!');
                return false;
            }
            
            return true;
        },
        
        // Создание badge элемента
        createBadge: function() {
            if (!this.cartLinkElement) {
                console.warn('[Mobile Footer Badge] Cannot create badge: cart link not found');
                return;
            }
            
            // Проверяем, нет ли уже badge
            this.badgeElement = this.cartLinkElement.querySelector('.mobile-cart-badge');
            
            if (!this.badgeElement) {
                // Создаем новый badge
                this.badgeElement = document.createElement('span');
                this.badgeElement.className = 'mobile-cart-badge';
                this.badgeElement.textContent = '0';
                
                // Добавляем к ссылке
                if (this.cartLinkElement.tagName === 'A') {
                    this.cartLinkElement.style.position = 'relative';
                    this.cartLinkElement.appendChild(this.badgeElement);
                } else {
                    // Если это wrapper
                    var link = this.cartLinkElement.querySelector('a');
                    if (link) {
                        link.style.position = 'relative';
                        link.appendChild(this.badgeElement);
                    } else {
                        this.cartLinkElement.appendChild(this.badgeElement);
                    }
                }
                
                console.log('[Mobile Footer Badge] Badge element created');
            } else {
                console.log('[Mobile Footer Badge] Badge element already exists');
            }
        },
        
        // Получение количества товаров
        getCartCount: function() {
            var count = 0;
            
            // 1. Из data-атрибута body
            var bodyCount = document.body.getAttribute('data-cart-count');
            if (bodyCount) {
                count = parseInt(bodyCount) || 0;
                if (count > 0) {
                    console.log('[Mobile Footer Badge] Count from body attr:', count);
                    return count;
                }
            }
            
            // 2. Из WooCommerce fragments
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
                            if (count > 0) {
                                console.log('[Mobile Footer Badge] Count from WC fragments:', count);
                                return count;
                            }
                        }
                    }
                }
            } catch(e) {
                console.warn('[Mobile Footer Badge] Error reading WC fragments:', e);
            }
            
            // 3. Из .w-cart-quantity в header
            var cartQtyElements = document.querySelectorAll('.w-cart-quantity');
            for (var i = 0; i < cartQtyElements.length; i++) {
                var qty = parseInt(cartQtyElements[i].textContent.trim()) || 0;
                if (qty > count) count = qty;
            }
            if (count > 0) {
                console.log('[Mobile Footer Badge] Count from .w-cart-quantity:', count);
                return count;
            }
            
            // 4. Из формы корзины
            var qtyInputs = document.querySelectorAll('.woocommerce-cart-form .quantity input.qty');
            qtyInputs.forEach(function(input) {
                count += parseInt(input.value) || 0;
            });
            if (count > 0) {
                console.log('[Mobile Footer Badge] Count from cart form:', count);
                return count;
            }
            
            // 5. Из localStorage
            try {
                var stored = localStorage.getItem('ecopackpro_cart_count');
                if (stored) {
                    count = parseInt(stored) || 0;
                    if (count > 0) {
                        console.log('[Mobile Footer Badge] Count from localStorage:', count);
                        return count;
                    }
                }
            } catch(e) {}
            
            // 6. Из global variable
            if (typeof window.initialCartCount !== 'undefined') {
                count = parseInt(window.initialCartCount) || 0;
                if (count > 0) {
                    console.log('[Mobile Footer Badge] Count from initialCartCount:', count);
                    return count;
                }
            }
            
            console.log('[Mobile Footer Badge] Final count:', count);
            return count;
        },
        
        // Обновление badge
        updateBadge: function() {
            var newCount = this.getCartCount();
            
            if (!this.badgeElement) {
                console.warn('[Mobile Footer Badge] Badge element not found, trying to create...');
                this.createBadge();
                if (!this.badgeElement) return;
            }
            
            // Обновляем только если изменилось
            if (newCount !== this.currentCount) {
                console.log('[Mobile Footer Badge] Updating badge:', this.currentCount, '→', newCount);
                this.currentCount = newCount;
                
                this.badgeElement.textContent = newCount;
                
                if (newCount > 0) {
                    this.badgeElement.classList.add('has-items');
                    this.badgeElement.classList.remove('empty');
                    this.badgeElement.style.display = 'inline-block';
                } else {
                    this.badgeElement.classList.remove('has-items');
                    this.badgeElement.classList.add('empty');
                    this.badgeElement.style.display = 'none';
                }
            }
        },
        
        // Подписка на события
        attachEvents: function() {
            var self = this;
            
            // jQuery события WooCommerce
            if (typeof jQuery !== 'undefined') {
                jQuery(document.body).on('wc_fragments_refreshed wc_fragments_loaded added_to_cart removed_from_cart updated_cart_totals', function(e) {
                    console.log('[Mobile Footer Badge] WooCommerce event:', e.type);
                    setTimeout(function() { self.updateBadge(); }, 100);
                });
            }
            
            // Custom event от bridge
            document.addEventListener('ecopackpro:cartUpdate', function(e) {
                console.log('[Mobile Footer Badge] Custom cart update event:', e.detail);
                setTimeout(function() { self.updateBadge(); }, 50);
            });
            
            // Изменение data-атрибута
            if (typeof MutationObserver !== 'undefined') {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && 
                            mutation.attributeName === 'data-cart-count') {
                            console.log('[Mobile Footer Badge] data-cart-count changed');
                            self.updateBadge();
                        }
                    });
                });
                
                observer.observe(document.body, {
                    attributes: true,
                    attributeFilter: ['data-cart-count']
                });
            }
        },
        
        // Периодическая проверка
        startPolling: function() {
            var self = this;
            
            // Быстрые проверки первые 10 секунд
            for (var i = 1; i <= 10; i++) {
                (function(sec) {
                    setTimeout(function() {
                        self.updateBadge();
                    }, sec * 1000);
                })(i);
            }
            
            // Регулярная проверка каждые 5 секунд
            setInterval(function() {
                self.updateBadge();
            }, 5000);
        }
    };
    
    // Инициализация после загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            MobileFooterCartBadge.init();
        });
    } else {
        MobileFooterCartBadge.init();
    }
    
    // Для отладки
    window.MobileFooterCartBadge = MobileFooterCartBadge;
    
})();

