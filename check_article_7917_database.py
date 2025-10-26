#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector
import requests
from requests.auth import HTTPBasicAuth
from datetime import datetime

# Конфигурация базы данных WordPress
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

# Настройки WordPress API
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKIVWpH96dgVI3HovlIhI4E"

class Article7917Checker:
    def __init__(self):
        self.db_config = DB_CONFIG
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
    
    def connect_to_database(self):
        """Подключение к базе данных MySQL"""
        try:
            connection = mysql.connector.connect(**self.db_config)
            return connection
        except mysql.connector.Error as e:
            print(f"❌ Ошибка подключения к базе данных: {e}")
            return None
    
    def check_post_database(self, post_id):
        """Проверка поста в базе данных"""
        connection = self.connect_to_database()
        if not connection:
            return None
        
        try:
            cursor = connection.cursor(dictionary=True)
            
            # Получаем данные поста
            cursor.execute("""
                SELECT ID, post_title, post_name, post_content, post_excerpt
                FROM wp_posts 
                WHERE ID = %s
            """, (post_id,))
            
            post_data = cursor.fetchone()
            
            if not post_data:
                print(f"❌ Пост с ID {post_id} не найден")
                return None
            
            # Получаем мета данные Yoast SEO
            cursor.execute("""
                SELECT meta_key, meta_value
                FROM wp_postmeta 
                WHERE post_id = %s 
                AND meta_key IN (
                    '_yoast_wpseo_focuskw',
                    '_yoast_wpseo_metadesc',
                    '_yoast_wpseo_title',
                    '_yoast_wpseo_canonical'
                )
            """, (post_id,))
            
            meta_data = cursor.fetchall()
            meta_dict = {row['meta_key']: row['meta_value'] for row in meta_data}
            
            post_data['meta'] = meta_dict
            return post_data
            
        except mysql.connector.Error as e:
            print(f"❌ Ошибка получения данных: {e}")
            return None
        finally:
            connection.close()
    
    def check_post_api(self, post_id):
        """Проверка поста через API"""
        try:
            response = requests.get(
                f"{WP_API_URL}/posts/{post_id}",
                auth=self.auth,
                headers=self.headers,
                timeout=60
            )
            
            if response.status_code == 200:
                return response.json()
            else:
                print(f"❌ Ошибка API: {response.status_code}")
                return None
                
        except Exception as e:
            print(f"❌ Ошибка API: {e}")
            return None
    
    def comprehensive_check(self, post_id):
        """Комплексная проверка статьи"""
        print("🔍 КОМПЛЕКСНАЯ ПРОВЕРКА СТАТЬИ 7917")
        print("=" * 60)
        
        print(f"\n📊 ПРОВЕРКА ЧЕРЕЗ БАЗУ ДАННЫХ:")
        print("-" * 40)
        
        db_data = self.check_post_database(post_id)
        if db_data:
            print(f"📄 Заголовок: {db_data['post_title']}")
            print(f"🔗 Slug: {db_data['post_name']}")
            
            meta = db_data.get('meta', {})
            print(f"🎯 Фокусное ключевое слово: {meta.get('_yoast_wpseo_focuskw', 'НЕ УСТАНОВЛЕНО')}")
            print(f"📝 Мета описание: {meta.get('_yoast_wpseo_metadesc', 'НЕ УСТАНОВЛЕНО')}")
            print(f"🏷️  SEO заголовок: {meta.get('_yoast_wpseo_title', 'НЕ УСТАНОВЛЕНО')}")
            print(f"🔗 Каноническая ссылка: {meta.get('_yoast_wpseo_canonical', 'НЕ УСТАНОВЛЕНА')}")
        else:
            print("❌ Не удалось получить данные из базы")
        
        print(f"\n📊 ПРОВЕРКА ЧЕРЕЗ API:")
        print("-" * 40)
        
        api_data = self.check_post_api(post_id)
        if api_data:
            print(f"📄 Заголовок: {api_data.get('title', {}).get('rendered', 'НЕ НАЙДЕН')}")
            print(f"🔗 Slug: {api_data.get('slug', 'НЕ НАЙДЕН')}")
            print(f"🌐 Ссылка: {api_data.get('link', 'НЕ НАЙДЕНА')}")
            
            # API может не показывать мета данные из-за прав доступа
            meta = api_data.get('meta', {})
            print(f"🎯 Фокусное ключевое слово (API): {meta.get('_yoast_wpseo_focuskw', 'НЕ ДОСТУПНО ЧЕРЕЗ API')}")
            print(f"📝 Мета описание (API): {meta.get('_yoast_wpseo_metadesc', 'НЕ ДОСТУПНО ЧЕРЕЗ API')}")
        else:
            print("❌ Не удалось получить данные через API")
        
        # Итоговая оценка
        print(f"\n" + "=" * 60)
        print("📊 ИТОГОВАЯ ОЦЕНКА")
        print("=" * 60)
        
        if db_data and db_data.get('meta'):
            meta = db_data['meta']
            focus_keyword = meta.get('_yoast_wpseo_focuskw', '')
            meta_description = meta.get('_yoast_wpseo_metadesc', '')
            slug = db_data.get('post_name', '')
            
            print(f"🔗 Slug (латинский): {'✅' if slug and slug == 'courier-packages-with-pocket' else '❌'}")
            print(f"🎯 Фокусное ключевое слово: {'✅' if focus_keyword else '❌'}")
            print(f"📝 Мета описание: {'✅' if meta_description else '❌'}")
            print(f"📝 Мета описание начинается с ключевой фразы: {'✅' if meta_description.startswith('курьерские пакеты с карманом') else '❌'}")
            
            if slug == 'courier-packages-with-pocket' and focus_keyword and meta_description.startswith('курьерские пакеты с карманом'):
                print(f"\n🎉 ВСЕ ТРЕБОВАНИЯ ВЫПОЛНЕНЫ!")
                print(f"🔗 Новая ссылка: https://ecopackpro.ru/{slug}/")
                print(f"📱 Админ панель: https://ecopackpro.ru/wp-admin/post.php?post={post_id}&action=edit")
                return True
            else:
                print(f"\n⚠️  НЕКОТОРЫЕ ТРЕБОВАНИЯ НЕ ВЫПОЛНЕНЫ")
                return False
        else:
            print(f"\n❌ НЕ УДАЛОСЬ ПРОВЕРИТЬ ДАННЫЕ")
            return False

def main():
    """Основная функция"""
    checker = Article7917Checker()
    success = checker.comprehensive_check(7917)
    
    if success:
        print(f"\n✅ Статья 7917 полностью соответствует требованиям!")
    else:
        print(f"\n❌ Статья 7917 требует доработки")

if __name__ == "__main__":
    main()
