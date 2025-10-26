#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для центрирования контента статьи 7942
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

def fix_centering(html):
    """Исправляет центрирование контента статьи"""
    
    print("=" * 80)
    print("🎯 ИСПРАВЛЕНИЕ ЦЕНТРИРОВАНИЯ КОНТЕНТА СТАТЬИ 7942")
    print("=" * 80)
    
    original_html = html
    fixes_applied = []
    
    # 1. Обертываем весь контент в центрирующий div
    print("\n1. ДОБАВЛЕНИЕ ЦЕНТРИРУЮЩЕЙ ОБЕРТКИ:")
    
    # Проверяем, есть ли уже обертка
    if '<div class="article-content"' not in html and '<div style="max-width: 1200px' not in html:
        # Добавляем центрирующую обертку в начало
        wrapper_start = '<div class="article-content" style="max-width: 1200px; margin: 0 auto; padding: 20px; text-align: left;">'
        wrapper_end = '</div>'
        
        # Обертываем весь контент
        html = wrapper_start + html + wrapper_end
        
        fixes_applied.append("Добавлена центрирующая обертка для всего контента")
        print("✅ Добавлена центрирующая обертка для всего контента")
    else:
        print("✅ Центрирующая обертка уже существует")
    
    # 2. Исправляем стили изображения
    print("\n2. ИСПРАВЛЕНИЕ СТИЛЕЙ ИЗОБРАЖЕНИЯ:")
    
    # Ищем изображение с max-width: 80% и исправляем
    img_pattern = r'<figure[^>]*style="[^"]*max-width:\s*80%[^"]*"'
    if re.search(img_pattern, html):
        # Заменяем max-width: 80% на max-width: 100%
        html = re.sub(r'max-width:\s*80%', 'max-width: 100%', html)
        
        # Добавляем дополнительные стили для лучшего отображения
        html = re.sub(r'(<figure[^>]*style="[^"]*)(margin:\s*[^;"]*)([^"]*")', r'\1margin: 20px 0\3', html)
        
        fixes_applied.append("Исправлены стили изображения для лучшего центрирования")
        print("✅ Исправлены стили изображения")
    else:
        print("✅ Стили изображения корректны")
    
    # 3. Исправляем стили таблицы содержания
    print("\n3. ИСПРАВЛЕНИЕ СТИЛЕЙ ТАБЛИЦЫ СОДЕРЖАНИЯ:")
    
    # Ищем div с table-of-contents
    toc_pattern = r'<div class="table-of-contents"[^>]*style="[^"]*max-width:\s*600px[^"]*"'
    if re.search(toc_pattern, html):
        # Увеличиваем max-width для лучшего отображения
        html = re.sub(r'max-width:\s*600px', 'max-width: 800px', html)
        
        fixes_applied.append("Увеличена ширина таблицы содержания")
        print("✅ Увеличена ширина таблицы содержания")
    else:
        print("✅ Стили таблицы содержания корректны")
    
    # 4. Добавляем общие стили для лучшего отображения
    print("\n4. ДОБАВЛЕНИЕ ОБЩИХ СТИЛЕЙ:")
    
    # Добавляем стили для параграфов
    html = re.sub(r'<p>', '<p style="line-height: 1.6; margin-bottom: 15px;">', html)
    
    # Добавляем стили для заголовков
    html = re.sub(r'<h2([^>]*)>', r'<h2\1 style="margin-top: 30px; margin-bottom: 15px;">', html)
    html = re.sub(r'<h3([^>]*)>', r'<h3\1 style="margin-top: 25px; margin-bottom: 12px;">', html)
    
    fixes_applied.append("Добавлены общие стили для лучшего отображения")
    print("✅ Добавлены общие стили")
    
    # Проверяем изменения
    if html != original_html:
        print(f"\n📊 ИЗМЕНЕНИЯ:")
        print(f"   Исходная длина: {len(original_html)} символов")
        print(f"   Новая длина: {len(html)} символов")
        print(f"   Разница: {len(html) - len(original_html)} символов")
        
        print(f"\n✅ ПРИМЕНЕНО ИСПРАВЛЕНИЙ: {len(fixes_applied)}")
        for i, fix in enumerate(fixes_applied, 1):
            print(f"   {i}. {fix}")
    else:
        print("\n⏭️ Изменений не требуется")
    
    return html, fixes_applied

def main():
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        
        # Получить содержимое статьи
        cursor.execute("SELECT post_content FROM wp_posts WHERE ID = 7942")
        original_content = cursor.fetchone()[0]
        
        print(f"📄 Исходная длина: {len(original_content)} символов")
        
        # Исправление центрирования
        fixed_content, fixes = fix_centering(original_content)
        
        # Обновить статью в базе данных
        cursor.execute("""
            UPDATE wp_posts 
            SET post_content = %s, 
                post_modified = NOW(), 
                post_modified_gmt = NOW() 
            WHERE ID = 7942
        """, (fixed_content,))
        
        conn.commit()
        print("\n✅ Статья обновлена в базе данных")
        
        cursor.close()
        conn.close()
        
        print("\n" + "=" * 80)
        print("✅ ЦЕНТРИРОВАНИЕ ИСПРАВЛЕНО!")
        print("=" * 80)
        
        return len(fixes) > 0
        
    except Exception as e:
        print(f"\n❌ ОШИБКА: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == '__main__':
    main()
