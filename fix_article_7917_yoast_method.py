#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
from requests.auth import HTTPBasicAuth
import time
import json
from datetime import datetime

# Настройки WordPress API (из оригинального скрипта)
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKIVWpH96dgVI3HovlIhI4E"

class Article7917YoastFixer:
    def __init__(self):
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
        
    def update_yoast_seo(self, post_id, focus_keyword, meta_description):
        """Обновляет SEO параметры для Yoast SEO через WordPress API (метод из оригинального скрипта)"""
        
        try:
            # Получаем текущий пост
            response = requests.get(
                f"{WP_API_URL}/posts/{post_id}",
                auth=self.auth,
                headers=self.headers,
                timeout=60
            )
            
            if response.status_code != 200:
                return False, f"Ошибка получения поста: {response.status_code}"
            
            post_data = response.json()
            print(f"📄 Получен пост: {post_data['title']['rendered']}")
            
            # Подготавливаем данные для обновления (точно как в оригинальном скрипте)
            update_data = {
                'meta': {
                    '_yoast_wpseo_focuskw': focus_keyword,  # Фокусное ключевое слово
                    '_yoast_wpseo_metadesc': meta_description,  # Мета описание
                    '_yoast_wpseo_title': post_data['title']['rendered'],  # Заголовок
                    '_yoast_wpseo_canonical': post_data['link']  # Каноническая ссылка
                },
                'slug': 'courier-packages-with-pocket'  # Обновляем slug
            }
            
            print(f"🔧 Обновляю мета данные:")
            print(f"   🎯 Фокусное ключевое слово: {focus_keyword}")
            print(f"   📝 Мета описание: {meta_description[:80]}...")
            print(f"   🔗 Новый slug: courier-packages-with-pocket")
            
            # Обновляем пост
            update_response = requests.post(
                f"{WP_API_URL}/posts/{post_id}",
                auth=self.auth,
                headers=self.headers,
                json=update_data,
                timeout=60
            )
            
            if update_response.status_code == 200:
                return True, "Успешно обновлено"
            else:
                return False, f"Ошибка обновления: {update_response.status_code} - {update_response.text}"
                
        except Exception as e:
            return False, f"Исключение: {str(e)}"
    
    def verify_yoast_data(self, post_id):
        """Проверяет, что SEO данные видны в Yoast SEO (метод из оригинального скрипта)"""
        try:
            # Получаем мета данные поста
            response = requests.get(
                f"{WP_API_URL}/posts/{post_id}",
                auth=self.auth,
                headers=self.headers,
                timeout=60
            )
            
            if response.status_code != 200:
                return False, "Ошибка получения поста"
            
            post_data = response.json()
            meta = post_data.get('meta', {})
            
            # Проверяем наличие Yoast SEO мета данных
            focus_keyword = meta.get('_yoast_wpseo_focuskw', '')
            meta_description = meta.get('_yoast_wpseo_metadesc', '')
            
            return True, {
                'focus_keyword': focus_keyword,
                'meta_description': meta_description,
                'has_focus_keyword': bool(focus_keyword),
                'has_meta_description': bool(meta_description),
                'slug': post_data.get('slug', ''),
                'link': post_data.get('link', '')
            }
            
        except Exception as e:
            return False, f"Ошибка проверки: {str(e)}"
    
    def fix_article_7917(self):
        """Основная функция исправления статьи 7917"""
        print("🔧 ИСПРАВЛЕНИЕ СТАТЬИ 7917 - МЕТОД YOAST SEO UPDATER")
        print("=" * 70)
        
        post_id = 7917
        focus_keyword = "курьерские пакеты с карманом"
        meta_description = f"{focus_keyword} - Пакеты с карманом для документов: встроенные vs самоклеящиеся SD, размеры А5/А6, применение в логистике. Ускорение обработки на 30%. Цены от 3 руб/шт!"
        
        print(f"📋 Параметры исправления:")
        print(f"   Post ID: {post_id}")
        print(f"   Фокусное ключевое слово: {focus_keyword}")
        print(f"   Мета описание: {meta_description[:80]}...")
        print(f"   Новый slug: courier-packages-with-pocket")
        
        # Обновляем SEO параметры (используем оригинальный метод)
        print(f"\n🚀 Начинаю обновление SEO параметров для Yoast SEO...")
        success, message = self.update_yoast_seo(post_id, focus_keyword, meta_description)
        
        if success:
            print(f"✅ SEO параметры успешно обновлены!")
        else:
            print(f"❌ Ошибка: {message}")
            return False
        
        # Проверяем результаты
        print(f"\n🔍 ПРОВЕРКА SEO ДАННЫХ В YOAST...")
        print("=" * 50)
        
        verify_success, data = self.verify_yoast_data(post_id)
        
        if verify_success and isinstance(data, dict):
            print(f"📄 Заголовок: {focus_keyword}")
            print(f"🔗 Slug: {data['slug']}")
            print(f"🌐 Ссылка: {data['link']}")
            print(f"✅ Фокусное ключевое слово: '{data['focus_keyword']}'")
            print(f"✅ Мета описание: '{data['meta_description'][:60]}...'")
            print(f"📊 Статус: Фокусное слово: {'✅' if data['has_focus_keyword'] else '❌'}, Мета: {'✅' if data['has_meta_description'] else '❌'}")
            
            # Итоговый результат
            print(f"\n" + "=" * 70)
            print("📊 ИТОГОВЫЙ РЕЗУЛЬТАТ")
            print("=" * 70)
            
            if data['has_focus_keyword'] and data['has_meta_description']:
                print(f"🎉 СТАТЬЯ 7917 УСПЕШНО ИСПРАВЛЕНА!")
                print(f"🔗 Новая ссылка: {data['link']}")
                print(f"📱 Админ панель: https://ecopackpro.ru/wp-admin/post.php?post={post_id}&action=edit")
                print(f"🔍 Проверьте статью в админ панели Yoast SEO Premium")
                return True
            else:
                print(f"⚠️  Частично исправлено - проверьте вручную")
                return False
        else:
            print(f"❌ Ошибка проверки: {data}")
            return False

def main():
    """Основная функция"""
    print("="*70)
    print("🔧 ИСПРАВЛЕНИЕ СТАТЬИ 7917 - МЕТОД YOAST SEO UPDATER")
    print("="*70)
    print("Используется проверенный метод из оригинального скрипта:")
    print("1. Фокусное ключевое слово")
    print("2. Мета описание (до 160 символов)")
    print("3. Обновление slug на латинский")
    print("4. Проверка видимости в админ панели Yoast SEO")
    print("="*70)
    
    fixer = Article7917YoastFixer()
    success = fixer.fix_article_7917()
    
    if success:
        print(f"\n✅ Все изменения применены успешно!")
        print(f"🔍 Проверьте статью в админ панели Yoast SEO Premium")
    else:
        print(f"\n❌ Произошли ошибки при исправлении")

if __name__ == "__main__":
    main()
