#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector

# Конфигурация базы данных WordPress
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

def check_existing_articles():
    """Проверка существующих статей в базе данных"""
    connection = mysql.connector.connect(**DB_CONFIG)
    cursor = connection.cursor(dictionary=True)
    
    print("🔍 ПРОВЕРКА СУЩЕСТВУЮЩИХ СТАТЕЙ В БАЗЕ ДАННЫХ")
    print("=" * 80)
    
    # Получаем все статьи с фокусными ключевыми словами
    cursor.execute("""
        SELECT p.ID, p.post_title, p.post_name, pm.meta_value as focus_keyword
        FROM wp_posts p
        INNER JOIN wp_postmeta pm ON p.ID = pm.post_id
        WHERE pm.meta_key = '_yoast_wpseo_focuskw'
        AND p.post_status = 'publish'
        AND p.post_type = 'post'
        ORDER BY p.ID DESC
    """)
    
    articles = cursor.fetchall()
    
    print(f"📚 Найдено статей с фокусными ключевыми словами: {len(articles)}")
    print("-" * 80)
    
    for i, article in enumerate(articles, 1):
        print(f"{i:2d}. ID {article['ID']:4d}: {article['focus_keyword']}")
        print(f"    Заголовок: {article['post_title']}")
        print(f"    Ярлык: {article['post_name']}")
        print()
    
    connection.close()
    return articles

if __name__ == "__main__":
    check_existing_articles()
