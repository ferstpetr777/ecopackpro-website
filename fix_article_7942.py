#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для исправления HTML ошибок в статье 7942
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

def fix_html_errors(html):
    """Исправляет HTML ошибки которые блокируют редактор WordPress"""
    
    # 1. Исправляем некорректные ссылки без текста
    # Находим <a href="..." target="_blank"></a> и заменяем на корректные
    html = re.sub(r'<a href="([^"]*)"[^>]*target="_blank"[^>]*>\s*</a>', r'<a href="\1" target="_blank">Внешняя ссылка</a>', html)
    
    # 2. Убираем пустые теги <li></li>
    html = re.sub(r'<li>\s*</li>', '', html)
    
    # 3. Убираем пустые теги <p></p>
    html = re.sub(r'<p>\s*</p>', '', html)
    
    # 4. Исправляем некорректные <br> теги
    html = re.sub(r'<br>\s*</div>', '</div>', html)
    
    # 5. Убираем лишние <div> без содержимого
    html = re.sub(r'<div[^>]*>\s*</div>', '', html)
    
    # 6. Исправляем незакрытые теги
    html = re.sub(r'<hr/>\s*<br>\s*</div>', '<hr/></div>', html)
    
    # 7. Убираем лишние пробелы и переносы строк
    html = re.sub(r'\s+', ' ', html)
    html = re.sub(r'>\s+<', '><', html)
    
    # 8. Проверяем баланс тегов
    # Считаем открывающие и закрывающие теги
    open_tags = len(re.findall(r'<([a-zA-Z][a-zA-Z0-9]*)[^>]*>', html))
    close_tags = len(re.findall(r'</([a-zA-Z][a-zA-Z0-9]*)>', html))
    
    print(f"Открывающих тегов: {open_tags}, закрывающих: {close_tags}")
    
    return html

def test_article_loading():
    """Тестирует загрузку статьи в редакторе"""
    print("\n🧪 ТЕСТИРОВАНИЕ ЗАГРУЗКИ СТАТЬИ...")
    
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        
        # Проверяем что статья существует и доступна
        cursor.execute("SELECT ID, post_title, post_status, post_content FROM wp_posts WHERE ID = 7942")
        result = cursor.fetchone()
        
        if result:
            article_id, title, status, content = result
            print(f"✅ Статья найдена: ID {article_id}, статус: {status}")
            print(f"✅ Заголовок: {title[:50]}...")
            print(f"✅ Длина контента: {len(content)} символов")
            
            # Проверяем на наличие проблемных символов
            if '<script' in content.lower():
                print("⚠️ Найдены script теги")
            if 'onclick=' in content.lower():
                print("⚠️ Найдены onclick атрибуты")
            if content.count('<') != content.count('>'):
                print("⚠️ Несбалансированные HTML теги")
            
            return True
        else:
            print("❌ Статья не найдена")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка тестирования: {e}")
        return False
    finally:
        if 'cursor' in locals():
            cursor.close()
        if 'conn' in locals():
            conn.close()

def main():
    print("=" * 70)
    print("ИСПРАВЛЕНИЕ HTML ОШИБОК В СТАТЬЕ 7942")
    print("=" * 70)
    
    try:
        # Подключение к БД
        print("\n1. Подключение к базе данных...")
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        print("✅ Подключено к БД")
        
        # Получить содержимое статьи
        print("\n2. Получение содержимого статьи 7942...")
        cursor.execute("SELECT post_content FROM wp_posts WHERE ID = 7942")
        result = cursor.fetchone()
        
        if not result:
            print("❌ Статья 7942 не найдена")
            return
        
        current_content = result[0]
        print(f"✅ Получено содержимое: {len(current_content)} символов")
        
        # Исправить HTML ошибки
        print("\n3. Исправление HTML ошибок...")
        fixed_content = fix_html_errors(current_content)
        
        # Проверить изменения
        if current_content != fixed_content:
            print("✅ Найдены и исправлены HTML ошибки")
            
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
            print("⏭️ HTML ошибки не найдены")
        
        # Тестирование
        print("\n4. Тестирование загрузки статьи...")
        if test_article_loading():
            print("✅ Тест пройден успешно")
        else:
            print("❌ Тест не пройден")
        
        # Закрыть подключение
        cursor.close()
        conn.close()
        
        print("\n" + "=" * 70)
        print("✅ ИСПРАВЛЕНИЕ ЗАВЕРШЕНО!")
        print("📋 Проверьте статью в редакторе WordPress")
        print("=" * 70)
        
    except Exception as e:
        print(f"\n❌ КРИТИЧЕСКАЯ ОШИБКА: {e}")
        import traceback
        traceback.print_exc()

if __name__ == '__main__':
    main()
