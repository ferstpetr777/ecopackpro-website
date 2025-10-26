#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Генерирует отчет о всех бэкапах на сервере
"""

import os
import glob
from datetime import datetime
import subprocess

SITE_DIR = '/var/www/fastuser/data/www/ecopackpro.ru'

def get_file_info(filepath):
    """Получает информацию о файле"""
    stat = os.stat(filepath)
    size = stat.st_size
    mtime = datetime.fromtimestamp(stat.st_mtime)
    
    # Форматируем размер
    if size < 1024:
        size_str = f"{size} B"
    elif size < 1024**2:
        size_str = f"{size/1024:.1f} KB"
    elif size < 1024**3:
        size_str = f"{size/1024**2:.1f} MB"
    else:
        size_str = f"{size/1024**3:.2f} GB"
    
    return {
        'path': filepath,
        'name': os.path.basename(filepath),
        'size': size,
        'size_str': size_str,
        'mtime': mtime,
        'mtime_str': mtime.strftime('%Y-%m-%d %H:%M:%S')
    }

def find_all_backups():
    """Находит все файлы бэкапов"""
    os.chdir(SITE_DIR)
    
    patterns = [
        'backup_*.sql',
        '*.tar.gz',
        'FULL_BACKUP_*.tar.gz',
        'ecopackpro_full_backup_*.tar.gz'
    ]
    
    all_backups = []
    seen = set()
    
    for pattern in patterns:
        for filepath in glob.glob(pattern):
            full_path = os.path.join(SITE_DIR, filepath)
            if full_path not in seen:
                seen.add(full_path)
                all_backups.append(get_file_info(full_path))
    
    # Также проверяем директории с бэкапами
    for item in os.listdir(SITE_DIR):
        if item.startswith('backup_') and os.path.isdir(os.path.join(SITE_DIR, item)):
            dir_path = os.path.join(SITE_DIR, item)
            all_backups.append({
                'path': dir_path,
                'name': item,
                'size': get_dir_size(dir_path),
                'size_str': format_size(get_dir_size(dir_path)),
                'mtime': datetime.fromtimestamp(os.path.getmtime(dir_path)),
                'mtime_str': datetime.fromtimestamp(os.path.getmtime(dir_path)).strftime('%Y-%m-%d %H:%M:%S')
            })
    
    # Сортируем по дате (новые сверху)
    all_backups.sort(key=lambda x: x['mtime'], reverse=True)
    
    return all_backups

def get_dir_size(path):
    """Получает размер директории"""
    total = 0
    for dirpath, dirnames, filenames in os.walk(path):
        for f in filenames:
            fp = os.path.join(dirpath, f)
            if os.path.exists(fp):
                total += os.path.getsize(fp)
    return total

def format_size(size):
    """Форматирует размер"""
    if size < 1024:
        return f"{size} B"
    elif size < 1024**2:
        return f"{size/1024:.1f} KB"
    elif size < 1024**3:
        return f"{size/1024**2:.1f} MB"
    else:
        return f"{size/1024**3:.2f} GB"

def categorize_backups(backups):
    """Категоризирует бэкапы"""
    categories = {
        'full': [],
        'database': [],
        'files': [],
        'drafts': [],
        'published': [],
        'other': []
    }
    
    for backup in backups:
        name = backup['name'].lower()
        
        if 'full_backup' in name or 'complete' in name:
            categories['full'].append(backup)
        elif 'database' in name or (name.endswith('.sql') and 'draft' not in name and 'publish' not in name):
            categories['database'].append(backup)
        elif 'files' in name or name.endswith('.tar.gz'):
            categories['files'].append(backup)
        elif 'draft' in name:
            categories['drafts'].append(backup)
        elif 'publish' in name:
            categories['published'].append(backup)
        else:
            categories['other'].append(backup)
    
    return categories

def main():
    print("\n" + "="*120)
    print("📊 ОТЧЕТ О ВСЕХ БЭКАПАХ НА СЕРВЕРЕ".center(120))
    print("="*120 + "\n")
    
    print(f"📅 Дата генерации отчета: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    print(f"📁 Директория: {SITE_DIR}\n")
    
    # Находим все бэкапы
    print("🔍 Поиск всех бэкапов...")
    backups = find_all_backups()
    print(f"✅ Найдено бэкапов: {len(backups)}\n")
    
    # Категоризируем
    categories = categorize_backups(backups)
    
    # Выводим по категориям
    print("="*120)
    print("📦 ПОЛНЫЕ БЭКАПЫ (БД + ФАЙЛЫ)".center(120))
    print("="*120)
    
    if categories['full']:
        print(f"\n{'№':<4} {'НАЗВАНИЕ':<60} {'РАЗМЕР':<12} {'ДАТА СОЗДАНИЯ':<20}")
        print("-"*120)
        for idx, backup in enumerate(categories['full'], 1):
            print(f"{idx:<4} {backup['name']:<60} {backup['size_str']:<12} {backup['mtime_str']:<20}")
        print(f"\nВсего полных бэкапов: {len(categories['full'])}")
    else:
        print("\nПолных бэкапов не найдено")
    
    print("\n" + "="*120)
    print("💾 БЭКАПЫ БАЗЫ ДАННЫХ".center(120))
    print("="*120)
    
    if categories['database']:
        print(f"\n{'№':<4} {'НАЗВАНИЕ':<60} {'РАЗМЕР':<12} {'ДАТА СОЗДАНИЯ':<20}")
        print("-"*120)
        for idx, backup in enumerate(categories['database'], 1):
            print(f"{idx:<4} {backup['name']:<60} {backup['size_str']:<12} {backup['mtime_str']:<20}")
        print(f"\nВсего бэкапов БД: {len(categories['database'])}")
    else:
        print("\nБэкапов базы данных не найдено")
    
    print("\n" + "="*120)
    print("📝 БЭКАПЫ ЧЕРНОВИКОВ".center(120))
    print("="*120)
    
    if categories['drafts']:
        print(f"\n{'№':<4} {'НАЗВАНИЕ':<60} {'РАЗМЕР':<12} {'ДАТА СОЗДАНИЯ':<20}")
        print("-"*120)
        for idx, backup in enumerate(categories['drafts'], 1):
            print(f"{idx:<4} {backup['name']:<60} {backup['size_str']:<12} {backup['mtime_str']:<20}")
        print(f"\nВсего бэкапов черновиков: {len(categories['drafts'])}")
    else:
        print("\nБэкапов черновиков не найдено")
    
    print("\n" + "="*120)
    print("📰 БЭКАПЫ ОПУБЛИКОВАННЫХ СТАТЕЙ".center(120))
    print("="*120)
    
    if categories['published']:
        print(f"\n{'№':<4} {'НАЗВАНИЕ':<60} {'РАЗМЕР':<12} {'ДАТА СОЗДАНИЯ':<20}")
        print("-"*120)
        for idx, backup in enumerate(categories['published'], 1):
            print(f"{idx:<4} {backup['name']:<60} {backup['size_str']:<12} {backup['mtime_str']:<20}")
        print(f"\nВсего бэкапов опубликованных: {len(categories['published'])}")
    else:
        print("\nБэкапов опубликованных статей не найдено")
    
    # Общая статистика
    total_size = sum(b['size'] for b in backups)
    
    print("\n" + "="*120)
    print("📊 ИТОГОВАЯ СТАТИСТИКА".center(120))
    print("="*120)
    print(f"📝 Всего бэкапов: {len(backups)}")
    print(f"📦 Полных бэкапов: {len(categories['full'])}")
    print(f"💾 Бэкапов БД: {len(categories['database'])}")
    print(f"📝 Бэкапов черновиков: {len(categories['drafts'])}")
    print(f"📰 Бэкапов опубликованных: {len(categories['published'])}")
    print(f"📊 Общий размер всех бэкапов: {format_size(total_size)}")
    print("="*120)
    
    # Сохраняем отчет
    report_filename = f'{SITE_DIR}/BACKUP_REPORT_{datetime.now().strftime("%Y%m%d_%H%M%S")}.txt'
    
    with open(report_filename, 'w', encoding='utf-8') as f:
        f.write("="*120 + "\n")
        f.write("ОТЧЕТ О ВСЕХ БЭКАПАХ НА СЕРВЕРЕ ECOPACKPRO.RU\n".center(120))
        f.write("="*120 + "\n\n")
        f.write(f"Дата генерации: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
        f.write(f"Директория: {SITE_DIR}\n\n")
        
        f.write("ПОЛНЫЕ БЭКАПЫ:\n")
        f.write("-"*120 + "\n")
        for backup in categories['full']:
            f.write(f"{backup['name']:<60} {backup['size_str']:<12} {backup['mtime_str']}\n")
        
        f.write("\nБЭКАПЫ БАЗЫ ДАННЫХ:\n")
        f.write("-"*120 + "\n")
        for backup in categories['database']:
            f.write(f"{backup['name']:<60} {backup['size_str']:<12} {backup['mtime_str']}\n")
        
        f.write("\n" + "="*120 + "\n")
        f.write(f"Всего бэкапов: {len(backups)}\n")
        f.write(f"Общий размер: {format_size(total_size)}\n")
    
    print(f"\n📄 Отчет сохранен: {report_filename}")
    
    print("\n" + "="*120)
    print("🎉 ОТЧЕТ О БЭКАПАХ СОЗДАН!".center(120))
    print("="*120)

if __name__ == "__main__":
    main()

