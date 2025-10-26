#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Финальный отчет по всем 50 опубликованным статьям
"""

import mysql.connector
import requests
from bs4 import BeautifulSoup
import re
from datetime import datetime
import xml.etree.ElementTree as ET

# Параметры подключения к MySQL (WordPress)
WP_DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

BASE_URL = 'https://ecopackpro.ru'
SITEMAP_URL = f'{BASE_URL}/post-sitemap.xml'

def count_words_in_html(html_content):
    """Подсчитывает количество слов в HTML"""
    soup = BeautifulSoup(html_content, 'html.parser')
    for script in soup(["script", "style"]):
        script.decompose()
    text = soup.get_text()
    words = re.findall(r'\b[а-яёА-ЯЁa-zA-Z]+\b', text)
    return len(words)

def get_all_published_articles():
    """Получает все 50 опубликованных статей"""
    conn = mysql.connector.connect(**WP_DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    
    query = """
    SELECT 
        ID,
        post_title,
        post_name,
        post_content,
        post_date,
        post_modified,
        CHAR_LENGTH(post_content) as content_length
    FROM wp_posts
    WHERE post_status = 'publish' 
    AND post_type = 'post'
    AND ID >= 7907
    ORDER BY ID
    """
    
    cursor.execute(query)
    articles = cursor.fetchall()
    
    cursor.close()
    conn.close()
    
    return articles

def check_url_status(url):
    """Проверяет HTTP статус"""
    try:
        response = requests.get(url, timeout=10, allow_redirects=True)
        return response.status_code
    except:
        return None

def get_sitemap_urls():
    """Получает все URL из sitemap"""
    try:
        response = requests.get(SITEMAP_URL, timeout=30)
        root = ET.fromstring(response.content)
        ns = {'sm': 'http://www.sitemaps.org/schemas/sitemap/0.9'}
        
        urls = set()
        for url_elem in root.findall('.//sm:url', ns):
            loc = url_elem.find('sm:loc', ns)
            if loc is not None:
                urls.add(loc.text.rstrip('/'))
        
        return urls
    except:
        return set()

def main():
    print("\n" + "="*120)
    print("📊 ФИНАЛЬНЫЙ ОТЧЕТ ПО ВСЕМ 50 ОПУБЛИКОВАННЫМ СТАТЬЯМ".center(120))
    print("="*120 + "\n")
    
    start_time = datetime.now()
    
    # Получаем все статьи
    print("📥 Загружаю данные из WordPress...")
    articles = get_all_published_articles()
    print(f"✅ Получено {len(articles)} статей\n")
    
    # Получаем sitemap
    print("🗺️  Загружаю sitemap...")
    sitemap_urls = get_sitemap_urls()
    print(f"✅ В sitemap {len(sitemap_urls)} URL\n")
    
    print("="*120)
    print(f"{'№':<4} {'ID':<6} {'HTTP':<6} {'SITEMAP':<9} {'СЛОВ':<6} {'НАЗВАНИЕ':<60}")
    print("="*120)
    
    results = []
    http_200_count = 0
    in_sitemap_count = 0
    total_words = 0
    
    for idx, article in enumerate(articles, 1):
        wp_id = article['ID']
        title = article['post_title']
        slug = article['post_name']
        url = f"{BASE_URL}/{slug}/"
        content = article['post_content']
        content_length = article['content_length']
        
        # Подсчитываем слова
        word_count = count_words_in_html(content)
        total_words += word_count
        
        # Проверяем HTTP статус
        http_status = check_url_status(url)
        if http_status == 200:
            http_200_count += 1
            http_icon = "✅"
        else:
            http_icon = "❌"
        
        # Проверяем sitemap
        url_normalized = url.rstrip('/')
        in_sitemap = url_normalized in sitemap_urls
        if in_sitemap:
            in_sitemap_count += 1
            sitemap_icon = "✅"
        else:
            sitemap_icon = "❌"
        
        display_title = title[:57] + "..." if len(title) > 60 else title
        
        print(f"{idx:<4} {wp_id:<6} {http_icon} {http_status or 'ERR':<4} {sitemap_icon} {'Да' if in_sitemap else 'Нет':<6} {word_count:<6} {display_title}")
        
        results.append({
            'id': wp_id,
            'title': title,
            'url': url,
            'http_status': http_status,
            'in_sitemap': in_sitemap,
            'word_count': word_count,
            'content_length': content_length
        })
    
    print("="*120)
    
    # Статистика
    print("\n" + "="*120)
    print("📊 ИТОГОВАЯ СТАТИСТИКА".center(120))
    print("="*120)
    print(f"📝 Всего статей: {len(articles)}")
    print(f"✅ HTTP 200: {http_200_count} ({http_200_count*100//len(articles)}%)")
    print(f"✅ В sitemap: {in_sitemap_count} ({in_sitemap_count*100//len(articles)}%)")
    print(f"📝 Всего слов: {total_words:,}")
    print(f"📊 Среднее слов/статья: {total_words//len(articles)}")
    print(f"⏱️  Время выполнения: {datetime.now() - start_time}")
    print("="*120)
    
    # Топ-5 самых длинных
    print("\n📈 ТОП-5 САМЫХ ДЛИННЫХ СТАТЕЙ:")
    print("-"*120)
    top_5 = sorted(results, key=lambda x: x['word_count'], reverse=True)[:5]
    for idx, r in enumerate(top_5, 1):
        print(f"{idx}. ID {r['id']}: {r['title'][:70]}")
        print(f"   📝 Слов: {r['word_count']}, URL: {r['url']}\n")
    
    # Топ-5 самых коротких
    print("📉 ТОП-5 САМЫХ КОРОТКИХ СТАТЕЙ:")
    print("-"*120)
    bottom_5 = sorted(results, key=lambda x: x['word_count'])[:5]
    for idx, r in enumerate(bottom_5, 1):
        print(f"{idx}. ID {r['id']}: {r['title'][:70]}")
        print(f"   📝 Слов: {r['word_count']}, URL: {r['url']}\n")
    
    # Проверяем недостающие в sitemap
    missing_in_sitemap = [r for r in results if not r['in_sitemap']]
    if missing_in_sitemap:
        print("\n⚠️  СТАТЬИ, ОТСУТСТВУЮЩИЕ В SITEMAP:")
        print("-"*120)
        for r in missing_in_sitemap:
            print(f"❌ ID {r['id']}: {r['title']}")
            print(f"   URL: {r['url']}\n")
    
    # Проверяем недоступные
    unavailable = [r for r in results if r['http_status'] != 200]
    if unavailable:
        print("\n⚠️  НЕДОСТУПНЫЕ СТАТЬИ (НЕ HTTP 200):")
        print("-"*120)
        for r in unavailable:
            print(f"❌ ID {r['id']}: {r['title']}")
            print(f"   URL: {r['url']}, HTTP: {r['http_status']}\n")
    
    # Сохраняем отчет
    import json
    report_filename = f'final_50_articles_complete_report_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
    with open(report_filename, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=4)
    
    print(f"\n📄 Детальный отчет сохранен: {report_filename}")
    
    # Итоговое сообщение
    if http_200_count == len(articles) and in_sitemap_count == len(articles):
        print("\n" + "="*120)
        print("🎉 ВСЕ 50 СТАТЕЙ ОПУБЛИКОВАНЫ, ДОСТУПНЫ И ДОБАВЛЕНЫ В SITEMAP!".center(120))
        print("="*120)

if __name__ == "__main__":
    main()

