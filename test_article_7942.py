#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Финальное тестирование статьи 7942
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

def comprehensive_test():
    """Комплексное тестирование статьи 7942"""
    print("=" * 70)
    print("🧪 КОМПЛЕКСНОЕ ТЕСТИРОВАНИЕ СТАТЬИ 7942")
    print("=" * 70)
    
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        
        # 1. Проверка существования статьи
        print("\n1. Проверка существования статьи...")
        cursor.execute("SELECT ID, post_title, post_status, post_modified FROM wp_posts WHERE ID = 7942")
        result = cursor.fetchone()
        
        if result:
            article_id, title, status, modified = result
            print(f"✅ Статья найдена: ID {article_id}")
            print(f"✅ Заголовок: {title}")
            print(f"✅ Статус: {status}")
            print(f"✅ Последнее изменение: {modified}")
        else:
            print("❌ Статья не найдена")
            return False
        
        # 2. Проверка содержимого
        print("\n2. Проверка содержимого...")
        cursor.execute("SELECT post_content FROM wp_posts WHERE ID = 7942")
        content = cursor.fetchone()[0]
        
        print(f"✅ Длина контента: {len(content)} символов")
        
        # 3. Проверка HTML валидности
        print("\n3. Проверка HTML валидности...")
        
        # Считаем теги
        open_tags = len(re.findall(r'<([a-zA-Z][a-zA-Z0-9]*)[^>]*>', content))
        close_tags = len(re.findall(r'</([a-zA-Z][a-zA-Z0-9]*)>', content))
        self_closing = len(re.findall(r'<([a-zA-Z][a-zA-Z0-9]*)[^>]*/>', content))
        
        print(f"✅ Открывающих тегов: {open_tags}")
        print(f"✅ Закрывающих тегов: {close_tags}")
        print(f"✅ Самозакрывающихся тегов: {self_closing}")
        
        # Проверяем баланс
        if open_tags == close_tags:
            print("✅ HTML теги сбалансированы")
        else:
            print(f"⚠️ Несбалансированные теги: {open_tags - close_tags}")
        
        # 4. Проверка проблемных элементов
        print("\n4. Проверка проблемных элементов...")
        
        issues = []
        
        if '<script' in content.lower():
            issues.append("script теги")
        if 'onclick=' in content.lower():
            issues.append("onclick атрибуты")
        if 'javascript:' in content.lower():
            issues.append("javascript ссылки")
        if content.count('<') != content.count('>'):
            issues.append("несбалансированные угловые скобки")
        
        if issues:
            print(f"⚠️ Найдены проблемы: {', '.join(issues)}")
        else:
            print("✅ Проблемных элементов не найдено")
        
        # 5. Проверка внешних ссылок
        print("\n5. Проверка внешних ссылок...")
        external_links = re.findall(r'href="https?://[^"]*"', content)
        print(f"✅ Найдено внешних ссылок: {len(external_links)}")
        
        # 6. Проверка метаданных
        print("\n6. Проверка метаданных...")
        cursor.execute("SELECT COUNT(*) FROM wp_postmeta WHERE post_id = 7942")
        meta_count = cursor.fetchone()[0]
        print(f"✅ Мета-полей: {meta_count}")
        
        # 7. Финальная оценка
        print("\n7. Финальная оценка...")
        score = 100
        
        if open_tags != close_tags:
            score -= 30
        if issues:
            score -= 20
        if len(content) < 1000:
            score -= 10
        
        print(f"✅ Общий балл: {score}/100")
        
        if score >= 90:
            print("🎉 СТАТЬЯ ГОТОВА К ИСПОЛЬЗОВАНИЮ!")
            return True
        elif score >= 70:
            print("⚠️ Статья требует дополнительной проверки")
            return False
        else:
            print("❌ Статья требует исправления")
            return False
            
    except Exception as e:
        print(f"❌ Ошибка тестирования: {e}")
        return False
    finally:
        if 'cursor' in locals():
            cursor.close()
        if 'conn' in locals():
            conn.close()

if __name__ == '__main__':
    comprehensive_test()
