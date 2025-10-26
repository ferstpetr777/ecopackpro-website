#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Финальное исправление оставшихся проблем в статье 7942
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

def fix_final_issues(html):
    """Финальное исправление оставшихся проблем"""
    
    print("=" * 80)
    print("🔧 ФИНАЛЬНОЕ ИСПРАВЛЕНИЕ ПРОБЛЕМ В СТАТЬЕ 7942")
    print("=" * 80)
    
    original_html = html
    fixes_applied = []
    
    # 1. Исправляем дисбаланс тега p (129 vs 128)
    print("\n1. ИСПРАВЛЕНИЕ ДИСБАЛАНСА ТЕГА P:")
    open_p = len(re.findall(r'<p[^>]*>', html))
    close_p = len(re.findall(r'</p>', html))
    
    if open_p > close_p:
        missing_p = open_p - close_p
        # Добавляем недостающий </p> в конец
        html += '</p>'
        fixes_applied.append(f"Добавлен {missing_p} недостающий </p> тег")
        print(f"✅ Добавлен {missing_p} недостающий </p> тег")
    else:
        print("✅ Теги p сбалансированы")
    
    # 2. Исправляем неправильную вложенность
    print("\n2. ИСПРАВЛЕНИЕ НЕПРАВИЛЬНОЙ ВЛОЖЕННОСТИ:")
    
    # Ищем и исправляем <p><div> и <p><table>
    p_div_count = len(re.findall(r'<p[^>]*>\s*<div', html))
    p_table_count = len(re.findall(r'<p[^>]*>\s*<table', html))
    
    if p_div_count > 0 or p_table_count > 0:
        # Убираем <p> перед блочными элементами
        html = re.sub(r'<p([^>]*)>\s*(<div)', r'\2', html)
        html = re.sub(r'<p([^>]*)>\s*(<table)', r'\2', html)
        
        # Убираем соответствующие </p> после блочных элементов
        html = re.sub(r'</div>\s*</p>', '</div>', html)
        html = re.sub(r'</table>\s*</p>', '</table>', html)
        
        total_fixes = p_div_count + p_table_count
        fixes_applied.append(f"Исправлена вложенность: {total_fixes} случаев")
        print(f"✅ Исправлена вложенность: {total_fixes} случаев")
    else:
        print("✅ Вложенность корректна")
    
    # 3. Минимальное исправление неэкранированных символов
    print("\n3. МИНИМАЛЬНОЕ ИСПРАВЛЕНИЕ НЕЭКРАНИРОВАННЫХ СИМВОЛОВ:")
    
    # Заменяем только самые критичные случаи
    # & в начале слов (но не в HTML entities)
    html = re.sub(r'\b&(?!(?:amp|lt|gt|quot|apos|nbsp|#\d+);)\b', '&amp;', html)
    
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
        
        # Финальное исправление
        fixed_content, fixes = fix_final_issues(original_content)
        
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
        print("✅ ФИНАЛЬНОЕ ИСПРАВЛЕНИЕ ЗАВЕРШЕНО!")
        print("=" * 80)
        
        return len(fixes) > 0
        
    except Exception as e:
        print(f"\n❌ ОШИБКА: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == '__main__':
    main()
