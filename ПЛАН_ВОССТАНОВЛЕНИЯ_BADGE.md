# 🎯 ПЛАН ВОССТАНОВЛЕНИЯ BADGE КОРЗИНЫ

**Проблема:** После всех моих изменений badge корзины не показывается  
**Причина:** Я добавил КОСТЫЛИ которые блокируют стандартный механизм WooCommerce

---

## 📊 ТЕКУЩАЯ СИТУАЦИЯ

### Проверка через браузер показала:

**Desktop:**
- Badge найден: `.w-cart-quantity`
- Значение: "0"
- Display: `none` (корзина пустая)
- ✅ Скрывается когда пустая - это ПРАВИЛЬНО

**Mobile:**
- Верхнее меню: "Корзина 0" - показывает
- Нижняя панель: МОЙ `.mobile-cart-badge` - показывает "0"
- ⚠️  Использую костыль вместо стандартного механизма!

**Корзина пустая:**
- В базе данных / сессии нет товаров
- На скриншотах пользователя есть 15 товаров
- Видимо другая сессия/браузер

---

## 🔧 МОИ КОСТЫЛИ (ЧТО НУЖНО УБРАТЬ/ИСПРАВИТЬ)

### 1. `ecopackpro-mobile-cart-bridge.php`
**Что делает:** Подключает мои JS/CSS файлы  
**Проблема:** Загружает костыли  
**Решение:** ОТКЛЮЧИТЬ или оставить ТОЛЬКО WebView API

### 2. `fix-all-cart-issues.css`
**Что делает:**
- Скрывает `.w-cart-quantity` в mobile footer
- Стилизует `.mobile-cart-badge`
- Изменяет цвет на зелёный

**Проблема:**
```css
.l-subheader.at_bottom .w-cart-quantity {
    display: none !important;  ← БЛОКИРУЕТ стандартный badge!
}
```

**Решение:** УБРАТЬ правила которые скрывают стандартные элементы!

### 3. `mobile-cart-badge-v3-green.js`
**Что делает:** Создаёт `.mobile-cart-badge` в mobile footer  
**Проблема:** КОСТЫЛЬ! Стандартного badge там НЕТ!  
**Решение:** ОТКЛЮЧИТЬ и использовать стандартный механизм

### 4. `restore-native-woocommerce-cart.php`
**Что делает:**
- Dequeue `cart-counter-fix.js` (скрипт темы!)
- Добавляет свой inline скрипт

**Проблема:** Отключил СТАНДАРТНЫЙ скрипт темы!  
**Решение:** ВЕРНУТЬ `cart-counter-fix.js`!

### 5. `footer.php` (закомментировал inline скрипт)
**Проблема:** Возможно там был НУЖНЫЙ код!  
**Решение:** ВЕРНУТЬ как было!

---

## ✅ РЕШЕНИЕ (ПОШАГОВЫЙ ПЛАН)

### ШАГ 1: БЭКАП ТЕКУЩЕГО СОСТОЯНИЯ
```bash
cp /var/www/fastuser/data/www/ecopackpro.ru/wp-content/mu-plugins/ecopackpro-mobile-cart-bridge.php \
   /var/www/fastuser/data/www/ecopackpro.ru/wp-content/mu-plugins/ecopackpro-mobile-cart-bridge.php.BACKUP-BEFORE-RESTORE

cp /var/www/fastuser/data/www/ecopackpro.ru/wp-content/plugins/us-core/templates/footer.php \
   /var/www/fastuser/data/www/ecopackpro.ru/wp-content/plugins/us-core/templates/footer.php.CURRENT
```

### ШАГ 2: ОТКЛЮЧИТЬ МОИ КОСТЫЛИ

**Переименовать в .DISABLED:**
```bash
mv /var/www/fastuser/data/www/ecopackpro.ru/wp-content/mu-plugins/ecopackpro-mobile-cart-bridge.php \
   /var/www/fastuser/data/www/ecopackpro.ru/wp-content/mu-plugins/ecopackpro-mobile-cart-bridge.php.DISABLED

mv /var/www/fastuser/data/www/ecopackpro.ru/wp-content/mu-plugins/restore-native-woocommerce-cart.php \
   /var/www/fastuser/data/www/ecopackpro.ru/wp-content/mu-plugins/restore-native-woocommerce-cart.php.DISABLED
```

