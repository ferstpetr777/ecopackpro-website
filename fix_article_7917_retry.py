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

def fix_article_7917():
    """Повторное исправление статьи 7917"""
    print("🔧 ПОВТОРНОЕ ИСПРАВЛЕНИЕ СТАТЬИ 7917")
    print("=" * 50)
    
    # Создание заголовков для аутентификации
    credentials = f"{USERNAME}:{APPLICATION_PASSWORD}"
    encoded_credentials = base64.b64encode(credentials.encode()).decode()
    
    headers = {
        'Authorization': f'Basic {encoded_credentials}',
        'Content-Type': 'application/json',
        'User-Agent': 'WordPress-API-Client/1.0'
    }
    
    post_id = 7917
    
    try:
        # Получаем данные поста
        response = requests.get(
            f"{WORDPRESS_URL}/wp-json/wp/v2/posts/{post_id}",
            headers=headers,
            params={'_embed': 'wp:featuredmedia'},
            timeout=30
        )
        
        if response.status_code != 200:
            print(f"❌ Ошибка получения поста: {response.status_code}")
            return False
        
        post_data = response.json()
        post_title = post_data.get('title', {}).get('rendered', '')
        post_content = post_data.get('content', {}).get('rendered', '')
        
        print(f"📄 Заголовок: {post_title}")
        
        # Получаем информацию о главном изображении
        featured_media = post_data.get('_embedded', {}).get('wp:featuredmedia', [])
        
        if not featured_media:
            print("❌ Главное изображение не найдено")
            return False
        
        media_info = featured_media[0]
        featured_img = {
            'id': media_info.get('id'),
            'url': media_info.get('source_url'),
            'alt': media_info.get('alt_text', ''),
            'title': media_info.get('title', {}).get('rendered', ''),
        }
        
        print(f"🖼️  Главное изображение: {featured_img['url'].split('/')[-1]}")
        
        # Ищем неправильные изображения
        soup = BeautifulSoup(post_content, 'html.parser')
        wrong_images = []
        featured_filename = featured_img['url'].split('/')[-1]
        
        for img_tag in soup.find_all('img'):
            src = img_tag.get('src', '')
            img_filename = src.split('/')[-1] if src else ''
            
            if img_filename and img_filename != featured_filename and 'Tvist-PRO' not in img_filename:
                wrong_images.append({
                    'tag': img_tag,
                    'src': src,
                    'filename': img_filename
                })
        
        print(f"🚨 Найдено неправильных изображений: {len(wrong_images)}")
        
        if not wrong_images:
            print("✅ Неправильных изображений не найдено")
            return True
        
        # Выводим список найденных неправильных изображений
        for img in wrong_images:
            print(f"   - {img['filename']}")
        
        # Создаем правильный HTML для изображения
        correct_img_html = f'''<figure class="wp-block-image size-large" style="text-align: center; margin: 20px auto; max-width: 80%;">
<img alt="{post_title}" class="wp-image-{featured_img['id']}" decoding="async" height="1024" loading="lazy" sizes="auto, (max-width: 1536px) 100vw, 1536px" src="{featured_img['url']}" srcset="{featured_img['url']} 1536w, {featured_img['url']} 300w, {featured_img['url']} 1024w, {featured_img['url']} 600w, {featured_img['url']} 64w" style="border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: transform 0.3s ease, box-shadow 0.3s ease; max-width: 100%; height: auto;" width="1536"/>
</figure>'''
        
        # Заменяем неправильные изображения
        new_content = post_content
        
        # Заменяем figure блоки с неправильными изображениями
        figure_pattern = r'<figure[^>]*>.*?<img[^>]*src="[^"]*"[^>]*>.*?</figure>'
        
        def replace_figure(match):
            figure_html = match.group(0)
            if featured_filename in figure_html:
                return figure_html
            else:
                return correct_img_html
        
        new_content = re.sub(figure_pattern, replace_figure, new_content, flags=re.DOTALL)
        
        # Заменяем отдельные img теги с неправильными изображениями
        img_pattern = r'<img[^>]*src="[^"]*"[^>]*>'
        
        def replace_img(match):
            img_html = match.group(0)
            if featured_filename in img_html:
                return img_html
            else:
                return f'<img alt="{post_title}" class="wp-image-{featured_img["id"]}" decoding="async" height="1024" loading="lazy" sizes="auto, (max-width: 1536px) 100vw, 1536px" src="{featured_img["url"]}" srcset="{featured_img["url"]} 1536w, {featured_img["url"]} 300w, {featured_img["url"]} 1024w, {featured_img["url"]} 600w, {featured_img["url"]} 64w" style="border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.15); transition: transform 0.3s ease, box-shadow 0.3s ease; max-width: 100%; height: auto;" width="1536"/>'
        
        new_content = re.sub(img_pattern, replace_img, new_content)
        
        # Проверяем, были ли изменения
        if new_content == post_content:
            print("ℹ️  Изменений не требуется")
            return True
        
        print(f"🔄 Заменено неправильных изображений: {len(wrong_images)}")
        
        # Сохраняем изменения
        update_data = {
            'content': new_content
        }
        
        response = requests.post(
            f"{WORDPRESS_URL}/wp-json/wp/v2/posts/{post_id}",
            headers=headers,
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
        print(f"❌ Ошибка: {e}")
        return False

if __name__ == "__main__":
    fix_article_7917()
