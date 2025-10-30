/**
 * Mobile Footer Cart Badge - ИСПРАВЛЕННАЯ ВЕРСИЯ
 * Гарантированное создание badge для элемента "Корзина" в нижней мобильной навигации
 * 
 * Версия: 2.0
 * Дата: 2025-10-30
 */

(function() {
    'use strict';
    
    console.log('[Mobile Footer Badge v2] Starting initialization...');
    
    var CartBadgeManager = {
        
        badge: null,
        cartElement: null,
        currentCount: 0,
        initialized: false,
        
        // Инициализация
        init: function() {
            var self = this;
            
            console.log('[Mobile Footer Badge v2] Initializing...');
            
            // Пробуем найти и создать badge несколько раз
            var attempts = 0;
            var maxAttempts = 20;
            
            var tryInit = setInterval(function() {
                attempts++;
                
                if (self.findAndCreateBadge() || attempts >= maxAttempts) {
                    clearInterval(tryInit);
                    
                    if (self.initialized) {
                        console.log('[Mobile Footer Badge v2] ✅ Successfully initialized!');
                        
                        // Начинаем обновление
                        self.update();
                        self.attachEvents();
                        self.startPolling();
                    } else {
                        console.warn('[Mobile Footer Badge v2] ❌ Failed to initialize after', attempts, 'attempts');
                    }
                }
            }, 200);
        },
        
        // Поиск элемента корзины и создание badge
        findAndCreateBadge: function() {
            if (this.initialized) return true;
            
            // Способ 1: Поиск по классу ush_vwrapper_5
            var wrapper = document.querySelector('.ush_vwrapper_5');
            if (wrapper) {
                var link = wrapper.querySelector('a');
                if (link && (link.href.includes('/cart') || link.textContent.includes('Корзина'))) {
                    this.cartElement = link;
                    console.log('[Mobile Footer Badge v2] ✓ Found via .ush_vwrapper_5');
                }
            }
            
            // Способ 2: Поиск по тексту "Корзина" в нижней навигации
            if (!this.cartElement) {
                var bottomNav = document.querySelector('.l-subheader.at_bottom');
                if (bottomNav) {
                    var links = bottomNav.querySelectorAll('a');
                    for (var i = 0; i < links.length; i++) {
                        if (links[i].textContent.trim() === 'Корзина' || links[i].href.includes('/cart')) {
                            this.cartElement = links[i];
                            console.log('[Mobile Footer Badge v2] ✓ Found via bottom nav text');
                            break;
                        }
                    }
                }
            }
            
            // Способ 3: Поиск всех ссылок на /cart/ в hidden_for_laptops
            if (!this.cartElement) {
                var hiddenElements = document.querySelectorAll('.hidden_for_laptops a[href*="/cart"]');
                if (hiddenElements.length > 0) {
                    this.cartElement = hiddenElements[hiddenElements.length - 1]; // Берем последний (обычно в footer)
                    console.log('[Mobile Footer Badge v2] ✓ Found via hidden_for_laptops');
                }
            }
            
            if (!this.cartElement) {
                return false;  // Не нашли, попробуем еще раз
            }
            
            // Создаем badge
            return this.createBadge();
        },
        
        // Создание badge элемента
        createBadge: function() {
            if (!this.cartElement) {
                console.warn('[Mobile Footer Badge v2] Cannot create badge: cartElement not found');
                return false;
            }
            
            // Проверяем нет ли уже badge
            this.badge = this.cartElement.querySelector('.mobile-cart-badge');
            
            if (!this.badge) {
                // Создаем badge
                this.badge = document.createElement('span');
                this.badge.className = 'mobile-cart-badge empty';
                this.badge.textContent = '0';
                this.badge.setAttribute('data-source', 'mobile-footer-v2');
                
                // Устанавливаем position на родителе
                this.cartElement.style.position = 'relative';
                this.cartElement.style.display = 'inline-block';
                
                // Добавляем badge
                this.cartElement.appendChild(this.badge);
                
                console.log('[Mobile Footer Badge v2] ✓ Badge element created and appended');
            } else {
                console.log('[Mobile Footer Badge v2] Badge already exists');
            }
            
            this.initialized = true;
            return true;
        },
        
        // Получение количества товаров
        getCount: function() {
            var count = 0;
            
            // 1. Из data-атрибута body
            var bodyCount = document.body.getAttribute('data-cart-count');
            if (bodyCount && parseInt(bodyCount) > 0) {
                count = parseInt(bodyCount);
                console.log('[Mobile Footer Badge v2] Count from body:', count);
                return count;
            }
            
            // 2. Из WooCommerce fragments
            try {
                var wcFragments = sessionStorage.getItem('wc_fragments');
                if (wcFragments) {
                    var data = JSON.parse(wcFragments);
                    if (data && data['.w-cart-quantity']) {
                        var tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data['.w-cart-quantity'];
                        count = parseInt(tempDiv.textContent) || 0;
                        if (count > 0) {
                            console.log('[Mobile Footer Badge v2] Count from WC fragments:', count);
                            return count;
                        }
                    }
                }
            } catch(e) {
                console.warn('[Mobile Footer Badge v2] Error reading fragments:', e);
            }
            
            // 3. Из header .w-cart-quantity
            var headerBadge = document.querySelector('.w-cart .w-cart-quantity');
            if (headerBadge) {
                count = parseInt(headerBadge.textContent) || 0;
                if (count > 0) {
                    console.log('[Mobile Footer Badge v2] Count from header badge:', count);
                    return count;
                }
            }
            
            // 4. Из localStorage
            try {
                var stored = localStorage.getItem('ecopackpro_cart_count');
                if (stored) {
                    count = parseInt(stored) || 0;
                    if (count > 0) {
                        console.log('[Mobile Footer Badge v2] Count from localStorage:', count);
                        return count;
                    }
                }
            } catch(e) {}
            
            console.log('[Mobile Footer Badge v2] Final count:', count);
            return count;
        },
        
        // Обновление badge
        update: function() {
            if (!this.badge || !this.initialized) {
                console.warn('[Mobile Footer Badge v2] Cannot update: not initialized');
                return;
            }
            
            var newCount = this.getCount();
            
            if (newCount !== this.currentCount) {
                console.log('[Mobile Footer Badge v2] Updating:', this.currentCount, '→', newCount);
                this.currentCount = newCount;
                
                this.badge.textContent = newCount;
                
                if (newCount > 0) {
                    this.badge.classList.remove('empty');
                    this.badge.classList.add('has-items');
                    this.badge.style.display = 'inline-block';
                } else {
                    this.badge.classList.add('empty');
                    this.badge.classList.remove('has-items');
                    this.badge.style.display = 'none';
                }
            }
        },
        
        // Подписка на события
        attachEvents: function() {
            var self = this;
            
            // WooCommerce события
            if (typeof jQuery !== 'undefined') {
                jQuery(document.body).on('wc_fragments_refreshed added_to_cart removed_from_cart updated_cart_totals', function(e) {
                    console.log('[Mobile Footer Badge v2] WooCommerce event:', e.type);
                    setTimeout(function() { self.update(); }, 100);
                });
            }
            
            // Custom события
            document.addEventListener('ecopackpro:cartUpdate', function(e) {
                console.log('[Mobile Footer Badge v2] Custom cart update');
                self.update();
            });
            
            // Изменение data-атрибута
            if (typeof MutationObserver !== 'undefined') {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'data-cart-count') {
                            console.log('[Mobile Footer Badge v2] data-cart-count changed');
                            self.update();
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
            
            // Быстрая проверка первые 10 секунд
            for (var i = 1; i <= 10; i++) {
                setTimeout(function() {
                    self.update();
                }, i * 1000);
            }
            
            // Регулярная проверка каждые 5 секунд
            setInterval(function() {
                self.update();
            }, 5000);
        }
    };
    
    // Инициализация после загрузки DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            CartBadgeManager.init();
        });
    } else {
        // DOM уже загружен
        CartBadgeManager.init();
    }
    
    // Экспорт для отладки
    window.CartBadgeManager = CartBadgeManager;
    
})();

