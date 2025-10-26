#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Исправление оставшихся проблем в статье 7942
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

def fix_remaining_issues(html):
    """Исправляет оставшиеся проблемы"""
    
    print("=" * 80)
    print("🔧 ИСПРАВЛЕНИЕ ОСТАВШИХСЯ ПРОБЛЕМ В СТАТЬЕ 7942")
    print("=" * 80)
    
    original_html = html
    fixes_applied = []
    
    # 1. Исправляем неправильную вложенность div в p
    print("\n1. ИСПРАВЛЕНИЕ НЕПРАВИЛЬНОЙ ВЛОЖЕННОСТИ:")
    
    # Более точный поиск проблемной вложенности
    # Ищем <p> содержащие <div> или <table>
    problematic_p = re.findall(r'<p([^>]*)>.*?(<div|<table)', html, re.DOTALL)
    if problematic_p:
        print(f"❌ Найдено {len(problematic_p)} проблемных вложений")
        
        # Убираем <p> перед <div> и <table>
        html = re.sub(r'<p([^>]*)>\s*(<div)', r'\2', html)
        html = re.sub(r'<p([^>]*)>\s*(<table)', r'\2', html)
        
        # Убираем соответствующие </p> после </div> и </table>
        html = re.sub(r'</div>\s*</p>', '</div>', html)
        html = re.sub(r'</table>\s*</p>', '</table>', html)
        
        fixes_applied.append("Исправлена неправильная вложенность div/table в p")
        print("✅ Исправлена вложенность div/table в p")
    else:
        print("✅ Вложенность корректна")
    
    # 2. Исправляем проблемы с изображениями
    print("\n2. ИСПРАВЛЕНИЕ ПРОБЛЕМ С ИЗОБРАЖЕНИЯМИ:")
    
    # Ищем изображения без alt атрибута (простой способ)
    all_images = re.findall(r'<img[^>]*>', html)
    images_without_alt = [img for img in all_images if 'alt=' not in img]
    
    if images_without_alt:
        # Добавляем alt атрибут к изображениям без него
        for img in images_without_alt:
            new_img = img.replace('<img', '<img alt="Изображение"')
            html = html.replace(img, new_img, 1)
        
        fixes_applied.append(f"Добавлены alt атрибуты к {len(images_without_alt)} изображениям")
        print(f"✅ Добавлены alt атрибуты к {len(images_without_alt)} изображениям")
    else:
        print("✅ Все изображения имеют alt атрибуты")
    
    # 3. Частичное исправление неэкранированных символов
    print("\n3. ИСПРАВЛЕНИЕ НЕЭКРАНИРОВАННЫХ СИМВОЛОВ:")
    
    # Заменяем наиболее критичные случаи (упрощенный подход)
    # & в тексте (но не в HTML entities)
    html = re.sub(r'&(?!(?:amp|lt|gt|quot|apos|nbsp|#\d+);)', '&amp;', html)
    
    fixes_applied.append("Исправлены критичные неэкранированные символы")
    print("✅ Исправлены критичные неэкранированные символы")
    
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
        
        # Исправление оставшихся проблем
        fixed_content, fixes = fix_remaining_issues(original_content)
        
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
        print("✅ ИСПРАВЛЕНИЕ ЗАВЕРШЕНО!")
        print("=" * 80)
        
        return len(fixes) > 0
        
    except Exception as e:
        print(f"\n❌ ОШИБКА: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == '__main__':
    main()
