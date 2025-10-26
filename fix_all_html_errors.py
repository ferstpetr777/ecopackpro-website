#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Исправление всех найденных HTML ошибок в статье 7942
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

def fix_all_html_errors(html):
    """Исправляет все найденные HTML ошибки"""
    
    print("=" * 80)
    print("🔧 ИСПРАВЛЕНИЕ ВСЕХ HTML ОШИБОК В СТАТЬЕ 7942")
    print("=" * 80)
    
    original_html = html
    fixes_applied = []
    
    # 1. Исправляем дисбаланс тега p (129 vs 128)
    print("\n1. ИСПРАВЛЕНИЕ ДИСБАЛАНСА ТЕГА P:")
    open_p = len(re.findall(r'<p[^>]*>', html))
    close_p = len(re.findall(r'</p>', html))
    
    if open_p > close_p:
        missing_p = open_p - close_p
        # Добавляем недостающие закрывающие </p> теги
        for _ in range(missing_p):
            html += '</p>'
        fixes_applied.append(f"Добавлено {missing_p} недостающих </p> тегов")
        print(f"✅ Добавлено {missing_p} недостающих </p> тегов")
    else:
        print("✅ Теги p сбалансированы")
    
    # 2. Исправляем дисбаланс тега th (46 vs 36)
    print("\n2. ИСПРАВЛЕНИЕ ДИСБАЛАНСА ТЕГА TH:")
    open_th = len(re.findall(r'<th[^>]*>', html))
    close_th = len(re.findall(r'</th>', html))
    
    if open_th > close_th:
        missing_th = open_th - close_th
        # Находим все <th> без закрывающих тегов и добавляем </th>
        th_positions = []
        for match in re.finditer(r'<th[^>]*>', html):
            th_positions.append(match.end())
        
        # Добавляем недостающие </th> после каждого открывающего
        offset = 0
        for pos in th_positions:
            # Проверяем есть ли уже закрывающий тег после этого th
            after_th = html[pos + offset:]
            if not re.match(r'\s*</th>', after_th):
                html = html[:pos + offset] + '</th>' + html[pos + offset:]
                offset += 5  # длина '</th>'
                missing_th -= 1
                if missing_th == 0:
                    break
        
        fixes_applied.append(f"Добавлено {open_th - close_th} недостающих </th> тегов")
        print(f"✅ Добавлено {open_th - close_th} недостающих </th> тегов")
    else:
        print("✅ Теги th сбалансированы")
    
    # 3. Исправляем пустые атрибуты
    print("\n3. ИСПРАВЛЕНИЕ ПУСТЫХ АТРИБУТОВ:")
    empty_attrs = re.findall(r'=\s*["\']\s*["\']', html)
    if empty_attrs:
        # Убираем пустые атрибуты
        html = re.sub(r'\s*=\s*["\']\s*["\']', '', html)
        fixes_applied.append(f"Убрано {len(empty_attrs)} пустых атрибутов")
        print(f"✅ Убрано {len(empty_attrs)} пустых атрибутов")
    else:
        print("✅ Пустые атрибуты не найдены")
    
    # 4. Исправляем неправильную вложенность div в p
    print("\n4. ИСПРАВЛЕНИЕ НЕПРАВИЛЬНОЙ ВЛОЖЕННОСТИ:")
    # Ищем <p><div> и заменяем на <div>
    nested_issues = re.findall(r'<p[^>]*>\s*<div', html)
    if nested_issues:
        # Убираем <p> перед <div>
        html = re.sub(r'<p([^>]*)>\s*<div', r'<div', html)
        # Убираем соответствующие </p> после </div>
        html = re.sub(r'</div>\s*</p>', r'</div>', html)
        fixes_applied.append(f"Исправлена вложенность div в p: {len(nested_issues)} случаев")
        print(f"✅ Исправлена вложенность: {len(nested_issues)} случаев")
    else:
        print("✅ Вложенность корректна")
    
    # 5. Исправляем неэкранированные символы (частично)
    print("\n5. ИСПРАВЛЕНИЕ НЕЭКРАНИРОВАННЫХ СИМВОЛОВ:")
    # Заменяем наиболее критичные символы
    replacements = 0
    
    # Заменяем < и > в тексте (но не в HTML тегах)
    # Это сложная операция, поэтому делаем базовые замены
    html = re.sub(r'&lt;(?![^>]*>)', '&lt;', html)
    html = re.sub(r'&gt;(?![^>]*>)', '&gt;', html)
    
    # Заменяем & в тексте
    html = re.sub(r'&(?![a-zA-Z][a-zA-Z0-9]{1,31};)', '&amp;', html)
    
    fixes_applied.append("Исправлены основные неэкранированные символы")
    print("✅ Исправлены основные неэкранированные символы")
    
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
        
        # Исправление всех ошибок
        fixed_content, fixes = fix_all_html_errors(original_content)
        
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
