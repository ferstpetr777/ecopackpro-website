#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector
import requests
from requests.auth import HTTPBasicAuth
from datetime import datetime
import re

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

# Список всех исправленных статей
FIXED_ARTICLES = [
    7912, 7913, 7915, 7917, 7926, 7928, 7929, 7930, 7932, 7934, 7938, 7939, 
    7941, 7943, 7944, 7945, 7946, 7947, 7948, 7949, 7952, 7953, 7954, 7955, 7956
]

class FinalControlChecker:
    def __init__(self):
        self.db_config = DB_CONFIG
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
        
        # Статистика проверки
        self.control_stats = {
            'total': 0,
            'compliant': 0,
            'non_compliant': 0,
            'errors': 0,
            'problematic_articles': []
        }
    
    def connect_to_database(self):
        """Подключение к базе данных MySQL"""
        try:
            connection = mysql.connector.connect(**self.db_config)
            return connection
        except mysql.connector.Error as e:
            print(f"❌ Ошибка подключения к базе данных: {e}")
            return None
    
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
    
    def check_compliance(self, post_id):
        """Проверка соответствия критериям для одной статьи"""
        try:
            article_data = self.get_article_seo_data(post_id)
            if not article_data:
                return {
                    'post_id': post_id,
                    'title': 'НЕ НАЙДЕНА',
                    'status': 'error',
                    'issues': ['Статья не найдена в базе данных']
                }
            
            post_title = article_data['post_title']
            current_slug = article_data['post_name']
            meta = article_data.get('meta', {})
            focus_keyword = meta.get('_yoast_wpseo_focuskw', '')
            meta_description = meta.get('_yoast_wpseo_metadesc', '')
            
            issues = []
            status = 'compliant'
            
            # Проверка 1: Соответствие slug фокусному ключевому слову
            if focus_keyword:
                expected_slug = self.transliterate_to_latin(focus_keyword)
                if current_slug != expected_slug:
                    issues.append(f"Slug не соответствует. Ожидается: {expected_slug}, текущий: {current_slug}")
                    status = 'non_compliant'
            else:
                issues.append("Отсутствует фокусное ключевое слово")
                status = 'non_compliant'
            
            # Проверка 2: Соответствие мета-описания фокусному ключевому слову
            if meta_description and focus_keyword:
                if not meta_description.strip().lower().startswith(focus_keyword.lower()):
                    issues.append(f"Мета-описание не начинается с ключевого слова. Начало: {meta_description[:50]}...")
                    status = 'non_compliant'
            elif not meta_description:
                issues.append("Отсутствует мета-описание")
                status = 'non_compliant'
            
            return {
                'post_id': post_id,
                'title': post_title,
                'focus_keyword': focus_keyword,
                'current_slug': current_slug,
                'meta_description': meta_description,
                'status': status,
                'issues': issues
            }
            
        except Exception as e:
            return {
                'post_id': post_id,
                'title': 'ОШИБКА',
                'status': 'error',
                'issues': [f"Ошибка обработки: {str(e)}"]
            }
    
    def control_check_all_articles(self):
        """Контрольная проверка всех исправленных статей"""
        print("🔍 КОНТРОЛЬНАЯ ПРОВЕРКА ИСПРАВЛЕННЫХ СТАТЕЙ")
        print("=" * 80)
        print("Проверяемые критерии:")
        print("1. Ярлык (slug) соответствует фокусному ключевому слову (латинскими буквами)")
        print("2. Мета-описание начинается с фокусного ключевого слова (русскими буквами)")
        print("=" * 80)
        
        self.control_stats['total'] = len(FIXED_ARTICLES)
        
        for i, post_id in enumerate(FIXED_ARTICLES, 1):
            print(f"\n📋 {i}/{len(FIXED_ARTICLES)} Проверка ID {post_id}")
            
            result = self.check_compliance(post_id)
            
            if result['status'] == 'compliant':
                print(f"✅ {result['title']}")
                print(f"   🎯 Ключевое слово: {result['focus_keyword']}")
                print(f"   🔗 Slug: {result['current_slug']}")
                print(f"   📝 Мета-описание: {result['meta_description'][:60]}...")
                self.control_stats['compliant'] += 1
                
            elif result['status'] == 'non_compliant':
                print(f"❌ {result['title']}")
                print(f"   🎯 Ключевое слово: {result['focus_keyword']}")
                print(f"   🔗 Slug: {result['current_slug']}")
                print(f"   📝 Мета-описание: {result['meta_description'][:60]}...")
                for issue in result['issues']:
                    print(f"   ⚠️  {issue}")
                
                self.control_stats['non_compliant'] += 1
                self.control_stats['problematic_articles'].append(result)
                
            else:  # error
                print(f"🚨 {result['title']}")
                for issue in result['issues']:
                    print(f"   ❌ {issue}")
                self.control_stats['errors'] += 1
        
        return self.control_stats
    
    def print_control_report(self):
        """Вывод отчета контрольной проверки"""
        print("\n" + "=" * 80)
        print("📊 ОТЧЕТ КОНТРОЛЬНОЙ ПРОВЕРКИ")
        print("=" * 80)
        
        print(f"📚 Всего проверено статей: {self.control_stats['total']}")
        print(f"✅ Соответствуют критериям: {self.control_stats['compliant']}")
        print(f"❌ Не соответствуют критериям: {self.control_stats['non_compliant']}")
        print(f"🚨 Ошибки: {self.control_stats['errors']}")
        
        if self.control_stats['total'] > 0:
            compliance_rate = (self.control_stats['compliant'] / self.control_stats['total']) * 100
            print(f"📊 Процент соответствия: {compliance_rate:.1f}%")
        
        if self.control_stats['problematic_articles']:
            print(f"\n🚨 СТАТЬИ ТРЕБУЮЩИЕ ДОРАБОТКИ ({len(self.control_stats['problematic_articles'])}):")
            print("-" * 80)
            
            for article in self.control_stats['problematic_articles']:
                print(f"\n📄 ID {article['post_id']}: {article['title']}")
                print(f"   🎯 Ключевое слово: {article['focus_keyword']}")
                print(f"   🔗 Текущий slug: {article['current_slug']}")
                print(f"   📝 Мета-описание: {article['meta_description'][:80]}...")
                print(f"   🔗 Ссылка: https://ecopackpro.ru/wp-admin/post.php?post={article['post_id']}&action=edit")
                
                for issue in article['issues']:
                    print(f"   ⚠️  {issue}")
        
        return self.control_stats['problematic_articles']

def main():
    """Основная функция"""
    checker = FinalControlChecker()
    
    # Проводим контрольную проверку
    stats = checker.control_check_all_articles()
    
    # Выводим отчет
    problematic_articles = checker.print_control_report()
    
    print(f"\n" + "=" * 80)
    if problematic_articles:
        print(f"🚨 НАЙДЕНО {len(problematic_articles)} СТАТЕЙ ТРЕБУЮЩИХ ДОРАБОТКИ")
        print(f"📝 Список ссылок:")
        print("-" * 40)
        for article in problematic_articles:
            print(f"https://ecopackpro.ru/wp-admin/post.php?post={article['post_id']}&action=edit")
    else:
        print(f"🎉 ВСЕ СТАТЬИ СООТВЕТСТВУЮТ КРИТЕРИЯМ!")
    
    print(f"\n✅ КОНТРОЛЬНАЯ ПРОВЕРКА ЗАВЕРШЕНА!")

if __name__ == "__main__":
    main()
