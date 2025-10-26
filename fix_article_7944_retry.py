#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
from requests.auth import HTTPBasicAuth
import time
import re

# Настройки WordPress API
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKIVWpH96dgVI3HovlIhI4E"

def transliterate_to_latin(text):
    """Транслитерация русского текста в латинский"""
    translit_map = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo', 'ж': 'zh',
        'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm', 'н': 'n', 'о': 'o',
        'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u', 'ф': 'f', 'х': 'h', 'ц': 'ts',
        'ч': 'ch', 'ш': 'sh', 'щ': 'sch', 'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu',
        'я': 'ya', ' ': '-', '_': '-'
    }
    
    result = text.lower()
    for ru, en in translit_map.items():
        result = result.replace(ru, en)
    
    # Удаляем лишние символы
    result = re.sub(r'[^a-z0-9\-]', '', result)
    # Удаляем множественные дефисы
    result = re.sub(r'-+', '-', result)
    # Удаляем дефисы в начале и конце
    result = result.strip('-')
    
    return result

def fix_article_7944():
    """Повторное исправление статьи 7944"""
    print("🔧 ПОВТОРНОЕ ИСПРАВЛЕНИЕ СТАТЬИ 7944")
    print("=" * 50)
    
    post_id = 7944
    auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
    headers = {'Content-Type': 'application/json'}
    
    try:
        # Получаем текущий пост
        response = requests.get(
            f"{WP_API_URL}/posts/{post_id}",
            auth=auth,
            headers=headers,
            timeout=60
        )
        
        if response.status_code != 200:
            print(f"❌ Ошибка получения поста: {response.status_code}")
            return False
        
        post_data = response.json()
        print(f"📄 Получен пост: {post_data['title']['rendered']}")
        
        # Параметры для исправления
        focus_keyword = "инструмент для опломбирования бочек ¾ дюйма"
        new_slug = transliterate_to_latin(focus_keyword)
        meta_description = f"{focus_keyword} - Пломбиратор для резьбы ¾\" (19 мм): для сливных отверстий, малых бочек 50-100 л. Ручные и рычажные модели. Цены от 5000₽. Доставка по России."
        
        print(f"🎯 Фокусное ключевое слово: {focus_keyword}")
        print(f"🔗 Новый slug: {new_slug}")
        print(f"📝 Новое мета-описание: {meta_description[:80]}...")
        
        # Подготавливаем данные для обновления
        update_data = {
            'meta': {
                '_yoast_wpseo_focuskw': focus_keyword,
                '_yoast_wpseo_metadesc': meta_description,
                '_yoast_wpseo_title': post_data['title']['rendered'],
                '_yoast_wpseo_canonical': f"https://ecopackpro.ru/{new_slug}/"
            },
            'slug': new_slug
        }
        
        # Обновляем пост
        update_response = requests.post(
            f"{WP_API_URL}/posts/{post_id}",
            auth=auth,
            headers=headers,
            json=update_data,
            timeout=60
        )
        
        if update_response.status_code == 200:
            print("✅ Статья 7944 успешно исправлена!")
            return True
        else:
            print(f"❌ Ошибка обновления: {update_response.status_code} - {update_response.text}")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка: {e}")
        return False

if __name__ == "__main__":
    fix_article_7944()
