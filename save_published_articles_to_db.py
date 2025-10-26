#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Скрипт для сохранения информации об опубликованных статьях в базу данных проекта
и связывания их с исходными материалами из articles.db
"""

import sqlite3
import mysql.connector
from datetime import datetime
import json

# Параметры подключения к MySQL (WordPress)
WP_DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

# Путь к базе данных проекта
PROJECT_DB_PATH = '/root/seo_project/SEO_ecopackpro/articles.db'

# Базовый URL сайта
BASE_URL = 'https://ecopackpro.ru'

def get_published_articles_from_wp():
    """Получает список всех опубликованных статей из WordPress"""
    print("📊 Получаю список опубликованных статей из WordPress...")
    
    conn = mysql.connector.connect(**WP_DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    
    query = """
    SELECT 
        ID,
        post_title,
        post_name,
        post_date,
        post_modified
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
    
    print(f"✅ Получено {len(articles)} опубликованных статей")
    return articles

def create_published_articles_table(db_path):
    """Создает таблицу для хранения информации об опубликованных статьях"""
    print(f"📝 Создаю таблицу published_articles в {db_path}...")
    
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    
    # Создаем таблицу для опубликованных статей
    cursor.execute("""
    CREATE TABLE IF NOT EXISTS published_articles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        wp_post_id INTEGER UNIQUE NOT NULL,
        title TEXT NOT NULL,
        slug TEXT NOT NULL,
        url TEXT NOT NULL,
        post_date TEXT NOT NULL,
        post_modified TEXT NOT NULL,
        export_date TEXT NOT NULL,
        source_article_id INTEGER,
        FOREIGN KEY (source_article_id) REFERENCES articles(id)
    )
    """)
    
    conn.commit()
    conn.close()
    
    print("✅ Таблица published_articles создана")

def find_source_article_by_title(db_path, title):
    """Находит исходную статью в articles.db по названию"""
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    
    # Ищем по полному совпадению названия
    cursor.execute("""
    SELECT id, keyword 
    FROM articles 
    WHERE keyword = ? OR title = ?
    """, (title, title))
    
    result = cursor.fetchone()
    conn.close()
    
    return result[0] if result else None

def save_published_articles_to_db(articles, db_path):
    """Сохраняет информацию об опубликованных статьях в базу данных проекта"""
    print(f"💾 Сохраняю информацию об опубликованных статьях в {db_path}...")
    
    conn = sqlite3.connect(db_path)
    cursor = conn.cursor()
    
    export_date = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    saved_count = 0
    linked_count = 0
    
    for article in articles:
        wp_post_id = article['ID']
        title = article['post_title']
        slug = article['post_name']
        url = f"{BASE_URL}/{slug}/"
        post_date = str(article['post_date'])
        post_modified = str(article['post_modified'])
        
        # Ищем исходную статью по названию
        source_article_id = find_source_article_by_title(db_path, title)
        
        # Сохраняем или обновляем запись
        cursor.execute("""
        INSERT OR REPLACE INTO published_articles 
        (wp_post_id, title, slug, url, post_date, post_modified, export_date, source_article_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        """, (wp_post_id, title, slug, url, post_date, post_modified, export_date, source_article_id))
        
        saved_count += 1
        if source_article_id:
            linked_count += 1
            print(f"  ✅ ID {wp_post_id}: {title[:50]}... → Связана с исходником ID {source_article_id}")
        else:
            print(f"  ⚠️  ID {wp_post_id}: {title[:50]}... → Исходник не найден")
    
    conn.commit()
    conn.close()
    
    print(f"\n✅ Сохранено {saved_count} статей")
    print(f"🔗 Связано с исходниками: {linked_count} статей")
    print(f"⚠️  Без связи с исходниками: {saved_count - linked_count} статей")

def generate_report(articles, db_path):
    """Генерирует отчет об опубликованных статьях"""
    print("\n" + "="*100)
    print("📊 ФИНАЛЬНЫЙ ОТЧЕТ: ОПУБЛИКОВАННЫЕ СТАТЬИ НА САЙТЕ ECOPACKPRO.RU")
    print("="*100)
    print(f"\n📅 Дата и время генерации отчета: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"📝 Всего опубликовано статей: {len(articles)}\n")
    print("="*100)
    print(f"{'№':<4} {'ID':<6} {'НАЗВАНИЕ СТАТЬИ':<70} {'ДАТА ПУБЛИКАЦИИ':<20}")
    print("="*100)
    
    for idx, article in enumerate(articles, 1):
        wp_post_id = article['ID']
        title = article['post_title']
        post_date = str(article['post_date'])
        slug = article['post_name']
        url = f"{BASE_URL}/{slug}/"
        
        # Обрезаем длинное название
        display_title = title[:67] + "..." if len(title) > 70 else title
        
        print(f"{idx:<4} {wp_post_id:<6} {display_title:<70} {post_date:<20}")
        print(f"     🔗 {url}")
        print("-"*100)
    
    print("\n" + "="*100)
    print("✅ ВСЕ СТАТЬИ УСПЕШНО ОПУБЛИКОВАНЫ И СОХРАНЕНЫ В БАЗУ ДАННЫХ ПРОЕКТА")
    print("="*100)
    
    # Сохраняем отчет в JSON
    report_data = []
    for article in articles:
        slug = article['post_name']
        report_data.append({
            'id': article['ID'],
            'title': article['post_title'],
            'slug': slug,
            'url': f"{BASE_URL}/{slug}/",
            'post_date': str(article['post_date']),
            'post_modified': str(article['post_modified'])
        })
    
    report_filename = f'/var/www/fastuser/data/www/ecopackpro.ru/published_articles_report_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
    with open(report_filename, 'w', encoding='utf-8') as f:
        json.dump(report_data, f, ensure_ascii=False, indent=4)
    
    print(f"\n📄 Детальный отчет сохранен: {report_filename}")
    
    # Сохраняем список URL в текстовом файле
    urls_filename = f'/var/www/fastuser/data/www/ecopackpro.ru/published_articles_urls_{datetime.now().strftime("%Y%m%d_%H%M%S")}.txt'
    with open(urls_filename, 'w', encoding='utf-8') as f:
        f.write("СПИСОК URL ОПУБЛИКОВАННЫХ СТАТЕЙ НА ECOPACKPRO.RU\n")
        f.write("="*100 + "\n\n")
        for article in articles:
            slug = article['post_name']
            url = f"{BASE_URL}/{slug}/"
            f.write(f"{article['ID']}. {article['post_title']}\n")
            f.write(f"    {url}\n\n")
    
    print(f"📄 Список URL сохранен: {urls_filename}")

def main():
    print("\n" + "="*100)
    print("🚀 ФИНАЛЬНАЯ РЕВИЗИЯ ОПУБЛИКОВАННЫХ СТАТЕЙ".center(100))
    print("="*100 + "\n")
    
    # Получаем список опубликованных статей из WordPress
    articles = get_published_articles_from_wp()
    
    # Создаем таблицу в базе данных проекта
    create_published_articles_table(PROJECT_DB_PATH)
    
    # Сохраняем информацию в базу данных
    save_published_articles_to_db(articles, PROJECT_DB_PATH)
    
    # Генерируем отчет
    generate_report(articles, PROJECT_DB_PATH)
    
    print("\n" + "="*100)
    print("🎉 ФИНАЛЬНАЯ РЕВИЗИЯ ЗАВЕРШЕНА УСПЕШНО!".center(100))
    print("="*100)

if __name__ == "__main__":
    main()

