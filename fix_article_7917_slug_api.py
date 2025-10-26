#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import base64
import json
from datetime import datetime

# Конфигурация WordPress API
WORDPRESS_URL = "https://ecopackpro.ru"
APPLICATION_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"
USERNAME = "rtep1976@me.com"

class Article7917SlugUpdater:
    def __init__(self):
        self.wp_url = WORDPRESS_URL
        self.username = USERNAME
        self.app_password = APPLICATION_PASSWORD
        
        # Создание заголовков для аутентификации
        credentials = f"{self.username}:{self.app_password}"
        encoded_credentials = base64.b64encode(credentials.encode()).decode()
        
        self.headers = {
            'Authorization': f'Basic {encoded_credentials}',
            'Content-Type': 'application/json',
            'User-Agent': 'WordPress-API-Client/1.0'
        }
    
    def update_post_slug_via_api(self, post_id, new_slug):
        """Обновление slug через WordPress API"""
        try:
            # Получаем текущий пост
            response = requests.get(
                f"{self.wp_url}/wp-json/wp/v2/posts/{post_id}",
                headers=self.headers,
                timeout=30
            )
            
            if response.status_code != 200:
                print(f"❌ Ошибка получения поста: {response.status_code}")
                return False
            
            post_data = response.json()
            print(f"📄 Текущий slug: {post_data.get('slug', 'НЕ НАЙДЕН')}")
            
            # Обновляем slug
            update_data = {
                'slug': new_slug
            }
            
            update_response = requests.post(
                f"{self.wp_url}/wp-json/wp/v2/posts/{post_id}",
                headers=self.headers,
                json=update_data,
                timeout=30
            )
            
            if update_response.status_code == 200:
                updated_data = update_response.json()
                print(f"✅ Slug обновлен через API: {updated_data.get('slug', 'НЕ НАЙДЕН')}")
                return True
            else:
                print(f"❌ Ошибка обновления slug: {update_response.status_code} - {update_response.text}")
                return False
                
        except Exception as e:
            print(f"❌ Ошибка обновления slug: {e}")
            return False
    
    def verify_final_result(self, post_id):
        """Финальная проверка всех изменений"""
        print(f"\n🔍 ФИНАЛЬНАЯ ПРОВЕРКА СТАТЬИ {post_id}")
        print("=" * 50)
        
        try:
            # Проверяем через API
            response = requests.get(
                f"{self.wp_url}/wp-json/wp/v2/posts/{post_id}",
                headers=self.headers,
                timeout=30
            )
            
            if response.status_code == 200:
                api_data = response.json()
                print(f"📄 Заголовок: {api_data.get('title', {}).get('rendered', 'НЕ НАЙДЕН')}")
                print(f"🔗 Slug: {api_data.get('slug', 'НЕ НАЙДЕН')}")
                print(f"🌐 Ссылка: {api_data.get('link', 'НЕ НАЙДЕНА')}")
                
                # Проверяем мета данные
                meta = api_data.get('meta', {})
                print(f"🎯 Фокусное ключевое слово: {meta.get('_yoast_wpseo_focuskw', 'НЕ УСТАНОВЛЕНО')}")
                print(f"📝 Мета описание: {meta.get('_yoast_wpseo_metadesc', 'НЕ УСТАНОВЛЕНО')}")
                print(f"🏷️  SEO заголовок: {meta.get('_yoast_wpseo_title', 'НЕ УСТАНОВЛЕНО')}")
                print(f"🔗 Каноническая ссылка: {meta.get('_yoast_wpseo_canonical', 'НЕ УСТАНОВЛЕНА')}")
                
                return True
            else:
                print(f"❌ Ошибка API проверки: {response.status_code}")
                return False
                
        except Exception as e:
            print(f"❌ Ошибка API проверки: {e}")
            return False
    
    def fix_article_7917_slug(self):
        """Исправление slug статьи 7917"""
        print("🔧 ИСПРАВЛЕНИЕ SLUG СТАТЬИ 7917 ЧЕРЕЗ API")
        print("=" * 50)
        
        post_id = 7917
        new_slug = "courier-packages-with-pocket"
        
        print(f"📋 Параметры исправления:")
        print(f"   Post ID: {post_id}")
        print(f"   Новый slug: {new_slug}")
        
        # Обновляем slug через API
        print(f"\n🔧 Обновление slug через WordPress API...")
        slug_success = self.update_post_slug_via_api(post_id, new_slug)
        
        # Финальная проверка
        print(f"\n🔍 Финальная проверка...")
        verify_success = self.verify_final_result(post_id)
        
        # Итоговый результат
        print(f"\n" + "=" * 50)
        print("📊 ИТОГОВЫЙ РЕЗУЛЬТАТ")
        print("=" * 50)
        
        print(f"🔗 Slug обновлён: {'✅' if slug_success else '❌'}")
        print(f"🔍 Проверка изменений: {'✅' if verify_success else '❌'}")
        
        if slug_success and verify_success:
            print(f"\n🎉 SLUG СТАТЬИ 7917 УСПЕШНО ОБНОВЛЕН!")
            print(f"🔗 Новая ссылка: {self.wp_url}/{new_slug}/")
            print(f"📱 Админ панель: {self.wp_url}/wp-admin/post.php?post={post_id}&action=edit")
            return True
        else:
            print(f"\n❌ ОБНОВЛЕНИЕ SLUG ЗАВЕРШИЛОСЬ С ОШИБКАМИ")
            return False

def main():
    """Основная функция"""
    updater = Article7917SlugUpdater()
    success = updater.fix_article_7917_slug()
    
    if success:
        print(f"\n✅ Slug обновлен успешно!")
        print(f"🔍 Проверьте статью в админ панели")
    else:
        print(f"\n❌ Произошли ошибки при обновлении slug")

if __name__ == "__main__":
    main()
