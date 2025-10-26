#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для отката всех статей к последней ревизии с исходящими ссылками
"""

import mysql.connector
import sys

# Настройки подключения к БД
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6',
    'charset': 'utf8mb4'
}

def rollback_article_to_revision_with_links(article_id, conn):
    """Откатывает статью к последней ревизии с исходящими ссылками"""
    
    cursor = conn.cursor()
    
    try:
        # Найти последнюю ревизию с исходящими ссылками
        cursor.execute("""
            SELECT ID, post_date, CHAR_LENGTH(post_content) as length 
            FROM wp_posts 
            WHERE post_type = 'revision' 
            AND post_parent = %s 
            AND post_content LIKE '%https://%' 
            ORDER BY post_date DESC 
            LIMIT 1
        """, (article_id,))
        
        revision = cursor.fetchone()
        
        if not revision:
            print(f"❌ Статья {article_id}: ревизия с исходящими ссылками не найдена")
            return False
        
        revision_id, revision_date, revision_length = revision
        print(f"📝 Статья {article_id}: найдена ревизия {revision_id} от {revision_date} (размер: {revision_length})")
        
        # Получить содержимое ревизии
        cursor.execute("SELECT post_content FROM wp_posts WHERE ID = %s", (revision_id,))
        revision_content = cursor.fetchone()[0]
        
        # Обновить статью
        cursor.execute("""
            UPDATE wp_posts 
            SET post_content = %s, 
                post_modified = NOW(), 
                post_modified_gmt = NOW() 
            WHERE ID = %s
        """, (revision_content, article_id))
        
        print(f"✅ Статья {article_id}: откачена к ревизии {revision_id}")
        return True
        
    except Exception as e:
        print(f"❌ Ошибка при откате статьи {article_id}: {e}")
        return False
    finally:
        cursor.close()

def main():
    print("=" * 70)
    print("ОТКАТ ВСЕХ СТАТЕЙ К РЕВИЗИЯМ С ИСХОДЯЩИМИ ССЫЛКАМИ")
    print("=" * 70)
    
    try:
        # Подключение к БД
        print("\n1. Подключение к базе данных...")
        conn = mysql.connector.connect(**DB_CONFIG)
        print("✅ Подключено к БД")
        
        # Найти все статьи с исходящими ссылками (кроме 7939)
        print("\n2. Поиск статей с исходящими ссылками...")
        cursor = conn.cursor()
        cursor.execute("""
            SELECT ID, post_title 
            FROM wp_posts 
            WHERE post_type = 'post' 
            AND post_status = 'publish' 
            AND post_content LIKE '%https://%' 
            AND ID != 7939 
            ORDER BY ID
        """)
        
        articles = cursor.fetchall()
        print(f"✅ Найдено статей: {len(articles)}")
        
        # Откатить каждую статью
        print("\n3. Откат статей...")
        success_count = 0
        
        for article_id, article_title in articles:
            print(f"\n📄 Обработка статьи {article_id}: {article_title[:50]}...")
            
            if rollback_article_to_revision_with_links(article_id, conn):
                success_count += 1
                conn.commit()
            else:
                conn.rollback()
        
        # Закрыть подключение
        cursor.close()
        conn.close()
        
        print("\n" + "=" * 70)
        print(f"✅ ОТКАТ ЗАВЕРШЕН!")
        print(f"📊 Обработано статей: {len(articles)}")
        print(f"✅ Успешно откачено: {success_count}")
        print(f"❌ Ошибок: {len(articles) - success_count}")
        print("=" * 70)
        
    except Exception as e:
        print(f"\n❌ КРИТИЧЕСКАЯ ОШИБКА: {e}")
        import traceback
        traceback.print_exc()

if __name__ == '__main__':
    main()
