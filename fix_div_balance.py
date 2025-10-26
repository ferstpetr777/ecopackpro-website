#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для исправления дисбаланса div тегов в статье 7942
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

def fix_div_balance(html):
    """Исправляет дисбаланс div тегов"""
    
    print(f"📄 Исходная длина: {len(html)} символов")
    
    # Считаем теги
    open_divs = len(re.findall(r'<div[^>]*>', html))
    close_divs = len(re.findall(r'</div>', html))
    
    print(f"🔍 Открывающих div: {open_divs}")
    print(f"🔍 Закрывающих div: {close_divs}")
    print(f"🔍 Дисбаланс: {open_divs - close_divs}")
    
    # Если закрывающих больше чем открывающих - убираем лишние
    if close_divs > open_divs:
        excess = close_divs - open_divs
        print(f"⚠️ Найдено {excess} лишних закрывающих div тегов")
        
        # Убираем лишние </div> с конца строки
        # Находим все </div> и убираем лишние с конца
        div_positions = []
        for match in re.finditer(r'</div>', html):
            div_positions.append(match.end())
        
        # Убираем последние excess </div>
        if excess > 0 and len(div_positions) >= excess:
            # Находим позицию для обрезки
            cut_pos = div_positions[-excess]
            html = html[:cut_pos - 6]  # -6 для длины '</div>'
            print(f"✅ Убрано {excess} лишних </div> тегов")
    
    # Пересчитываем баланс
    open_divs_new = len(re.findall(r'<div[^>]*>', html))
    close_divs_new = len(re.findall(r'</div>', html))
    
    print(f"📊 Новый баланс:")
    print(f"   Открывающих: {open_divs_new}")
    print(f"   Закрывающих: {close_divs_new}")
    print(f"   Разница: {open_divs_new - close_divs_new}")
    
    print(f"📄 Исправленная длина: {len(html)} символов")
    
    return html

def main():
    print("=" * 70)
    print("ИСПРАВЛЕНИЕ ДИСБАЛАНСА DIV ТЕГОВ В СТАТЬЕ 7942")
    print("=" * 70)
    
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        
        # Получить содержимое статьи
        cursor.execute("SELECT post_content FROM wp_posts WHERE ID = 7942")
        current_content = cursor.fetchone()[0]
        
        # Исправить дисбаланс div тегов
        fixed_content = fix_div_balance(current_content)
        
        # Обновить статью
        cursor.execute("""
            UPDATE wp_posts 
            SET post_content = %s, 
                post_modified = NOW(), 
                post_modified_gmt = NOW() 
            WHERE ID = 7942
        """, (fixed_content,))
        
        conn.commit()
        print("✅ Статья обновлена в базе данных")
        
        cursor.close()
        conn.close()
        
        print("\n" + "=" * 70)
        print("✅ ИСПРАВЛЕНИЕ ЗАВЕРШЕНО!")
        print("📋 Проверьте статью на сайте")
        print("=" * 70)
        
    except Exception as e:
        print(f"\n❌ ОШИБКА: {e}")
        import traceback
        traceback.print_exc()

if __name__ == '__main__':
    main()
