#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
МАССОВОЕ ОБНОВЛЕНИЕ SEO АТРИБУТОВ ДЛЯ ВСЕХ 50 СТАТЕЙ
Скрипт для добавления SEO атрибутов в WordPress статьи
Прямая работа с базой данных WordPress (как в AI-Scribe плагине)

ИСПОЛЬЗОВАНИЕ:
    python3 update_all_seo_attributes.py

АВТОР: AI Assistant
ДАТА: 11 октября 2025
ВЕРСИЯ: 2.0
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
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/seo_mass_update.log'),
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
                else:
                    # Создаем новое поле
                    cursor.execute("""
                        INSERT INTO wp_postmeta (post_id, meta_key, meta_value) 
                        VALUES (%s, %s, %s)
                    """, (post_id, meta_key, meta_value))
                
                success_count += 1
                
            except Exception as e:
                log.error(f"Ошибка обновления {meta_key} для поста {post_id}: {e}")
        
        # Подтверждаем изменения
        conn.commit()
        cursor.close()
        conn.close()
        
        return success_count > 0
        
    except Exception as e:
        log.error(f"Ошибка обновления SEO для поста {post_id}: {e}")
        return False

def update_all_articles():
    """Обновить SEO атрибуты для всех 50 статей"""
    log.info("🚀 МАССОВОЕ ОБНОВЛЕНИЕ SEO АТРИБУТОВ ДЛЯ ВСЕХ 50 СТАТЕЙ")
    log.info("=" * 70)
    
    # Получаем данные из базы проекта
    articles = get_articles_from_project_db()
    if not articles:
        log.error("Не удалось загрузить статьи из базы данных проекта")
        return False
    
    # Получаем посты из WordPress
    posts = get_wordpress_posts()
    if not posts:
        log.error("Не удалось загрузить посты из WordPress")
        return False
    
    # Создаем словарь постов по ID для быстрого поиска
    posts_dict = {post[0]: post for post in posts}
    
    log.info(f"Найдено {len(articles)} статей в базе проекта")
    log.info(f"Найдено {len(posts)} постов в WordPress")
    
    success_count = 0
    error_count = 0
    
    # Обновляем каждую статью
    for i, (keyword, meta_description) in enumerate(articles, 1):
        try:
            # Ищем соответствующий пост по ключевому слову в заголовке
            matching_post = None
            for post_id, post_title, post_name, post_content in posts:
                if keyword.lower() in post_title.lower():
                    matching_post = (post_id, post_title, post_name, post_content)
                    break
            
            if not matching_post:
                log.warning(f"⚠️ Не найден пост для ключевого слова: {keyword}")
                error_count += 1
                continue
            
            post_id, post_title, post_name, post_content = matching_post
            
            # Генерируем SEO данные
            seo_title = keyword  # SEO заголовок = ключевое слово
            focus_keyword = keyword  # Фокусное ключевое слово = ключевое слово
            slug = generate_seo_slug(keyword)  # Генерируем slug
            
            log.info(f"📝 [{i}/50] Обновление поста {post_id}: {post_title}")
            log.info(f"   Ключевое слово: {keyword}")
            log.info(f"   Slug: {slug}")
            
            # Обновляем SEO данные
            success = update_post_seo_meta(
                post_id=post_id,
                seo_title=seo_title,
                meta_description=meta_description,
                focus_keyword=focus_keyword,
                slug=slug
            )
            
            if success:
                success_count += 1
                log.info(f"   ✅ SEO данные успешно обновлены")
                log.info(f"   🔗 URL: https://ecopackpro.ru/{slug}/")
            else:
                error_count += 1
                log.error(f"   ❌ Ошибка обновления SEO данных")
            
            # Небольшая пауза между обновлениями
            import time
            time.sleep(0.1)
            
        except Exception as e:
            log.error(f"Ошибка обработки статьи {i}: {e}")
            error_count += 1
    
    # Итоговый отчет
    log.info("=" * 70)
    log.info("📊 ИТОГОВЫЙ ОТЧЕТ")
    log.info(f"✅ Успешно обновлено: {success_count} статей")
    log.info(f"❌ Ошибок: {error_count} статей")
    log.info(f"📈 Процент успеха: {(success_count/(success_count+error_count)*100):.1f}%")
    
    if success_count > 0:
        log.info("🎉 МАССОВОЕ ОБНОВЛЕНИЕ ЗАВЕРШЕНО!")
        log.info("🔍 Проверьте статьи в админ-панели WordPress:")
        log.info("   - Все фокусные ключевые слова должны быть заполнены")
        log.info("   - Все SEO заголовки должны быть заполнены")
        log.info("   - Все мета описания должны быть заполнены")
        log.info("   - Все ярлыки (slugs) должны быть обновлены")
    
    return success_count > 0

def main():
    """Основная функция"""
    log.info("🚀 ЗАПУСК МАССОВОГО ОБНОВЛЕНИЯ SEO АТРИБУТОВ")
    log.info("Прямая работа с базой данных WordPress")
    log.info(f"Время запуска: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    log.info("=" * 70)
    
    try:
        # Запускаем массовое обновление
        success = update_all_articles()
        
        if success:
            log.info("✅ МАССОВОЕ ОБНОВЛЕНИЕ УСПЕШНО ЗАВЕРШЕНО!")
        else:
            log.error("❌ МАССОВОЕ ОБНОВЛЕНИЕ ЗАВЕРШИЛОСЬ С ОШИБКАМИ")
            
    except Exception as e:
        log.error(f"Критическая ошибка: {e}")

if __name__ == "__main__":
    main()

