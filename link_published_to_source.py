#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Скрипт для связывания опубликованных статей с исходными материалами в articles.db
"""

import sqlite3
import re

# Путь к базе данных проекта
PROJECT_DB_PATH = '/root/seo_project/SEO_ecopackpro/articles.db'

def normalize_title(title):
    """Нормализует название статьи для поиска соответствия"""
    # Убираем все после двоеточия (если есть)
    title = re.sub(r':.*$', '', title).strip()
    # Приводим к нижнему регистру
    title = title.lower()
    return title

def link_articles():
    """Связывает опубликованные статьи с исходными материалами"""
    print("\n" + "="*100)
    print("🔗 СВЯЗЫВАНИЕ ОПУБЛИКОВАННЫХ СТАТЕЙ С ИСХОДНИКАМИ".center(100))
    print("="*100 + "\n")
    
    conn = sqlite3.connect(PROJECT_DB_PATH)
    cursor = conn.cursor()
    
    # Получаем все опубликованные статьи без связи с исходниками
    cursor.execute("""
    SELECT id, wp_post_id, title, url
    FROM published_articles
    WHERE source_article_id IS NULL
    """)
    
    published_articles = cursor.fetchall()
    
    print(f"📊 Найдено {len(published_articles)} опубликованных статей без связи с исходниками")
    print("\n" + "-"*100 + "\n")
    
    linked_count = 0
    not_found_count = 0
    
    for pub_id, wp_post_id, title, url in published_articles:
        # Нормализуем название
        normalized_title = normalize_title(title)
        
        # Ищем в исходниках по keyword или title
        cursor.execute("""
        SELECT id, keyword, title
        FROM articles
        WHERE LOWER(keyword) = ? OR LOWER(title) = ?
        """, (normalized_title, normalized_title))
        
        source = cursor.fetchone()
        
        if source:
            source_id, source_keyword, source_title = source
            
            # Обновляем связь
            cursor.execute("""
            UPDATE published_articles
            SET source_article_id = ?
            WHERE id = ?
            """, (source_id, pub_id))
            
            linked_count += 1
            print(f"✅ WP ID {wp_post_id}: {title[:60]}...")
            print(f"   🔗 Связана с исходником ID {source_id}: {source_keyword}")
            print(f"   📄 URL: {url}\n")
        else:
            not_found_count += 1
            print(f"⚠️  WP ID {wp_post_id}: {title[:60]}...")
            print(f"   ❌ Исходник не найден (нормализованный поиск: '{normalized_title}')")
            print(f"   📄 URL: {url}\n")
    
    conn.commit()
    conn.close()
    
    print("\n" + "="*100)
    print("📊 ИТОГОВАЯ СТАТИСТИКА".center(100))
    print("="*100)
    print(f"✅ Связано с исходниками: {linked_count} статей")
    print(f"⚠️  Без связи с исходниками: {not_found_count} статей")
    print(f"📝 Всего обработано: {len(published_articles)} статей")
    print("="*100 + "\n")

def show_final_report():
    """Показывает финальный отчет о связанных статьях"""
    print("\n" + "="*100)
    print("📊 ФИНАЛЬНЫЙ ОТЧЕТ: СВЯЗЬ ОПУБЛИКОВАННЫХ СТАТЕЙ С ИСХОДНИКАМИ".center(100))
    print("="*100 + "\n")
    
    conn = sqlite3.connect(PROJECT_DB_PATH)
    cursor = conn.cursor()
    
    # Получаем все опубликованные статьи с их исходниками
    cursor.execute("""
    SELECT 
        pa.wp_post_id,
        pa.title AS published_title,
        pa.url,
        pa.post_date,
        pa.source_article_id,
        a.keyword AS source_keyword,
        a.title AS source_title
    FROM published_articles pa
    LEFT JOIN articles a ON pa.source_article_id = a.id
    WHERE pa.wp_post_id >= 7907
    ORDER BY pa.wp_post_id
    """)
    
    results = cursor.fetchall()
    
    print(f"{'№':<4} {'WP ID':<7} {'НАЗВАНИЕ СТАТЬИ':<50} {'ИСТОЧНИК ID':<12} {'ДАТА':<20}")
    print("="*100)
    
    linked_count = 0
    unlinked_count = 0
    
    for idx, (wp_id, pub_title, url, post_date, src_id, src_keyword, src_title) in enumerate(results, 1):
        display_title = pub_title[:47] + "..." if len(pub_title) > 50 else pub_title
        
        if src_id:
            linked_count += 1
            print(f"{idx:<4} {wp_id:<7} {display_title:<50} ✅ ID {src_id:<7} {post_date:<20}")
            print(f"     🔗 {url}")
            print(f"     📝 Исходник: {src_keyword}")
        else:
            unlinked_count += 1
            print(f"{idx:<4} {wp_id:<7} {display_title:<50} ❌ Не найден  {post_date:<20}")
            print(f"     🔗 {url}")
        
        print("-"*100)
    
    print("\n" + "="*100)
    print(f"✅ Связано с исходниками: {linked_count} статей")
    print(f"⚠️  Без связи: {unlinked_count} статей")
    print(f"📝 Всего статей: {len(results)}")
    print("="*100 + "\n")
    
    conn.close()

def main():
    link_articles()
    show_final_report()
    
    print("\n" + "="*100)
    print("🎉 СВЯЗЫВАНИЕ ЗАВЕРШЕНО УСПЕШНО!".center(100))
    print("="*100)

if __name__ == "__main__":
    main()

