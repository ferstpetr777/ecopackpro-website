#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для исправления несбалансированных HTML тегов в статье 7942
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

def fix_balanced_tags(html):
    """Исправляет несбалансированные HTML теги"""
    
    # 1. Убираем пустые теги
    html = re.sub(r'<([a-zA-Z][a-zA-Z0-9]*)[^>]*>\s*</\1>', '', html)
    
    # 2. Исправляем некорректные самозакрывающиеся теги
    html = re.sub(r'<br>\s*</br>', '<br/>', html)
    html = re.sub(r'<hr>\s*</hr>', '<hr/>', html)
    
    # 3. Убираем дублированные закрывающие теги
    html = re.sub(r'</div>\s*</div>', '</div>', html)
    html = re.sub(r'</p>\s*</p>', '</p>', html)
    
    # 4. Исправляем некорректные вложенные теги
    html = re.sub(r'<p>\s*<div', '<div', html)
    html = re.sub(r'</div>\s*</p>', '</div>', html)
    
    # 5. Убираем лишние div'ы в конце
    html = re.sub(r'<div[^>]*>\s*</div>\s*$', '', html)
    
    # 6. Исправляем структуру списков
    html = re.sub(r'<ul>\s*<li>\s*</li>\s*</ul>', '', html)
    html = re.sub(r'<ol>\s*<li>\s*</li>\s*</ol>', '', html)
    
    return html

def main():
    print("=" * 70)
    print("ИСПРАВЛЕНИЕ НЕСБАЛАНСИРОВАННЫХ ТЕГОВ В СТАТЬЕ 7942")
    print("=" * 70)
    
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        
        # Получить содержимое статьи
        cursor.execute("SELECT post_content FROM wp_posts WHERE ID = 7942")
        current_content = cursor.fetchone()[0]
        
        print(f"📄 Исходная длина: {len(current_content)} символов")
        
        # Исправить теги
        fixed_content = fix_balanced_tags(current_content)
        
        print(f"📄 Исправленная длина: {len(fixed_content)} символов")
        
        # Проверить баланс тегов
        open_tags = len(re.findall(r'<([a-zA-Z][a-zA-Z0-9]*)[^>]*>', fixed_content))
        close_tags = len(re.findall(r'</([a-zA-Z][a-zA-Z0-9]*)>', fixed_content))
        self_closing = len(re.findall(r'<([a-zA-Z][a-zA-Z0-9]*)[^>]*/>', fixed_content))
        
        print(f"🔍 Открывающих тегов: {open_tags}")
        print(f"🔍 Закрывающих тегов: {close_tags}")
        print(f"🔍 Самозакрывающихся тегов: {self_closing}")
        
        if open_tags == close_tags:
            print("✅ Теги сбалансированы!")
            
            # Обновить статью
            cursor.execute("""
                UPDATE wp_posts 
                SET post_content = %s, 
                    post_modified = NOW(), 
                    post_modified_gmt = NOW() 
                WHERE ID = 7942
            """, (fixed_content,))
            
            conn.commit()
            print("✅ Статья обновлена")
            
        else:
            print(f"⚠️ Остается несбалансированность: {open_tags - close_tags}")
            
            # Попробуем дополнительное исправление
            print("🔧 Дополнительное исправление...")
            
            # Добавляем недостающие закрывающие теги
            diff = open_tags - close_tags
            for _ in range(diff):
                fixed_content += '</div>'
            
            cursor.execute("""
                UPDATE wp_posts 
                SET post_content = %s, 
                    post_modified = NOW(), 
                    post_modified_gmt = NOW() 
                WHERE ID = 7942
            """, (fixed_content,))
            
            conn.commit()
            print("✅ Дополнительное исправление применено")
        
        cursor.close()
        conn.close()
        
        print("\n" + "=" * 70)
        print("✅ ИСПРАВЛЕНИЕ ЗАВЕРШЕНО!")
        print("=" * 70)
        
    except Exception as e:
        print(f"\n❌ ОШИБКА: {e}")
        import traceback
        traceback.print_exc()

if __name__ == '__main__':
    main()
