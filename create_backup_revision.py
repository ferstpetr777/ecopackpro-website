#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
СОЗДАНИЕ РЕЗЕРВНОЙ РЕВИЗИИ ВСЕХ СТАТЕЙ
Создает backup ревизию всех 50 статей перед внесением SEO улучшений
"""

import mysql.connector
import logging
from datetime import datetime

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/backup_revision.log'),
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

# ID всех 50 статей
ALL_ARTICLE_IDS = [
    7907, 7908, 7909, 7910, 7911, 7912, 7913, 7914, 7915, 7916,
    7917, 7918, 7919, 7920, 7921, 7922, 7923, 7924, 7925, 7926,
    7927, 7928, 7929, 7930, 7931, 7932, 7933, 7934, 7935, 7936,
    7937, 7938, 7939, 7940, 7941, 7942, 7943, 7944, 7945, 7946,
    7947, 7948, 7949, 7950, 7951, 7952, 7953, 7954, 7955, 7956
]

def get_db_connection():
    """Получить соединение с базой данных"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except mysql.connector.Error as err:
        log.error(f"Ошибка подключения к БД: {err}")
        return None

def save_revision(post_id):
    """Сохранить ревизию статьи"""
    try:
        conn = get_db_connection()
        if not conn:
            return False
        
        cursor = conn.cursor()
        
        # Получаем текущие данные статьи
        cursor.execute("""
            SELECT post_title, post_content
            FROM wp_posts
            WHERE ID = %s
        """, (post_id,))
        
        result = cursor.fetchone()
        if not result:
            log.warning(f"Статья {post_id} не найдена")
            return False
        
        post_title, post_content = result
        
        # Получаем featured image
        cursor.execute("""
            SELECT meta_value
            FROM wp_postmeta
            WHERE post_id = %s AND meta_key = '_thumbnail_id'
        """, (post_id,))
        
        featured_image_result = cursor.fetchone()
        featured_image_id = featured_image_result[0] if featured_image_result else None
        
        # Получаем URL изображения
        featured_image_url = ""
        if featured_image_id:
            cursor.execute("""
                SELECT guid
                FROM wp_posts
                WHERE ID = %s AND post_type = 'attachment'
            """, (featured_image_id,))
            
            url_result = cursor.fetchone()
            if url_result:
                featured_image_url = url_result[0]
        
        # Получаем meta description
        cursor.execute("""
            SELECT meta_value
            FROM wp_postmeta
            WHERE post_id = %s AND meta_key = '_yoast_wpseo_metadesc'
        """, (post_id,))
        
        meta_desc_result = cursor.fetchone()
        meta_description = meta_desc_result[0] if meta_desc_result else ""
        
        # Получаем focus keyword
        cursor.execute("""
            SELECT meta_value
            FROM wp_postmeta
            WHERE post_id = %s AND meta_key = '_yoast_wpseo_focuskw'
        """, (post_id,))
        
        focuskw_result = cursor.fetchone()
        yoast_focuskw = focuskw_result[0] if focuskw_result else ""
        
        # Подсчитываем слова и символы
        text = post_content.replace('<', ' ').replace('>', ' ')
        word_count = len(text.split())
        char_count = len(text)
        
        # Сохраняем ревизию
        cursor.execute("""
            INSERT INTO wp_article_revisions 
            (post_id, post_title, post_content, featured_image_id, featured_image_url,
             meta_description, yoast_focuskw, word_count, char_count)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
        """, (post_id, post_title, post_content, featured_image_id, featured_image_url,
              meta_description, yoast_focuskw, word_count, char_count))
        
        conn.commit()
        cursor.close()
        conn.close()
        
        log.info(f"✅ Резервная ревизия сохранена для статьи {post_id} (слов: {word_count})")
        return True
        
    except Exception as e:
        log.error(f"Ошибка сохранения резервной ревизии для статьи {post_id}: {e}")
        return False

def main():
    """Основная функция"""
    log.info("🚀 СОЗДАНИЕ РЕЗЕРВНОЙ РЕВИЗИИ ВСЕХ СТАТЕЙ")
    log.info(f"Время: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    log.info("=" * 60)
    
    stats = {
        'revisions_saved': 0,
        'errors': 0
    }
    
    # Обрабатываем каждую статью
    for i, post_id in enumerate(ALL_ARTICLE_IDS, 1):
        log.info(f"\n📦 [{i}/{len(ALL_ARTICLE_IDS)}] Создание резервной ревизии для статьи ID {post_id}")
        
        try:
            if save_revision(post_id):
                stats['revisions_saved'] += 1
            else:
                stats['errors'] += 1
                log.error(f"❌ Не удалось создать резервную ревизию для статьи {post_id}")
            
        except Exception as e:
            log.error(f"❌ Ошибка обработки статьи {post_id}: {e}")
            stats['errors'] += 1
    
    # Итоговая статистика
    log.info("\n" + "=" * 60)
    log.info("📊 ИТОГОВАЯ СТАТИСТИКА")
    log.info("=" * 60)
    log.info(f"📦 Резервных ревизий создано: {stats['revisions_saved']}")
    log.info(f"❌ Ошибок: {stats['errors']}")
    log.info("=" * 60)
    
    if stats['errors'] == 0:
        log.info("🎉 ВСЕ РЕЗЕРВНЫЕ РЕВИЗИИ СОЗДАНЫ УСПЕШНО!")
    else:
        log.warning(f"⚠️ Завершено с {stats['errors']} ошибками")
    
    log.info(f"Время завершения: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

if __name__ == "__main__":
    main()

