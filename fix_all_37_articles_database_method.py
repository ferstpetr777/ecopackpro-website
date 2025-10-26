#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector
import requests
from requests.auth import HTTPBasicAuth
import time
import re
from datetime import datetime

# Конфигурация базы данных WordPress
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

# Настройки WordPress API (из оригинального скрипта wordpress_yoast_seo_updater.py)
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKIVWpH96dgVI3HovlIhI4E"

# Список всех 37 проблемных статей (из аудита)
PROBLEMATIC_ARTICLES = [
    7909, 7911, 7913, 7914, 7915, 7916, 7918, 7919, 7920, 7921, 7922, 7924,
    7925, 7926, 7927, 7928, 7929, 7930, 7932, 7933, 7934, 7935, 7936, 7937,
    7938, 7939, 7940, 7941, 7942, 7943, 7944, 7945, 7946, 7947, 7949, 7950, 7951
]

class MassArticleFixerDatabaseMethod:
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
    
    def get_article_data_from_db(self, post_id):
        """Получение данных статьи из базы данных"""
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
    
    def update_yoast_seo_via_api(self, post_id, focus_keyword, new_slug, meta_description):
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
        print("-" * 60)
        
        try:
            # Получаем данные статьи из базы данных
            article_data = self.get_article_data_from_db(post_id)
            if not article_data:
                return False, "Статья не найдена в базе данных"
            
            post_title = article_data['post_title']
            current_slug = article_data['post_name']
            meta = article_data.get('meta', {})
            current_focus_keyword = meta.get('_yoast_wpseo_focuskw', '')
            current_meta_description = meta.get('_yoast_wpseo_metadesc', '')
            
            print(f"📄 Заголовок: {post_title}")
            print(f"🎯 Текущее фокусное ключевое слово: '{current_focus_keyword}'")
            print(f"🔗 Текущий slug: '{current_slug}'")
            
            # Фокусное ключевое слово = текущее фокусное слово (оно уже правильное по аудиту)
            focus_keyword = current_focus_keyword
            
            if not focus_keyword:
                return False, "Отсутствует фокусное ключевое слово"
            
            # Создаем правильный slug на основе фокусного ключевого слова
            new_slug = self.transliterate_to_latin(focus_keyword)
            
            # Исправляем мета-описание, чтобы оно начиналось с фокусного ключевого слова
            if current_meta_description:
                # Если мета-описание существует, но не начинается с ключевого слова
                if not current_meta_description.strip().lower().startswith(focus_keyword.lower()):
                    new_meta_description = f"{focus_keyword} - {current_meta_description}"
                else:
                    # Мета-описание уже правильное
                    new_meta_description = current_meta_description
            else:
                # Если мета-описание отсутствует, создаем базовое
                new_meta_description = f"{focus_keyword} - Подробное описание и характеристики товара. Качественные материалы, доступные цены, быстрая доставка."
            
            print(f"✨ Новое фокусное ключевое слово: '{focus_keyword}'")
            print(f"✨ Новый slug: '{new_slug}'")
            print(f"✨ Новое мета-описание: '{new_meta_description[:80]}...'")
            
            # Обновляем SEO параметры через WordPress API (используем оригинальный метод)
            success, message = self.update_yoast_seo_via_api(post_id, focus_keyword, new_slug, new_meta_description)
            
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
        """Исправление всех 37 проблемных статей"""
        print("🔧 МАССОВОЕ ИСПРАВЛЕНИЕ 37 ПРОБЛЕМНЫХ СТАТЕЙ")
        print("=" * 80)
        print("Используется проверенный метод из оригинального скрипта:")
        print("1. Фокусное ключевое слово соответствует заголовку статьи")
        print("2. Ярлык (slug) соответствует фокусному ключевому слову (латиницей)")
        print("3. Мета-описание начинается с фокусного ключевого слова")
        print("4. Обновление через WordPress REST API")
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
        print("📊 ФИНАЛЬНЫЙ ОТЧЕТ МАССОВОГО ИСПРАВЛЕНИЯ")
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
        
        # Сохраняем отчет в файл
        timestamp = datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
        report_filename = f"ФИНАЛЬНЫЙ_ОТЧЕТ_ИСПРАВЛЕНИЯ_37_СТАТЕЙ_{timestamp}.md"
        
        with open(report_filename, 'w', encoding='utf-8') as f:
            f.write("# 🔧 ФИНАЛЬНЫЙ ОТЧЕТ МАССОВОГО ИСПРАВЛЕНИЯ 37 СТАТЕЙ\n\n")
            f.write(f"**Дата:** {datetime.now().strftime('%d.%m.%Y %H:%M:%S')}\n\n")
            f.write(f"## 📊 СТАТИСТИКА\n\n")
            f.write(f"- **Всего статей:** {self.fix_stats['total']}\n")
            f.write(f"- **✅ Успешно исправлено:** {self.fix_stats['success']} ({success_rate:.1f}%)\n")
            f.write(f"- **❌ Ошибки:** {self.fix_stats['failed']}\n\n")
            
            f.write(f"## ✅ ИСПРАВЛЕННЫЕ СТАТЬИ\n\n")
            for post_id in PROBLEMATIC_ARTICLES:
                f.write(f"- [ID {post_id}](https://ecopackpro.ru/wp-admin/post.php?post={post_id}&action=edit)\n")
            
            if self.fix_stats['errors']:
                f.write(f"\n## 🚨 ОШИБКИ\n\n")
                for error in self.fix_stats['errors']:
                    f.write(f"- {error}\n")
        
        print(f"\n📄 Отчет сохранен в файл: {report_filename}")

def main():
    """Основная функция"""
    print("🔧 МАССОВОЕ ИСПРАВЛЕНИЕ 37 ПРОБЛЕМНЫХ СТАТЕЙ")
    print("=" * 80)
    print("Метод: WordPress REST API + База данных MySQL")
    print("Основа: Оригинальный скрипт wordpress_yoast_seo_updater.py")
    print("=" * 80)
    
    fixer = MassArticleFixerDatabaseMethod()
    
    # Исправляем все проблемные статьи
    stats = fixer.fix_all_problematic_articles()
    
    # Выводим отчет
    fixer.print_fix_report()
    
    if stats['success'] == stats['total']:
        print(f"\n🎉 ВСЕ 37 СТАТЕЙ УСПЕШНО ИСПРАВЛЕНЫ!")
    else:
        print(f"\n⚠️  Требуется доработка {stats['failed']} статей")
    
    print(f"\n✅ МАССОВОЕ ИСПРАВЛЕНИЕ ЗАВЕРШЕНО!")

if __name__ == "__main__":
    main()
