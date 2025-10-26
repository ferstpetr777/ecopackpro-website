#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для очистки лишних <br> тегов в статьях
"""

import mysql.connector
import re

# Настройки подключения к БД
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6',
    'charset': 'utf8mb4'
}

def clean_br_tags(html):
    """Очищает лишние <br> теги"""
    
    # 1. Убираем <br> перед тегами
    html = re.sub(r'<br>\s*(<[^>]+>)', r'\1', html)
    
    # 2. Убираем <br> после закрывающих тегов
    html = re.sub(r'(</[^>]+>)\s*<br>', r'\1', html)
    
    # 3. Убираем множественные <br><br><br> -> <br><br>
    html = re.sub(r'(<br>\s*){3,}', '<br><br>', html)
    
    # 4. Убираем <br> в начале и конце параграфов
    html = re.sub(r'<p>\s*<br>\s*', '<p>', html)
    html = re.sub(r'\s*<br>\s*</p>', '</p>', html)
    
    # 5. Убираем <br> в начале и конце заголовков
    html = re.sub(r'<h[1-6]>\s*<br>\s*', r'<h1>', html)
    html = re.sub(r'\s*<br>\s*</h[1-6]>', r'</h1>', html)
    
    # 6. Исправляем переносы в списках
    html = re.sub(r'<br>\s*[-•]\s*', '<br>• ', html)
    
    # 7. Убираем <br> перед списками
    html = re.sub(r'<br>\s*<ul>', '<ul>', html)
    html = re.sub(r'<br>\s*<ol>', '<ol>', html)
    
    return html

def main():
    print("=" * 70)
    print("ОЧИСТКА ЛИШНИХ <BR> ТЕГОВ В СТАТЬЯХ")
    print("=" * 70)
    
    try:
        # Подключение к БД
        print("\n1. Подключение к базе данных...")
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        print("✅ Подключено к БД")
        
        # Найти все статьи
        print("\n2. Поиск всех статей...")
        cursor.execute("""
            SELECT ID, post_title 
            FROM wp_posts 
            WHERE post_type = 'post' 
            AND post_status = 'publish'
            ORDER BY ID
        """)
        
        articles = cursor.fetchall()
        print(f"✅ Найдено статей: {len(articles)}")
        
        # Обработать каждую статью
        print("\n3. Очистка <br> тегов...")
        success_count = 0
        
        for article_id, article_title in articles:
            try:
                # Получить текущее содержимое
                cursor.execute("SELECT post_content FROM wp_posts WHERE ID = %s", (article_id,))
                current_content = cursor.fetchone()[0]
                
                # Подсчитать <br> теги до очистки
                br_before = current_content.count('<br>')
                
                # Очистить содержимое
                cleaned_content = clean_br_tags(current_content)
                
                # Подсчитать <br> теги после очистки
                br_after = cleaned_content.count('<br>')
                
                # Обновить статью только если есть изменения
                if current_content != cleaned_content:
                    cursor.execute("""
                        UPDATE wp_posts 
                        SET post_content = %s, 
                            post_modified = NOW(), 
                            post_modified_gmt = NOW() 
                        WHERE ID = %s
                    """, (cleaned_content, article_id))
                    
                    print(f"✅ Статья {article_id}: убрано {br_before - br_after} лишних <br> тегов")
                    success_count += 1
                else:
                    print(f"⏭️ Статья {article_id}: изменений не требуется")
                
                conn.commit()
                
            except Exception as e:
                print(f"❌ Ошибка при обработке статьи {article_id}: {e}")
                conn.rollback()
        
        # Закрыть подключение
        cursor.close()
        conn.close()
        
        print("\n" + "=" * 70)
        print(f"✅ ОЧИСТКА ЗАВЕРШЕНА!")
        print(f"📊 Обработано статей: {len(articles)}")
        print(f"✅ Обновлено статей: {success_count}")
        print(f"⏭️ Без изменений: {len(articles) - success_count}")
        print("=" * 70)
        
    except Exception as e:
        print(f"\n❌ КРИТИЧЕСКАЯ ОШИБКА: {e}")
        import traceback
        traceback.print_exc()

if __name__ == '__main__':
    main()
