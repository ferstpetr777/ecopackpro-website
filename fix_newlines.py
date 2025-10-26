#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для исправления ошибок с переносами строк в статьях
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

def fix_newlines(html):
    """Исправляет ошибки с переносами строк в HTML"""
    
    # 1. Исправляем \\n -> \n
    html = html.replace('\\\\n', '\n')
    
    # 2. Исправляем \\п -> \n (кириллическая п)
    html = html.replace('\\\\п', '\n')
    
    # 3. Исправляем одиночные \n в начале строк (должны быть <br>)
    html = re.sub(r'(?<!>)\n(?![<\s])', '<br>', html)
    
    # 4. Исправляем множественные <br><br><br> -> <br><br>
    html = re.sub(r'(<br>){3,}', '<br><br>', html)
    
    # 5. Убираем <br> перед тегами
    html = re.sub(r'<br>\s*(<[^>]+>)', r'\1', html)
    
    # 6. Убираем <br> после закрывающих тегов
    html = re.sub(r'(</[^>]+>)\s*<br>', r'\1', html)
    
    # 7. Исправляем переносы в списках
    html = re.sub(r'<br>\s*[-•]\s*', '<br>• ', html)
    
    return html

def main():
    print("=" * 70)
    print("ИСПРАВЛЕНИЕ ОШИБОК С ПЕРЕНОСАМИ СТРОК В СТАТЬЯХ")
    print("=" * 70)
    
    try:
        # Подключение к БД
        print("\n1. Подключение к базе данных...")
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        print("✅ Подключено к БД")
        
        # Найти статьи с ошибками
        print("\n2. Поиск статей с ошибками \\n...")
        cursor.execute("""
            SELECT ID, post_title 
            FROM wp_posts 
            WHERE post_type = 'post' 
            AND post_status = 'publish' 
            AND post_content LIKE '%\\\\\\\\n%'
            ORDER BY ID
        """)
        
        articles = cursor.fetchall()
        print(f"✅ Найдено статей с ошибками: {len(articles)}")
        
        # Исправить каждую статью
        print("\n3. Исправление статей...")
        success_count = 0
        
        for article_id, article_title in articles:
            print(f"\n📄 Обработка статьи {article_id}: {article_title[:50]}...")
            
            try:
                # Получить текущее содержимое
                cursor.execute("SELECT post_content FROM wp_posts WHERE ID = %s", (article_id,))
                current_content = cursor.fetchone()[0]
                
                # Подсчитать ошибки до исправления
                errors_before = current_content.count('\\\\n')
                
                # Исправить содержимое
                fixed_content = fix_newlines(current_content)
                
                # Подсчитать ошибки после исправления
                errors_after = fixed_content.count('\\\\n')
                
                # Обновить статью
                cursor.execute("""
                    UPDATE wp_posts 
                    SET post_content = %s, 
                        post_modified = NOW(), 
                        post_modified_gmt = NOW() 
                    WHERE ID = %s
                """, (fixed_content, article_id))
                
                print(f"✅ Статья {article_id}: исправлено {errors_before} ошибок, осталось {errors_after}")
                success_count += 1
                conn.commit()
                
            except Exception as e:
                print(f"❌ Ошибка при исправлении статьи {article_id}: {e}")
                conn.rollback()
        
        # Закрыть подключение
        cursor.close()
        conn.close()
        
        print("\n" + "=" * 70)
        print(f"✅ ИСПРАВЛЕНИЕ ЗАВЕРШЕНО!")
        print(f"📊 Обработано статей: {len(articles)}")
        print(f"✅ Успешно исправлено: {success_count}")
        print(f"❌ Ошибок: {len(articles) - success_count}")
        print("=" * 70)
        
    except Exception as e:
        print(f"\n❌ КРИТИЧЕСКАЯ ОШИБКА: {e}")
        import traceback
        traceback.print_exc()

if __name__ == '__main__':
    main()
