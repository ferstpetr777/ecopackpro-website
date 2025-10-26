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
    
    def find_and_replace_placeholder_image(self, content, featured_img, post_title):
        """Поиск и замена placeholder изображения"""
        try:
            soup = BeautifulSoup(content, 'html.parser')
            
            # Ищем все изображения
            img_tags = soup.find_all('img')
            
            for img_tag in img_tags:
                src = img_tag.get('src', '')
                
                # Проверяем, является ли это placeholder изображением
                if 'Tvist-PRO' in src or 'placeholder' in src.lower():
                    print(f"🔄 Найден placeholder: {src.split('/')[-1]}")
                    
                    # Создаем новое изображение
                    new_img = soup.new_tag('img')
                    new_img['alt'] = post_title
                    new_img['class'] = f"wp-image-{featured_img['id']}"
                    new_img['decoding'] = "async"
                    new_img['height'] = "1024"
                    new_img['loading'] = "lazy"
                    new_img['sizes'] = "auto, (max-width: 1536px) 100vw, 1536px"
                    new_img['src'] = featured_img['url']
                    new_img['srcset'] = f"{featured_img['url']} 1536w, {featured_img['url']} 300w, {featured_img['url']} 1024w, {featured_img['url']} 600w, {featured_img['url']} 64w"
                    new_img['style'] = "border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: transform 0.3s ease, box-shadow 0.3s ease; max-width: 100%; height: auto;"
                    new_img['width'] = "1536"
                    
                    # Заменяем старое изображение новым
                    img_tag.replace_with(new_img)
                    
                    print(f"✅ Заменено на: {featured_img['url'].split('/')[-1]}")
            
            return str(soup)
            
        except Exception as e:
            print(f"❌ Ошибка замены изображения: {e}")
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
        
        # Проверяем, есть ли placeholder в контенте
        if 'Tvist-PRO' not in post_content:
            print("✅ Placeholder изображений не найдено")
            return True
        
        print("🚨 Найден placeholder в контенте")
        
        # Заменяем placeholder изображение
        new_content = self.find_and_replace_placeholder_image(post_content, featured_img, post_title)
        
        # Проверяем, были ли изменения
        if new_content == post_content:
            print("ℹ️  Изменений не требуется")
            return True
        
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
    print("🔧 ТОЧНОЕ ИСПРАВЛЕНИЕ СТАТЬИ 7954 - ПАКЕТЫ ИЗ ВОЗДУШНО-ПУЗЫРЬКОВОЙ ПЛЁНКИ")
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
