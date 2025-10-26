#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для создания резинового дизайна с автоматическим центрированием статьи 7942
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

def fix_responsive_centering(html):
    """Создает резиновый дизайн с автоматическим центрированием"""
    
    print("=" * 80)
    print("🎯 СОЗДАНИЕ РЕЗИНОВОГО ДИЗАЙНА С ЦЕНТРИРОВАНИЕМ")
    print("=" * 80)
    
    original_html = html
    fixes_applied = []
    
    # 1. Заменяем существующую обертку на адаптивную
    print("\n1. СОЗДАНИЕ АДАПТИВНОЙ ОБЕРТКИ:")
    
    # Удаляем старую обертку если есть
    if '<div class="article-content"' in html:
        html = re.sub(r'<div class="article-content"[^>]*>', '', html)
        html = re.sub(r'</div>\s*$', '', html)
        fixes_applied.append("Удалена старая обертка")
    
    # Создаем новую адаптивную обертку
    responsive_wrapper = '''<div class="article-responsive-wrapper" style="
        max-width: 90%;
        margin: 0 auto;
        padding: 20px 15px;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
    ">
        <div class="article-content" style="
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0;
            text-align: left;
            box-sizing: border-box;
        ">'''
    
    wrapper_end = '''</div>
    </div>'''
    
    # Обертываем весь контент
    html = responsive_wrapper + html + wrapper_end
    
    fixes_applied.append("Создана адаптивная обертка с flexbox центрированием")
    print("✅ Создана адаптивная обертка с flexbox центрированием")
    
    # 2. Исправляем стили изображения для полной адаптивности
    print("\n2. АДАПТИВНЫЕ СТИЛИ ИЗОБРАЖЕНИЯ:")
    
    # Заменяем стили figure для адаптивности
    figure_pattern = r'<figure[^>]*style="[^"]*"'
    if re.search(figure_pattern, html):
        new_figure_style = '''<figure class="wp-block-image size-large" style="
            width: 100%;
            max-width: 100%;
            margin: 20px auto;
            text-align: center;
            display: block;
        ">'''
        
        html = re.sub(figure_pattern, new_figure_style, html)
        fixes_applied.append("Обновлены стили изображения для адаптивности")
        print("✅ Обновлены стили изображения для адаптивности")
    
    # 3. Адаптивные стили для таблицы содержания
    print("\n3. АДАПТИВНЫЕ СТИЛИ ТАБЛИЦЫ СОДЕРЖАНИЯ:")
    
    toc_pattern = r'<div class="table-of-contents"[^>]*style="[^"]*"'
    if re.search(toc_pattern, html):
        new_toc_style = '''<div class="table-of-contents" style="
            width: 100%;
            max-width: 100%;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            box-sizing: border-box;
        ">'''
        
        html = re.sub(toc_pattern, new_toc_style, html)
        fixes_applied.append("Обновлены стили таблицы содержания для адаптивности")
        print("✅ Обновлены стили таблицы содержания для адаптивности")
    
    # 4. Адаптивные стили для сетки ссылок в содержании
    print("\n4. АДАПТИВНАЯ СЕТКА СОДЕРЖАНИЯ:")
    
    grid_pattern = r'<div style="display: grid; grid-template-columns: repeat\(auto-fit, minmax\(280px, 1fr\)\); gap: 6px; margin: 0;">'
    if re.search(grid_pattern, html):
        new_grid_style = '''<div style="
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 8px;
            margin: 0;
            width: 100%;
        ">'''
        
        html = re.sub(grid_pattern, new_grid_style, html)
        fixes_applied.append("Обновлена сетка содержания для адаптивности")
        print("✅ Обновлена сетка содержания для адаптивности")
    
    # 5. Адаптивные стили для таблиц
    print("\n5. АДАПТИВНЫЕ СТИЛИ ТАБЛИЦ:")
    
    # Добавляем стили для всех таблиц
    html = re.sub(r'<table>', '''<table style="
        width: 100%;
        max-width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        overflow-x: auto;
        display: block;
        white-space: nowrap;
    ">''', html)
    
    fixes_applied.append("Добавлены адаптивные стили для таблиц")
    print("✅ Добавлены адаптивные стили для таблиц")
    
    # 6. Адаптивные стили для параграфов
    print("\n6. АДАПТИВНЫЕ СТИЛИ ПАРАГРАФОВ:")
    
    # Заменяем существующие стили параграфов
    html = re.sub(r'<p style="[^"]*">', '''<p style="
        width: 100%;
        max-width: 100%;
        line-height: 1.6;
        margin-bottom: 15px;
        word-wrap: break-word;
        box-sizing: border-box;
    ">''', html)
    
    # Для параграфов без стилей (простой способ)
    # Находим все <p> теги без style атрибута
    p_tags_without_style = re.findall(r'<p>(?!.*style)', html)
    for p_tag in p_tags_without_style:
        new_p_tag = '''<p style="
        width: 100%;
        max-width: 100%;
        line-height: 1.6;
        margin-bottom: 15px;
        word-wrap: break-word;
        box-sizing: border-box;
    ">'''
        html = html.replace(p_tag, new_p_tag, 1)
    
    fixes_applied.append("Обновлены стили параграфов для адаптивности")
    print("✅ Обновлены стили параграфов для адаптивности")
    
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
        
        # Создание резинового дизайна
        fixed_content, fixes = fix_responsive_centering(original_content)
        
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
        print("✅ РЕЗИНОВЫЙ ДИЗАЙН СОЗДАН!")
        print("=" * 80)
        
        return len(fixes) > 0
        
    except Exception as e:
        print(f"\n❌ ОШИБКА: {e}")
        import traceback
        traceback.print_exc()
        return False

if __name__ == '__main__':
    main()
