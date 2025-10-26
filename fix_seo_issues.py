#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
ИСПРАВЛЕНИЕ SEO ПРОБЛЕМ В СТАТЬЯХ
- Сохранение ревизий перед изменениями
- Исправление заголовков статей
- Исправление изображений и alt-текстов
- Проверка объема текста
"""

import mysql.connector
import sqlite3
import logging
import os
from datetime import datetime
from bs4 import BeautifulSoup
import re

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/fix_seo_issues.log'),
        logging.StreamHandler()
    ]
)
log = logging.getLogger(__name__)

# Конфигурация базы данных WordPress
DB_CONFIG_WP = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6',
    'charset': 'utf8mb4'
}

# Конфигурация базы данных проекта
PROJECT_DB_PATH = "/root/seo_project/SEO_ecopackpro/articles.db"

# Статьи для исправления заголовков (ID -> новый заголовок)
TITLE_FIXES = {
    7907: "курьерские пакеты",
    7908: "почтовые коробки",
    7910: "zip lock пакеты с бегунком",
    7911: "конверты с воздушной подушкой",
    7912: "конверты с воздушной прослойкой",
    7913: "крафтовые пакеты с воздушной подушкой",
}

# Статьи для исправления изображений (ID -> ожидаемое название изображения)
IMAGE_FIXES = {
    7907: "курьерские пакеты",
    7910: "zip lock пакеты с бегунком",
    7914: "курьерские пакеты прозрачные",
    7915: "курьерские пакеты номерные",
    7917: "курьерские пакеты с карманом",
    7918: "zip lock пакеты матовые",
    7924: "самоклеящиеся карманы",
    7925: "антимагнитная пломба",
    7926: "наклейка пломба антимагнит",
    7928: "номерные пломбы наклейки",
    7929: "zip lock пакеты с белой полосой",
    7930: "белые крафт пакеты с пузырчатой плёнкой",
    7932: "купить курьерские пакеты с номерным штрих-кодом",
    7934: "курьерские пакеты черно-белые с карманом цена",
    7937: "крафт конверты с воздушной подушкой F/3",
    7938: "почтовые коробки размера S 260×170×80",
    7939: "почтовые коробки размера XL 530×360×220",
    7941: "антимагнитные наклейки для водяных счётчиков",
    7943: "пломбиратор для евробочек 2 дюйма",
    7944: "инструмент для опломбирования бочек ¾ дюйма",
    7945: "курьерские пакеты черно-белые без логотипа А4",
    7946: "курьерские пакеты прозрачные для одежды",
    7947: "курьерские пакеты для маркетплейсов Ozon",
    7948: "почтовые коробки с логотипом на заказ",
    7949: "зип пакеты с бегунком купить Москва",
    7952: "белые крафт-пакеты с пузырчатой плёнкой оптом",
    7953: "плоские конверты с воздушной подушкой для документов",
    7954: "пакеты из воздушно-пузырьковой плёнки оптом",
    7955: "антимагнитные пломбы для газовых счётчиков",
    7956: "самоклеящиеся карманы для транспортных накладных",
}

def get_db_connection():
    """Получить соединение с БД WordPress"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG_WP)
        return conn
    except mysql.connector.Error as err:
        log.error(f"Ошибка подключения к БД WordPress: {err}")
        return None

def get_project_db_connection():
    """Получить соединение с БД проекта"""
    try:
        conn = sqlite3.connect(PROJECT_DB_PATH)
        return conn
    except Exception as e:
        log.error(f"Ошибка подключения к БД проекта: {e}")
        return None

