#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для исправления оставшихся ошибок в статьях
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

def fix_remaining_errors(html):
    """Исправляет оставшиеся ошибки в HTML"""
    
    # 1. Исправляем дублированные <br> теги в конце
    html = re.sub(r'<br>\s*<br>\s*<br>\s*', '<br><br>', html)
    
    # 2. Убираем лишние <br> теги в конце div'ов
    html = re.sub(r'<div([^>]*)><br>\s*([^<]+)<br>\s*</div>', r'<div\1>\2</div>', html)
    
    # 3. Исправляем незакрытые теги
    html = re.sub(r'<p>\s*<a([^>]*)><span([^>]*)>←</span></a>\s*</p>', r'<a\1><span\2>←</span></a>', html)
    
    # 4. Убираем пустые параграфы
    html = re.sub(r'<p>\s*</p>', '', html)
    
    # 5. Исправляем некорректные закрывающие теги в навигации
    html = re.sub(r'<p>\s*<a([^>]*)style="[^"]*"><span([^>]*)>←</span></a>\s*</p>', r'<a\1><span\2>←</span></a>', html)
    
    # 6. Убираем лишние <br> в конце div'ов с навигацией
    html = re.sub(r'<div([^>]*)><br>\s*Последняя статья<br>\s*</div>', r'<div\1>Последняя статья</div>', html)
    
    # 7. Исправляем некорректные <br> теги в контактах
    html = re.sub(r'<h4([^>]*)><br>\s*📞 Контактные телефоны</h4>', r'<h4\1>📞 Контактные телефоны</h4>', html)
    html = re.sub(r'<h4([^>]*)><br>\s*✉️ Email</h4>', r'<h4\1>✉️ Email</h4>', html)
    
    # 8. Убираем лишние <br> теги в конце
    html = re.sub(r'<br>\s*💡 Переходите между статьями для изучения всех видов упаковки<br>\s*</div>', r'💡 Переходите между статьями для изучения всех видов упаковки</div>', html)
    
    # 9. Исправляем некорректные <br> в стилях
    html = re.sub(r'<br>\s*Последняя статья', 'Последняя статья', html)
    
    # 10. Убираем лишние пробелы и переносы строк
    html = re.sub(r'\s+', ' ', html)
    html = re.sub(r'>\s+<', '><', html)
    
    return html

def main():
    print("=" * 70)
    print("ИСПРАВЛЕНИЕ ОСТАВШИХСЯ ОШИБОК В СТАТЬЯХ")
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
        print("\n3. Исправление оставшихся ошибок...")
        success_count = 0
        
        for article_id, article_title in articles:
            try:
                # Получить текущее содержимое
                cursor.execute("SELECT post_content FROM wp_posts WHERE ID = %s", (article_id,))
                current_content = cursor.fetchone()[0]
                
                # Подсчитать ошибки до исправления
                errors_before = current_content.count('<br><br><br>') + current_content.count('<p> </p>') + current_content.count('<div><br>')
                
                # Исправить содержимое
                fixed_content = fix_remaining_errors(current_content)
                
                # Подсчитать ошибки после исправления
                errors_after = fixed_content.count('<br><br><br>') + fixed_content.count('<p> </p>') + fixed_content.count('<div><br>')
                
                # Обновить статью только если есть изменения
                if current_content != fixed_content:
                    cursor.execute("""
                        UPDATE wp_posts 
                        SET post_content = %s, 
                            post_modified = NOW(), 
                            post_modified_gmt = NOW() 
                        WHERE ID = %s
                    """, (fixed_content, article_id))
                    
                    print(f"✅ Статья {article_id}: исправлено {errors_before - errors_after} ошибок")
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
        print(f"✅ ИСПРАВЛЕНИЕ ОСТАВШИХСЯ ОШИБОК ЗАВЕРШЕНО!")
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
