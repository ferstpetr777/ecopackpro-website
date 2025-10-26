#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для добавления SEO атрибутов в WordPress статьи
Прямая работа с базой данных WordPress (как в AI-Scribe плагине)
"""

import mysql.connector
import sqlite3
import re
import logging
import os
from datetime import datetime

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/seo_update_v2.log'),
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

# Конфигурация базы данных проекта
PROJECT_DB_PATH = "/root/seo_project/SEO_ecopackpro/articles.db"

def get_db_connection():
    """Получить соединение с базой данных WordPress"""
    try:
        conn = mysql.connector.connect(**DB_CONFIG)
        return conn
    except mysql.connector.Error as err:
        log.error(f"Ошибка подключения к БД WordPress: {err}")
        return None

def get_articles_from_project_db():
    """Получить статьи из базы данных проекта"""
    try:
        conn = sqlite3.connect(PROJECT_DB_PATH)
        cursor = conn.cursor()
        
        # Получаем все статьи с SEO данными
        cursor.execute("""
            SELECT keyword, meta_description 
            FROM articles 
            WHERE keyword IS NOT NULL AND meta_description IS NOT NULL
            ORDER BY id
        """)
        
        articles = cursor.fetchall()
        cursor.close()
        conn.close()
        
        log.info(f"Загружено {len(articles)} статей из базы данных проекта")
        return articles
        
    except Exception as e:
        log.error(f"Ошибка загрузки из БД проекта: {e}")
        return []

def get_wordpress_posts():
    """Получить посты из WordPress"""
    try:
        conn = get_db_connection()
        if not conn:
            return []
        
        cursor = conn.cursor()
        
        # Получаем посты в статусе draft с ID в диапазоне 7907-7956
        cursor.execute("""
            SELECT ID, post_title, post_name, post_content
            FROM wp_posts 
            WHERE post_type = 'post' 
            AND post_status = 'draft'
            AND ID BETWEEN 7907 AND 7956
            ORDER BY ID ASC
        """)
        
        posts = cursor.fetchall()
        cursor.close()
        conn.close()
        
        log.info(f"Загружено {len(posts)} постов из WordPress")
        return posts
        
    except Exception as e:
        log.error(f"Ошибка загрузки постов из WordPress: {e}")
        return []

def generate_seo_slug(keyword):
    """Генерировать SEO-friendly slug из ключевого слова"""
    # Транслитерация кириллицы в латиницу
    translit_map = {
        'а': 'a', 'б': 'b', 'в': 'v', 'г': 'g', 'д': 'd', 'е': 'e', 'ё': 'yo',
        'ж': 'zh', 'з': 'z', 'и': 'i', 'й': 'y', 'к': 'k', 'л': 'l', 'м': 'm',
        'н': 'n', 'о': 'o', 'п': 'p', 'р': 'r', 'с': 's', 'т': 't', 'у': 'u',
        'ф': 'f', 'х': 'h', 'ц': 'ts', 'ч': 'ch', 'ш': 'sh', 'щ': 'sch',
        'ъ': '', 'ы': 'y', 'ь': '', 'э': 'e', 'ю': 'yu', 'я': 'ya',
        'А': 'A', 'Б': 'B', 'В': 'V', 'Г': 'G', 'Д': 'D', 'Е': 'E', 'Ё': 'Yo',
        'Ж': 'Zh', 'З': 'Z', 'И': 'I', 'Й': 'Y', 'К': 'K', 'Л': 'L', 'М': 'M',
        'Н': 'N', 'О': 'O', 'П': 'P', 'Р': 'R', 'С': 'S', 'Т': 'T', 'У': 'U',
        'Ф': 'F', 'Х': 'H', 'Ц': 'Ts', 'Ч': 'Ch', 'Ш': 'Sh', 'Щ': 'Sch',
        'Ъ': '', 'Ы': 'Y', 'Ь': '', 'Э': 'E', 'Ю': 'Yu', 'Я': 'Ya'
    }
    
    # Транслитерация
    slug = ""
    for char in keyword:
        if char in translit_map:
            slug += translit_map[char]
        elif char.isalnum():
            slug += char
        else:
            slug += "-"
    
    # Очистка и форматирование
    slug = re.sub(r'-+', '-', slug)  # Заменяем множественные дефисы на один
    slug = slug.strip('-')  # Убираем дефисы в начале и конце
    slug = slug.lower()  # Переводим в нижний регистр
    
    return slug

def update_post_seo_meta(post_id, seo_title, meta_description, focus_keyword, slug):
    """Обновить SEO мета-данные поста напрямую в базе данных"""
    try:
        conn = get_db_connection()
        if not conn:
            return False
        
        cursor = conn.cursor()
        
        # 1. Обновляем slug поста в таблице wp_posts
        cursor.execute("""
            UPDATE wp_posts 
            SET post_name = %s 
            WHERE ID = %s
        """, (slug, post_id))
        
        # 2. Обновляем SEO мета-данные в таблице wp_postmeta
        # Используем тот же подход, что и в AI-Scribe плагине
        
        seo_meta_data = [
            ('_yoast_wpseo_title', seo_title),
            ('_yoast_wpseo_metadesc', meta_description),
            ('_yoast_wpseo_focuskw', focus_keyword),
            ('_yoast_wpseo_focuskw_text_input', focus_keyword),
            ('_yoast_wpseo_content_score', '90'),
            ('_yoast_wpseo_estimated_reading_time_minutes', '5')
        ]
        
        success_count = 0
        
        for meta_key, meta_value in seo_meta_data:
            try:
                # Проверяем, существует ли уже это мета-поле
                cursor.execute("""
                    SELECT meta_id FROM wp_postmeta 
                    WHERE post_id = %s AND meta_key = %s
                """, (post_id, meta_key))
                
                existing_meta = cursor.fetchone()
                
                if existing_meta:
                    # Обновляем существующее поле
                    cursor.execute("""
                        UPDATE wp_postmeta 
                        SET meta_value = %s 
                        WHERE post_id = %s AND meta_key = %s
                    """, (meta_value, post_id, meta_key))
                    log.info(f"✅ Обновлено {meta_key} для поста {post_id}")
                else:
                    # Создаем новое поле
                    cursor.execute("""
                        INSERT INTO wp_postmeta (post_id, meta_key, meta_value) 
                        VALUES (%s, %s, %s)
                    """, (post_id, meta_key, meta_value))
                    log.info(f"✅ Создано {meta_key} для поста {post_id}")
                
                success_count += 1
                
            except Exception as e:
                log.error(f"Ошибка обновления {meta_key}: {e}")
        
        # Подтверждаем изменения
        conn.commit()
        cursor.close()
        conn.close()
        
        log.info(f"Обновлено {success_count}/{len(seo_meta_data)} мета-полей для поста {post_id}")
        return success_count > 0
        
    except Exception as e:
        log.error(f"Ошибка обновления SEO для поста {post_id}: {e}")
        return False

def test_single_article():
    """Тестировать на одной статье (ID 7956)"""
    log.info("🧪 ТЕСТИРОВАНИЕ НА ОДНОЙ СТАТЬЕ")
    
    # Получаем данные из базы проекта
    articles = get_articles_from_project_db()
    if not articles:
        log.error("Не удалось загрузить статьи из базы данных проекта")
        return False
    
    # Берем последнюю статью (самоклеящиеся карманы)
    keyword, meta_description = articles[-1]
    
    log.info(f"Ключевое слово: {keyword}")
    log.info(f"Мета описание: {meta_description}")
    
    # Генерируем SEO данные
    seo_title = keyword  # SEO заголовок = ключевое слово
    focus_keyword = keyword  # Фокусное ключевое слово = ключевое слово
    slug = generate_seo_slug(keyword)  # Генерируем slug
    
    log.info(f"SEO заголовок: {seo_title}")
    log.info(f"Slug: {slug}")
    
    # Получаем посты из WordPress
    posts = get_wordpress_posts()
    if not posts:
        log.error("Не удалось загрузить посты из WordPress")
        return False
    
    # Ищем пост с ID 7956
    target_post = None
    for post in posts:
        if post[0] == 7956:  # ID в первой колонке
            target_post = post
            break
    
    if not target_post:
        log.error("Пост с ID 7956 не найден")
        return False
    
    post_id, post_title, post_name, post_content = target_post
    log.info(f"Найден пост: {post_title}")
    log.info(f"Текущий slug: {post_name}")
    
    # Обновляем SEO данные
    success = update_post_seo_meta(
        post_id=post_id,
        seo_title=seo_title,
        meta_description=meta_description,
        focus_keyword=focus_keyword,
        slug=slug
    )
    
    if success:
        log.info("✅ SEO данные успешно обновлены!")
        log.info("🔍 Проверьте в админ-панели WordPress:")
        log.info("   - Фокусное ключевое слово должно быть заполнено")
        log.info("   - SEO заголовок должен быть заполнен")
        log.info("   - Мета описание должно быть заполнено")
        log.info("   - Ярлык (slug) должен быть обновлен")
        log.info(f"   - URL статьи: https://ecopackpro.ru/{slug}/")
        return True
    else:
        log.error("❌ Ошибка обновления SEO данных")
        return False

def main():
    """Основная функция"""
    log.info("🚀 ЗАПУСК СКРИПТА ДОБАВЛЕНИЯ SEO АТРИБУТОВ v2.0")
    log.info("Прямая работа с базой данных WordPress")
    log.info("=" * 60)
    
    try:
        # Тестируем на одной статье
        success = test_single_article()
        
        if success:
            log.info("✅ ТЕСТ УСПЕШЕН!")
            log.info("Теперь можно применить ко всем 50 статьям")
            log.info("Скрипт готов к массовому обновлению")
        else:
            log.error("❌ ТЕСТ НЕ ПРОШЕЛ")
            
    except Exception as e:
        log.error(f"Критическая ошибка: {e}")

if __name__ == "__main__":
    main()

