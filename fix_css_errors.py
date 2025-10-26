#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для исправления CSS ошибок в статьях
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

def fix_css_errors(html):
    """Исправляет ошибки CSS в HTML"""
    
    # 1. Исправляем style атрибуты с <br> тегами
    # Ищем pattern: style="<br>    background: ...<br>    border: ...<br>"
    def fix_style_attr(match):
        style_content = match.group(1)
        # Убираем все <br> теги и лишние пробелы
        style_content = re.sub(r'<br>\s*', ' ', style_content)
        style_content = re.sub(r'\s+', ' ', style_content).strip()
        # Убираем лишние точки с запятой
        style_content = re.sub(r';\s*;', ';', style_content)
        return f'style="{style_content}"'
    
    html = re.sub(r'style="(<br>\s*[^"]*)"', fix_style_attr, html)
    
    # 2. Исправляем отдельные блоки CSS кода
    # Ищем блоки типа: color: #007cba;<br>margin: 0 0 15px 0;<br>font-size: 18px;
    css_pattern = r'(color:\s*[^;]+;)\s*<br>\s*(margin:[^;]+;)\s*<br>\s*(font-size:[^;]+;)\s*<br>\s*(text-align:[^;]+;)\s*<br>\s*">'
    html = re.sub(css_pattern, r'\1 \2 \3 \4">', html)
    
    # 3. Исправляем другие CSS блоки
    css_pattern2 = r'(background:\s*[^;]+;)\s*<br>\s*(padding:[^;]+;)\s*<br>\s*(border-radius:[^;]+;)\s*<br>\s*(border:[^;]+;)\s*<br>\s*">'
    html = re.sub(css_pattern2, r'\1 \2 \3 \4">', html)
    
    # 4. Исправляем display и grid свойства
    grid_pattern = r'(display:\s*[^;]+;)\s*<br>\s*(grid-template-columns:[^;]+;)\s*<br>\s*(gap:[^;]+;)\s*<br>\s*(align-items:[^;]+;)\s*<br>\s*">'
    html = re.sub(grid_pattern, r'\1 \2 \3 \4">', html)
    
    # 5. Исправляем flex свойства
    flex_pattern = r'(display:\s*[^;]+;)\s*<br>\s*(align-items:[^;]+;)\s*<br>\s*">'
    html = re.sub(flex_pattern, r'\1 \2">', html)
    
    # 6. Исправляем margin и padding блоки
    margin_pattern = r'(margin-top:\s*[^;]+;)\s*<br>\s*(text-align:[^;]+;)\s*<br>\s*(font-size:[^;]+;)\s*<br>\s*(color:[^;]+;)\s*<br>\s*">'
    html = re.sub(margin_pattern, r'\1 \2 \3 \4">', html)
    
    # 7. Убираем оставшиеся одиночные <br> в style атрибутах
    html = re.sub(r'style="([^"]*<br>[^"]*)"', lambda m: f'style="{m.group(1).replace("<br>", " ").strip()}"', html)
    
    # 8. Убираем лишние пробелы в style атрибутах
    html = re.sub(r'style="\s*([^"]*)\s*"', lambda m: f'style="{re.sub(r"\s+", " ", m.group(1)).strip()}"', html)
    
    # 9. Исправляем некорректные закрывающие теги
    html = re.sub(r'<p>\s*</p>', '', html)
    html = re.sub(r'<div>\s*</div>', '', html)
    
    # 10. Убираем лишние <br> теги в начале и конце
    html = re.sub(r'^\s*<br>\s*', '', html)
    html = re.sub(r'\s*<br>\s*$', '', html)
    
    return html

def main():
    print("=" * 70)
    print("ИСПРАВЛЕНИЕ CSS ОШИБОК В СТАТЬЯХ")
    print("=" * 70)
    
    try:
        # Подключение к БД
        print("\n1. Подключение к базе данных...")
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        print("✅ Подключено к БД")
        
        # Найти статьи с CSS ошибками
        print("\n2. Поиск статей с CSS ошибками...")
        cursor.execute("""
            SELECT ID, post_title 
            FROM wp_posts 
            WHERE post_type = 'post' 
            AND post_status = 'publish'
            AND post_content LIKE '%<br>%style%'
            ORDER BY ID
        """)
        
        articles = cursor.fetchall()
        print(f"✅ Найдено статей с CSS ошибками: {len(articles)}")
        
        if len(articles) == 0:
            print("❌ Статьи с CSS ошибками не найдены")
            return
        
        # Исправить каждую статью
        print("\n3. Исправление CSS ошибок...")
        success_count = 0
        
        for article_id, article_title in articles:
            try:
                # Получить текущее содержимое
                cursor.execute("SELECT post_content FROM wp_posts WHERE ID = %s", (article_id,))
                current_content = cursor.fetchone()[0]
                
                # Подсчитать CSS ошибки до исправления
                css_errors_before = current_content.count('<br>')
                
                # Исправить содержимое
                fixed_content = fix_css_errors(current_content)
                
                # Подсчитать CSS ошибки после исправления
                css_errors_after = fixed_content.count('<br>')
                
                # Обновить статью только если есть изменения
                if current_content != fixed_content:
                    cursor.execute("""
                        UPDATE wp_posts 
                        SET post_content = %s, 
                            post_modified = NOW(), 
                            post_modified_gmt = NOW() 
                        WHERE ID = %s
                    """, (fixed_content, article_id))
                    
                    print(f"✅ Статья {article_id}: исправлено {css_errors_before - css_errors_after} CSS ошибок")
                    success_count += 1
                else:
                    print(f"⏭️ Статья {article_id}: изменений не требуется")
                
                conn.commit()
                
            except Exception as e:
                print(f"❌ Ошибка при исправлении статьи {article_id}: {e}")
                conn.rollback()
        
        # Закрыть подключение
        cursor.close()
        conn.close()
        
        print("\n" + "=" * 70)
        print(f"✅ ИСПРАВЛЕНИЕ CSS ОШИБОК ЗАВЕРШЕНО!")
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
