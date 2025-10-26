#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import base64
import json
from datetime import datetime
from bs4 import BeautifulSoup

# Конфигурация WordPress API
WORDPRESS_URL = "https://ecopackpro.ru"
APPLICATION_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"
USERNAME = "rtep1976@me.com"

def verify_article_7954():
    """Финальная проверка статьи 7954"""
    print("🔍 ФИНАЛЬНАЯ ПРОВЕРКА СТАТЬИ 7954")
    print("=" * 50)
    
    # Создание заголовков для аутентификации
    credentials = f"{USERNAME}:{APPLICATION_PASSWORD}"
    encoded_credentials = base64.b64encode(credentials.encode()).decode()
    
    headers = {
        'Authorization': f'Basic {encoded_credentials}',
        'Content-Type': 'application/json',
        'User-Agent': 'WordPress-API-Client/1.0'
    }
    
    try:
        # Получаем данные поста
        response = requests.get(
            f"{WORDPRESS_URL}/wp-json/wp/v2/posts/7954",
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
        
        if featured_media:
            media_info = featured_media[0]
            featured_img = {
                'id': media_info.get('id'),
                'url': media_info.get('source_url'),
                'alt': media_info.get('alt_text', ''),
                'title': media_info.get('title', {}).get('rendered', ''),
            }
            
            print(f"🖼️  Главное изображение: {featured_img['url'].split('/')[-1]}")
            print(f"📝 Alt текст: {featured_img['alt']}")
        else:
            print("❌ Главное изображение не найдено")
            return False
        
        # Анализируем контент
        soup = BeautifulSoup(post_content, 'html.parser')
        images = soup.find_all('img')
        
        print(f"\n📊 Анализ контента:")
        print(f"  - Всего изображений в контенте: {len(images)}")
        
        # Проверяем на placeholder изображения
        placeholder_count = post_content.count('Tvist-PRO')
        print(f"  - Placeholder изображений: {placeholder_count}")
        
        # Проверяем на правильные изображения
        correct_img_count = post_content.count(featured_img['url'].split('/')[-1])
        print(f"  - Правильных изображений: {correct_img_count}")
        
        # Анализируем каждое изображение
        print(f"\n🔍 Детальный анализ изображений:")
        for i, img in enumerate(images, 1):
            src = img.get('src', '')
            alt = img.get('alt', '')
            img_filename = src.split('/')[-1] if src else 'unknown'
            
            print(f"  {i}. {img_filename}")
            print(f"     Alt: {alt}")
            
            if 'Tvist-PRO' in src:
                print(f"     ❌ Placeholder изображение")
            elif featured_img['url'].split('/')[-1] in src:
                print(f"     ✅ Правильное изображение")
            else:
                print(f"     ⚠️  Другое изображение")
        
        # Итоговый результат
        print(f"\n📋 ИТОГОВЫЙ РЕЗУЛЬТАТ:")
        if placeholder_count == 0 and correct_img_count > 0:
            print("✅ СТАТЬЯ ИСПРАВЛЕНА УСПЕШНО!")
            print("✅ Все placeholder изображения заменены на правильные")
            print(f"✅ Визуально отображается: {featured_img['url'].split('/')[-1]}")
            print(f"🔗 Проверьте результат: {WORDPRESS_URL}/?p=7954&preview=true")
            return True
        else:
            print("❌ СТАТЬЯ НЕ ИСПРАВЛЕНА ПОЛНОСТЬЮ")
            if placeholder_count > 0:
                print(f"❌ Остались placeholder изображения: {placeholder_count}")
            if correct_img_count == 0:
                print("❌ Правильные изображения не найдены")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка проверки: {e}")
        return False

if __name__ == "__main__":
    verify_article_7954()
