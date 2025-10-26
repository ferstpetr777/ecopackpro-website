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

# Альтернативные варианты имени пользователя
ALTERNATIVE_USERNAMES = [
    "rtep1976@me.com",
    "login6xa23sp8zhxg57qv",
    "admin",
    "administrator"
]

# Список ключевых слов для поиска статей
KEYWORDS = [
    "курьерские пакеты",
    "почтовые коробки", 
    "зип пакеты",
    "zip lock пакеты с бегунком",
    "конверты с воздушной подушкой",
    "конверты с воздушной прослойкой",
    "крафтовые пакеты с воздушной подушкой",
    "курьерские пакеты прозрачные",
    "курьерские пакеты номерные",
    "курьерские пакеты черно-белые",
    "курьерские пакеты с карманом",
    "zip lock пакеты матовые",
    "zip lock пакеты оптом",
    "крафтовые конверты",
    "пузырчатые пакеты ВПП",
    "коробки для почты",
    "коробки для отправки",
    "самоклеящиеся карманы",
    "антимагнитная пломба",
    "наклейка пломба антимагнит",
    "пломбиратор для бочек",
    "номерные пломбы наклейки",
    "zip lock пакеты с белой полосой",
    "белые крафт пакеты с пузырчатой плёнкой",
    "прозрачные zip lock пакеты",
    "купить курьерские пакеты с номерным штрих-кодом",
    "заказать прозрачные курьерские пакеты оптом",
    "курьерские пакеты черно-белые с карманом цена",
    "матовые zip lock пакеты с бегунком 10×15",
    "купить оптом zip lock пакеты матовые 30 мкм",
    "крафт конверты с воздушной подушкой F/3",
    "почтовые коробки размера S 260×170×80",
    "почтовые коробки размера XL 530×360×220",
    "купить самоклеящиеся карманы SD для документов",
    "антимагнитные наклейки для водяных счётчиков",
    "антимагнитная пломба цена за 100 штук",
    "пломбиратор для евробочек 2 дюйма",
    "инструмент для опломбирования бочек ¾ дюйма",
    "курьерские пакеты черно-белые без логотипа А4",
    "курьерские пакеты прозрачные для одежды",
    "курьерские пакеты для маркетплейсов Ozon",
    "почтовые коробки с логотипом на заказ",
    "зип пакеты с бегунком купить Москва",
    "матовые zip lock пакеты для чая",
    "zip lock пакеты с подвесом",
    "белые крафт-пакеты с пузырчатой плёнкой оптом",
    "плоские конверты с воздушной подушкой для документов",
    "пакеты из воздушно-пузырьковой плёнки оптом",
    "антимагнитные пломбы для газовых счётчиков",
    "самоклеящиеся карманы для транспортных накладных"
]

class WordPressAPI:
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
    
    @classmethod
    def create_with_alternative_usernames(cls, url, app_password):
        """Создание экземпляра API с тестированием разных имен пользователей"""
        for username in ALTERNATIVE_USERNAMES:
            print(f"🔍 Тестирование имени пользователя: {username}")
            api = cls(url, username, app_password)
            if api.test_connection():
                return api
        return None
    
    def get_categories(self):
        """Получение списка категорий"""
        try:
            response = requests.get(
                f"{self.url}/wp-json/wp/v2/categories",
                headers=self.headers,
                timeout=30
            )
            
            if response.status_code == 200:
                return response.json()
            else:
                print(f"❌ Ошибка получения категорий: {response.status_code}")
                return []
                
        except Exception as e:
            print(f"❌ Ошибка получения категорий: {e}")
            return []
    
    def find_or_create_blog_category(self):
        """Поиск или создание категории 'блог'"""
        categories = self.get_categories()
        
        # Ищем существующую категорию "блог"
        for category in categories:
            if category['name'].lower() == 'блог' or category['slug'] == 'blog':
                print(f"✅ Найдена категория 'блог': ID {category['id']}")
                return category['id']
        
        # Создаем новую категорию "блог"
        try:
            category_data = {
                'name': 'блог',
                'slug': 'blog',
                'description': 'Статьи блога'
            }
            
            response = requests.post(
                f"{self.url}/wp-json/wp/v2/categories",
                headers=self.headers,
                json=category_data,
                timeout=30
            )
            
            if response.status_code == 201:
                category = response.json()
                print(f"✅ Создана категория 'блог': ID {category['id']}")
                return category['id']
            else:
                print(f"❌ Ошибка создания категории: {response.status_code} - {response.text}")
                return None
                
        except Exception as e:
            print(f"❌ Ошибка создания категории: {e}")
            return None
    
    def search_posts_by_title(self, keyword, status='draft'):
        """Поиск постов по заголовку"""
        try:
            params = {
                'search': keyword,
                'status': status,
                'per_page': 10
            }
            
            response = requests.get(
                f"{self.url}/wp-json/wp/v2/posts",
                headers=self.headers,
                params=params,
                timeout=30
            )
            
            if response.status_code == 200:
                posts = response.json()
                # Фильтруем по точному соответствию в заголовке
                matching_posts = []
                for post in posts:
                    if keyword.lower() in post['title']['rendered'].lower():
                        matching_posts.append(post)
                return matching_posts
            else:
                print(f"❌ Ошибка поиска постов: {response.status_code}")
                return []
                
        except Exception as e:
            print(f"❌ Ошибка поиска постов: {e}")
            return []
    
    def update_post_categories(self, post_id, category_id):
        """Обновление категорий поста"""
        try:
            # Сначала получаем текущие данные поста
            response = requests.get(
                f"{self.url}/wp-json/wp/v2/posts/{post_id}",
                headers=self.headers,
                timeout=30
            )
            
            if response.status_code != 200:
                print(f"❌ Ошибка получения поста {post_id}: {response.status_code}")
                return False
            
            post_data = response.json()
            current_categories = post_data.get('categories', [])
            
            # Добавляем категорию "блог", если её нет
            if category_id not in current_categories:
                current_categories.append(category_id)
                
                # Обновляем пост
                update_data = {
                    'categories': current_categories
                }
                
                update_response = requests.post(
                    f"{self.url}/wp-json/wp/v2/posts/{post_id}",
                    headers=self.headers,
                    json=update_data,
                    timeout=30
                )
                
                if update_response.status_code == 200:
                    return True
                else:
                    print(f"❌ Ошибка обновления поста {post_id}: {update_response.status_code}")
                    return False
            else:
                print(f"ℹ️  Пост {post_id} уже имеет категорию 'блог'")
                return True
                
        except Exception as e:
            print(f"❌ Ошибка обновления поста {post_id}: {e}")
            return False

