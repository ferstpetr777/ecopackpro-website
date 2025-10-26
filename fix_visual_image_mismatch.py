#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
ИСПРАВЛЕНИЕ НЕСООТВЕТСТВИЯ ВИЗУАЛЬНЫХ ИЗОБРАЖЕНИЙ
Проверяет и исправляет несоответствие между главным изображением и изображением в контенте статьи
"""

import mysql.connector
import logging
from datetime import datetime
from bs4 import BeautifulSoup
import re

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/fix_visual_images.log'),
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

# ID всех 50 статей с их ключевыми словами
ARTICLES_DATA = {
    7907: "курьерские пакеты",
    7908: "почтовые коробки",
    7909: "зип пакеты",
    7910: "zip lock пакеты с бегунком",
    7911: "конверты с воздушной подушкой",
    7912: "конверты с воздушной прослойкой",
    7913: "крафтовые пакеты с воздушной подушкой",
    7914: "курьерские пакеты прозрачные",
    7915: "курьерские пакеты номерные",
    7916: "курьерские пакеты черно-белые",
    7917: "курьерские пакеты с карманом",
    7918: "zip lock пакеты матовые",
    7919: "zip lock пакеты оптом",
    7920: "крафтовые конверты",
    7921: "пузырчатые пакеты ВПП",
    7922: "коробки для почты",
    7923: "коробки для отправки",
    7924: "самоклеящиеся карманы",
    7925: "антимагнитная пломба",
    7926: "наклейка пломба антимагнит",
    7927: "пломбиратор для бочек",
    7928: "номерные пломбы наклейки",
    7929: "zip lock пакеты с белой полосой",
    7930: "белые крафт пакеты с пузырчатой плёнкой",
    7931: "прозрачные zip lock пакеты",
    7932: "купить курьерские пакеты с номерным штрих-кодом",
    7933: "заказать прозрачные курьерские пакеты оптом",
    7934: "курьерские пакеты черно-белые с карманом цена",
    7935: "матовые zip lock пакеты с бегунком 10×15",
    7936: "купить оптом zip lock пакеты матовые 30 мкм",
    7937: "крафт конверты с воздушной подушкой F/3",
    7938: "почтовые коробки размера S 260×170×80",
    7939: "почтовые коробки размера XL 530×360×220",
    7940: "купить самоклеящиеся карманы SD для документов",
    7941: "антимагнитные наклейки для водяных счётчиков",
    7942: "антимагнитная пломба цена за 100 штук",
    7943: "пломбиратор для евробочек 2 дюйма",
    7944: "инструмент для опломбирования бочек ¾ дюйма",
    7945: "курьерские пакеты черно-белые без логотипа А4",
    7946: "курьерские пакеты прозрачные для одежды",
    7947: "курьерские пакеты для маркетплейсов Ozon",
    7948: "почтовые коробки с логотипом на заказ",
    7949: "зип пакеты с бегунком купить Москва",
    7950: "матовые zip lock пакеты для чая",
    7951: "zip lock пакеты с подвесом",
    7952: "белые крафт-пакеты с пузырчатой плёнкой оптом",
    7953: "плоские конверты с воздушной подушкой для документов",
    7954: "пакеты из воздушно-пузырьковой плёнки оптом",
    7955: "антимагнитные пломбы для газовых счётчиков",
    7956: "самоклеящиеся карманы для транспортных накладных"
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

def get_featured_image_info(post_id):
    """Получить информацию о главном изображении статьи"""
    try:
        conn = get_db_connection()
        if not conn:
            return None
        
        cursor = conn.cursor()
        
        # Получаем ID главного изображения
        cursor.execute("""
            SELECT meta_value
            FROM wp_postmeta
            WHERE post_id = %s AND meta_key = '_thumbnail_id'
        """, (post_id,))
        
        result = cursor.fetchone()
        if not result or not result[0]:
            cursor.close()
            conn.close()
            return None
        
        featured_image_id = result[0]
        
        # Получаем информацию об изображении
        cursor.execute("""
            SELECT post_title, post_excerpt, guid
            FROM wp_posts
            WHERE ID = %s AND post_type = 'attachment'
        """, (featured_image_id,))
        
        image_result = cursor.fetchone()
        cursor.close()
        conn.close()
        
        if image_result:
            return {
                'id': featured_image_id,
                'title': image_result[0],
                'alt': image_result[1],
                'url': image_result[2]
            }
        return None
        
    except Exception as e:
        log.error(f"Ошибка получения информации о главном изображении для статьи {post_id}: {e}")
        return None

def get_content_images(post_id):
    """Получить все изображения из контента статьи"""
    try:
        conn = get_db_connection()
        if not conn:
            return []
        
        cursor = conn.cursor()
        
        # Получаем контент статьи
        cursor.execute("""
            SELECT post_content
            FROM wp_posts
            WHERE ID = %s
        """, (post_id,))
        
        result = cursor.fetchone()
        cursor.close()
        conn.close()
        
        if not result:
            return []
        
        content = result[0]
        soup = BeautifulSoup(content, 'html.parser')
        
        images = []
        for img in soup.find_all('img'):
            src = img.get('src', '')
            alt = img.get('alt', '')
            title = img.get('title', '')
            
            # Извлекаем ID изображения из URL если возможно
            image_id = None
            if 'wp-content/uploads' in src:
                # Пытаемся найти ID в URL
                match = re.search(r'/(\d+)/', src)
                if match:
                    image_id = match.group(1)
            
            images.append({
                'src': src,
                'alt': alt,
                'title': title,
                'id': image_id
            })
        
        return images
        
    except Exception as e:
        log.error(f"Ошибка получения изображений из контента для статьи {post_id}: {e}")
        return []

def find_matching_image_in_media(keyword):
    """Найти подходящее изображение в медиатеке по ключевому слову"""
    try:
        conn = get_db_connection()
        if not conn:
            return None
        
        cursor = conn.cursor()
        
        # Ищем изображения по названию и alt-тексту
        keyword_normalized = keyword.lower().strip()
        
        cursor.execute("""
            SELECT p.ID, p.post_title, p.post_excerpt, p.guid
            FROM wp_posts p
            WHERE p.post_type = 'attachment'
            AND p.post_mime_type LIKE 'image/%'
            AND (
                LOWER(p.post_title) LIKE %s
                OR LOWER(p.post_excerpt) LIKE %s
            )
            ORDER BY p.post_date DESC
        """, (f'%{keyword_normalized}%', f'%{keyword_normalized}%'))
        
        results = cursor.fetchall()
        cursor.close()
        conn.close()
        
        if results:
            # Возвращаем первое найденное изображение
            result = results[0]
            return {
                'id': result[0],
                'title': result[1],
                'alt': result[2],
                'url': result[3]
            }
        
        return None
        
    except Exception as e:
        log.error(f"Ошибка поиска изображения для ключевого слова '{keyword}': {e}")
        return None

def update_featured_image(post_id, new_image_id):
    """Обновить главное изображение статьи"""
    try:
        conn = get_db_connection()
        if not conn:
            return False
        
        cursor = conn.cursor()
        
        # Проверяем, есть ли уже главное изображение
        cursor.execute("""
            SELECT meta_id FROM wp_postmeta
            WHERE post_id = %s AND meta_key = '_thumbnail_id'
        """, (post_id,))
        
        existing = cursor.fetchone()
        
        if existing:
            # Обновляем существующее
            cursor.execute("""
                UPDATE wp_postmeta
                SET meta_value = %s
                WHERE post_id = %s AND meta_key = '_thumbnail_id'
            """, (new_image_id, post_id))
        else:
            # Создаем новое
            cursor.execute("""
                INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
                VALUES (%s, '_thumbnail_id', %s)
            """, (post_id, new_image_id))
        
        conn.commit()
        cursor.close()
        conn.close()
        
        log.info(f"✅ Главное изображение статьи {post_id} обновлено на ID {new_image_id}")
        return True
        
    except Exception as e:
        log.error(f"Ошибка обновления главного изображения для статьи {post_id}: {e}")
        return False

def check_image_mismatch(post_id, keyword):
    """Проверить несоответствие между главным изображением и изображением в контенте"""
    try:
        # Получаем информацию о главном изображении
        featured_image = get_featured_image_info(post_id)
        
        # Получаем изображения из контента
        content_images = get_content_images(post_id)
        
        if not featured_image or not content_images:
            return {
                'has_mismatch': False,
                'featured_image': featured_image,
                'content_images': content_images,
                'issue': 'Нет главного изображения или изображений в контенте'
            }
        
        # Проверяем соответствие ключевому слову
        keyword_lower = keyword.lower().strip()
        featured_title_lower = featured_image['title'].lower() if featured_image['title'] else ''
        featured_alt_lower = featured_image['alt'].lower() if featured_image['alt'] else ''
        
        # Проверяем, соответствует ли главное изображение ключевому слову
        featured_matches = (
            keyword_lower in featured_title_lower or
            keyword_lower in featured_alt_lower
        )
        
        # Проверяем изображения в контенте
        content_matches = []
        for img in content_images:
            img_title_lower = img['title'].lower() if img['title'] else ''
            img_alt_lower = img['alt'].lower() if img['alt'] else ''
            
            img_matches = (
                keyword_lower in img_title_lower or
                keyword_lower in img_alt_lower
            )
            content_matches.append(img_matches)
        
        # Определяем несоответствие
        has_content_match = any(content_matches)
        
        mismatch_detected = featured_matches != has_content_match
        
        return {
            'has_mismatch': mismatch_detected,
            'featured_image': featured_image,
            'content_images': content_images,
            'featured_matches_keyword': featured_matches,
            'content_matches_keyword': has_content_match,
            'issue': 'Несоответствие между главным изображением и контентом' if mismatch_detected else 'Соответствие найдено'
        }
        
    except Exception as e:
        log.error(f"Ошибка проверки несоответствия для статьи {post_id}: {e}")
        return {
            'has_mismatch': False,
            'featured_image': None,
            'content_images': [],
            'issue': f'Ошибка проверки: {e}'
        }

def main():
    """Основная функция"""
    log.info("🚀 ПРОВЕРКА И ИСПРАВЛЕНИЕ НЕСООТВЕТСТВИЯ ВИЗУАЛЬНЫХ ИЗОБРАЖЕНИЙ")
    log.info(f"Время: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    log.info("=" * 60)
    
    stats = {
        'revisions_saved': 0,
        'articles_checked': 0,
        'mismatches_found': 0,
        'images_updated': 0,
        'errors': 0
    }
    
    mismatches_report = []
    
    # Проверяем каждую статью
    for i, (post_id, keyword) in enumerate(ARTICLES_DATA.items(), 1):
        log.info(f"\n🔍 [{i}/{len(ARTICLES_DATA)}] Проверка статьи ID {post_id}")
        log.info(f"   Ключевое слово: '{keyword}'")
        log.info("-" * 50)
        
        try:
            stats['articles_checked'] += 1
            
            # Проверяем несоответствие
            mismatch_info = check_image_mismatch(post_id, keyword)
            
            if mismatch_info['has_mismatch']:
                log.warning(f"❌ НАЙДЕНО НЕСООТВЕТСТВИЕ!")
                log.warning(f"   Проблема: {mismatch_info['issue']}")
                
                if mismatch_info['featured_image']:
                    log.warning(f"   Главное изображение: '{mismatch_info['featured_image']['title']}'")
                else:
                    log.warning(f"   Главное изображение: НЕ УСТАНОВЛЕНО")
                
                log.warning(f"   Изображений в контенте: {len(mismatch_info['content_images'])}")
                
                stats['mismatches_found'] += 1
                mismatches_report.append({
                    'post_id': post_id,
                    'keyword': keyword,
                    'issue': mismatch_info['issue'],
                    'featured_image': mismatch_info['featured_image'],
                    'content_images_count': len(mismatch_info['content_images'])
                })
                
                # Сохраняем ревизию перед исправлением
                log.info("📦 Сохранение ревизии перед исправлением...")
                if save_revision(post_id):
                    stats['revisions_saved'] += 1
                
                # Ищем подходящее изображение в медиатеке
                log.info("🔍 Поиск подходящего изображения в медиатеке...")
                matching_image = find_matching_image_in_media(keyword)
                
                if matching_image:
                    log.info(f"✅ Найдено подходящее изображение: '{matching_image['title']}' (ID: {matching_image['id']})")
                    
                    # Обновляем главное изображение
                    log.info("🖼️ Обновление главного изображения...")
                    if update_featured_image(post_id, matching_image['id']):
                        stats['images_updated'] += 1
                        log.info(f"✅ Статья {post_id} исправлена успешно")
                    else:
                        stats['errors'] += 1
                else:
                    log.warning(f"⚠️ Не найдено подходящего изображения для ключевого слова '{keyword}'")
                    stats['errors'] += 1
            else:
                log.info(f"✅ Соответствие найдено: {mismatch_info['issue']}")
            
        except Exception as e:
            log.error(f"❌ Ошибка обработки статьи {post_id}: {e}")
            stats['errors'] += 1
    
    # Итоговая статистика
    log.info("\n" + "=" * 60)
    log.info("📊 ИТОГОВАЯ СТАТИСТИКА")
    log.info("=" * 60)
    log.info(f"🔍 Статей проверено: {stats['articles_checked']}")
    log.info(f"❌ Несоответствий найдено: {stats['mismatches_found']}")
    log.info(f"📦 Ревизий сохранено: {stats['revisions_saved']}")
    log.info(f"🖼️ Изображений обновлено: {stats['images_updated']}")
    log.info(f"❌ Ошибок: {stats['errors']}")
    
    if mismatches_report:
        log.info("\n🚨 ДЕТАЛЬНЫЙ ОТЧЕТ О НЕСООТВЕТСТВИЯХ:")
        log.info("-" * 50)
        for item in mismatches_report:
            log.info(f"ID {item['post_id']}: {item['keyword']}")
            log.info(f"   Проблема: {item['issue']}")
            if item['featured_image']:
                log.info(f"   Главное: '{item['featured_image']['title']}'")
            else:
                log.info(f"   Главное: НЕ УСТАНОВЛЕНО")
            log.info(f"   В контенте: {item['content_images_count']} изображений")
            log.info("")
    
    log.info("=" * 60)
    
    if stats['errors'] == 0 and stats['mismatches_found'] == 0:
        log.info("🎉 ВСЕ ИЗОБРАЖЕНИЯ СООТВЕТСТВУЮТ!")
    elif stats['mismatches_found'] > 0:
        log.info(f"✅ ИСПРАВЛЕНО {stats['images_updated']} НЕСООТВЕТСТВИЙ")
    else:
        log.warning(f"⚠️ Завершено с {stats['errors']} ошибками")
    
    log.info(f"Время завершения: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

if __name__ == "__main__":
    main()

