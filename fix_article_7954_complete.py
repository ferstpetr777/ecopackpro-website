#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import base64
import json
from datetime import datetime
from bs4 import BeautifulSoup
import re

# Конфигурация WordPress API
WORDPRESS_URL = "https://ecopackpro.ru"
APPLICATION_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"
USERNAME = "rtep1976@me.com"

class FeaturedImageFixer:
    def __init__(self, url, username, app_password):
        self.url = url.rstrip('/')
        self.username = username
        self.app_password = app_password
        
        # Создание заголовков для аутентификации
        credentials = f"{username}:{app_password}"
        encoded_credentials = base64.b64encode(credentials.encode()).decode()
        
        self.headers = {
            'Authorization': f'Basic {encoded_credentials}',
            'Content-Type': 'application/json',
            'User-Agent': 'WordPress-API-Client/1.0'
        }
    
    def test_connection(self):
        """Тестирование подключения к WordPress API"""
        try:
            response = requests.get(
                f"{self.url}/wp-json/wp/v2/users/me",
                headers=self.headers,
                timeout=30
            )
            
            if response.status_code == 200:
                user_data = response.json()
                print(f"✅ Подключение успешно! Пользователь: {user_data.get('name', 'Unknown')}")
                return True
            else:
                print(f"❌ Ошибка подключения: {response.status_code} - {response.text}")
                return False
                
        except Exception as e:
            print(f"❌ Ошибка подключения: {e}")
            return False
    
    def get_post_data(self, post_id):
        """Получение данных поста включая главное изображение"""
        try:
            # Получаем данные поста с главным изображением
            response = requests.get(
                f"{self.url}/wp-json/wp/v2/posts/{post_id}",
                headers=self.headers,
                params={'_embed': 'wp:featuredmedia'},
                timeout=30
            )
            
            if response.status_code == 200:
                return response.json()
            else:
                print(f"❌ Ошибка получения поста {post_id}: {response.status_code}")
                return None
                
        except Exception as e:
            print(f"❌ Ошибка получения поста {post_id}: {e}")
            return None
    
    def get_featured_image_info(self, post_data):
        """Извлечение информации о главном изображении"""
        featured_media = post_data.get('_embedded', {}).get('wp:featuredmedia', [])
        
        if featured_media:
            media_info = featured_media[0]
            return {
                'id': media_info.get('id'),
                'url': media_info.get('source_url'),
                'alt': media_info.get('alt_text', ''),
                'title': media_info.get('title', {}).get('rendered', ''),
                'caption': media_info.get('caption', {}).get('rendered', '')
            }
        return None
    
    def replace_all_placeholder_images(self, content, featured_img, post_title):
        """Замена всех placeholder изображений"""
        try:
            # Создаем правильный HTML для изображения
            correct_img_html = f'''<figure class="wp-block-image size-large" style="text-align: center; margin: 20px auto; max-width: 80%;">
<img alt="{post_title}" class="wp-image-{featured_img['id']}" decoding="async" height="1024" loading="lazy" sizes="auto, (max-width: 1536px) 100vw, 1536px" src="{featured_img['url']}" srcset="{featured_img['url']} 1536w, {featured_img['url']} 300w, {featured_img['url']} 1024w, {featured_img['url']} 600w, {featured_img['url']} 64w" style="border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: transform 0.3s ease, box-shadow 0.3s ease; max-width: 100%; height: auto;" width="1536"/>
</figure>'''
            
            # Используем регулярные выражения для поиска и замены всех placeholder изображений
            # Ищем все figure блоки с placeholder изображениями
            placeholder_pattern = r'<figure[^>]*>.*?<img[^>]*src="[^"]*Tvist-PRO[^"]*"[^>]*>.*?</figure>'
            
            # Заменяем все найденные placeholder блоки
            new_content = re.sub(placeholder_pattern, correct_img_html, content, flags=re.DOTALL)
            
            # Также заменяем отдельные img теги с placeholder
            img_placeholder_pattern = r'<img[^>]*src="[^"]*Tvist-PRO[^"]*"[^>]*>'
            new_content = re.sub(img_placeholder_pattern, f'<img alt="{post_title}" class="wp-image-{featured_img["id"]}" decoding="async" height="1024" loading="lazy" sizes="auto, (max-width: 1536px) 100vw, 1536px" src="{featured_img["url"]}" srcset="{featured_img["url"]} 1536w, {featured_img["url"]} 300w, {featured_img["url"]} 1024w, {featured_img["url"]} 600w, {featured_img["url"]} 64w" style="border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: transform 0.3s ease, box-shadow 0.3s ease; max-width: 100%; height: auto;" width="1536"/>', new_content)
            
            return new_content
            
        except Exception as e:
            print(f"❌ Ошибка замены изображений: {e}")
            return content
    
    def fix_post_images(self, post_id):
        """Исправление изображений в посте"""
        print(f"\n🔧 Исправление изображений в статье ID {post_id}")
        
        # Получаем данные поста
        post_data = self.get_post_data(post_id)
        if not post_data:
            return False
        
        post_title = post_data.get('title', {}).get('rendered', '')
        post_content = post_data.get('content', {}).get('rendered', '')
        
        print(f"📄 Заголовок: {post_title}")
        
        # Получаем информацию о главном изображении
        featured_img = self.get_featured_image_info(post_data)
        if not featured_img:
            print("❌ Главное изображение не найдено")
            return False
        
        print(f"🖼️  Главное изображение: {featured_img['url'].split('/')[-1]}")
        print(f"📝 Alt текст: {featured_img['alt']}")
        
        # Подсчитываем количество placeholder изображений
        placeholder_count = post_content.count('Tvist-PRO')
        print(f"🚨 Найдено placeholder изображений: {placeholder_count}")
        
        if placeholder_count == 0:
            print("✅ Placeholder изображений не найдено")
            return True
        
        # Заменяем все placeholder изображения
        new_content = self.replace_all_placeholder_images(post_content, featured_img, post_title)
        
        # Проверяем, были ли изменения
        if new_content == post_content:
            print("ℹ️  Изменений не требуется")
            return True
        
        # Подсчитываем количество замен
        new_placeholder_count = new_content.count('Tvist-PRO')
        replaced_count = placeholder_count - new_placeholder_count
        print(f"🔄 Заменено placeholder изображений: {replaced_count}")
        
        # Сохраняем изменения
        try:
            update_data = {
                'content': new_content
            }
            
            response = requests.post(
                f"{self.url}/wp-json/wp/v2/posts/{post_id}",
                headers=self.headers,
                json=update_data,
                timeout=30
            )
            
            if response.status_code == 200:
                print("✅ Изображения успешно исправлены!")
                return True
            else:
                print(f"❌ Ошибка сохранения: {response.status_code} - {response.text}")
                return False
                
        except Exception as e:
            print(f"❌ Ошибка сохранения: {e}")
            return False
    
    def verify_fix(self, post_id):
        """Проверка исправления"""
        print(f"\n🔍 Проверка исправления для статьи ID {post_id}")
        
        # Получаем обновленные данные поста
        post_data = self.get_post_data(post_id)
        if not post_data:
            return False
        
        post_title = post_data.get('title', {}).get('rendered', '')
        post_content = post_data.get('content', {}).get('rendered', '')
        
        # Получаем информацию о главном изображении
        featured_img = self.get_featured_image_info(post_data)
        if not featured_img:
            print("❌ Главное изображение не найдено")
            return False
        
        # Проверяем, остались ли placeholder изображения
        placeholder_count = post_content.count('Tvist-PRO')
        
        print(f"📊 Результат проверки:")
        print(f"  - Placeholder изображений: {placeholder_count}")
        
        if placeholder_count == 0:
            print("✅ Все placeholder изображения исправлены!")
            return True
        else:
            print("❌ Остались placeholder изображения")
            return False

