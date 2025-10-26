#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector
import requests
from requests.auth import HTTPBasicAuth
from datetime import datetime
import re
import time

# Конфигурация базы данных WordPress
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

# Настройки WordPress API (из оригинального скрипта)
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKIVWpH96dgVI3HovlIhI4E"

# Список проблемных статей из аудита
PROBLEMATIC_ARTICLES = [
    7913, 7915, 7917, 7926, 7928, 7929, 7930, 7932, 7934, 7938, 7939, 
    7941, 7943, 7944, 7945, 7946, 7947, 7948, 7949, 7952, 7953, 7954, 7955
]

class MassSEOFixer:
    def __init__(self):
        self.db_config = DB_CONFIG
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
        
        # Статистика исправления
        self.fix_stats = {
            'total': 0,
            'success': 0,
            'failed': 0,
            'errors': []
        }
    
    def connect_to_database(self):
        """Подключение к базе данных MySQL"""
        try:
            connection = mysql.connector.connect(**self.db_config)
            return connection
        except mysql.connector.Error as e:
            print(f"❌ Ошибка подключения к базе данных: {e}")
            return None
    
    def transliterate_to_latin(self, text):
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
    
    def get_article_seo_data(self, post_id):
        """Получение SEO данных статьи из базы данных"""
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
            print(f"❌ Ошибка получения данных для ID {post_id}: {e}")
            return None
        finally:
            connection.close()
    
    def update_yoast_seo_via_api(self, post_id, focus_keyword, meta_description, new_slug):
        """Обновление SEO параметров через WordPress API (метод из оригинального скрипта)"""
        
        try:
            # Получаем текущий пост
            response = requests.get(
                f"{WP_API_URL}/posts/{post_id}",
                auth=self.auth,
                headers=self.headers,
                timeout=60
            )
            
            if response.status_code != 200:
                return False, f"Ошибка получения поста: {response.status_code}"
            
            post_data = response.json()
            
            # Подготавливаем данные для обновления (точно как в оригинальном скрипте)
            update_data = {
                'meta': {
                    '_yoast_wpseo_focuskw': focus_keyword,  # Фокусное ключевое слово
                    '_yoast_wpseo_metadesc': meta_description,  # Мета описание
                    '_yoast_wpseo_title': post_data['title']['rendered'],  # Заголовок
                    '_yoast_wpseo_canonical': f"https://ecopackpro.ru/{new_slug}/"  # Каноническая ссылка
                },
                'slug': new_slug  # Обновляем slug
            }
            
            # Обновляем пост
            update_response = requests.post(
                f"{WP_API_URL}/posts/{post_id}",
                auth=self.auth,
                headers=self.headers,
                json=update_data,
                timeout=60
            )
            
            if update_response.status_code == 200:
                return True, "Успешно обновлено"
            else:
                return False, f"Ошибка обновления: {update_response.status_code} - {update_response.text}"
                
        except Exception as e:
            return False, f"Исключение: {str(e)}"
    
    def fix_single_article(self, post_id):
        """Исправление одной статьи"""
        print(f"\n🔧 ИСПРАВЛЕНИЕ СТАТЬИ ID {post_id}")
        print("-" * 50)
        
        try:
            # Получаем текущие данные статьи
            article_data = self.get_article_seo_data(post_id)
            if not article_data:
                return False, "Статья не найдена в базе данных"
            
            post_title = article_data['post_title']
            focus_keyword = article_data.get('meta', {}).get('_yoast_wpseo_focuskw', '')
            current_meta_description = article_data.get('meta', {}).get('_yoast_wpseo_metadesc', '')
            
            print(f"📄 Заголовок: {post_title}")
            print(f"🎯 Фокусное ключевое слово: {focus_keyword}")
            
            if not focus_keyword:
                return False, "Отсутствует фокусное ключевое слово"
            
            # Создаем правильный slug на основе фокусного ключевого слова
            new_slug = self.transliterate_to_latin(focus_keyword)
            print(f"🔗 Новый slug: {new_slug}")
            
            # Исправляем мета-описание, чтобы оно начиналось с фокусного ключевого слова
            if current_meta_description and not current_meta_description.lower().startswith(focus_keyword.lower()):
                # Если мета-описание не начинается с ключевого слова, исправляем его
                new_meta_description = f"{focus_keyword} - {current_meta_description}"
            elif not current_meta_description:
                # Если мета-описание отсутствует, создаем базовое
                new_meta_description = f"{focus_keyword} - Подробное описание и характеристики товара. Качественные материалы, доступные цены, быстрая доставка."
            else:
                # Если мета-описание уже правильное, оставляем как есть
                new_meta_description = current_meta_description
            
            print(f"📝 Новое мета-описание: {new_meta_description[:80]}...")
            
            # Обновляем SEO параметры через API
            success, message = self.update_yoast_seo_via_api(post_id, focus_keyword, new_meta_description, new_slug)
            
            if success:
                print(f"✅ Статья успешно исправлена!")
                return True, "Успешно исправлено"
            else:
                print(f"❌ Ошибка исправления: {message}")
                return False, message
                
        except Exception as e:
            error_msg = f"Ошибка обработки статьи {post_id}: {str(e)}"
            print(f"❌ {error_msg}")
            return False, error_msg
    
    def fix_all_problematic_articles(self):
        """Исправление всех проблемных статей"""
        print("🔧 МАССОВОЕ ИСПРАВЛЕНИЕ ПРОБЛЕМНЫХ СТАТЕЙ")
        print("=" * 80)
        print("Используется проверенный метод из статьи 7917:")
        print("1. Обновление slug на латинский (транслитерация фокусного ключевого слова)")
        print("2. Исправление мета-описания (начало с фокусного ключевого слова)")
        print("3. Обновление через WordPress REST API")
        print("=" * 80)
        
        self.fix_stats['total'] = len(PROBLEMATIC_ARTICLES)
        
        for i, post_id in enumerate(PROBLEMATIC_ARTICLES, 1):
            print(f"\n📋 {i}/{len(PROBLEMATIC_ARTICLES)}")
            
            success, message = self.fix_single_article(post_id)
            
            if success:
                self.fix_stats['success'] += 1
            else:
                self.fix_stats['failed'] += 1
                self.fix_stats['errors'].append(f"ID {post_id}: {message}")
            
            # Небольшая пауза между запросами
            if i < len(PROBLEMATIC_ARTICLES):
                time.sleep(1)
        
        return self.fix_stats
    
    def print_fix_report(self):
        """Вывод отчета об исправлении"""
        print("\n" + "=" * 80)
        print("📊 ОТЧЕТ ОБ ИСПРАВЛЕНИИ")
        print("=" * 80)
        
        print(f"📚 Всего статей: {self.fix_stats['total']}")
        print(f"✅ Успешно исправлено: {self.fix_stats['success']}")
        print(f"❌ Ошибки: {self.fix_stats['failed']}")
        
        if self.fix_stats['total'] > 0:
            success_rate = (self.fix_stats['success'] / self.fix_stats['total']) * 100
            print(f"📊 Процент успешности: {success_rate:.1f}%")
        
        if self.fix_stats['errors']:
            print(f"\n🚨 ОШИБКИ:")
            for error in self.fix_stats['errors']:
                print(f"  - {error}")
        
        print(f"\n🔗 Ссылки на исправленные статьи:")
        for post_id in PROBLEMATIC_ARTICLES:
            print(f"  https://ecopackpro.ru/wp-admin/post.php?post={post_id}&action=edit")

def main():
    """Основная функция"""
    print("🔧 МАССОВОЕ ИСПРАВЛЕНИЕ ПРОБЛЕМНЫХ SEO СТАТЕЙ")
    print("=" * 80)
    
    fixer = MassSEOFixer()
    
    # Исправляем все проблемные статьи
    stats = fixer.fix_all_problematic_articles()
    
    # Выводим отчет
    fixer.print_fix_report()
    
    if stats['success'] == stats['total']:
        print(f"\n🎉 ВСЕ СТАТЬИ УСПЕШНО ИСПРАВЛЕНЫ!")
    else:
        print(f"\n⚠️  Требуется доработка {stats['failed']} статей")

if __name__ == "__main__":
    main()
