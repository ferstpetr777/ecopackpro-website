#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
ПРОВЕРКА МЕДИАФАЙЛОВ В WORDPRESS
Проверяет наличие нужных изображений в медиатеке
"""

import mysql.connector
import logging
from datetime import datetime

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/check_media.log'),
        logging.StreamHandler()
    ]
)
log = logging.getLogger(__name__)

# Конфигурация базы данных WordPress
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6',
    'charset': 'utf8mb4'
}

# Ключевые слова для поиска изображений
KEYWORDS_TO_FIND = [
    "конверты с воздушной подушкой",
    "конверты с воздушной прослойкой", 
    "крафтовые пакеты с воздушной подушкой",
    "наклейка пломба антимагнит",
    "номерные пломбы наклейки",
    "zip lock пакеты с белой полосой",
    "белые крафт пакеты с пузырчатой плёнкой",
    "крафт конверты с воздушной подушкой F/3",
    "антимагнитные наклейки для водяных счётчиков",
    "пломбиратор для евробочек 2 дюйма",
    "инструмент для опломбирования бочек ¾ дюйма",
    "курьерские пакеты для маркетплейсов Ozon",
    "белые крафт-пакеты с пузырчатой плёнкой оптом",
    "плоские конверты с воздушной подушкой для документов",
    "пакеты из воздушно-пузырьковой плёнки оптом"
]

def get_db_connection():
    """Получить соединение с базой данных"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except mysql.connector.Error as err:
        log.error(f"Ошибка подключения к БД: {err}")
        return None

def get_all_media_files():
    """Получить все медиафайлы из базы данных"""
    try:
        conn = get_db_connection()
        if not conn:
            return []
        
        cursor = conn.cursor()
        
        # Получаем все изображения
        cursor.execute("""
            SELECT 
                p.ID,
                p.post_title,
                p.post_date,
                pm.meta_value as alt_text,
                p.post_mime_type
            FROM wp_posts p
            LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt'
            WHERE p.post_type = 'attachment'
            AND p.post_mime_type LIKE 'image/%'
            ORDER BY p.post_date DESC
        """)
        
        media_files = cursor.fetchall()
        cursor.close()
        conn.close()
        
        log.info(f"Найдено {len(media_files)} медиафайлов в базе данных")
        return media_files
        
    except Exception as e:
        log.error(f"Ошибка получения медиафайлов: {e}")
        return []

def find_matching_images(keyword, media_files):
    """Найти изображения, соответствующие ключевому слову"""
    matches = []
    keyword_lower = keyword.lower().strip()
    
    for media_file in media_files:
        media_id, title, date, alt_text, mime_type = media_file
        
        # Проверяем название файла и alt-текст
        title_lower = (title or "").lower().strip()
        alt_lower = (alt_text or "").lower().strip()
        
        # Точное совпадение
        if title_lower == keyword_lower or alt_lower == keyword_lower:
            matches.append({
                'id': media_id,
                'title': title,
                'alt': alt_text,
                'date': date,
                'match_type': 'exact'
            })
            continue
        
        # Частичное совпадение (содержит ключевое слово)
        if keyword_lower in title_lower or keyword_lower in alt_lower:
            matches.append({
                'id': media_id,
                'title': title,
                'alt': alt_text,
                'date': date,
                'match_type': 'partial'
            })
    
    return matches

def check_keywords_in_media():
    """Проверить наличие ключевых слов в медиафайлах"""
    log.info("🔍 ПРОВЕРКА МЕДИАФАЙЛОВ")
    log.info("=" * 60)
    
    # Получаем все медиафайлы
    media_files = get_all_media_files()
    
    if not media_files:
        log.error("Не удалось получить медиафайлы")
        return
    
    # Проверяем каждое ключевое слово
    found_keywords = []
    missing_keywords = []
    
    for keyword in KEYWORDS_TO_FIND:
        log.info(f"\n🔍 Поиск: '{keyword}'")
        
        matches = find_matching_images(keyword, media_files)
        
        if matches:
            log.info(f"✅ Найдено {len(matches)} совпадений:")
            
            for match in matches:
                log.info(f"   ID: {match['id']}, Тип: {match['match_type']}")
                log.info(f"   Название: '{match['title']}'")
                log.info(f"   Alt: '{match['alt']}'")
                log.info(f"   Дата: {match['date']}")
            
            found_keywords.append({
                'keyword': keyword,
                'matches': matches
            })
        else:
            log.warning(f"❌ Не найдено изображений для: '{keyword}'")
            missing_keywords.append(keyword)
    
    # Итоговый отчет
    log.info("\n" + "=" * 60)
    log.info("📊 ИТОГОВЫЙ ОТЧЕТ")
    log.info("=" * 60)
    log.info(f"✅ Найдено изображений: {len(found_keywords)}")
    log.info(f"❌ Не найдено изображений: {len(missing_keywords)}")
    
    if missing_keywords:
        log.info("\n🚨 КЛЮЧЕВЫЕ СЛОВА БЕЗ ИЗОБРАЖЕНИЙ:")
        for keyword in missing_keywords:
            log.info(f"   - {keyword}")
    
    return {
        'found': found_keywords,
        'missing': missing_keywords,
        'total_media': len(media_files)
    }

def show_recent_uploads():
    """Показать недавно загруженные файлы"""
    try:
        conn = get_db_connection()
        if not conn:
            return
        
        cursor = conn.cursor()
        
        # Получаем файлы, загруженные сегодня
        cursor.execute("""
            SELECT 
                p.ID,
                p.post_title,
                p.post_date,
                pm.meta_value as alt_text
            FROM wp_posts p
            LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt'
            WHERE p.post_type = 'attachment'
            AND p.post_mime_type LIKE 'image/%'
            AND DATE(p.post_date) = CURDATE()
            ORDER BY p.post_date DESC
        """)
        
        recent_files = cursor.fetchall()
        cursor.close()
        conn.close()
        
        log.info(f"\n📅 ФАЙЛЫ, ЗАГРУЖЕННЫЕ СЕГОДНЯ: {len(recent_files)}")
        log.info("-" * 50)
        
        for file_data in recent_files:
            media_id, title, date, alt_text = file_data
            log.info(f"ID: {media_id} | '{title}' | Alt: '{alt_text}' | {date}")
        
    except Exception as e:
        log.error(f"Ошибка получения недавних файлов: {e}")

def main():
    """Основная функция"""
    log.info("🚀 ПРОВЕРКА МЕДИАФАЙЛОВ В WORDPRESS")
    log.info(f"Время: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    
    # Показываем недавно загруженные файлы
    show_recent_uploads()
    
    # Проверяем наличие нужных изображений
    result = check_keywords_in_media()
    
    if result and result['missing']:
        log.info("\n💡 РЕКОМЕНДАЦИЯ:")
        log.info("Необходимо загрузить недостающие изображения из папки на MacBook")
        log.info("Или проверить названия изображений в медиатеке")

if __name__ == "__main__":
    main()