### ШАГ 3: ВЕРНУТЬ СТАНДАРТНЫЕ СКРИПТЫ

**Включить `cart-counter-fix.js`:**
```bash
mv /var/www/fastuser/data/www/ecopackpro.ru/wp-content/themes/Impreza/cart-counter-fix.js.DISABLED-20251030 \
   /var/www/fastuser/data/www/ecopackpro.ru/wp-content/themes/Impreza/cart-counter-fix.js
```

**Восстановить `footer.php`:**
- Найти backup: `footer.php.backup-20251030_122218`
- Вернуть inline скрипт

### ШАГ 4: ОЧИСТИТЬ ВСЕ КЕШИ
```bash
redis-cli FLUSHALL
rm -rf /var/cache/nginx/microcache/*
sudo -u www-data wp cache flush
systemctl reload nginx
```

### ШАГ 5: ТЕСТИРОВАНИЕ

**Desktop:**
1. Открыть https://ecopackpro.ru/
2. Добавить товар в корзину
3. ✅ Badge в header должен показать количество

**Mobile:**
1. Открыть https://ecopackpro.ru/ (мобильный браузер)
2. Добавить товар
3. ✅ Проверить ВЕРХНЕЕ меню
4. ✅ Проверить НИЖНЮЮ панель (если есть badge)

### ШАГ 6: ЕСЛИ НЕ РАБОТАЕТ

**Анализ:**
- Проверить console.log в браузере
- Проверить `sessionStorage['wc_fragments']`
- Проверить события `wc_fragments_refreshed`
- Проверить что `cart-fragments.js` загружен

**Минимальные исправления:**
- ТОЛЬКО если стандартный механизм не работает
- БЕЗ костылей!
- Используя WooCommerce hooks и filters

---

## 🔍 ЧТО ПРОВЕРИТЬ В БРАУЗЕРЕ

### Console commands:

```javascript
// 1. Проверка фрагментов
JSON.parse(sessionStorage.getItem('wc_fragments'))

// 2. Проверка cookie
document.cookie.split(';').filter(c => c.includes('cart'))

// 3. Проверка badge элемента
document.querySelector('.w-cart-quantity')

// 4. Принудительное обновление
jQuery(document.body).trigger('wc_fragment_refresh')

// 5. Проверка скриптов
typeof wc_cart_fragments_params
```

---

## 📱 СТАНДАРТНЫЕ ЭЛЕМЕНТЫ ТЕМЫ IMPREZA

### Desktop/Mobile Header:
```html
<div class="w-cart dropdown_mdesign ush_cart_1">
  <div class="w-cart-h">
    <a class="w-cart-link" href="/cart/">
      <span class="w-cart-icon">
        <i class="far fa-shopping-cart"></i>
      </span>
      <span class="w-cart-quantity">15</span> ← СТАНДАРТНЫЙ BADGE
    </a>
  </div>
</div>
```

### Mobile Footer:
```html
<div class="l-subheader at_bottom">
  <div class="w-text ush_text_8">
    <a href="/cart/">
      <span class="w-text-value">Корзина</span>
      <!-- НЕТ СТАНДАРТНОГО BADGE! -->
    </a>
  </div>
</div>
```

**Вывод:** В mobile footer изначально НЕТ badge! Это нормально!

---

## 🎯 ИТОГОВЫЙ ПЛАН

1. ✅ **Отключить все мои костыли**
2. ✅ **Вернуть стандартные скрипты темы**
3. ✅ **Очистить кеши**
4. 🧪 **Тестировать на реальной сессии с товарами**
5. 🔧 **Если нужно - минимальные исправления**
6. 📸 **Скриншоты ВСЕХ меню**
7. 📄 **Финальный отчёт**

---

**Готов начать восстановление!**

