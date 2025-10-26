#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
ИСПРАВЛЕНИЕ ОСТАВШИХСЯ ИЗОБРАЖЕНИЙ
Обновляет изображения для 15 статей, используя загруженные сегодня файлы
"""

import mysql.connector
import logging
from datetime import datetime
import re

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/fix_remaining_images.log'),
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

# Маппинг статей к изображениям на основе загруженных сегодня файлов
IMAGE_MAPPING = {
    7911: 7187,  # конверты с воздушной подушкой
    7912: 7154,  # конверты с воздушной прослойкой
    7913: 7173,  # крафтовые пакеты с воздушной подушкой
    7926: 7155,  # наклейка пломба антимагнит
    7928: 7183,  # номерные пломбы наклейки
    7929: 7171,  # zip lock пакеты с белой полосой
    7930: 7195,  # белые крафт пакеты с пузырчатой плёнкой
    7937: 7185,  # крафт конверты с воздушной подушкой F/3 (F:3 в файле)
    7941: 7190,  # антимагнитные наклейки для водяных счётчиков
    7943: 7184,  # пломбиратор для евробочек 2 дюйма
    7944: 7197,  # инструмент для опломбирования бочек ¾ дюйма
    7947: 7178,  # курьерские пакеты для маркетплейсов Ozon
    7952: 7165,  # белые крафт-пакеты с пузырчатой плёнкой оптом
    7953: 7194,  # плоские конверты с воздушной подушкой для документов
    7954: 7156,  # пакеты из воздушно-пузырьковой плёнки оптом
}

def get_db_connection():
    """Получить соединение с базой данных"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except mysql.connector.Error as err:
        log.error(f"Ошибка подключения к БД: {err}")
        return None

def save_revision(post_id):
    """Сохранить ревизию статьи перед изменениями"""
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
        
        log.info(f"✅ Ревизия сохранена для статьи {post_id}")
        return True
        
    except Exception as e:
        log.error(f"Ошибка сохранения ревизии для статьи {post_id}: {e}")
        return False

def get_image_info(image_id):
    """Получить информацию об изображении"""
    try:
        conn = get_db_connection()
        if not conn:
            return None
        
        cursor = conn.cursor()
        
        cursor.execute("""
            SELECT post_title, post_excerpt
            FROM wp_posts
            WHERE ID = %s AND post_type = 'attachment'
        """, (image_id,))
        
        result = cursor.fetchone()
        cursor.close()
        conn.close()
        
        if result:
            return {
                'title': result[0],
                'excerpt': result[1]
            }
        return None
        
    except Exception as e:
        log.error(f"Ошибка получения информации об изображении {image_id}: {e}")
        return None

def update_article_image(post_id, image_id, keyword):
    """Обновить изображение статьи"""
    try:
        # Получаем информацию об изображении
        image_info = get_image_info(image_id)
        if not image_info:
            log.error(f"Не удалось получить информацию об изображении {image_id}")
            return False
        
        conn = get_db_connection()
        if not conn:
            return False
        
        cursor = conn.cursor()
        
        # Обновляем featured image
        cursor.execute("""
            SELECT meta_id FROM wp_postmeta
            WHERE post_id = %s AND meta_key = '_thumbnail_id'
        """, (post_id,))
        
        existing = cursor.fetchone()
        
        if existing:
            cursor.execute("""
                UPDATE wp_postmeta
                SET meta_value = %s
                WHERE post_id = %s AND meta_key = '_thumbnail_id'
            """, (image_id, post_id))
        else:
            cursor.execute("""
                INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
                VALUES (%s, '_thumbnail_id', %s)
            """, (post_id, image_id))
        
        # Обновляем alt-текст изображения
        cursor.execute("""
            SELECT meta_id FROM wp_postmeta
            WHERE post_id = %s AND meta_key = '_wp_attachment_image_alt'
        """, (image_id,))
        
        alt_existing = cursor.fetchone()
        
        if alt_existing:
            cursor.execute("""
                UPDATE wp_postmeta
                SET meta_value = %s
                WHERE post_id = %s AND meta_key = '_wp_attachment_image_alt'
            """, (keyword, image_id))
        else:
            cursor.execute("""
                INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
                VALUES (%s, '_wp_attachment_image_alt', %s)
            """, (image_id, keyword))
        
        conn.commit()
        cursor.close()
        conn.close()
        
        log.info(f"✅ Изображение статьи {post_id} обновлено: ID {image_id}")
        log.info(f"   Название: '{image_info['title']}'")
        log.info(f"   Alt: '{keyword}'")
        return True
        
    except Exception as e:
        log.error(f"Ошибка обновления изображения статьи {post_id}: {e}")
        return False

