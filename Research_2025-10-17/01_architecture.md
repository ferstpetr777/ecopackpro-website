# Исследование архитектуры (первый проход)

## Хостинг и веб-сервер
- FASTPANEL (Ubuntu 22.04), Nginx фронт.
- Конфиг: /etc/nginx/fastpanel2-sites/fastuser/ecopackpro.ru.conf; include: /etc/nginx/fastpanel2-sites/fastuser/ecopackpro.ru.includes
- root: /var/www/fastuser/data/www/ecopackpro.ru

## Приложение
- CMS: WordPress (wp-admin, wp-includes, wp-content)
- Тема: Impreza + Impreza-child
- Магазин: WooCommerce
- Мультиязычность: WPML

## Ключевые плагины
- Yoast SEO (wordpress-seo, wordpress-seo-premium, wpseo-*), Redis Cache (redis-cache), Autoptimize, Duplicator Pro, Query Monitor, ACF Pro, Contact Form 7, WooCommerce-1C exchange, Wishlist, YML for Yandex Market, WebP Converter, Ajax Search for Woo

## MU-plugins
- contact-extra-email.php, force-mail-from.php, health-check-troubleshooting-mode.php и др.

## Настройки WP (wp-config.php)
- DB_NAME: m1shqamai2_worp6; префикс: wp_
- WP_DEBUG=true; WP_DEBUG_LOG=true; DISPLAY=false
- FS_METHOD=direct (без FTP)
- DISABLE_WP_CRON=true (используется системный cron)
- WP Mail SMTP принудительно настроен (SMTP ecopackpro.ru:587, TLS)

## Веб-сервер
- Nginx include добавляет preload для CSS/шрифта
- .htaccess: базовые WordPress правила, gzip, кеш, security headers, 301 редиректы

## Файловая структура (верхний уровень)
- Тема: wp-content/themes/Impreza, Impreza-child
- Плагины: wp-content/plugins (46 директорий)
- Медиа: wp-content/uploads
- Кэш: wp-content/cache, object-cache.php, advanced-cache.php

## Ссылки
- Витрина: https://ecopackpro.ru/
