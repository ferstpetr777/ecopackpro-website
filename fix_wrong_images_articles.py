#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import base64
import json
import re
import time
from datetime import datetime
from bs4 import BeautifulSoup

# Конфигурация WordPress API
WORDPRESS_URL = "https://ecopackpro.ru"
APPLICATION_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"
USERNAME = "rtep1976@me.com"

# Список статей с неправильными изображениями
WRONG_IMAGES_ARTICLES = [
    7915, 7917, 7932, 7934, 7938, 7939, 7945, 7946, 7947, 7948, 7949, 7956
]

class WrongImagesFixer:
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
        
        # Статистика
        self.stats = {
            'total': 0,
            'success': 0,
            'failed': 0,
            'skipped': 0,
            'errors': []
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
            }
        return None
    
    def find_wrong_images_in_content(self, content, featured_img_url):
        """Поиск неправильных изображений в контенте"""
        try:
            soup = BeautifulSoup(content, 'html.parser')
            wrong_images = []
            
            featured_filename = featured_img_url.split('/')[-1] if featured_img_url else ''
            
            for img_tag in soup.find_all('img'):
                src = img_tag.get('src', '')
                img_filename = src.split('/')[-1] if src else ''
                
                # Проверяем, является ли изображение неправильным
                if img_filename and img_filename != featured_filename:
                    # Исключаем placeholder изображения (они уже исправлены)
                    if 'Tvist-PRO' not in img_filename:
                        wrong_images.append({
                            'tag': img_tag,
                            'src': src,
                            'filename': img_filename
                        })
            
            return wrong_images
            
        except Exception as e:
            print(f"❌ Ошибка поиска неправильных изображений: {e}")
            return []
    
    def replace_wrong_images_with_correct(self, content, featured_img, post_title):
        """Замена неправильных изображений на правильные"""
        try:
            # Создаем правильный HTML для изображения
            correct_img_html = f'''<figure class="wp-block-image size-large" style="text-align: center; margin: 20px auto; max-width: 80%;">
<img alt="{post_title}" class="wp-image-{featured_img['id']}" decoding="async" height="1024" loading="lazy" sizes="auto, (max-width: 1536px) 100vw, 1536px" src="{featured_img['url']}" srcset="{featured_img['url']} 1536w, {featured_img['url']} 300w, {featured_img['url']} 1024w, {featured_img['url']} 600w, {featured_img['url']} 64w" style="border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: transform 0.3s ease, box-shadow 0.3s ease; max-width: 100%; height: auto;" width="1536"/>
</figure>'''
            
            # Ищем все figure блоки с изображениями (кроме правильного)
            featured_filename = featured_img['url'].split('/')[-1]
            
            # Паттерн для поиска figure блоков с неправильными изображениями
            figure_pattern = r'<figure[^>]*>.*?<img[^>]*src="[^"]*"[^>]*>.*?</figure>'
            
            def replace_figure(match):
                figure_html = match.group(0)
                # Проверяем, содержит ли этот figure правильное изображение
                if featured_filename in figure_html:
                    return figure_html  # Возвращаем как есть, если это правильное изображение
                else:
                    return correct_img_html  # Заменяем на правильное
            
            # Заменяем все figure блоки с неправильными изображениями
            new_content = re.sub(figure_pattern, replace_figure, content, flags=re.DOTALL)
            
            # Также заменяем отдельные img теги с неправильными изображениями
            img_pattern = r'<img[^>]*src="[^"]*"[^>]*>'
            
            def replace_img(match):
                img_html = match.group(0)
                # Проверяем, является ли это правильным изображением
                if featured_filename in img_html:
                    return img_html  # Возвращаем как есть, если это правильное изображение
                else:
                    # Заменяем на правильное изображение без figure
                    return f'<img alt="{post_title}" class="wp-image-{featured_img["id"]}" decoding="async" height="1024" loading="lazy" sizes="auto, (max-width: 1536px) 100vw, 1536px" src="{featured_img["url"]}" srcset="{featured_img["url"]} 1536w, {featured_img["url"]} 300w, {featured_img["url"]} 1024w, {featured_img["url"]} 600w, {featured_img["url"]} 64w" style="border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: transform 0.3s ease, box-shadow 0.3s ease; max-width: 100%; height: auto;" width="1536"/>'
            
            new_content = re.sub(img_pattern, replace_img, new_content)
            
            return new_content
            
        except Exception as e:
            print(f"❌ Ошибка замены изображений: {e}")
            return content
    
    def fix_single_article(self, post_id):
        """Исправление одной статьи"""
        print(f"\n🔧 Исправление статьи ID {post_id}")
        
        try:
            # Получаем данные поста
            post_data = self.get_post_data(post_id)
            if not post_data:
                self.stats['failed'] += 1
                self.stats['errors'].append(f"ID {post_id}: Ошибка получения данных")
                return False
            
            post_title = post_data.get('title', {}).get('rendered', '')
            post_content = post_data.get('content', {}).get('rendered', '')
            
            print(f"📄 Заголовок: {post_title}")
            
            # Получаем информацию о главном изображении
            featured_img = self.get_featured_image_info(post_data)
            if not featured_img:
                print("❌ Главное изображение не найдено")
                self.stats['failed'] += 1
                self.stats['errors'].append(f"ID {post_id}: Главное изображение не найдено")
                return False
            
            print(f"🖼️  Главное изображение: {featured_img['url'].split('/')[-1]}")
            
            # Ищем неправильные изображения в контенте
            wrong_images = self.find_wrong_images_in_content(post_content, featured_img['url'])
            print(f"🚨 Найдено неправильных изображений: {len(wrong_images)}")
            
            if not wrong_images:
                print("✅ Неправильных изображений не найдено")
                self.stats['skipped'] += 1
                return True
            
            # Выводим список найденных неправильных изображений
            for img in wrong_images:
                print(f"   - {img['filename']}")
            
            # Заменяем неправильные изображения на правильные
            new_content = self.replace_wrong_images_with_correct(post_content, featured_img, post_title)
            
            # Проверяем, были ли изменения
            if new_content == post_content:
                print("ℹ️  Изменений не требуется")
                self.stats['skipped'] += 1
                return True
            
            print(f"🔄 Заменено неправильных изображений: {len(wrong_images)}")
            
            # Сохраняем изменения
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
                self.stats['success'] += 1
                return True
            else:
                print(f"❌ Ошибка сохранения: {response.status_code} - {response.text}")
                self.stats['failed'] += 1
                self.stats['errors'].append(f"ID {post_id}: Ошибка сохранения - {response.status_code}")
                return False
                
        except Exception as e:
            print(f"❌ Ошибка обработки статьи {post_id}: {e}")
            self.stats['failed'] += 1
            self.stats['errors'].append(f"ID {post_id}: {str(e)}")
            return False
    
    def fix_all_articles(self):
        """Исправление всех статей с неправильными изображениями"""
        print("🔧 ИСПРАВЛЕНИЕ СТАТЕЙ С НЕПРАВИЛЬНЫМИ ИЗОБРАЖЕНИЯМИ")
        print("=" * 60)
        
        self.stats['total'] = len(WRONG_IMAGES_ARTICLES)
        
        for i, post_id in enumerate(WRONG_IMAGES_ARTICLES, 1):
            print(f"\n📋 {i}/{len(WRONG_IMAGES_ARTICLES)}")
            
            # Исправляем статью
            self.fix_single_article(post_id)
            
            # Небольшая пауза между запросами
            if i < len(WRONG_IMAGES_ARTICLES):
                time.sleep(1)
        
        return self.stats
    
    def print_final_report(self):
        """Вывод итогового отчета"""
        print("\n" + "=" * 60)
        print("📊 ИТОГОВЫЙ ОТЧЕТ")
        print("=" * 60)
        
        print(f"📚 Всего статей: {self.stats['total']}")
        print(f"✅ Успешно исправлено: {self.stats['success']}")
        print(f"❌ Ошибок: {self.stats['failed']}")
        print(f"⏭️  Пропущено: {self.stats['skipped']}")
        
        if self.stats['total'] > 0:
            success_rate = (self.stats['success'] / (self.stats['total'] - self.stats['skipped'])) * 100 if (self.stats['total'] - self.stats['skipped']) > 0 else 0
            print(f"📊 Процент успешности: {success_rate:.1f}%")
        
        if self.stats['errors']:
            print(f"\n🚨 ОШИБКИ:")
            for error in self.stats['errors']:
                print(f"  - {error}")
        
        print(f"\n🔗 Ссылки на исправленные статьи:")
        for post_id in WRONG_IMAGES_ARTICLES:
            print(f"  https://ecopackpro.ru/?p={post_id}&preview=true")

def main():
    """Основная функция"""
    print("🔧 ИСПРАВЛЕНИЕ СТАТЕЙ С НЕПРАВИЛЬНЫМИ ИЗОБРАЖЕНИЯМИ")
    print("=" * 80)
    
    # Создание экземпляра исправителя
    fixer = WrongImagesFixer(WORDPRESS_URL, USERNAME, APPLICATION_PASSWORD)
    
    # Тестирование подключения
    print("\n🔍 Тестирование подключения к WordPress API...")
    if not fixer.test_connection():
        print("❌ Не удалось подключиться к WordPress API")
        return
    
    # Исправление всех статей
    print(f"\n🎯 Начинаем исправление {len(WRONG_IMAGES_ARTICLES)} статей с неправильными изображениями...")
    stats = fixer.fix_all_articles()
    
    # Вывод отчета
    fixer.print_final_report()
    
    print(f"\n✅ Исправление завершено!")

if __name__ == "__main__":
    main()