def create_revision_table():
    """Создать таблицу ревизий, если её нет"""
    try:
        conn = get_db_connection()
        if not conn:
            return False
        
        cursor = conn.cursor()
        
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS wp_article_revisions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                post_id INT NOT NULL,
                post_title VARCHAR(500),
                post_content LONGTEXT,
                featured_image_id BIGINT,
                featured_image_url TEXT,
                meta_description TEXT,
                yoast_focuskw TEXT,
                word_count INT,
                char_count INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_post_id (post_id),
                INDEX idx_created_at (created_at)
            )
        """)
        
        conn.commit()
        cursor.close()
        conn.close()
        
        log.info("✅ Таблица ревизий готова")
        return True
        
    except Exception as e:
        log.error(f"Ошибка создания таблицы ревизий: {e}")
        return False

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
        soup = BeautifulSoup(post_content, 'html.parser')
        text = soup.get_text()
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
        
        log.info(f"✅ Ревизия сохранена для статьи {post_id} (слов: {word_count})")
        return True
        
    except Exception as e:
        log.error(f"Ошибка сохранения ревизии для статьи {post_id}: {e}")
        return False

def fix_article_title(post_id, new_title):
    """Исправить заголовок статьи"""
    try:
        conn = get_db_connection()
        if not conn:
            return False
        
        cursor = conn.cursor()
        
        # Обновляем заголовок
        cursor.execute("""
            UPDATE wp_posts
            SET post_title = %s
            WHERE ID = %s
        """, (new_title, post_id))
        
        conn.commit()
        cursor.close()
        conn.close()
        
        log.info(f"✅ Заголовок статьи {post_id} обновлен: '{new_title}'")
        return True
        
    except Exception as e:
        log.error(f"Ошибка обновления заголовка статьи {post_id}: {e}")
        return False

def find_image_by_keyword(keyword):
    """Найти изображение по ключевому слову"""
    try:
        conn = get_db_connection()
        if not conn:
            return None
        
        cursor = conn.cursor()
        
        # Ищем изображение по названию или alt-тексту
        cursor.execute("""
            SELECT p.ID, p.post_title, pm.meta_value as alt_text
            FROM wp_posts p
            LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt'
            WHERE p.post_type = 'attachment'
            AND p.post_mime_type LIKE 'image/%'
            AND (LOWER(p.post_title) = LOWER(%s) OR LOWER(pm.meta_value) = LOWER(%s))
            ORDER BY p.ID DESC
            LIMIT 1
        """, (keyword, keyword))
        
        result = cursor.fetchone()
        cursor.close()
        conn.close()
        
        if result:
            log.info(f"✅ Найдено изображение ID {result[0]} для ключевого слова '{keyword}'")
            return result[0]  # Возвращаем ID изображения
        else:
            log.warning(f"⚠️ Изображение не найдено для ключевого слова '{keyword}'")
            return None
        
    except Exception as e:
        log.error(f"Ошибка поиска изображения: {e}")
        return None

def fix_article_image(post_id, keyword):
    """Исправить изображение статьи"""
    try:
        # Находим правильное изображение
        image_id = find_image_by_keyword(keyword)
        
        if not image_id:
            log.warning(f"⚠️ Не удалось найти изображение для статьи {post_id}")
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
        
        log.info(f"✅ Изображение статьи {post_id} обновлено: ID {image_id}, alt='{keyword}'")
        return True
        
    except Exception as e:
        log.error(f"Ошибка обновления изображения статьи {post_id}: {e}")
        return False

def get_article_word_count_wordpress(post_id):
    """Получить количество слов в статье WordPress"""
    try:
        conn = get_db_connection()
        if not conn:
            return 0
        
        cursor = conn.cursor()
        
        cursor.execute("""
            SELECT post_content
            FROM wp_posts
            WHERE ID = %s
        """, (post_id,))
        
        result = cursor.fetchone()
        cursor.close()
        conn.close()
        
        if result:
            soup = BeautifulSoup(result[0], 'html.parser')
            text = soup.get_text()
            word_count = len(text.split())
            return word_count
        
        return 0
        
    except Exception as e:
        log.error(f"Ошибка подсчета слов для статьи {post_id}: {e}")
        return 0

def get_article_word_count_project(keyword):
    """Получить количество слов из БД проекта"""
    try:
        conn = get_project_db_connection()
        if not conn:
            return 0
        
        cursor = conn.cursor()
        
        cursor.execute("""
            SELECT word_count
            FROM articles
            WHERE LOWER(keyword) = LOWER(?)
        """, (keyword,))
        
        result = cursor.fetchone()
        cursor.close()
        conn.close()
        
        if result:
            return result[0]
        
        return 0
        
    except Exception as e:
        log.error(f"Ошибка получения количества слов из БД проекта: {e}")
        return 0

def verify_word_counts(post_id, keyword):
    """Проверить соответствие количества слов"""
    wp_count = get_article_word_count_wordpress(post_id)
    project_count = get_article_word_count_project(keyword)
    
    log.info(f"📊 Статья {post_id} ('{keyword}'): WordPress={wp_count} слов, Проект={project_count} слов")
    
    if wp_count >= project_count * 0.95:  # Допускаем 5% погрешность
        log.info(f"✅ Объем текста соответствует требованиям")
        return True
    else:
        log.warning(f"⚠️ ВНИМАНИЕ: Объем текста на сайте меньше ожидаемого!")
        return False

def main():
    """Основная функция исправления"""
    log.info("🚀 ЗАПУСК ИСПРАВЛЕНИЯ SEO ПРОБЛЕМ")
    log.info(f"Время: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    log.info("=" * 80)
    
    # Создаем таблицу ревизий
    if not create_revision_table():
        log.error("Не удалось создать таблицу ревизий. Прерывание.")
        return
    
    # Статистика
    stats = {
        'revisions_saved': 0,
        'titles_fixed': 0,
        'images_fixed': 0,
        'word_count_ok': 0,
        'word_count_warning': 0,
        'errors': 0
    }
    
    # Получаем все статьи, которые нужно исправить
    all_posts_to_fix = set(list(TITLE_FIXES.keys()) + list(IMAGE_FIXES.keys()))
    
    log.info(f"📝 Всего статей для исправления: {len(all_posts_to_fix)}")
    log.info("=" * 80)
    
    # Обрабатываем каждую статью
    for i, post_id in enumerate(sorted(all_posts_to_fix), 1):
        log.info(f"\n🔧 [{i}/{len(all_posts_to_fix)}] Обработка статьи ID {post_id}")
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
            
            # 2. Исправляем заголовок (если нужно)
            if post_id in TITLE_FIXES:
                log.info("📝 Исправление заголовка...")
                new_title = TITLE_FIXES[post_id]
                if fix_article_title(post_id, new_title):
                    stats['titles_fixed'] += 1
                else:
                    stats['errors'] += 1
            
            # 3. Исправляем изображение (если нужно)
            if post_id in IMAGE_FIXES:
                log.info("🖼️  Исправление изображения...")
                keyword = IMAGE_FIXES[post_id]
                if fix_article_image(post_id, keyword):
                    stats['images_fixed'] += 1
                else:
                    stats['errors'] += 1
            
            # 4. Проверяем объем текста
            log.info("📊 Проверка объема текста...")
            keyword = IMAGE_FIXES.get(post_id) or TITLE_FIXES.get(post_id)
            if verify_word_counts(post_id, keyword):
                stats['word_count_ok'] += 1
            else:
                stats['word_count_warning'] += 1
            
            log.info(f"✅ Статья {post_id} обработана успешно")
            
        except Exception as e:
            log.error(f"❌ Ошибка обработки статьи {post_id}: {e}")
            stats['errors'] += 1
    
    # Итоговая статистика
    log.info("\n" + "=" * 80)
    log.info("📊 ИТОГОВАЯ СТАТИСТИКА")
    log.info("=" * 80)
    log.info(f"📦 Ревизий сохранено: {stats['revisions_saved']}")
    log.info(f"📝 Заголовков исправлено: {stats['titles_fixed']}")
    log.info(f"🖼️  Изображений исправлено: {stats['images_fixed']}")
    log.info(f"✅ Объем текста соответствует: {stats['word_count_ok']}")
    log.info(f"⚠️  Объем текста требует внимания: {stats['word_count_warning']}")
    log.info(f"❌ Ошибок: {stats['errors']}")
    log.info("=" * 80)
    
    if stats['errors'] == 0:
        log.info("🎉 ВСЕ ИСПРАВЛЕНИЯ ВЫПОЛНЕНЫ УСПЕШНО!")
    else:
        log.warning(f"⚠️ Завершено с {stats['errors']} ошибками")
    
    log.info(f"Время завершения: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

if __name__ == "__main__":
    main()

