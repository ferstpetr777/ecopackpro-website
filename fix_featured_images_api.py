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
    
    def extract_images_from_content(self, content):
        """Извлечение всех изображений из контента"""
        try:
            soup = BeautifulSoup(content, 'html.parser')
            images = []
            
            for img_tag in soup.find_all('img'):
                img_info = {
                    'tag': img_tag,
                    'src': img_tag.get('src', ''),
                    'alt': img_tag.get('alt', ''),
                    'class': img_tag.get('class', []),
                    'title': img_tag.get('title', ''),
                    'parent_tag': img_tag.parent.name if img_tag.parent else 'unknown'
                }
                images.append(img_info)
            
            return images
            
        except Exception as e:
            print(f"❌ Ошибка извлечения изображений: {e}")
            return []
    
    def find_placeholder_images(self, images):
        """Поиск placeholder изображений"""
        placeholder_indicators = ['Tvist-PRO', 'placeholder', 'default']
        placeholders = []
        
        for img_info in images:
            src = img_info['src'].lower()
            if any(indicator.lower() in src for indicator in placeholder_indicators):
                placeholders.append(img_info)
        
        return placeholders
    
    def find_wrong_images(self, images, featured_image_url):
        """Поиск неправильных изображений (не соответствующих главному)"""
        if not featured_image_url:
            return []
        
        featured_filename = featured_image_url.split('/')[-1]
        wrong_images = []
        
        for img_info in images:
            img_filename = img_info['src'].split('/')[-1]
            if img_filename != featured_filename:
                wrong_images.append(img_info)
        
        return wrong_images
    
    def create_correct_image_html(self, featured_img, post_title):
        """Создание правильного HTML для изображения"""
        if not featured_img:
            return None
        
        # Создаем alt текст на основе заголовка статьи
        alt_text = post_title
        
        # Создаем HTML для изображения в стиле WordPress
        img_html = f'''<figure class="wp-block-image size-large" style="text-align: center; margin: 20px auto; max-width: 80%;">
<img alt="{alt_text}" class="wp-image-{featured_img['id']}" decoding="async" height="1024" loading="lazy" sizes="auto, (max-width: 1536px) 100vw, 1536px" src="{featured_img['url']}" srcset="{featured_img['url']} 1536w, {featured_img['url']} 300w, {featured_img['url']} 1024w, {featured_img['url']} 600w, {featured_img['url']} 64w" style="border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: transform 0.3s ease, box-shadow 0.3s ease; max-width: 100%; height: auto;" width="1536"/>
</figure>'''
        
        return img_html
    
    def fix_post_images(self, post_id, preview_mode=True):
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
        
        # Извлекаем изображения из контента
        images = self.extract_images_from_content(post_content)
        print(f"🔍 Найдено изображений в контенте: {len(images)}")
        
        # Ищем проблемные изображения
        placeholder_images = self.find_placeholder_images(images)
        wrong_images = self.find_wrong_images(images, featured_img['url'])
        
        print(f"🚨 Найдено placeholder изображений: {len(placeholder_images)}")
        print(f"🚨 Найдено неправильных изображений: {len(wrong_images)}")
        
        if not placeholder_images and not wrong_images:
            print("✅ Проблемных изображений не найдено")
            return True
        
        # Создаем правильный HTML для изображения
        correct_img_html = self.create_correct_image_html(featured_img, post_title)
        if not correct_img_html:
            print("❌ Не удалось создать правильный HTML для изображения")
            return False
        
        # Обновляем контент
        new_content = post_content
        
        # Заменяем placeholder изображения
        for placeholder in placeholder_images:
            placeholder_html = str(placeholder['tag'])
            print(f"🔄 Замена placeholder: {placeholder['src'].split('/')[-1]}")
            new_content = new_content.replace(placeholder_html, correct_img_html)
        
        # Заменяем неправильные изображения
        for wrong_img in wrong_images:
            wrong_html = str(wrong_img['tag'])
            print(f"🔄 Замена неправильного изображения: {wrong_img['src'].split('/')[-1]}")
            new_content = new_content.replace(wrong_html, correct_img_html)
        
        # Проверяем, были ли изменения
        if new_content == post_content:
            print("ℹ️  Изменений не требуется")
            return True
        
        if preview_mode:
            print("\n📋 ПРЕВЬЮ РЕЖИМ - изменения не будут сохранены")
            print("=" * 60)
            print("Было найдено проблемных изображений:")
            for placeholder in placeholder_images:
                print(f"  - Placeholder: {placeholder['src'].split('/')[-1]}")
            for wrong_img in wrong_images:
                print(f"  - Неправильное: {wrong_img['src'].split('/')[-1]}")
            print(f"\nБудет заменено на: {featured_img['url'].split('/')[-1]}")
            print("=" * 60)
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
        
        # Извлекаем изображения из контента
        images = self.extract_images_from_content(post_content)
        
        # Ищем проблемные изображения
        placeholder_images = self.find_placeholder_images(images)
        wrong_images = self.find_wrong_images(images, featured_img['url'])
        
        print(f"📊 Результат проверки:")
        print(f"  - Всего изображений в контенте: {len(images)}")
        print(f"  - Placeholder изображений: {len(placeholder_images)}")
        print(f"  - Неправильных изображений: {len(wrong_images)}")
        
        if not placeholder_images and not wrong_images:
            print("✅ Все изображения исправлены!")
            return True
        else:
            print("❌ Остались проблемные изображения")
            return False

def main():
    """Основная функция"""
    print("🔧 ИСПРАВЛЕНИЕ ГЛАВНЫХ ИЗОБРАЖЕНИЙ ЧЕРЕЗ WORDPRESS API")
    print("=" * 70)
    
    # Создание экземпляра исправителя
    fixer = FeaturedImageFixer(WORDPRESS_URL, USERNAME, APPLICATION_PASSWORD)
    
    # Тестирование подключения
    print("\n🔍 Тестирование подключения к WordPress API...")
    if not fixer.test_connection():
        print("❌ Не удалось подключиться к WordPress API")
        return
    
    # Тестирование на статье 7954
    test_post_id = 7954
    print(f"\n🧪 ТЕСТИРОВАНИЕ НА СТАТЬЕ ID {test_post_id}")
    print("=" * 50)
    
    # Сначала запускаем в режиме превью
    print("\n📋 РЕЖИМ ПРЕВЬЮ:")
    preview_success = fixer.fix_post_images(test_post_id, preview_mode=True)
    
    if not preview_success:
        print("❌ Ошибка в режиме превью")
        return
    
    # Спрашиваем подтверждение
    print(f"\n❓ Применить изменения к статье ID {test_post_id}? (y/n): ", end="")
    confirm = input().lower().strip()
    
    if confirm == 'y':
        print("\n💾 ПРИМЕНЕНИЕ ИЗМЕНЕНИЙ:")
        fix_success = fixer.fix_post_images(test_post_id, preview_mode=False)
        
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
    else:
        print("\n❌ Изменения отменены")

if __name__ == "__main__":
    main()
