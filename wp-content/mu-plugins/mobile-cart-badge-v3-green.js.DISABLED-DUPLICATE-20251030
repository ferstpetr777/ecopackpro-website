/**
 * Mobile Cart Badge v3.0 - ЗЕЛЁНЫЙ + ПРАВИЛЬНАЯ СИНХРОНИЗАЦИЯ
 * 
 * Исправляет:
 * 1. Цвет badge - ЗЕЛЁНЫЙ (как у избранного)
 * 2. Позиционирование - вместе с иконкой
 * 3. Синхронизация количества между страницами
 * 
 * Версия: 3.0
 * Дата: 2025-10-30
 */

(function() {
    'use strict';
    
    console.log('[Mobile Cart Badge v3 GREEN] Starting...');
    
    var MobileCartBadge = {
        badge: null,
        cartLink: null,
        initialized: false,
        currentCount: 0,
        storageKey: 'ecopackpro_cart_count_sync',
        
        init: function() {
            var self = this;
            console.log('[v3] Initializing...');
            
            // Пытаемся найти элемент несколько раз
            var attempts = 0;
            var maxAttempts = 30;
            
            var tryInit = setInterval(function() {
                attempts++;
                
                if (self.findCartElement() || attempts >= maxAttempts) {
                    clearInterval(tryInit);
                    
                    if (self.initialized) {
                        console.log('[v3] ✅ Initialized successfully!');
                        self.createBadge();
                        self.updateFromServer();
                        self.attachEvents();
                        self.startSync();
                    } else {
                        console.warn('[v3] ❌ Failed after', attempts, 'attempts');
                    }
                }
            }, 200);
        },
        
        findCartElement: function() {
            if (this.initialized) return true;
            
            // Способ 1: По классу w-text и тексту
            var textElements = document.querySelectorAll('.w-text.ush_text_8');
            for (var i = 0; i < textElements.length; i++) {
                var link = textElements[i].querySelector('a');
                if (link && link.textContent.trim() === 'Корзина') {
                    this.cartLink = link;
                    console.log('[v3] ✓ Found via w-text.ush_text_8');
                    this.initialized = true;
                    return true;
                }
            }
            
            // Способ 2: По ush_vwrapper_5
            var wrapper = document.querySelector('.ush_vwrapper_5');
            if (wrapper) {
                var link = wrapper.querySelector('a[href*="/cart"]');
                if (link) {
                    this.cartLink = link;
                    console.log('[v3] ✓ Found via ush_vwrapper_5');
                    this.initialized = true;
                    return true;
                }
            }
            
            // Способ 3: Любая ссылка с текстом "Корзина" в нижней навигации
            var bottomNav = document.querySelector('.l-subheader.at_bottom');
            if (bottomNav) {
                var links = bottomNav.querySelectorAll('a');
                for (var j = 0; j < links.length; j++) {
                    if (links[j].textContent.trim() === 'Корзина') {
                        this.cartLink = links[j];
                        console.log('[v3] ✓ Found via bottom nav');
                        this.initialized = true;
                        return true;
                    }
                }
            }
            
            return false;
        },
        
        createBadge: function() {
            if (!this.cartLink) {
                console.error('[v3] Cannot create badge: cartLink not found');
                return;
            }
            
            // Проверяем есть ли уже badge
            this.badge = this.cartLink.querySelector('.mobile-cart-badge');
            
            if (!this.badge) {
                // Ищем ИКОНКУ внутри ссылки корзины
                var iconElement = this.cartLink.querySelector('i, svg, .icon, [class*="icon"]');
                
                // Если не нашли, ищем первый child (обычно иконка)
                if (!iconElement && this.cartLink.children.length > 0) {
                    iconElement = this.cartLink.children[0];
                }
                
                // Если всё ещё нет, используем саму ссылку
                if (!iconElement) {
                    iconElement = this.cartLink;
                }
                
                this.badge = document.createElement('span');
                this.badge.className = 'mobile-cart-badge empty';
                this.badge.textContent = '0';
                this.badge.setAttribute('data-source', 'v3-green');
                
                // Устанавливаем position на ИКОНКЕ (не на всей ссылке!)
                iconElement.style.position = 'relative';
                iconElement.style.display = 'inline-block';
                
                // Добавляем badge к ИКОНКЕ
                iconElement.appendChild(this.badge);
                
                console.log('[v3] ✓ Badge added to ICON element (not text!)');
            } else {
                console.log('[v3] Badge already exists, reusing');
            }
        },
        
        getCountFromServer: function() {
            var count = 0;
            
            // 1. Из data-cart-count на body
            var bodyCount = document.body.getAttribute('data-cart-count');
            if (bodyCount) {
                count = parseInt(bodyCount) || 0;
                if (count > 0) {
                    console.log('[v3] Count from body:', count);
                    return count;
                }
            }
            
            // 2. Из localStorage (синхронизация между страницами)
            try {
                var stored = localStorage.getItem(this.storageKey);
                if (stored) {
                    var data = JSON.parse(stored);
                    var age = Date.now() - (data.timestamp || 0);
                    
                    // Используем если данные свежие (< 5 минут)
                    if (age < 5 * 60 * 1000) {
                        count = parseInt(data.count) || 0;
                        if (count > 0) {
                            console.log('[v3] Count from localStorage:', count, '(age:', Math.round(age/1000), 's)');
                            return count;
                        }
                    }
                }
            } catch(e) {
                console.warn('[v3] localStorage error:', e);
            }
            
            // 3. Из WooCommerce fragments
            try {
                var fragments = sessionStorage.getItem('wc_fragments');
                if (fragments) {
                    var data = JSON.parse(fragments);
                    if (data && data['.w-cart-quantity']) {
                        var div = document.createElement('div');
                        div.innerHTML = data['.w-cart-quantity'];
                        count = parseInt(div.textContent) || 0;
                        if (count > 0) {
                            console.log('[v3] Count from WC fragments:', count);
                            return count;
                        }
                    }
                }
            } catch(e) {
                console.warn('[v3] sessionStorage error:', e);
            }
            
            // 4. Из header badge
            var headerBadge = document.querySelector('.w-cart .w-cart-quantity');
            if (headerBadge) {
                count = parseInt(headerBadge.textContent) || 0;
                if (count > 0) {
                    console.log('[v3] Count from header:', count);
                    return count;
                }
            }
            
            console.log('[v3] Final count:', count);
            return count;
        },
        
        updateFromServer: function() {
            if (!this.badge) return;
            
            var newCount = this.getCountFromServer();
            
            // Сохраняем в localStorage для синхронизации
            try {
                localStorage.setItem(this.storageKey, JSON.stringify({
                    count: newCount,
                    timestamp: Date.now()
                }));
            } catch(e) {}
            
            if (newCount !== this.currentCount) {
                console.log('[v3] Updating:', this.currentCount, '→', newCount);
                this.currentCount = newCount;
                this.updateBadge(newCount);
            }
        },
        
        updateBadge: function(count) {
            if (!this.badge) return;
            
            this.badge.textContent = count;
            
            if (count > 0) {
                this.badge.classList.remove('empty');
                this.badge.classList.add('has-items');
                this.badge.style.display = 'inline-block';
            } else {
                this.badge.classList.add('empty');
                this.badge.classList.remove('has-items');
                this.badge.style.display = 'none';
            }
        },
        
        attachEvents: function() {
            var self = this;
            
            // WooCommerce события
            if (typeof jQuery !== 'undefined') {
                jQuery(document.body).on('wc_fragments_refreshed added_to_cart removed_from_cart updated_cart_totals updated_wc_div', function(e) {
                    console.log('[v3] WooCommerce event:', e.type);
                    setTimeout(function() {
                        self.updateFromServer();
                    }, 100);
                });
            }
            
            // Изменение data-cart-count
            if (typeof MutationObserver !== 'undefined') {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'data-cart-count') {
                            console.log('[v3] data-cart-count changed');
                            self.updateFromServer();
                        }
                    });
                });
                
                observer.observe(document.body, {
                    attributes: true,
                    attributeFilter: ['data-cart-count']
                });
            }
            
            // localStorage изменения (синхронизация между вкладками)
            window.addEventListener('storage', function(e) {
                if (e.key === self.storageKey) {
                    console.log('[v3] Storage changed from another tab');
                    self.updateFromServer();
                }
            });
            
            // Custom события
            document.addEventListener('ecopackpro:cartUpdate', function() {
                console.log('[v3] Custom cart update');
                self.updateFromServer();
            });
        },
        
        startSync: function() {
            var self = this;
            
            // Быстрая проверка первые 10 секунд
            for (var i = 1; i <= 10; i++) {
                setTimeout(function() {
                    self.updateFromServer();
                }, i * 1000);
            }
            
            // Регулярная проверка каждые 3 секунды
            setInterval(function() {
                self.updateFromServer();
            }, 3000);
        }
    };
    
    // Инициализация
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            MobileCartBadge.init();
        });
    } else {
        MobileCartBadge.init();
    }
    
    // Экспорт для отладки
    window.MobileCartBadge = MobileCartBadge;
    
})();