def main():
    """Основная функция"""
    print("🔧 ПОЛНОЕ ИСПРАВЛЕНИЕ СТАТЬИ 7954 - ПАКЕТЫ ИЗ ВОЗДУШНО-ПУЗЫРЬКОВОЙ ПЛЁНКИ")
    print("=" * 80)
    
    # Создание экземпляра исправителя
    fixer = FeaturedImageFixer(WORDPRESS_URL, USERNAME, APPLICATION_PASSWORD)
    
    # Тестирование подключения
    print("\n🔍 Тестирование подключения к WordPress API...")
    if not fixer.test_connection():
        print("❌ Не удалось подключиться к WordPress API")
        return
    
    # Исправление статьи 7954
    test_post_id = 7954
    print(f"\n🧪 ИСПРАВЛЕНИЕ СТАТЬИ ID {test_post_id}")
    print("=" * 50)
    
    # Применяем исправления
    fix_success = fixer.fix_post_images(test_post_id)
    
    if fix_success:
        print("\n🔍 ПРОВЕРКА РЕЗУЛЬТАТА:")
        verify_success = fixer.verify_fix(test_post_id)
        
        if verify_success:
            print(f"\n✅ Статья ID {test_post_id} успешно исправлена!")
            print(f"🔗 Проверьте результат: {WORDPRESS_URL}/?p={test_post_id}&preview=true")
        else:
            print(f"\n❌ Исправление не завершено для статьи ID {test_post_id}")
    else:
        print(f"\n❌ Ошибка применения изменений к статье ID {test_post_id}")

if __name__ == "__main__":
    main()
