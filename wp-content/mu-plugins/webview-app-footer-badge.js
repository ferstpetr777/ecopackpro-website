/**
 * WebView App Footer Badge
 * Badge корзины для МОБИЛЬНОГО ПРИЛОЖЕНИЯ (WebView)
 * НЕ влияет на веб-версию сайта!
 */

(function() {
    'use strict';
    
    console.log('[WebView App Badge] Initializing for mobile...');
    
    // Создаём badge для ВСЕХ мобильных (включая WebView)
    // Определение ширины экрана
    if (window.innerWidth > 1024) {
        console.log('[WebView App Badge] Desktop detected, skipping...');
        return;
    }
    
    console.log('[WebView App Badge] Mobile detected (width:', window.innerWidth, '), creating badge...');
    
    var AppCartBadge = {
        badge: null,
        cartElement: null,
        currentCount: 0,
        
        init: function() {
            var self = this;
            
            // Пытаемся найти элемент корзины
            var attempts = 0;
            var maxAttempts = 30;
            
            var tryInit = setInterval(function() {
                attempts++;
                
                if (self.findCartElement() || attempts >= maxAttempts) {
                    clearInterval(tryInit);
                    
                    if (self.cartElement) {
                        console.log('[WebView App Badge] ✓ Cart element found');
                        self.createBadge();
                        self.update();
                        self.attachEvents();
                        self.startPolling();
                    } else {
                        console.warn('[WebView App Badge] ✗ Cart element not found after', attempts, 'attempts');
                    }
                }
            }, 200);
        },
        
        findCartElement: function() {
            if (this.cartElement) return true;
            
            // Поиск в нижнем меню приложения
            const selectors = [
                '.l-subheader.at_bottom a[href*="/cart"]',
                '.ush_vwrapper_3',
                '.ush_text_8',
                'a[href="/cart/"]',
                'a[href*="/cart"] .w-cart-icon'
            ];
            
            for (let selector of selectors) {
                const el = document.querySelector(selector);
                if (el) {
                    this.cartElement = el;
                    console.log('[WebView App Badge] Found via:', selector);
                    return true;
                }
            }
            
            return false;
        },
        
        createBadge: function() {
            if (!this.cartElement) return;
            
            // Ищем иконку внутри элемента
            let iconElement = this.cartElement.querySelector('.w-cart-icon, i, svg, [class*="icon"]');
            if (!iconElement && this.cartElement.children.length > 0) {
                iconElement = this.cartElement.children[0];
            }
            if (!iconElement) {
                iconElement = this.cartElement;
            }
            
            // Проверяем нет ли уже badge
            this.badge = iconElement.querySelector('.webview-app-badge');
            
            if (!this.badge) {
                this.badge = document.createElement('span');
                this.badge.className = 'webview-app-badge empty';
                this.badge.textContent = '0';
                this.badge.setAttribute('data-source', 'webview-app');
                
                iconElement.style.position = 'relative';
                iconElement.style.display = 'inline-block';
                iconElement.appendChild(this.badge);
                
                console.log('[WebView App Badge] ✓ Badge created');
            }
        },
        
        getCount: function() {
            // 1. Из data-cart-count
            const bodyCount = document.body.getAttribute('data-cart-count');
            if (bodyCount) {
                return parseInt(bodyCount) || 0;
            }
            
            // 2. Из sessionStorage WooCommerce
            try {
                const fragments = sessionStorage.getItem('wc_fragments');
                if (fragments) {
                    const data = JSON.parse(fragments);
                    if (data && data['.w-cart-quantity']) {
                        const div = document.createElement('div');
                        div.innerHTML = data['.w-cart-quantity'];
                        return parseInt(div.textContent) || 0;
                    }
                }
            } catch(e) {}
            
            // 3. Из header badge
            const headerBadge = document.querySelector('.w-cart .w-cart-quantity');
            if (headerBadge) {
                return parseInt(headerBadge.textContent) || 0;
            }
            
            return 0;
        },
        
        update: function() {
            if (!this.badge) return;
            
            const newCount = this.getCount();
            
            if (newCount !== this.currentCount) {
                console.log('[WebView App Badge] Updating:', this.currentCount, '→', newCount);
                this.currentCount = newCount;
                
                this.badge.textContent = newCount;
                
                if (newCount > 0) {
                    this.badge.classList.remove('empty');
                    this.badge.classList.add('has-items');
                } else {
                    this.badge.classList.add('empty');
                    this.badge.classList.remove('has-items');
                }
                
                // Уведомляем приложение (если есть bridge)
                if (typeof window.Android !== 'undefined' && window.Android.onCartUpdate) {
                    window.Android.onCartUpdate(newCount);
                }
                if (typeof window.webkit !== 'undefined' && window.webkit.messageHandlers && window.webkit.messageHandlers.cartUpdate) {
                    window.webkit.messageHandlers.cartUpdate.postMessage({count: newCount});
                }
            }
        },
        
        attachEvents: function() {
            var self = this;
            
            if (typeof jQuery !== 'undefined') {
                jQuery(document.body).on('wc_fragments_refreshed added_to_cart removed_from_cart updated_cart_totals', function(e) {
                    console.log('[WebView App Badge] WooCommerce event:', e.type);
                    setTimeout(function() { self.update(); }, 100);
                });
            }
            
            // Mutation observer для data-cart-count
            if (typeof MutationObserver !== 'undefined') {
                new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'data-cart-count') {
                            self.update();
                        }
                    });
                }).observe(document.body, {
                    attributes: true,
                    attributeFilter: ['data-cart-count']
                });
            }
        },
        
        startPolling: function() {
            var self = this;
            
            // Быстрые проверки первые 10 секунд
            for (var i = 1; i <= 10; i++) {
                setTimeout(function() { self.update(); }, i * 1000);
            }
            
            // Регулярная проверка каждые 5 секунд
            setInterval(function() { self.update(); }, 5000);
        }
    };
    
    // Инициализация
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            AppCartBadge.init();
        });
    } else {
        AppCartBadge.init();
    }
    
    // Экспорт для отладки
    window.AppCartBadge = AppCartBadge;
    
})();

