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

# Список всех 50 статей с ключевыми словами
KEYWORDS_LIST = [
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

class ComprehensiveSEOAuditor:
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
            'problematic_articles': [],
            'compliant_articles': []
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
    
    def find_article_by_keyword(self, keyword):
        """Поиск статьи по ключевому слову"""
        connection = self.connect_to_database()
        if not connection:
            return None
        
        try:
            cursor = connection.cursor(dictionary=True)
            
            # Ищем статью по ключевому слову в фокусном ключевом слове
            cursor.execute("""
                SELECT p.ID, p.post_title, p.post_name, p.post_content, p.post_excerpt
                FROM wp_posts p
                INNER JOIN wp_postmeta pm ON p.ID = pm.post_id
                WHERE pm.meta_key = '_yoast_wpseo_focuskw' 
                AND pm.meta_value = %s
                AND p.post_status = 'publish'
                ORDER BY p.ID DESC
                LIMIT 1
            """, (keyword,))
            
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
            """, (post_data['ID'],))
            
            meta_data = cursor.fetchall()
            meta_dict = {row['meta_key']: row['meta_value'] for row in meta_data}
            
            post_data['meta'] = meta_dict
            return post_data
            
        except mysql.connector.Error as e:
            print(f"❌ Ошибка поиска статьи для ключевого слова '{keyword}': {e}")
            return None
        finally:
            connection.close()
    
    def check_article_compliance(self, keyword, article_data):
        """Проверка соответствия статьи критериям"""
        if not article_data:
            return {
                'keyword': keyword,
                'post_id': None,
                'title': 'НЕ НАЙДЕНА',
                'status': 'error',
                'issues': ['Статья с данным ключевым словом не найдена'],
                'focus_keyword_match': False,
                'slug_match': False,
                'meta_description_match': False
            }
        
        post_id = article_data['ID']
        post_title = article_data['post_title']
        current_slug = article_data['post_name']
        meta = article_data.get('meta', {})
        focus_keyword = meta.get('_yoast_wpseo_focuskw', '')
        meta_description = meta.get('_yoast_wpseo_metadesc', '')
        
        issues = []
        status = 'compliant'
        
        # Проверка 1: Фокусное ключевое слово соответствует названию статьи
        focus_keyword_match = (focus_keyword == keyword)
        if not focus_keyword_match:
            issues.append(f"Фокусное ключевое слово не соответствует: ожидается '{keyword}', получено '{focus_keyword}'")
            status = 'non_compliant'
        
        # Проверка 2: Ярлык соответствует фокусному ключевому слову (латинскими буквами)
        expected_slug = self.transliterate_to_latin(keyword)
        slug_match = (current_slug == expected_slug)
        if not slug_match:
            issues.append(f"Ярлык не соответствует: ожидается '{expected_slug}', получен '{current_slug}'")
            status = 'non_compliant'
        
        # Проверка 3: Мета-описание начинается с фокусного ключевого слова (русскими буквами)
        meta_description_match = False
        if meta_description and focus_keyword:
            meta_description_match = meta_description.strip().lower().startswith(focus_keyword.lower())
            if not meta_description_match:
                issues.append(f"Мета-описание не начинается с ключевого слова: начало '{meta_description[:50]}...'")
                status = 'non_compliant'
        elif not meta_description:
            issues.append("Отсутствует мета-описание")
            status = 'non_compliant'
        elif not focus_keyword:
            issues.append("Отсутствует фокусное ключевое слово")
            status = 'non_compliant'
        
        return {
            'keyword': keyword,
            'post_id': post_id,
            'title': post_title,
            'focus_keyword': focus_keyword,
            'current_slug': current_slug,
            'expected_slug': expected_slug,
            'meta_description': meta_description,
            'status': status,
            'issues': issues,
            'focus_keyword_match': focus_keyword_match,
            'slug_match': slug_match,
            'meta_description_match': meta_description_match
        }
    
    def audit_all_articles(self):
        """Аудит всех 50 статей"""
        print("🔍 КОМПЛЕКСНЫЙ АУДИТ 50 СТАТЕЙ ПО SEO КРИТЕРИЯМ")
        print("=" * 80)
        print("Проверяемые критерии:")
        print("1. Фокусное ключевое слово соответствует названию статьи")
        print("2. Ярлык (slug) соответствует фокусному ключевому слову (латинскими буквами)")
        print("3. Мета-описание начинается с фокусного ключевого слова (русскими буквами)")
        print("=" * 80)
        
        self.audit_stats['total'] = len(KEYWORDS_LIST)
        
        for i, keyword in enumerate(KEYWORDS_LIST, 1):
            print(f"\n📋 {i}/{len(KEYWORDS_LIST)} Проверка: {keyword}")
            
            # Ищем статью по ключевому слову
            article_data = self.find_article_by_keyword(keyword)
            
            # Проверяем соответствие критериям
            result = self.check_article_compliance(keyword, article_data)
            
            if result['status'] == 'compliant':
                print(f"✅ {result['title']} (ID: {result['post_id']})")
                print(f"   🎯 Фокусное ключевое слово: ✅")
                print(f"   🔗 Ярлык: ✅")
                print(f"   📝 Мета-описание: ✅")
                self.audit_stats['compliant'] += 1
                self.audit_stats['compliant_articles'].append(result)
                
            elif result['status'] == 'non_compliant':
                print(f"❌ {result['title']} (ID: {result['post_id']})")
                print(f"   🎯 Фокусное ключевое слово: {'✅' if result['focus_keyword_match'] else '❌'}")
                print(f"   🔗 Ярлык: {'✅' if result['slug_match'] else '❌'}")
                print(f"   📝 Мета-описание: {'✅' if result['meta_description_match'] else '❌'}")
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
    
    def print_comprehensive_report(self):
        """Вывод комплексного отчета"""
        print("\n" + "=" * 80)
        print("📊 КОМПЛЕКСНЫЙ ОТЧЕТ АУДИТА 50 СТАТЕЙ")
        print("=" * 80)
        
        print(f"📚 Всего проверено статей: {self.audit_stats['total']}")
        print(f"✅ Соответствуют всем критериям: {self.audit_stats['compliant']}")
        print(f"❌ Не соответствуют критериям: {self.audit_stats['non_compliant']}")
        print(f"🚨 Ошибки (статьи не найдены): {self.audit_stats['errors']}")
        
        if self.audit_stats['total'] > 0:
            compliance_rate = (self.audit_stats['compliant'] / self.audit_stats['total']) * 100
            print(f"📊 Процент соответствия: {compliance_rate:.1f}%")
        
        # Детальная статистика по критериям
        print(f"\n📊 ДЕТАЛЬНАЯ СТАТИСТИКА ПО КРИТЕРИЯМ:")
        print("-" * 60)
        
        focus_keyword_ok = sum(1 for article in self.audit_stats['compliant_articles'] if article['focus_keyword_match'])
        slug_ok = sum(1 for article in self.audit_stats['compliant_articles'] if article['slug_match'])
        meta_ok = sum(1 for article in self.audit_stats['compliant_articles'] if article['meta_description_match'])
        
        print(f"🎯 Фокусное ключевое слово соответствует названию: {focus_keyword_ok}/{self.audit_stats['total']} ({focus_keyword_ok/self.audit_stats['total']*100:.1f}%)")
        print(f"🔗 Ярлык соответствует фокусному ключевому слову: {slug_ok}/{self.audit_stats['total']} ({slug_ok/self.audit_stats['total']*100:.1f}%)")
        print(f"📝 Мета-описание начинается с ключевого слова: {meta_ok}/{self.audit_stats['total']} ({meta_ok/self.audit_stats['total']*100:.1f}%)")
        
        if self.audit_stats['compliant_articles']:
            print(f"\n✅ СТАТЬИ, СООТВЕТСТВУЮЩИЕ ВСЕМ КРИТЕРИЯМ ({len(self.audit_stats['compliant_articles'])}):")
            print("-" * 80)
            for article in self.audit_stats['compliant_articles']:
                print(f"📄 ID {article['post_id']}: {article['title']}")
                print(f"   🎯 Ключевое слово: {article['focus_keyword']}")
                print(f"   🔗 Ярлык: {article['current_slug']}")
                print(f"   📝 Мета-описание: {article['meta_description'][:60]}...")
                print(f"   🔗 Ссылка: https://ecopackpro.ru/wp-admin/post.php?post={article['post_id']}&action=edit")
                print()
        
        if self.audit_stats['problematic_articles']:
            print(f"\n🚨 СТАТЬИ, ТРЕБУЮЩИЕ ДОРАБОТКИ ({len(self.audit_stats['problematic_articles'])}):")
            print("-" * 80)
            for article in self.audit_stats['problematic_articles']:
                print(f"📄 ID {article['post_id']}: {article['title']}")
                print(f"   🎯 Ключевое слово: {article['focus_keyword']}")
                print(f"   🔗 Ярлык: {article['current_slug']} (ожидается: {article['expected_slug']})")
                print(f"   📝 Мета-описание: {article['meta_description'][:60]}...")
                print(f"   🔗 Ссылка: https://ecopackpro.ru/wp-admin/post.php?post={article['post_id']}&action=edit")
                for issue in article['issues']:
                    print(f"   ⚠️  {issue}")
                print()
        
        return self.audit_stats['problematic_articles'], self.audit_stats['compliant_articles']

def main():
    """Основная функция"""
    auditor = ComprehensiveSEOAuditor()
    
    # Проводим комплексный аудит
    stats = auditor.audit_all_articles()
    
    # Выводим отчет
    problematic_articles, compliant_articles = auditor.print_comprehensive_report()
    
    print(f"\n" + "=" * 80)
    if problematic_articles:
        print(f"🚨 НАЙДЕНО {len(problematic_articles)} СТАТЕЙ ТРЕБУЮЩИХ ДОРАБОТКИ")
        print(f"📝 Список ссылок на проблемные статьи:")
        print("-" * 40)
        for article in problematic_articles:
            print(f"https://ecopackpro.ru/wp-admin/post.php?post={article['post_id']}&action=edit")
    else:
        print(f"🎉 ВСЕ 50 СТАТЕЙ СООТВЕТСТВУЮТ КРИТЕРИЯМ!")
    
    print(f"\n✅ КОМПЛЕКСНЫЙ АУДИТ ЗАВЕРШЕН!")

if __name__ == "__main__":
    main()
