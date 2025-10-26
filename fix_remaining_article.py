#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import base64

# Конфигурация WordPress API
WORDPRESS_URL = "https://ecopackpro.ru"
APPLICATION_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"
USERNAME = "rtep1976@me.com"
BLOG_CATEGORY_ID = 649
PROBLEM_POST_ID = 7940

def fix_remaining_article():
    """Исправление оставшейся статьи с ошибкой"""
    
    # Создание заголовков для аутентификации
    credentials = f"{USERNAME}:{APPLICATION_PASSWORD}"
    encoded_credentials = base64.b64encode(credentials.encode()).decode()
    
    headers = {
        'Authorization': f'Basic {encoded_credentials}',
        'Content-Type': 'application/json',
        'User-Agent': 'WordPress-API-Client/1.0'
    }
    
    print(f"🔧 Исправление статьи ID {PROBLEM_POST_ID}...")
    
    try:
        # Получаем текущие данные поста
        response = requests.get(
            f"{WORDPRESS_URL}/wp-json/wp/v2/posts/{PROBLEM_POST_ID}",
            headers=headers,
            timeout=30
        )
        
        if response.status_code != 200:
            print(f"❌ Ошибка получения поста {PROBLEM_POST_ID}: {response.status_code}")
            return False
        
        post_data = response.json()
        post_title = post_data['title']['rendered']
        current_categories = post_data.get('categories', [])
        
        print(f"📄 Заголовок: {post_title}")
        print(f"📂 Текущие категории: {current_categories}")
        
        # Добавляем категорию "блог", если её нет
        if BLOG_CATEGORY_ID not in current_categories:
            current_categories.append(BLOG_CATEGORY_ID)
            
            # Обновляем пост
            update_data = {
                'categories': current_categories
            }
            
            update_response = requests.post(
                f"{WORDPRESS_URL}/wp-json/wp/v2/posts/{PROBLEM_POST_ID}",
                headers=headers,
                json=update_data,
                timeout=30
            )
            
            if update_response.status_code == 200:
                print(f"✅ Категория 'блог' успешно добавлена!")
                return True
            else:
                print(f"❌ Ошибка обновления: {update_response.status_code} - {update_response.text}")
                return False
        else:
            print(f"ℹ️  Пост уже имеет категорию 'блог'")
            return True
            
    except Exception as e:
        print(f"❌ Ошибка: {e}")
        return False

if __name__ == "__main__":
    print("🔧 ИСПРАВЛЕНИЕ ОСТАВШЕЙСЯ СТАТЬИ")
    print("=" * 50)
    
    if fix_remaining_article():
        print("\n✅ Статья успешно исправлена!")
    else:
        print("\n❌ Не удалось исправить статью")
