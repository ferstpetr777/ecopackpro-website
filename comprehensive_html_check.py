#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Комплексная проверка HTML кода статьи 7942
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

def comprehensive_html_check(html):
    """Комплексная проверка HTML кода на все возможные ошибки"""
    
    print("=" * 80)
    print("🔍 КОМПЛЕКСНАЯ ПРОВЕРКА HTML КОДА СТАТЬИ 7942")
    print("=" * 80)
    
    issues = []
    
    # 1. Проверка баланса тегов
    print("\n1. ПРОВЕРКА БАЛАНСА ТЕГОВ:")
    
    # Считаем все основные теги
    tags_to_check = ['div', 'p', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'ul', 'ol', 'li', 'table', 'tr', 'td', 'th', 'thead', 'tbody']
    
    for tag in tags_to_check:
        open_tags = len(re.findall(f'<{tag}[^>]*>', html, re.IGNORECASE))
        close_tags = len(re.findall(f'</{tag}>', html, re.IGNORECASE))
        
        if open_tags != close_tags:
            issues.append(f"Дисбаланс тега {tag}: {open_tags} открывающих vs {close_tags} закрывающих")
            print(f"❌ {tag}: {open_tags} vs {close_tags}")
        else:
            print(f"✅ {tag}: {open_tags} сбалансированы")
    
    # 2. Проверка самозакрывающихся тегов
    print("\n2. ПРОВЕРКА САМОЗАКРЫВАЮЩИХСЯ ТЕГОВ:")
    
    self_closing_tags = ['br', 'hr', 'img', 'input', 'meta', 'link']
    for tag in self_closing_tags:
        # Ищем некорректные самозакрывающиеся теги
        incorrect_pattern = f'<{tag}[^>]*></{tag}>'
        matches = re.findall(incorrect_pattern, html, re.IGNORECASE)
        if matches:
            issues.append(f"Некорректные самозакрывающиеся теги {tag}: {len(matches)} шт")
            print(f"❌ {tag}: {len(matches)} некорректных тегов")
        else:
            print(f"✅ {tag}: корректные теги")
    
    # 3. Проверка атрибутов
    print("\n3. ПРОВЕРКА АТРИБУТОВ:")
    
    # Проверяем незакрытые кавычки в атрибутах
    unclosed_quotes = re.findall(r'=\s*"[^"]*$|=\s*\'[^\']*$', html)
    if unclosed_quotes:
        issues.append(f"Незакрытые кавычки в атрибутах: {len(unclosed_quotes)} шт")
        print(f"❌ Незакрытые кавычки: {len(unclosed_quotes)}")
    else:
        print("✅ Кавычки в атрибутах закрыты")
    
    # Проверяем пустые атрибуты
    empty_attrs = re.findall(r'=\s*["\']\s*["\']', html)
    if empty_attrs:
        issues.append(f"Пустые атрибуты: {len(empty_attrs)} шт")
        print(f"❌ Пустые атрибуты: {len(empty_attrs)}")
    else:
        print("✅ Пустые атрибуты не найдены")
    
    # 4. Проверка вложенности
    print("\n4. ПРОВЕРКА ВЛОЖЕННОСТИ:")
    
    # Проверяем неправильную вложенность блочных элементов
    block_inline_issues = re.findall(r'<div[^>]*>.*?<p[^>]*>.*?<div[^>]*>', html, re.DOTALL)
    if block_inline_issues:
        issues.append(f"Неправильная вложенность div в p: {len(block_inline_issues)} шт")
        print(f"❌ Неправильная вложенность: {len(block_inline_issues)}")
    else:
        print("✅ Вложенность корректна")
    
    # 5. Проверка специальных символов
    print("\n5. ПРОВЕРКА СПЕЦИАЛЬНЫХ СИМВОЛОВ:")
    
    # Проверяем HTML entities
    unescaped_chars = re.findall(r'[<>&]', html)
    if unescaped_chars:
        issues.append(f"Неэкранированные символы: {len(unescaped_chars)} шт")
        print(f"❌ Неэкранированные символы: {len(unescaped_chars)}")
    else:
        print("✅ Специальные символы экранированы")
    
    # 6. Проверка CSS в style атрибутах
    print("\n6. ПРОВЕРКА CSS В STYLE АТРИБУТАХ:")
    
    style_attrs = re.findall(r'style\s*=\s*["\']([^"\']*)["\']', html)
    css_issues = 0
    for style in style_attrs:
        # Проверяем незакрытые CSS правила
        if style.count('{') != style.count('}'):
            css_issues += 1
        # Проверяем некорректные CSS значения
        if re.search(r';\s*;', style):
            css_issues += 1
    
    if css_issues:
        issues.append(f"Проблемы в CSS: {css_issues} шт")
        print(f"❌ Проблемы в CSS: {css_issues}")
    else:
        print("✅ CSS в атрибутах корректный")
    
    # 7. Проверка ссылок
    print("\n7. ПРОВЕРКА ССЫЛОК:")
    
    links = re.findall(r'<a[^>]*href\s*=\s*["\']([^"\']*)["\'][^>]*>', html)
    broken_links = 0
    for link in links:
        if link.startswith('http') and (' ' in link or link.count('//') > 1):
            broken_links += 1
    
    if broken_links:
        issues.append(f"Проблемные ссылки: {broken_links} шт")
        print(f"❌ Проблемные ссылки: {broken_links}")
    else:
        print("✅ Ссылки корректные")
    
    # 8. Проверка изображений
    print("\n8. ПРОВЕРКА ИЗОБРАЖЕНИЙ:")
    
    images = re.findall(r'<img[^>]*>', html)
    img_issues = 0
    for img in images:
        if not re.search(r'alt\s*=', img):
            img_issues += 1
        if not re.search(r'src\s*=', img):
            img_issues += 1
    
    if img_issues:
        issues.append(f"Проблемы с изображениями: {img_issues} шт")
        print(f"❌ Проблемы с изображениями: {img_issues}")
    else:
        print("✅ Изображения корректные")
    
    # Итоговый отчет
    print("\n" + "=" * 80)
    print("📊 ИТОГОВЫЙ ОТЧЕТ:")
    print("=" * 80)
    
    if issues:
        print(f"❌ НАЙДЕНО {len(issues)} ПРОБЛЕМ:")
        for i, issue in enumerate(issues, 1):
            print(f"   {i}. {issue}")
        return False, issues
    else:
        print("✅ HTML КОД КОРРЕКТЕН - ПРОБЛЕМ НЕ НАЙДЕНО!")
        return True, []

def main():
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        
        # Получить содержимое статьи
        cursor.execute("SELECT post_content FROM wp_posts WHERE ID = 7942")
        content = cursor.fetchone()[0]
        
        print(f"📄 Длина контента: {len(content)} символов")
        
        # Комплексная проверка
        is_valid, issues = comprehensive_html_check(content)
        
        cursor.close()
        conn.close()
        
        return is_valid, issues
        
    except Exception as e:
        print(f"\n❌ ОШИБКА: {e}")
        import traceback
        traceback.print_exc()
        return False, [str(e)]

if __name__ == '__main__':
    main()
