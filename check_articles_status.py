#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Скрипт для проверки доступности всех опубликованных статей (HTTP 200)
и подсчета количества слов в каждой статье
"""

import sqlite3
import requests
import mysql.connector
from bs4 import BeautifulSoup
import re
import time
from datetime import datetime

# Путь к базе данных проекта
PROJECT_DB_PATH = '/root/seo_project/SEO_ecopackpro/articles.db'

# Параметры подключения к MySQL (WordPress)
WP_DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

def count_words_in_html(html_content):
    """Подсчитывает количество слов в HTML-контенте"""
    # Удаляем HTML-теги
    soup = BeautifulSoup(html_content, 'html.parser')
    
    # Удаляем script и style элементы
    for script in soup(["script", "style"]):
        script.decompose()
    
    # Получаем текст
    text = soup.get_text()
    
    # Разбиваем на слова (кириллица и латиница)
    words = re.findall(r'\b[а-яёА-ЯЁa-zA-Z]+\b', text)
    
    return len(words)

def check_url_status(url):
    """Проверяет HTTP статус URL"""
    try:
        response = requests.get(url, timeout=10, allow_redirects=True)
        return response.status_code, response.reason
    except requests.exceptions.RequestException as e:
        return None, str(e)

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

def get_word_counts_from_wordpress():
    """Получает количество слов в каждой статье из WordPress БД"""
    conn = mysql.connector.connect(**WP_DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    
    query = """
    SELECT 
        ID,
        post_title,
        post_content,
        CHAR_LENGTH(post_content) as content_length
    FROM wp_posts
    WHERE post_status = 'publish' 
    AND post_type = 'post'
    AND ID >= 7907
    ORDER BY ID
    """
    
    cursor.execute(query)
    articles = cursor.fetchall()
    
    word_counts = {}
    for article in articles:
        word_count = count_words_in_html(article['post_content'])
        word_counts[article['ID']] = {
            'title': article['post_title'],
            'word_count': word_count,
            'content_length': article['content_length']
        }
    
    cursor.close()
    conn.close()
    
    return word_counts

def main():
    print("\n" + "="*120)
    print("🔍 ПРОВЕРКА ДОСТУПНОСТИ И ПОДСЧЕТ СЛОВ В ОПУБЛИКОВАННЫХ СТАТЬЯХ".center(120))
    print("="*120 + "\n")
    
    start_time = datetime.now()
    
    # Получаем список статей
    print("📊 Получаю список опубликованных статей...")
    articles = get_published_articles()
    print(f"✅ Получено {len(articles)} статей\n")
    
    # Получаем количество слов из WordPress
    print("📝 Подсчитываю количество слов в каждой статье...")
    word_counts = get_word_counts_from_wordpress()
    print(f"✅ Подсчитано слов в {len(word_counts)} статьях\n")
    
    print("🌐 Проверяю доступность URL...\n")
    print("="*120)
    
    results = []
    success_count = 0
    failed_count = 0
    
    for idx, (wp_id, title, url) in enumerate(articles, 1):
        # Проверяем HTTP статус
        status_code, reason = check_url_status(url)
        
        # Получаем количество слов
        word_data = word_counts.get(wp_id, {'word_count': 0, 'content_length': 0})
        word_count = word_data['word_count']
        content_length = word_data['content_length']
        
        if status_code == 200:
            success_count += 1
            status_icon = "✅"
        else:
            failed_count += 1
            status_icon = "❌"
        
        result = {
            'index': idx,
            'wp_id': wp_id,
            'title': title,
            'url': url,
            'status_code': status_code,
            'reason': reason,
            'word_count': word_count,
            'content_length': content_length
        }
        results.append(result)
        
        # Выводим результат
        print(f"{idx:<3} {status_icon} ID {wp_id:<6} HTTP {status_code or 'ERROR':<4} | Слов: {word_count:<5} | {title[:60]}")
        print(f"    🔗 {url}")
        
        if status_code != 200:
            print(f"    ⚠️  {reason}")
        
        print("-"*120)
        
        # Задержка для предотвращения перегрузки сервера
        time.sleep(0.3)
    
    end_time = datetime.now()
    duration = end_time - start_time
    
    # Статистика
    print("\n" + "="*120)
    print("📊 ИТОГОВАЯ СТАТИСТИКА".center(120))
    print("="*120)
    print(f"⏱️  Время выполнения: {duration}")
    print(f"📝 Всего статей проверено: {len(articles)}")
    print(f"✅ Доступны (HTTP 200): {success_count}")
    print(f"❌ Недоступны: {failed_count}")
    print(f"📝 Всего слов во всех статьях: {sum(r['word_count'] for r in results)}")
    print(f"📊 Среднее количество слов на статью: {sum(r['word_count'] for r in results) // len(results) if results else 0}")
    print("="*120)
    
    # Подробный отчет по количеству слов
    print("\n" + "="*120)
    print("📝 КОЛИЧЕСТВО СЛОВ В КАЖДОЙ СТАТЬЕ".center(120))
    print("="*120)
    print(f"{'№':<4} {'ID':<7} {'СЛОВ':<7} {'СИМВОЛОВ':<10} {'НАЗВАНИЕ':<85}")
    print("="*120)
    
    for result in results:
        display_title = result['title'][:82] + "..." if len(result['title']) > 85 else result['title']
        print(f"{result['index']:<4} {result['wp_id']:<7} {result['word_count']:<7} {result['content_length']:<10} {display_title}")
    
    print("="*120)
    
    # Топ-5 самых длинных статей
    print("\n📈 ТОП-5 САМЫХ ДЛИННЫХ СТАТЕЙ:")
    print("="*120)
    top_5 = sorted(results, key=lambda x: x['word_count'], reverse=True)[:5]
    for idx, result in enumerate(top_5, 1):
        print(f"{idx}. ID {result['wp_id']}: {result['title'][:60]}")
        print(f"   📝 Слов: {result['word_count']}, Символов: {result['content_length']}")
        print(f"   🔗 {result['url']}\n")
    
    # Топ-5 самых коротких статей
    print("\n📉 ТОП-5 САМЫХ КОРОТКИХ СТАТЕЙ:")
    print("="*120)
    bottom_5 = sorted(results, key=lambda x: x['word_count'])[:5]
    for idx, result in enumerate(bottom_5, 1):
        print(f"{idx}. ID {result['wp_id']}: {result['title'][:60]}")
        print(f"   📝 Слов: {result['word_count']}, Символов: {result['content_length']}")
        print(f"   🔗 {result['url']}\n")
    
    # Сохраняем отчет
    report_filename = f'/var/www/fastuser/data/www/ecopackpro.ru/articles_check_report_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
    import json
    with open(report_filename, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=4)
    
    print(f"\n📄 Детальный отчет сохранен: {report_filename}")
    
    # Проверяем проблемные статьи
    if failed_count > 0:
        print("\n" + "="*120)
        print("⚠️  ПРОБЛЕМНЫЕ СТАТЬИ (НЕ HTTP 200)".center(120))
        print("="*120)
        for result in results:
            if result['status_code'] != 200:
                print(f"❌ ID {result['wp_id']}: {result['title']}")
                print(f"   URL: {result['url']}")
                print(f"   Статус: {result['status_code']} - {result['reason']}\n")
    
    print("\n" + "="*120)
    print("🎉 ПРОВЕРКА ЗАВЕРШЕНА УСПЕШНО!".center(120))
    print("="*120)

if __name__ == "__main__":
    main()

