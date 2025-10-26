#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для добавления SEO атрибутов в WordPress статьи
Основан на анализе плагина AI-Scribe
"""

import requests
import json
import time
import sqlite3
import re
from urllib.parse import quote
import logging
import os

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/seo_update.log'),
        logging.StreamHandler()
    ]
)
log = logging.getLogger(__name__)

# Конфигурация WordPress API
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APPLICATION_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"

# Конфигурация базы данных проекта
DB_PATH = "/root/seo_project/SEO_ecopackpro/articles.db"

def get_wordpress_credentials():
    """Получить учетные данные WordPress"""
    return WP_USERNAME, WP_APPLICATION_PASSWORD

def get_articles_from_db():
    """Получить статьи из базы данных проекта"""
    try:
        conn = sqlite3.connect(DB_PATH)
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
        
        log.info(f"Загружено {len(articles)} статей из базы данных")
        return articles
        
    except Exception as e:
        log.error(f"Ошибка загрузки из БД: {e}")
        return []

def get_all_posts_from_wordpress():
    """Получить все посты из WordPress"""
    try:
        username, password = get_wordpress_credentials()
        auth = (username, password)
        
        all_posts = []
        page = 1
        per_page = 100
        
        while True:
            url = f"{WP_API_URL}/posts"
            params = {
                'status': 'draft',
                'per_page': per_page,
                'page': page,
                'orderby': 'id',
                'order': 'asc'
            }
            
            response = requests.get(url, auth=auth, params=params, timeout=60)
            
            if response.status_code == 200:
                posts = response.json()
                if not posts:
                    break
                all_posts.extend(posts)
                log.info(f"Загружено {len(posts)} постов со страницы {page}")
                page += 1
                time.sleep(0.5)
            else:
                log.error(f"Ошибка API WordPress: {response.status_code}")
                break
                
        log.info(f"Всего загружено {len(all_posts)} постов из WordPress")
        return all_posts
        
    except Exception as e:
        log.error(f"Ошибка получения постов: {e}")
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
    """Обновить SEO мета-данные поста в WordPress"""
    try:
        username, password = get_wordpress_credentials()
        auth = (username, password)
        
        # 1. Обновляем slug поста
        update_data = {
            'slug': slug
        }
        
        url = f"{WP_API_URL}/posts/{post_id}"
        response = requests.post(url, auth=auth, json=update_data, timeout=30)
        
        if response.status_code != 200:
            log.error(f"Ошибка обновления slug для поста {post_id}: {response.status_code}")
            return False
        
        # 2. Обновляем SEO мета-данные через update_post_meta
        # Используем тот же подход, что и в AI-Scribe плагине
        
        # Проверяем, какой SEO плагин активен, и обновляем соответствующие поля
        meta_updates = []
        
        # Yoast SEO поля (основные)
        meta_updates.extend([
            {
                'meta_key': '_yoast_wpseo_title',
                'meta_value': seo_title
            },
            {
                'meta_key': '_yoast_wpseo_metadesc',
                'meta_value': meta_description
            },
            {
                'meta_key': '_yoast_wpseo_focuskw',
                'meta_value': focus_keyword
            }
        ])
        
        # Дополнительные Yoast поля для лучшей интеграции
        meta_updates.extend([
            {
                'meta_key': '_yoast_wpseo_focuskw_text_input',
                'meta_value': focus_keyword
            },
            {
                'meta_key': '_yoast_wpseo_content_score',
                'meta_value': '90'
            }
        ])
        
        # Обновляем каждое мета-поле
        success_count = 0
        for meta_update in meta_updates:
            try:
                # Используем WordPress REST API для обновления мета-данных
                meta_url = f"{WP_API_URL}/posts/{post_id}/meta"
                meta_data = {
                    'key': meta_update['meta_key'],
                    'value': meta_update['meta_value']
                }
                
                meta_response = requests.post(meta_url, auth=auth, json=meta_data, timeout=30)
                
                if meta_response.status_code in [200, 201]:
                    success_count += 1
                    log.info(f"✅ Обновлено {meta_update['meta_key']} для поста {post_id}")
                else:
                    log.warning(f"⚠️ Не удалось обновить {meta_update['meta_key']}: {meta_response.status_code}")
                    
            except Exception as e:
                log.error(f"Ошибка обновления {meta_update['meta_key']}: {e}")
        
        log.info(f"Обновлено {success_count}/{len(meta_updates)} мета-полей для поста {post_id}")
        return success_count > 0
        
    except Exception as e:
        log.error(f"Ошибка обновления SEO для поста {post_id}: {e}")
        return False

def test_single_article():
    """Тестировать на одной статье (ID 7956)"""
    log.info("🧪 ТЕСТИРОВАНИЕ НА ОДНОЙ СТАТЬЕ")
    
    # Получаем данные из базы
    articles = get_articles_from_db()
    if not articles:
        log.error("Не удалось загрузить статьи из базы данных")
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
    posts = get_all_posts_from_wordpress()
    if not posts:
        log.error("Не удалось загрузить посты из WordPress")
        return False
    
    # Ищем пост с ID 7956
    target_post = None
    for post in posts:
        if post['id'] == 7956:
            target_post = post
            break
    
    if not target_post:
        log.error("Пост с ID 7956 не найден")
        return False
    
    log.info(f"Найден пост: {target_post['title']['rendered']}")
    log.info(f"Текущий slug: {target_post['slug']}")
    
    # Обновляем SEO данные
    success = update_post_seo_meta(
        post_id=7956,
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
        return True
    else:
        log.error("❌ Ошибка обновления SEO данных")
        return False

def main():
    """Основная функция"""
    log.info("🚀 ЗАПУСК СКРИПТА ДОБАВЛЕНИЯ SEO АТРИБУТОВ")
    log.info("=" * 50)
    
    try:
        # Тестируем на одной статье
        success = test_single_article()
        
        if success:
            log.info("✅ ТЕСТ УСПЕШЕН!")
            log.info("Теперь можно применить ко всем статьям")
        else:
            log.error("❌ ТЕСТ НЕ ПРОШЕЛ")
            
    except Exception as e:
        log.error(f"Критическая ошибка: {e}")

if __name__ == "__main__":
    main()

