#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для исправления дублированных div тегов в статье 7942
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

def fix_duplicate_divs(html):
    """Убирает дублированные div теги в конце статьи"""
    
    print(f"📄 Исходная длина: {len(html)} символов")
    
    # Находим последний правильный закрывающий тег перед дублированными div'ами
    # Ищем паттерн: </div></div></div>... (много подряд)
    
    # Подсчитываем количество </div> в конце
    div_count = 0
    pos = len(html) - 1
    
    while pos >= 0:
        if html[pos:pos+6] == '</div>':
            div_count += 1
            pos -= 6
        else:
            break
    
    print(f"🔍 Найдено {div_count} дублированных </div> тегов в конце")
    
    if div_count > 10:  # Если слишком много div'ов
        # Находим последний значимый контент
        # Ищем последний </p> или </hr> перед дублированными div'ами
        
        # Ищем последний осмысленный контент
        last_meaningful = html.rfind('</div>', 0, html.rfind('💡 Переходите между статьями'))
        
        if last_meaningful > 0:
            # Обрезаем все дублированные div'ы после осмысленного контента
            html = html[:last_meaningful + 6]  # +6 для </div>
            print(f"✅ Удалены дублированные div теги")
        else:
            # Альтернативный способ - убираем лишние </div> в конце
            # Считаем сколько </div> должно быть
            open_divs = len(re.findall(r'<div[^>]*>', html))
            close_divs = len(re.findall(r'</div>', html))
            
            # Оставляем только нужное количество закрывающих тегов
            excess_divs = close_divs - open_divs
            if excess_divs > 0:
                # Убираем лишние </div> с конца
                for _ in range(excess_divs):
                    html = html.rsplit('</div>', 1)[0]
                print(f"✅ Убрано {excess_divs} лишних </div> тегов")
    
    print(f"📄 Исправленная длина: {len(html)} символов")
    
    return html

def main():
    print("=" * 70)
    print("ИСПРАВЛЕНИЕ ДУБЛИРОВАННЫХ DIV ТЕГОВ В СТАТЬЕ 7942")
    print("=" * 70)
    
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        
        # Получить содержимое статьи
        cursor.execute("SELECT post_content FROM wp_posts WHERE ID = 7942")
        current_content = cursor.fetchone()[0]
        
        # Исправить дублированные div теги
        fixed_content = fix_duplicate_divs(current_content)
        
        # Проверить изменения
        if current_content != fixed_content:
            print("✅ Найдены и исправлены дублированные div теги")
            
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
            
        else:
            print("⏭️ Дублированные div теги не найдены")
        
        # Проверить баланс тегов
        open_divs = len(re.findall(r'<div[^>]*>', fixed_content))
        close_divs = len(re.findall(r'</div>', fixed_content))
        
        print(f"\n📊 Баланс div тегов:")
        print(f"   Открывающих: {open_divs}")
        print(f"   Закрывающих: {close_divs}")
        print(f"   Разница: {open_divs - close_divs}")
        
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
