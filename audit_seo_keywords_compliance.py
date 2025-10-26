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

# Список всех 50 статей (кроме 7917, которая уже исправлена)
ARTICLE_IDS = [
    7204, 7205, 7206, 7207, 7253, 7208, 7209, 7210, 7211, 7212, 7213, 7214, 7215, 7216, 7217,
    7218, 7219, 7220, 7221, 7222, 7223, 7224, 7225, 7226, 7227, 7228, 7229, 7230, 7231, 7232,
    7233, 7234, 7235, 7236, 7237, 7238, 7239, 7240, 7241, 7242, 7243, 7244, 7245, 7246, 7247,
    7248, 7249, 7250, 7251, 7252, 7912, 7913, 7915, 7917, 7926, 7928, 7929, 7930, 7932, 7934,
    7938, 7939, 7941, 7943, 7944, 7945, 7946, 7947, 7948, 7949, 7952, 7953, 7954, 7955, 7956
]

class SEOKeywordsAuditor:
    def __init__(self):
        self.db_config = DB_CONFIG
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
        
        # Статистика аудита
        self.audit_stats = {
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
    
    def check_slug_compliance(self, post_title, current_slug, focus_keyword):
        """Проверка соответствия slug фокусному ключевому слову"""
        if not current_slug or not focus_keyword:
            return False, "Отсутствует slug или фокусное ключевое слово"
        
        # Транслитерируем фокусное ключевое слово
        expected_slug = self.transliterate_to_latin(focus_keyword)
        
        # Проверяем соответствие
        if current_slug == expected_slug:
            return True, f"Slug соответствует: {current_slug}"
        else:
            return False, f"Slug не соответствует. Ожидается: {expected_slug}, текущий: {current_slug}"
    
    def check_meta_description_compliance(self, meta_description, focus_keyword):
        """Проверка соответствия мета-описания фокусному ключевому слову"""
        if not meta_description or not focus_keyword:
            return False, "Отсутствует мета-описание или фокусное ключевое слово"
        
        # Проверяем, начинается ли мета-описание с фокусного ключевого слова
        if meta_description.strip().lower().startswith(focus_keyword.lower()):
            return True, f"Мета-описание начинается с ключевого слова: {focus_keyword}"
        else:
            return False, f"Мета-описание не начинается с ключевого слова. Начало: {meta_description[:50]}..."
    
    def audit_single_article(self, post_id):
        """Аудит одной статьи"""
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
            slug_ok, slug_message = self.check_slug_compliance(post_title, current_slug, focus_keyword)
            if not slug_ok:
                issues.append(f"Slug: {slug_message}")
                status = 'non_compliant'
            
            # Проверка 2: Соответствие мета-описания фокусному ключевому слову
            meta_ok, meta_message = self.check_meta_description_compliance(meta_description, focus_keyword)
            if not meta_ok:
                issues.append(f"Мета-описание: {meta_message}")
                status = 'non_compliant'
            
            return {
                'post_id': post_id,
                'title': post_title,
                'focus_keyword': focus_keyword,
                'current_slug': current_slug,
                'meta_description': meta_description,
                'status': status,
                'issues': issues,
                'slug_ok': slug_ok,
                'meta_ok': meta_ok
            }
            
        except Exception as e:
            return {
                'post_id': post_id,
                'title': 'ОШИБКА',
                'status': 'error',
                'issues': [f"Ошибка обработки: {str(e)}"]
            }
    
    def audit_all_articles(self):
        """Аудит всех статей"""
        print("🔍 АУДИТ СООТВЕТСТВИЯ SEO ПАРАМЕТРОВ")
        print("=" * 80)
        print("Проверяемые критерии:")
        print("1. Ярлык (slug) соответствует фокусному ключевому слову (латинскими буквами)")
        print("2. Мета-описание начинается с фокусного ключевого слова (русскими буквами)")
        print("=" * 80)
        
        self.audit_stats['total'] = len(ARTICLE_IDS)
        
        for i, post_id in enumerate(ARTICLE_IDS, 1):
            print(f"\n📋 {i}/{len(ARTICLE_IDS)} Проверка ID {post_id}")
            
            result = self.audit_single_article(post_id)
            
            if result['status'] == 'compliant':
                print(f"✅ {result['title']}")
                print(f"   🎯 Ключевое слово: {result['focus_keyword']}")
                print(f"   🔗 Slug: {result['current_slug']}")
                print(f"   📝 Мета-описание: {result['meta_description'][:60]}...")
                self.audit_stats['compliant'] += 1
                
            elif result['status'] == 'non_compliant':
                print(f"❌ {result['title']}")
                print(f"   🎯 Ключевое слово: {result['focus_keyword']}")
                print(f"   🔗 Slug: {result['current_slug']} {'✅' if result['slug_ok'] else '❌'}")
                print(f"   📝 Мета-описание: {'✅' if result['meta_ok'] else '❌'}")
                for issue in result['issues']:
                    print(f"   ⚠️  {issue}")
                
                self.audit_stats['non_compliant'] += 1
                self.audit_stats['problematic_articles'].append(result)
                
            else:  # error
                print(f"🚨 {result['title']}")
                for issue in result['issues']:
                    print(f"   ❌ {issue}")
                self.audit_stats['errors'] += 1
        
        return self.audit_stats
    
    def print_audit_report(self):
        """Вывод отчета аудита"""
        print("\n" + "=" * 80)
        print("📊 ОТЧЕТ АУДИТА SEO ПАРАМЕТРОВ")
        print("=" * 80)
        
        print(f"📚 Всего проверено статей: {self.audit_stats['total']}")
        print(f"✅ Соответствуют критериям: {self.audit_stats['compliant']}")
        print(f"❌ Не соответствуют критериям: {self.audit_stats['non_compliant']}")
        print(f"🚨 Ошибки: {self.audit_stats['errors']}")
        
        if self.audit_stats['total'] > 0:
            compliance_rate = (self.audit_stats['compliant'] / self.audit_stats['total']) * 100
            print(f"📊 Процент соответствия: {compliance_rate:.1f}%")
        
        if self.audit_stats['problematic_articles']:
            print(f"\n🚨 ПРОБЛЕМНЫЕ СТАТЬИ ({len(self.audit_stats['problematic_articles'])}):")
            print("-" * 80)
            
            for article in self.audit_stats['problematic_articles']:
                print(f"\n📄 ID {article['post_id']}: {article['title']}")
                print(f"   🎯 Ключевое слово: {article['focus_keyword']}")
                print(f"   🔗 Текущий slug: {article['current_slug']}")
                print(f"   📝 Мета-описание: {article['meta_description'][:80]}...")
                print(f"   🔗 Ссылка: https://ecopackpro.ru/wp-admin/post.php?post={article['post_id']}&action=edit")
                
                for issue in article['issues']:
                    print(f"   ⚠️  {issue}")
        
        return self.audit_stats['problematic_articles']

def main():
    """Основная функция"""
    auditor = SEOKeywordsAuditor()
    
    # Проводим аудит всех статей
    stats = auditor.audit_all_articles()
    
    # Выводим отчет
    problematic_articles = auditor.print_audit_report()
    
    print(f"\n" + "=" * 80)
    if problematic_articles:
        print(f"🚨 НАЙДЕНО {len(problematic_articles)} ПРОБЛЕМНЫХ СТАТЕЙ")
        print(f"📝 Список ссылок на проблемные статьи:")
        print("-" * 40)
        for article in problematic_articles:
            print(f"https://ecopackpro.ru/wp-admin/post.php?post={article['post_id']}&action=edit")
    else:
        print(f"🎉 ВСЕ СТАТЬИ СООТВЕТСТВУЮТ КРИТЕРИЯМ!")
    
    print(f"\n✅ АУДИТ ЗАВЕРШЕН!")

if __name__ == "__main__":
    main()
