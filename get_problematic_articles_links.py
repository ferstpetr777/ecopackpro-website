#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import json
import os
from datetime import datetime

def get_problematic_articles_links():
    """Извлечение ссылок на проблемные статьи из отчета аудита"""
    
    print("🔍 ПОИСК ОТЧЕТА АУДИТА И ИЗВЛЕЧЕНИЕ ССЫЛОК НА ПРОБЛЕМНЫЕ СТАТЬИ")
    print("=" * 80)
    
    # Ищем файл отчета аудита
    report_files = [f for f in os.listdir('.') if f.startswith('audit_featured_images_report_') and f.endswith('.json')]
    
    if not report_files:
        print("❌ Файл отчета аудита не найден")
        return
    
    # Берем самый свежий файл
    latest_report = sorted(report_files)[-1]
    print(f"📁 Найден отчет: {latest_report}")
    
    try:
        with open(latest_report, 'r', encoding='utf-8') as f:
            report_data = json.load(f)
        
        detailed_results = report_data.get('detailed_results', [])
        
        # Фильтруем проблемные статьи
        problematic_articles = [article for article in detailed_results if not article['is_match']]
        
        print(f"\n📊 СТАТИСТИКА:")
        print(f"  - Всего статей в отчете: {len(detailed_results)}")
        print(f"  - Проблемных статей: {len(problematic_articles)}")
        
        # Группируем по типам проблем
        placeholder_issues = []
        wrong_images = []
        missing_images = []
        other_issues = []
        
        for article in problematic_articles:
            reason = article['reason'].lower()
            if 'placeholder' in reason:
                placeholder_issues.append(article)
            elif 'разные изображения' in reason:
                wrong_images.append(article)
            elif 'не найдено' in reason:
                missing_images.append(article)
            else:
                other_issues.append(article)
        
        print(f"\n📋 РАСПРЕДЕЛЕНИЕ ПО ТИПАМ ПРОБЛЕМ:")
        print(f"  - Placeholder изображения: {len(placeholder_issues)}")
        print(f"  - Неправильные изображения: {len(wrong_images)}")
        print(f"  - Отсутствующие изображения: {len(missing_images)}")
        print(f"  - Другие проблемы: {len(other_issues)}")
        
        # Выводим ссылки на проблемные статьи
        print(f"\n🔗 ССЫЛКИ НА ПРОБЛЕМНЫЕ СТАТЬИ:")
        print("=" * 80)
        
        # 1. Placeholder изображения (приоритет 1)
        if placeholder_issues:
            print(f"\n🚨 1. СТАТЬИ С PLACEHOLDER ИЗОБРАЖЕНИЯМИ ({len(placeholder_issues)} статей):")
            print("-" * 60)
            for i, article in enumerate(placeholder_issues, 1):
                post_id = article['post_id']
                title = article['title']
                url = f"https://ecopackpro.ru/?p={post_id}&preview=true"
                print(f"{i:2d}. ID {post_id}: {title}")
                print(f"    🔗 {url}")
                print()
        
        # 2. Неправильные изображения (приоритет 2)
        if wrong_images:
            print(f"\n⚠️  2. СТАТЬИ С НЕПРАВИЛЬНЫМИ ИЗОБРАЖЕНИЯМИ ({len(wrong_images)} статей):")
            print("-" * 60)
            for i, article in enumerate(wrong_images, 1):
                post_id = article['post_id']
                title = article['title']
                url = f"https://ecopackpro.ru/?p={post_id}&preview=true"
                print(f"{i:2d}. ID {post_id}: {title}")
                print(f"    🔗 {url}")
                print()
        
        # 3. Отсутствующие изображения (приоритет 3)
        if missing_images:
            print(f"\n📭 3. СТАТЬИ С ОТСУТСТВУЮЩИМИ ИЗОБРАЖЕНИЯМИ ({len(missing_images)} статей):")
            print("-" * 60)
            for i, article in enumerate(missing_images, 1):
                post_id = article['post_id']
                title = article['title']
                url = f"https://ecopackpro.ru/?p={post_id}&preview=true"
                print(f"{i:2d}. ID {post_id}: {title}")
                print(f"    🔗 {url}")
                print()
        
        # 4. Другие проблемы
        if other_issues:
            print(f"\n❓ 4. СТАТЬИ С ДРУГИМИ ПРОБЛЕМАМИ ({len(other_issues)} статей):")
            print("-" * 60)
            for i, article in enumerate(other_issues, 1):
                post_id = article['post_id']
                title = article['title']
                url = f"https://ecopackpro.ru/?p={post_id}&preview=true"
                reason = article['reason']
                print(f"{i:2d}. ID {post_id}: {title}")
                print(f"    🔗 {url}")
                print(f"    📝 Проблема: {reason}")
                print()
        
        # Создаем файл со списком ссылок
        links_filename = f"problematic_articles_links_{datetime.now().strftime('%Y%m%d_%H%M%S')}.txt"
        
        with open(links_filename, 'w', encoding='utf-8') as f:
            f.write("ССЫЛКИ НА ПРОБЛЕМНЫЕ СТАТЬИ\n")
            f.write("=" * 50 + "\n\n")
            
            f.write(f"Всего проблемных статей: {len(problematic_articles)}\n")
            f.write(f"Дата создания: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n\n")
            
            # Placeholder изображения
            if placeholder_issues:
                f.write(f"СТАТЬИ С PLACEHOLDER ИЗОБРАЖЕНИЯМИ ({len(placeholder_issues)} статей):\n")
                f.write("-" * 50 + "\n")
                for article in placeholder_issues:
                    post_id = article['post_id']
                    title = article['title']
                    url = f"https://ecopackpro.ru/?p={post_id}&preview=true"
                    f.write(f"ID {post_id}: {title}\n")
                    f.write(f"{url}\n\n")
            
            # Неправильные изображения
            if wrong_images:
                f.write(f"СТАТЬИ С НЕПРАВИЛЬНЫМИ ИЗОБРАЖЕНИЯМИ ({len(wrong_images)} статей):\n")
                f.write("-" * 50 + "\n")
                for article in wrong_images:
                    post_id = article['post_id']
                    title = article['title']
                    url = f"https://ecopackpro.ru/?p={post_id}&preview=true"
                    f.write(f"ID {post_id}: {title}\n")
                    f.write(f"{url}\n\n")
            
            # Отсутствующие изображения
            if missing_images:
                f.write(f"СТАТЬИ С ОТСУТСТВУЮЩИМИ ИЗОБРАЖЕНИЯМИ ({len(missing_images)} статей):\n")
                f.write("-" * 50 + "\n")
                for article in missing_images:
                    post_id = article['post_id']
                    title = article['title']
                    url = f"https://ecopackpro.ru/?p={post_id}&preview=true"
                    f.write(f"ID {post_id}: {title}\n")
                    f.write(f"{url}\n\n")
        
        print(f"\n📁 Список ссылок сохранен в файл: {links_filename}")
        
        # Краткая сводка
        print(f"\n📋 КРАТКАЯ СВОДКА:")
        print(f"🎯 Всего проблемных статей: {len(problematic_articles)}")
        print(f"🔥 Критических (placeholder): {len(placeholder_issues)}")
        print(f"⚠️  Требующих исправления: {len(wrong_images)}")
        print(f"📭 С техническими проблемами: {len(missing_images)}")
        
        return problematic_articles
        
    except Exception as e:
        print(f"❌ Ошибка чтения отчета: {e}")
        return []

if __name__ == "__main__":
    get_problematic_articles_links()
