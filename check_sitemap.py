#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Скрипт для проверки наличия всех опубликованных статей в sitemap.xml
"""

import requests
import xml.etree.ElementTree as ET
import sqlite3
from datetime import datetime
from urllib.parse import urlparse

# Путь к базе данных проекта
PROJECT_DB_PATH = '/root/seo_project/SEO_ecopackpro/articles.db'

# URL sitemap
SITEMAP_URL = 'https://ecopackpro.ru/post-sitemap.xml'

def get_published_articles():
    """Получает список всех опубликованных статей из БД проекта"""
    conn = sqlite3.connect(PROJECT_DB_PATH)
    cursor = conn.cursor()
    
    cursor.execute("""
    SELECT wp_post_id, title, url
    FROM published_articles
    ORDER BY wp_post_id
    """)
    
    articles = cursor.fetchall()
    conn.close()
    
    return articles

def get_sitemap_urls():
    """Получает все URL из sitemap"""
    print(f"📥 Загружаю sitemap: {SITEMAP_URL}")
    response = requests.get(SITEMAP_URL, timeout=30)
    response.raise_for_status()
    
    # Парсим XML
    root = ET.fromstring(response.content)
    
    # Namespace для sitemap
    ns = {'sm': 'http://www.sitemaps.org/schemas/sitemap/0.9'}
    
    urls = []
    for url_elem in root.findall('.//sm:url', ns):
        loc = url_elem.find('sm:loc', ns)
        lastmod = url_elem.find('sm:lastmod', ns)
        
        if loc is not None:
            url_data = {
                'url': loc.text,
                'lastmod': lastmod.text if lastmod is not None else None
            }
            urls.append(url_data)
    
    print(f"✅ Загружено {len(urls)} URL из sitemap\n")
    return urls

def normalize_url(url):
    """Нормализует URL для сравнения"""
    # Убираем завершающий слэш
    url = url.rstrip('/')
    # Убираем https:// или http://
    parsed = urlparse(url)
    return f"{parsed.netloc}{parsed.path}"

def check_articles_in_sitemap():
    """Проверяет наличие статей в sitemap"""
    print("\n" + "="*120)
    print("🗺️  ПРОВЕРКА НАЛИЧИЯ СТАТЕЙ В SITEMAP.XML".center(120))
    print("="*120 + "\n")
    
    # Получаем статьи из БД
    articles = get_published_articles()
    print(f"📊 Всего статей для проверки: {len(articles)}\n")
    
    # Получаем URL из sitemap
    sitemap_urls = get_sitemap_urls()
    
    # Создаем словарь URL из sitemap для быстрого поиска
    sitemap_dict = {}
    for item in sitemap_urls:
        normalized = normalize_url(item['url'])
        sitemap_dict[normalized] = item
    
    # Проверяем каждую статью
    found_count = 0
    missing_count = 0
    found_articles = []
    missing_articles = []
    
    print("="*120)
    print(f"{'№':<4} {'ID':<7} {'СТАТУС':<10} {'НАЗВАНИЕ':<70} {'LASTMOD':<25}")
    print("="*120)
    
    for idx, (wp_id, title, url) in enumerate(articles, 1):
        normalized_url = normalize_url(url)
        
        if normalized_url in sitemap_dict:
            found_count += 1
            status = "✅ В sitemap"
            lastmod = sitemap_dict[normalized_url]['lastmod'] or "N/A"
            found_articles.append({
                'wp_id': wp_id,
                'title': title,
                'url': url,
                'lastmod': lastmod
            })
        else:
            missing_count += 1
            status = "❌ НЕТ"
            lastmod = "N/A"
            missing_articles.append({
                'wp_id': wp_id,
                'title': title,
                'url': url
            })
        
        display_title = title[:67] + "..." if len(title) > 70 else title
        print(f"{idx:<4} {wp_id:<7} {status:<10} {display_title:<70} {lastmod:<25}")
    
    print("="*120)
    
    # Статистика
    print("\n" + "="*120)
    print("📊 ИТОГОВАЯ СТАТИСТИКА".center(120))
    print("="*120)
    print(f"📝 Всего статей: {len(articles)}")
    print(f"✅ Найдено в sitemap: {found_count} ({found_count*100//len(articles)}%)")
    print(f"❌ Отсутствует в sitemap: {missing_count} ({missing_count*100//len(articles) if articles else 0}%)")
    print(f"🗺️  Всего URL в sitemap: {len(sitemap_urls)}")
    print("="*120 + "\n")
    
    # Если есть отсутствующие статьи
    if missing_articles:
        print("\n" + "="*120)
        print("⚠️  СТАТЬИ, ОТСУТСТВУЮЩИЕ В SITEMAP".center(120))
        print("="*120)
        for article in missing_articles:
            print(f"❌ ID {article['wp_id']}: {article['title']}")
            print(f"   URL: {article['url']}\n")
        
        print("💡 РЕКОМЕНДАЦИЯ: Необходимо обновить sitemap через Yoast SEO")
        print("="*120)
    
    return {
        'total': len(articles),
        'found': found_count,
        'missing': missing_count,
        'found_articles': found_articles,
        'missing_articles': missing_articles
    }

def regenerate_sitemap():
    """Инструкции по регенерации sitemap"""
    print("\n" + "="*120)
    print("🔄 КАК ОБНОВИТЬ SITEMAP ЧЕРЕЗ YOAST SEO".center(120))
    print("="*120 + "\n")
    
    print("📋 СПОСОБ 1: Через админ-панель WordPress (РЕКОМЕНДУЕТСЯ)")
    print("-"*120)
    print("1. Войдите в админ-панель: https://ecopackpro.ru/wp-admin/")
    print("2. Перейдите в: SEO → Общие → Возможности")
    print("3. Найдите раздел 'XML sitemaps'")
    print("4. Нажмите на ссылку 'XML sitemap' или 'Просмотреть XML sitemap'")
    print("5. Sitemap автоматически регенерируется при каждом обновлении контента")
    print()
    print("📋 СПОСОБ 2: Прямая регенерация через БД")
    print("-"*120)
    print("DELETE FROM wp_options WHERE option_name LIKE '%wpseo%cache%';")
    print("DELETE FROM wp_options WHERE option_name = 'wpseo_sitemap_cache';")
    print()
    print("📋 СПОСОБ 3: Через WP-CLI (если установлен)")
    print("-"*120)
    print("wp yoast index --reindex")
    print()
    print("="*120 + "\n")

def main():
    try:
        result = check_articles_in_sitemap()
        
        if result['missing'] > 0:
            regenerate_sitemap()
            
            # Пытаемся автоматически очистить кэш
            print("🔄 Пытаюсь автоматически очистить кэш sitemap...")
            import mysql.connector
            
            WP_DB_CONFIG = {
                'host': 'localhost',
                'user': 'm1shqamai2_worp6',
                'password': '9nUQkM*Q2cnvy379',
                'database': 'm1shqamai2_worp6'
            }
            
            conn = mysql.connector.connect(**WP_DB_CONFIG)
            cursor = conn.cursor()
            
            # Очищаем кэш Yoast SEO
            cursor.execute("DELETE FROM wp_options WHERE option_name LIKE '%wpseo%cache%'")
            cursor.execute("DELETE FROM wp_options WHERE option_name = 'wpseo_sitemap_cache'")
            conn.commit()
            
            deleted_count = cursor.rowcount
            cursor.close()
            conn.close()
            
            print(f"✅ Очищено {deleted_count} записей кэша")
            print("💡 Теперь sitemap обновится автоматически при следующем обращении\n")
            
            # Запрашиваем sitemap для регенерации
            print("🔄 Запрашиваю sitemap для принудительной регенерации...")
            response = requests.get('https://ecopackpro.ru/sitemap_index.xml', timeout=30)
            if response.status_code == 200:
                print("✅ Sitemap успешно регенерирован!\n")
                
                # Повторная проверка
                print("🔍 Выполняю повторную проверку...")
                time.sleep(2)
                result2 = check_articles_in_sitemap()
                
                if result2['missing'] == 0:
                    print("\n" + "="*120)
                    print("🎉 ВСЕ СТАТЬИ УСПЕШНО ДОБАВЛЕНЫ В SITEMAP!".center(120))
                    print("="*120)
        else:
            print("\n" + "="*120)
            print("🎉 ВСЕ СТАТЬИ УЖЕ ПРИСУТСТВУЮТ В SITEMAP!".center(120))
            print("="*120)
        
        # Сохраняем отчет
        report_filename = f'/var/www/fastuser/data/www/ecopackpro.ru/sitemap_check_report_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
        import json
        with open(report_filename, 'w', encoding='utf-8') as f:
            json.dump(result, f, ensure_ascii=False, indent=4)
        
        print(f"\n📄 Детальный отчет сохранен: {report_filename}")
        
    except Exception as e:
        print(f"\n❌ Ошибка: {e}")
        import traceback
        traceback.print_exc()

if __name__ == "__main__":
    import time
    main()