def main():
    """Основная функция"""
    print("🔗 ПОДКЛЮЧЕНИЕ К WORDPRESS API И НАЗНАЧЕНИЕ КАТЕГОРИИ 'БЛОГ'")
    print("=" * 70)
    
    # Создание экземпляра API с тестированием разных имен пользователей
    print("\n🔍 Тестирование подключения к WordPress API...")
    wp_api = WordPressAPI.create_with_alternative_usernames(WORDPRESS_URL, APPLICATION_PASSWORD)
    
    if not wp_api:
        print("❌ Не удалось подключиться к WordPress API с любым из имен пользователей")
        return
    
    # Получение или создание категории "блог"
    print("\n📂 Получение категории 'блог'...")
    blog_category_id = wp_api.find_or_create_blog_category()
    if not blog_category_id:
        print("❌ Не удалось получить категорию 'блог'")
        return
    
    # Поиск и обновление статей
    print("\n🔍 Поиск и обновление статей...")
    print("=" * 70)
    
    stats = {
        'total_keywords': len(KEYWORDS),
        'found_articles': 0,
        'updated_articles': 0,
        'failed_updates': 0
    }
    
    for i, keyword in enumerate(KEYWORDS, 1):
        print(f"\n📋 {i:2d}. Поиск: {keyword}")
        
        # Поиск статей по ключевому слову
        posts = wp_api.search_posts_by_title(keyword)
        
        if posts:
            for post in posts:
                post_id = post['id']
                post_title = post['title']['rendered']
                current_categories = post.get('categories', [])
                
                print(f"   📄 ID {post_id}: {post_title}")
                print(f"   📂 Текущие категории: {current_categories}")
                
                # Обновление категорий
                if wp_api.update_post_categories(post_id, blog_category_id):
                    print(f"   ✅ Добавлена категория 'блог' (ID: {blog_category_id})")
                    stats['updated_articles'] += 1
                else:
                    print(f"   ❌ Ошибка добавления категории")
                    stats['failed_updates'] += 1
                
                stats['found_articles'] += 1
        else:
            print(f"   ❌ Статьи не найдены")
    
    # Итоговый отчет
    print("\n" + "=" * 70)
    print("📊 ИТОГОВЫЙ ОТЧЕТ")
    print("=" * 70)
    
    print(f"📚 Всего ключевых слов: {stats['total_keywords']}")
    print(f"📄 Найдено статей: {stats['found_articles']}")
    print(f"✅ Обновлено статей: {stats['updated_articles']}")
    print(f"❌ Ошибок обновления: {stats['failed_updates']}")
    
    if stats['found_articles'] > 0:
        success_rate = (stats['updated_articles'] / stats['found_articles']) * 100
        print(f"📊 Процент успешных обновлений: {success_rate:.1f}%")
    
    print(f"\n✅ Назначение категории 'блог' завершено!")
    print(f"🆔 ID категории 'блог': {blog_category_id}")

if __name__ == "__main__":
    main()