def get_article_keyword(post_id):
    """Получить ключевое слово статьи"""
    try:
        conn = get_db_connection()
        if not conn:
            return None
        
        cursor = conn.cursor()
        
        cursor.execute("""
            SELECT meta_value
            FROM wp_postmeta
            WHERE post_id = %s AND meta_key = '_yoast_wpseo_focuskw'
        """, (post_id,))
        
        result = cursor.fetchone()
        cursor.close()
        conn.close()
        
        if result:
            return result[0]
        return None
        
    except Exception as e:
        log.error(f"Ошибка получения ключевого слова для статьи {post_id}: {e}")
        return None

def main():
    """Основная функция"""
    log.info("🚀 ИСПРАВЛЕНИЕ ОСТАВШИХСЯ ИЗОБРАЖЕНИЙ")
    log.info(f"Время: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    log.info("=" * 60)
    
    stats = {
        'revisions_saved': 0,
        'images_updated': 0,
        'errors': 0
    }
    
    # Обрабатываем каждую статью
    for post_id, image_id in IMAGE_MAPPING.items():
        log.info(f"\n🔧 Обработка статьи ID {post_id} -> изображение ID {image_id}")
        log.info("-" * 50)
        
        try:
            # 1. Сохраняем ревизию
            log.info("📦 Сохранение ревизии...")
            if save_revision(post_id):
                stats['revisions_saved'] += 1
            else:
                log.error(f"Не удалось сохранить ревизию для статьи {post_id}")
                stats['errors'] += 1
                continue
            
            # 2. Получаем ключевое слово статьи
            keyword = get_article_keyword(post_id)
            if not keyword:
                log.warning(f"Не найдено ключевое слово для статьи {post_id}")
                keyword = f"статья_{post_id}"
            
            # 3. Обновляем изображение
            log.info("🖼️ Обновление изображения...")
            if update_article_image(post_id, image_id, keyword):
                stats['images_updated'] += 1
                log.info(f"✅ Статья {post_id} обработана успешно")
            else:
                stats['errors'] += 1
                log.error(f"❌ Ошибка обновления изображения для статьи {post_id}")
            
        except Exception as e:
            log.error(f"❌ Ошибка обработки статьи {post_id}: {e}")
            stats['errors'] += 1
    
    # Итоговая статистика
    log.info("\n" + "=" * 60)
    log.info("📊 ИТОГОВАЯ СТАТИСТИКА")
    log.info("=" * 60)
    log.info(f"📦 Ревизий сохранено: {stats['revisions_saved']}")
    log.info(f"🖼️ Изображений обновлено: {stats['images_updated']}")
    log.info(f"❌ Ошибок: {stats['errors']}")
    log.info("=" * 60)
    
    if stats['errors'] == 0:
        log.info("🎉 ВСЕ ИЗОБРАЖЕНИЯ ОБНОВЛЕНЫ УСПЕШНО!")
    else:
        log.warning(f"⚠️ Завершено с {stats['errors']} ошибками")
    
    log.info(f"Время завершения: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

if __name__ == "__main__":
    main()

