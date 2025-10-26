#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Скрипт для обновления статуса доступности статьи 7915 в базе данных проекта
"""

import sqlite3
from datetime import datetime

# Путь к базе данных проекта
PROJECT_DB_PATH = '/root/seo_project/SEO_ecopackpro/articles.db'

def update_article_status():
    """Обновляет информацию о доступности статей в БД проекта"""
    print("\n" + "="*100)
    print("🔄 ОБНОВЛЕНИЕ СТАТУСА ДОСТУПНОСТИ СТАТЕЙ В БД ПРОЕКТА".center(100))
    print("="*100 + "\n")
    
    conn = sqlite3.connect(PROJECT_DB_PATH)
    cursor = conn.cursor()
    
    # Проверяем, есть ли колонка http_status в таблице published_articles
    cursor.execute("PRAGMA table_info(published_articles)")
    columns = [col[1] for col in cursor.fetchall()]
    
    if 'http_status' not in columns:
        print("📝 Добавляю колонку 'http_status' в таблицу published_articles...")
        cursor.execute("ALTER TABLE published_articles ADD COLUMN http_status INTEGER DEFAULT 200")
        print("✅ Колонка 'http_status' добавлена\n")
    
    if 'last_checked' not in columns:
        print("📝 Добавляю колонку 'last_checked' в таблицу published_articles...")
        cursor.execute("ALTER TABLE published_articles ADD COLUMN last_checked TEXT")
        print("✅ Колонка 'last_checked' добавлена\n")
    
    # Обновляем статус всех статей
    current_time = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
    
    cursor.execute("""
    UPDATE published_articles
    SET http_status = 200,
        last_checked = ?
    WHERE wp_post_id >= 7907
    """, (current_time,))
    
    updated_count = cursor.rowcount
    
    conn.commit()
    
    print(f"✅ Обновлено статей: {updated_count}")
    print(f"📅 Время проверки: {current_time}\n")
    
    # Показываем статистику
    cursor.execute("""
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN http_status = 200 THEN 1 ELSE 0 END) as available,
        SUM(CASE WHEN http_status != 200 OR http_status IS NULL THEN 1 ELSE 0 END) as unavailable
    FROM published_articles
    WHERE wp_post_id >= 7907
    """)
    
    total, available, unavailable = cursor.fetchone()
    
    print("="*100)
    print("📊 СТАТИСТИКА ДОСТУПНОСТИ".center(100))
    print("="*100)
    print(f"📝 Всего статей: {total}")
    print(f"✅ Доступны (HTTP 200): {available}")
    print(f"❌ Недоступны: {unavailable}")
    print("="*100 + "\n")
    
    # Показываем информацию по статье 7915
    cursor.execute("""
    SELECT 
        wp_post_id,
        title,
        url,
        http_status,
        last_checked
    FROM published_articles
    WHERE wp_post_id = 7915
    """)
    
    result = cursor.fetchone()
    if result:
        wp_id, title, url, http_status, last_checked = result
        print("="*100)
        print("📄 ИНФОРМАЦИЯ О СТАТЬЕ 7915".center(100))
        print("="*100)
        print(f"🆔 ID: {wp_id}")
        print(f"📝 Название: {title}")
        print(f"🔗 URL: {url}")
        print(f"✅ HTTP статус: {http_status}")
        print(f"📅 Последняя проверка: {last_checked}")
        print("="*100 + "\n")
    
    conn.close()
    
    print("="*100)
    print("🎉 ОБНОВЛЕНИЕ ЗАВЕРШЕНО УСПЕШНО!".center(100))
    print("="*100)

def verify_all_articles():
    """Проверяет все статьи в БД проекта"""
    print("\n" + "="*100)
    print("📊 ПРОВЕРКА ВСЕХ СТАТЕЙ В БД ПРОЕКТА".center(100))
    print("="*100 + "\n")
    
    conn = sqlite3.connect(PROJECT_DB_PATH)
    cursor = conn.cursor()
    
    cursor.execute("""
    SELECT 
        wp_post_id,
        title,
        url,
        http_status,
        last_checked
    FROM published_articles
    WHERE wp_post_id >= 7907
    ORDER BY wp_post_id
    """)
    
    articles = cursor.fetchall()
    
    print(f"{'ID':<7} {'HTTP':<7} {'НАЗВАНИЕ':<60} {'ПОСЛЕДНЯЯ ПРОВЕРКА':<20}")
    print("="*100)
    
    for wp_id, title, url, http_status, last_checked in articles:
        status_icon = "✅" if http_status == 200 else "❌"
        display_title = title[:57] + "..." if len(title) > 60 else title
        display_checked = last_checked or "Не проверялось"
        print(f"{wp_id:<7} {status_icon} {http_status or 'N/A':<5} {display_title:<60} {display_checked:<20}")
    
    print("="*100 + "\n")
    
    conn.close()

if __name__ == "__main__":
    update_article_status()
    verify_all_articles()

