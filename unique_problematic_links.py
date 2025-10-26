#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import json
import os
from datetime import datetime

def get_unique_problematic_links():
    """Получение уникальных ссылок на проблемные статьи"""
    
    # Ищем файл отчета аудита
    report_files = [f for f in os.listdir('.') if f.startswith('audit_featured_images_report_') and f.endswith('.json')]
    
    if not report_files:
        print("❌ Файл отчета аудита не найден")
        return
    
    latest_report = sorted(report_files)[-1]
    
    with open(latest_report, 'r', encoding='utf-8') as f:
        report_data = json.load(f)
    
    detailed_results = report_data.get('detailed_results', [])
    problematic_articles = [article for article in detailed_results if not article['is_match']]
    
    # Создаем словарь для устранения дублирования
    unique_articles = {}
    
    for article in problematic_articles:
        post_id = article['post_id']
        if post_id not in unique_articles:
            unique_articles[post_id] = article
    
    # Группируем по типам проблем
    placeholder_issues = []
    wrong_images = []
    missing_images = []
    
    for article in unique_articles.values():
        reason = article['reason'].lower()
        if 'placeholder' in reason:
            placeholder_issues.append(article)
        elif 'разные изображения' in reason:
            wrong_images.append(article)
        elif 'не найдено' in reason:
            missing_images.append(article)
    
    print("🔗 УНИКАЛЬНЫЕ ССЫЛКИ НА ПРОБЛЕМНЫЕ СТАТЬИ")
    print("=" * 60)
    print(f"📊 Всего уникальных проблемных статей: {len(unique_articles)}")
    print()
    
    # 1. Placeholder изображения (КРИТИЧЕСКИЕ)
    print("🚨 КРИТИЧЕСКИЕ - PLACEHOLDER ИЗОБРАЖЕНИЯ:")
    print("-" * 50)
    for article in sorted(placeholder_issues, key=lambda x: x['post_id']):
        post_id = article['post_id']
        title = article['title']
        url = f"https://ecopackpro.ru/?p={post_id}&preview=true"
        print(f"ID {post_id}: {title}")
        print(f"🔗 {url}")
        print()
    
    # 2. Неправильные изображения
    print("⚠️  НЕПРАВИЛЬНЫЕ ИЗОБРАЖЕНИЯ:")
    print("-" * 50)
    for article in sorted(wrong_images, key=lambda x: x['post_id']):
        post_id = article['post_id']
        title = article['title']
        url = f"https://ecopackpro.ru/?p={post_id}&preview=true"
        print(f"ID {post_id}: {title}")
        print(f"🔗 {url}")
        print()
    
    # 3. Отсутствующие изображения
    if missing_images:
        print("📭 ОТСУТСТВУЮЩИЕ ИЗОБРАЖЕНИЯ:")
        print("-" * 50)
        for article in sorted(missing_images, key=lambda x: x['post_id']):
            post_id = article['post_id']
            title = article['title']
            url = f"https://ecopackpro.ru/?p={post_id}&preview=true"
            print(f"ID {post_id}: {title}")
            print(f"🔗 {url}")
            print()
    
    print("📋 ИТОГО:")
    print(f"🔥 Критических (placeholder): {len(placeholder_issues)}")
    print(f"⚠️  Неправильных изображений: {len(wrong_images)}")
    print(f"📭 Отсутствующих: {len(missing_images)}")
    print(f"🎯 Всего уникальных: {len(unique_articles)}")

if __name__ == "__main__":
    get_unique_problematic_links()
