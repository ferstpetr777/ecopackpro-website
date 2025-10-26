#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Генерирует полную таблицу со всеми данными по 50 статьям
"""

import sqlite3
from datetime import datetime

# Путь к базе данных проекта
PROJECT_DB_PATH = '/root/seo_project/SEO_ecopackpro/articles.db'

def generate_complete_table():
    """Генерирует полную таблицу со всеми данными"""
    print("\n" + "="*150)
    print("📊 ПОЛНАЯ ТАБЛИЦА: ВСЕ 50 ОПУБЛИКОВАННЫХ СТАТЕЙ".center(150))
    print("="*150 + "\n")
    
    conn = sqlite3.connect(PROJECT_DB_PATH)
    cursor = conn.cursor()
    
    query = """
    SELECT 
        pa.wp_post_id,
        pa.source_article_id,
        pa.title,
        pa.url,
        a.keyword as source_keyword
    FROM published_articles pa
    LEFT JOIN articles a ON pa.source_article_id = a.id
    WHERE pa.wp_post_id >= 7907
    ORDER BY pa.wp_post_id
    """
    
    cursor.execute(query)
    results = cursor.fetchall()
    
    # Выводим таблицу в консоль
    print(f"{'№':<5} {'ID ИСХОДНИКА':<15} {'ID WORDPRESS':<15} {'НАЗВАНИЕ СТАТЬИ':<70} {'ССЫЛКА':<60}")
    print("="*150)
    
    table_data = []
    
    for idx, (wp_id, source_id, title, url, source_keyword) in enumerate(results, 1):
        source_display = f"{source_id}" if source_id else "N/A"
        display_title = title[:67] + "..." if len(title) > 70 else title
        display_url = url
        
        print(f"{idx:<5} {source_display:<15} {wp_id:<15} {display_title:<70} {display_url:<60}")
        
        table_data.append({
            'number': idx,
            'source_id': source_id,
            'wp_id': wp_id,
            'title': title,
            'url': url,
            'source_keyword': source_keyword
        })
    
    print("="*150)
    
    conn.close()
    
    return table_data

def save_to_markdown(table_data):
    """Сохраняет таблицу в Markdown формате"""
    filename = f'/root/seo_project/SEO_ecopackpro/COMPLETE_TABLE_50_ARTICLES_{datetime.now().strftime("%Y%m%d_%H%M%S")}.md'
    
    with open(filename, 'w', encoding='utf-8') as f:
        f.write("# 📊 ПОЛНАЯ ТАБЛИЦА: ВСЕ 50 ОПУБЛИКОВАННЫХ СТАТЕЙ\n\n")
        f.write(f"## 📅 Дата создания: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n\n")
        f.write("---\n\n")
        
        f.write("| № | ID ИСХОДНИКА | ID WORDPRESS | НАЗВАНИЕ СТАТЬИ | ССЫЛКА |\n")
        f.write("|---|--------------|--------------|-----------------|--------|\n")
        
        for row in table_data:
            num = row['number']
            source_id = row['source_id'] if row['source_id'] else "N/A"
            wp_id = row['wp_id']
            title = row['title']
            url = row['url']
            
            f.write(f"| {num} | {source_id} | {wp_id} | {title} | [🔗]({url}) |\n")
        
        f.write("\n---\n\n")
        f.write("## ✅ ИТОГОВАЯ СТАТИСТИКА:\n\n")
        f.write(f"- **Всего статей:** {len(table_data)}\n")
        f.write(f"- **Связано с исходниками:** {sum(1 for r in table_data if r['source_id'])}\n")
        f.write(f"- **Без связи:** {sum(1 for r in table_data if not r['source_id'])}\n\n")
        
        f.write("---\n\n")
        f.write("**Дата создания отчета:** " + datetime.now().strftime('%Y-%m-%d %H:%M:%S') + "  \n")
        f.write("**Проект:** EcoPackPro.ru - Complete Articles Table  \n")
        f.write("**Статус:** ✅ **ЗАВЕРШЕНО**\n")
    
    print(f"\n📄 Таблица сохранена в Markdown: {filename}")
    return filename

def save_to_csv(table_data):
    """Сохраняет таблицу в CSV формате"""
    import csv
    
    filename = f'/root/seo_project/SEO_ecopackpro/COMPLETE_TABLE_50_ARTICLES_{datetime.now().strftime("%Y%m%d_%H%M%S")}.csv'
    
    with open(filename, 'w', encoding='utf-8-sig', newline='') as f:
        writer = csv.writer(f, delimiter=';')
        
        # Заголовки
        writer.writerow(['№', 'ID ИСХОДНИКА', 'ID WORDPRESS', 'НАЗВАНИЕ СТАТЬИ', 'ССЫЛКА', 'КЛЮЧЕВОЕ СЛОВО ИСХОДНИКА'])
        
        # Данные
        for row in table_data:
            writer.writerow([
                row['number'],
                row['source_id'] if row['source_id'] else 'N/A',
                row['wp_id'],
                row['title'],
                row['url'],
                row['source_keyword'] if row['source_keyword'] else 'N/A'
            ])
    
    print(f"📄 Таблица сохранена в CSV: {filename}")
    return filename

def save_to_excel(table_data):
    """Сохраняет таблицу в формат, пригодный для Excel"""
    filename = f'/root/seo_project/SEO_ecopackpro/COMPLETE_TABLE_50_ARTICLES_{datetime.now().strftime("%Y%m%d_%H%M%S")}.txt'
    
    with open(filename, 'w', encoding='utf-8') as f:
        f.write("№\tID ИСХОДНИКА\tID WORDPRESS\tНАЗВАНИЕ СТАТЬИ\tССЫЛКА\tКЛЮЧЕВОЕ СЛОВО ИСХОДНИКА\n")
        
        for row in table_data:
            f.write(f"{row['number']}\t")
            f.write(f"{row['source_id'] if row['source_id'] else 'N/A'}\t")
            f.write(f"{row['wp_id']}\t")
            f.write(f"{row['title']}\t")
            f.write(f"{row['url']}\t")
            f.write(f"{row['source_keyword'] if row['source_keyword'] else 'N/A'}\n")
    
    print(f"📄 Таблица сохранена в TXT (для Excel): {filename}")
    return filename

def save_to_json(table_data):
    """Сохраняет таблицу в JSON формате"""
    import json
    
    filename = f'/root/seo_project/SEO_ecopackpro/COMPLETE_TABLE_50_ARTICLES_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
    
    with open(filename, 'w', encoding='utf-8') as f:
        json.dump(table_data, f, ensure_ascii=False, indent=4)
    
    print(f"📄 Таблица сохранена в JSON: {filename}")
    return filename

def main():
    # Генерируем таблицу
    table_data = generate_complete_table()
    
    print("\n" + "="*150)
    print("💾 СОХРАНЕНИЕ ОТЧЕТОВ В РАЗНЫХ ФОРМАТАХ".center(150))
    print("="*150 + "\n")
    
    # Сохраняем в разных форматах
    md_file = save_to_markdown(table_data)
    csv_file = save_to_csv(table_data)
    txt_file = save_to_excel(table_data)
    json_file = save_to_json(table_data)
    
    print("\n" + "="*150)
    print("📊 ИТОГОВАЯ СТАТИСТИКА".center(150))
    print("="*150)
    print(f"✅ Всего статей в таблице: {len(table_data)}")
    print(f"✅ Связано с исходниками: {sum(1 for r in table_data if r['source_id'])}")
    print(f"✅ Сохранено файлов: 4 (MD, CSV, TXT, JSON)")
    print("="*150)
    
    print("\n" + "="*150)
    print("🎉 ВСЕ ОТЧЕТЫ УСПЕШНО СОЗДАНЫ И СОХРАНЕНЫ!".center(150))
    print("="*150)

if __name__ == "__main__":
    main()

