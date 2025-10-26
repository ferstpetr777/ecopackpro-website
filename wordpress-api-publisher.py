#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
WordPress API Publisher Script
Подключение к WordPress API и публикация статей
"""

import requests
import json
import base64
from datetime import datetime
import sys
import os

class WordPressAPI:
    def __init__(self, site_url, username, app_password):
        """
        Инициализация подключения к WordPress API
        
        Args:
            site_url (str): URL сайта WordPress
            username (str): Имя пользователя
            app_password (str): Application Password
        """
        self.site_url = site_url.rstrip('/')
        self.api_url = f"{self.site_url}/wp-json/wp/v2"
        self.username = username
        self.app_password = app_password
        
        # Создаем заголовки для аутентификации
        credentials = f"{username}:{app_password}"
        token = base64.b64encode(credentials.encode()).decode('utf-8')
        
        self.headers = {
            'Authorization': f'Basic {token}',
            'Content-Type': 'application/json',
            'User-Agent': 'WordPress-API-Publisher/1.0'
        }
        
        print(f"🔗 Подключение к WordPress API: {self.api_url}")
    
    def test_connection(self):
        """
        Тестирование подключения к WordPress API
        """
        try:
            print("🔍 Тестирование подключения...")
            
            # Проверяем доступность API
            response = requests.get(f"{self.api_url}/", headers=self.headers, timeout=10)
            
            if response.status_code == 200:
                print("✅ Подключение к WordPress API успешно!")
                api_info = response.json()
                print(f"📊 Версия WordPress API: {api_info.get('version', 'Неизвестно')}")
                print(f"🏠 Название сайта: {api_info.get('name', 'Неизвестно')}")
                return True
            else:
                print(f"❌ Ошибка подключения: {response.status_code}")
                print(f"📝 Ответ сервера: {response.text}")
                return False
                
        except requests.exceptions.RequestException as e:
            print(f"❌ Ошибка сети: {e}")
            return False
        except Exception as e:
            print(f"❌ Неожиданная ошибка: {e}")
            return False
    
    def get_user_info(self):
        """
        Получение информации о текущем пользователе
        """
        try:
            print("👤 Получение информации о пользователе...")
            
            response = requests.get(f"{self.api_url}/users/me", headers=self.headers, timeout=10)
            
            if response.status_code == 200:
                user_info = response.json()
                print(f"✅ Пользователь: {user_info.get('name', 'Неизвестно')}")
                print(f"📧 Email: {user_info.get('email', 'Неизвестно')}")
                print(f"🔑 Роли: {', '.join(user_info.get('roles', []))}")
                return user_info
            else:
                print(f"❌ Ошибка получения информации о пользователе: {response.status_code}")
                return None
                
        except Exception as e:
            print(f"❌ Ошибка: {e}")
            return None
    
    def get_categories(self):
        """
        Получение списка категорий
        """
        try:
            print("📂 Получение списка категорий...")
            
            response = requests.get(f"{self.api_url}/categories", headers=self.headers, timeout=10)
            
            if response.status_code == 200:
                categories = response.json()
                print(f"✅ Найдено категорий: {len(categories)}")
                for cat in categories:
                    print(f"   - {cat['name']} (ID: {cat['id']})")
                return categories
            else:
                print(f"❌ Ошибка получения категорий: {response.status_code}")
                return []
                
        except Exception as e:
            print(f"❌ Ошибка: {e}")
            return []
    
    def create_category(self, name, slug=None, description=""):
        """
        Создание новой категории
        """
        try:
            print(f"📂 Создание категории: {name}")
            
            data = {
                'name': name,
                'slug': slug or name.lower().replace(' ', '-'),
                'description': description
            }
            
            response = requests.post(f"{self.api_url}/categories", 
                                   headers=self.headers, 
                                   json=data, 
                                   timeout=10)
            
            if response.status_code == 201:
                category = response.json()
                print(f"✅ Категория создана: {category['name']} (ID: {category['id']})")
                return category
            else:
                print(f"❌ Ошибка создания категории: {response.status_code}")
                print(f"📝 Ответ: {response.text}")
                return None
                
        except Exception as e:
            print(f"❌ Ошибка: {e}")
            return None

    def publish_post(self, title, content, slug=None, status='publish', categories=None, tags=None, meta=None):
        """
        Публикация поста в WordPress
        
        Args:
            title (str): Заголовок поста
            content (str): Содержимое поста (HTML)
            slug (str): URL slug
            status (str): Статус поста (draft, publish, private)
            categories (list): Список ID категорий
            tags (list): Список тегов
            meta (dict): Мета-данные
        """
        try:
            print(f"📝 Публикация поста: {title}")
            
            post_data = {
                'title': title,
                'content': content,
                'status': status,
                'format': 'standard'
            }
            
            if slug:
                post_data['slug'] = slug
            
            if categories:
                post_data['categories'] = categories
            
            if tags:
                post_data['tags'] = tags
            
            if meta:
                post_data['meta'] = meta
            
            response = requests.post(f"{self.api_url}/posts", 
                                   headers=self.headers, 
                                   json=post_data, 
                                   timeout=30)
            
            if response.status_code == 201:
                post = response.json()
                print(f"✅ Пост успешно опубликован!")
                print(f"🔗 ID: {post['id']}")
                print(f"🌐 URL: {post['link']}")
                print(f"📅 Дата: {post['date']}")
                return post
            else:
                print(f"❌ Ошибка публикации поста: {response.status_code}")
                print(f"📝 Ответ сервера: {response.text}")
                return None
                
        except Exception as e:
            print(f"❌ Ошибка: {e}")
            return None
    
    def load_article_from_file(self, file_path):
        """
        Загрузка статьи из HTML файла
        """
        try:
            print(f"📂 Загрузка статьи из файла: {file_path}")
            
            if not os.path.exists(file_path):
                print(f"❌ Файл не найден: {file_path}")
                return None
            
            with open(file_path, 'r', encoding='utf-8') as file:
                content = file.read()
            
            print(f"✅ Файл загружен, размер: {len(content)} символов")
            return content
            
        except Exception as e:
            print(f"❌ Ошибка загрузки файла: {e}")
            return None
    
    def extract_article_data(self, html_content):
        """
        Извлечение данных статьи из HTML
        """
        try:
            print("🔍 Извлечение данных статьи...")
            
            # Извлекаем заголовок из <title>
            import re
            
            title_match = re.search(r'<title>(.*?)</title>', html_content, re.IGNORECASE | re.DOTALL)
            title = title_match.group(1).strip() if title_match else "Без заголовка"
            
            # Извлекаем H1 заголовок
            h1_match = re.search(r'<h1[^>]*>(.*?)</h1>', html_content, re.IGNORECASE | re.DOTALL)
            h1_title = h1_match.group(1).strip() if h1_match else title
            
            # Извлекаем slug из canonical URL
            slug_match = re.search(r'<link rel="canonical" href="[^"]*/([^/]+)/?"', html_content, re.IGNORECASE)
            slug = slug_match.group(1) if slug_match else None
            
            # Извлекаем meta description
            desc_match = re.search(r'<meta name="description" content="([^"]*)"', html_content, re.IGNORECASE)
            meta_description = desc_match.group(1) if desc_match else ""
            
            # Очищаем HTML от служебных тегов для контента
            content = html_content
            
            # Удаляем head секцию
            content = re.sub(r'<head>.*?</head>', '', content, flags=re.IGNORECASE | re.DOTALL)
            
            # Удаляем script теги
            content = re.sub(r'<script[^>]*>.*?</script>', '', content, flags=re.IGNORECASE | re.DOTALL)
            
            # Удаляем style теги
            content = re.sub(r'<style[^>]*>.*?</style>', '', content, flags=re.IGNORECASE | re.DOTALL)
            
            print(f"✅ Данные извлечены:")
            print(f"   📝 Заголовок: {h1_title[:50]}...")
            print(f"   🔗 Slug: {slug}")
            print(f"   📄 Описание: {meta_description[:50]}...")
            
            return {
                'title': h1_title,
                'content': content,
                'slug': slug,
                'meta_description': meta_description
            }
            
        except Exception as e:
            print(f"❌ Ошибка извлечения данных: {e}")
            return None

def publish_tender_guarantee_article():
    """
    Публикация статьи о тендерной гарантии
    """
    print("🚀 WordPress API Publisher - Публикация статьи")
    print("=" * 60)
    
    # Параметры подключения
    SITE_URL = "https://bizfin-pro.ru"
    USERNAME = "bizfin_pro_r"
    APP_PASSWORD = "U3Ep gU2T clRu FcwN QU6l Dsda"
    
    # Создаем экземпляр API
    wp_api = WordPressAPI(SITE_URL, USERNAME, APP_PASSWORD)
    
    # Тестируем подключение
    if not wp_api.test_connection():
        print("❌ Не удалось подключиться к WordPress API")
        return False
    
    print("\n" + "=" * 60)
    
    # Загружаем статью из файла
    article_file = "tender-guarantee-article.html"
    html_content = wp_api.load_article_from_file(article_file)
    
    if not html_content:
        print("❌ Не удалось загрузить статью")
        return False
    
    print("\n" + "=" * 60)
    
    # Извлекаем данные статьи
    article_data = wp_api.extract_article_data(html_content)
    
    if not article_data:
        print("❌ Не удалось извлечь данные статьи")
        return False
    
    print("\n" + "=" * 60)
    
    # Получаем или создаем категорию
    categories = wp_api.get_categories()
    category_id = None
    
    # Ищем категорию "Банковские гарантии" или создаем её
    for cat in categories:
        if "банковск" in cat['name'].lower() or "гарант" in cat['name'].lower():
            category_id = cat['id']
            break
    
    if not category_id:
        print("📂 Создание категории 'Банковские гарантии'...")
        new_category = wp_api.create_category(
            name="Банковские гарантии",
            slug="bankovskie-garantii",
            description="Статьи о банковских гарантиях и тендерах"
        )
        if new_category:
            category_id = new_category['id']
    
    print("\n" + "=" * 60)
    
    # Публикуем статью
    post_data = {
        'title': article_data['title'],
        'content': article_data['content'],
        'slug': article_data['slug'],
        'status': 'publish',
        'categories': [category_id] if category_id else [],
        'meta': {
            '_yoast_wpseo_title': article_data['title'],
            '_yoast_wpseo_metadesc': article_data['meta_description'],
            '_yoast_wpseo_focuskw': 'тендерная гарантия'
        }
    }
    
    published_post = wp_api.publish_post(**post_data)
    
    if published_post:
        print("\n" + "=" * 60)
        print("🎉 СТАТЬЯ УСПЕШНО ОПУБЛИКОВАНА!")
        print(f"🔗 Ссылка для ознакомления: {published_post['link']}")
        print(f"📝 ID поста: {published_post['id']}")
        print(f"📅 Дата публикации: {published_post['date']}")
        return True
    else:
        print("❌ Не удалось опубликовать статью")
        return False

def main():
    """
    Основная функция для тестирования подключения
    """
    print("🚀 WordPress API Publisher - Тест подключения")
    print("=" * 50)
    
    # Параметры подключения
    SITE_URL = "https://bizfin-pro.ru"
    USERNAME = "bizfin_pro_r"
    APP_PASSWORD = "U3Ep gU2T clRu FcwN QU6l Dsda"
    
    # Создаем экземпляр API
    wp_api = WordPressAPI(SITE_URL, USERNAME, APP_PASSWORD)
    
    # Тестируем подключение
    if wp_api.test_connection():
        print("\n" + "=" * 50)
        
        # Получаем информацию о пользователе
        user_info = wp_api.get_user_info()
        
        if user_info:
            print("\n" + "=" * 50)
            
            # Получаем категории
            categories = wp_api.get_categories()
            
            print("\n" + "=" * 50)
            print("✅ Подключение к WordPress API настроено успешно!")
            print("📋 Готов к публикации статей")
            
        else:
            print("❌ Не удалось получить информацию о пользователе")
    else:
        print("❌ Не удалось подключиться к WordPress API")
        print("🔧 Проверьте параметры подключения:")
        print(f"   - URL: {SITE_URL}")
        print(f"   - Username: {USERNAME}")
        print(f"   - App Password: {APP_PASSWORD[:10]}...")

if __name__ == "__main__":
    # Запускаем публикацию статьи
    print("🚀 Запуск публикации статьи о тендерной гарантии...")
    print("=" * 60)
    
    success = publish_tender_guarantee_article()
    
    if success:
        print("\n🎉 ПУБЛИКАЦИЯ ЗАВЕРШЕНА УСПЕШНО!")
    else:
        print("\n❌ ПУБЛИКАЦИЯ НЕ УДАЛАСЬ!")
        print("🔧 Проверьте логи выше для диагностики проблемы")
