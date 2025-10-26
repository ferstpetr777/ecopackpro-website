#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
АУДИТ SEO СТАТЕЙ - ПРОВЕРКА СООТВЕТСТВИЯ
Проверяет соответствие между названием статьи, изображением и ключевым словом
"""

import mysql.connector
import sqlite3
import logging
import os
from datetime import datetime
from bs4 import BeautifulSoup
import requests
from urllib.parse import quote

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/seo_audit.log'),
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

# Список ключевых слов для проверки
EXPECTED_KEYWORDS = [
    "курьерские пакеты",
    "почтовые коробки", 
    "зип пакеты",
    "zip lock пакеты с бегунком",
    "конверты с воздушной подушкой",
    "конверты с воздушной прослойкой",
    "крафтовые пакеты с воздушной подушкой",
    "курьерские пакеты прозрачные",
    "курьерские пакеты номерные",
    "курьерские пакеты черно-белые",
    "курьерские пакеты с карманом",
    "zip lock пакеты матовые",
    "zip lock пакеты оптом",
    "крафтовые конверты",
    "пузырчатые пакеты ВПП",
    "коробки для почты",
    "коробки для отправки",
    "самоклеящиеся карманы",
    "антимагнитная пломба",
    "наклейка пломба антимагнит",
    "пломбиратор для бочек",
    "номерные пломбы наклейки",
    "zip lock пакеты с белой полосой",
    "белые крафт пакеты с пузырчатой плёнкой",
    "прозрачные zip lock пакеты",
    "купить курьерские пакеты с номерным штрих-кодом",
    "заказать прозрачные курьерские пакеты оптом",
    "курьерские пакеты черно-белые с карманом цена",
    "матовые zip lock пакеты с бегунком 10×15",
    "купить оптом zip lock пакеты матовые 30 мкм",
    "крафт конверты с воздушной подушкой F/3",
    "почтовые коробки размера S 260×170×80",
    "почтовые коробки размера XL 530×360×220",
    "купить самоклеящиеся карманы SD для документов",
    "антимагнитные наклейки для водяных счётчиков",
    "антимагнитная пломба цена за 100 штук",
    "пломбиратор для евробочек 2 дюйма",
    "инструмент для опломбирования бочек ¾ дюйма",
    "курьерские пакеты черно-белые без логотипа А4",
    "курьерские пакеты прозрачные для одежды",
    "курьерские пакеты для маркетплейсов Ozon",
    "почтовые коробки с логотипом на заказ",
    "зип пакеты с бегунком купить Москва",
    "матовые zip lock пакеты для чая",
    "zip lock пакеты с подвесом",
    "белые крафт-пакеты с пузырчатой плёнкой оптом",
    "плоские конверты с воздушной подушкой для документов",
    "пакеты из воздушно-пузырьковой плёнки оптом",
    "антимагнитные пломбы для газовых счётчиков",
    "самоклеящиеся карманы для транспортных накладных"
]

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

def get_wordpress_posts_with_images():
    """Получить посты из WordPress с информацией об изображениях"""
    try:
        conn = get_db_connection()
        if not conn:
            return []
        
        cursor = conn.cursor()
        
        # Получаем посты с их изображениями
        cursor.execute("""
            SELECT 
                p.ID, 
                p.post_title, 
                p.post_name, 
                p.post_content,
                pm.meta_value as featured_image_id
            FROM wp_posts p
            LEFT JOIN wp_postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_thumbnail_id'
            WHERE p.post_type = 'post' 
            AND p.post_status = 'draft'
            AND p.ID BETWEEN 7907 AND 7956
            ORDER BY p.ID ASC
        """)
        
        posts = cursor.fetchall()
        
        # Получаем информацию об изображениях
        posts_with_images = []
        for post in posts:
            post_id, title, slug, content, featured_image_id = post
            
            image_title = ""
            image_alt = ""
            
            if featured_image_id:
                cursor.execute("""
                    SELECT post_title, post_excerpt
                    FROM wp_posts 
                    WHERE ID = %s AND post_type = 'attachment'
                """, (featured_image_id,))
                
                image_data = cursor.fetchone()
                if image_data:
                    image_title = image_data[0] or ""
                    image_alt = image_data[1] or ""
                
                # Получаем alt текст из мета-полей
                cursor.execute("""
                    SELECT meta_value
                    FROM wp_postmeta 
                    WHERE post_id = %s AND meta_key = '_wp_attachment_image_alt'
                """, (featured_image_id,))
                
                alt_data = cursor.fetchone()
                if alt_data:
                    image_alt = alt_data[0] or ""
            
            posts_with_images.append({
                'id': post_id,
                'title': title,
                'slug': slug,
                'content': content,
                'featured_image_id': featured_image_id,
                'image_title': image_title,
                'image_alt': image_alt
            })
        
        cursor.close()
        conn.close()
        
        log.info(f"Загружено {len(posts_with_images)} постов с изображениями из WordPress")
        return posts_with_images
        
    except Exception as e:
        log.error(f"Ошибка загрузки постов из WordPress: {e}")
        return []

def normalize_text(text):
    """Нормализация текста для сравнения"""
    if not text:
        return ""
    
    # Приводим к нижнему регистру
    text = text.lower().strip()
    
    # Убираем лишние пробелы
    text = ' '.join(text.split())
    
    return text

def check_keyword_match(keyword1, keyword2):
    """Проверка соответствия ключевых слов"""
    if not keyword1 or not keyword2:
        return False
    
    norm1 = normalize_text(keyword1)
    norm2 = normalize_text(keyword2)
    
    return norm1 == norm2

def audit_article(article_data, expected_keyword, index):
    """Аудит одной статьи"""
    post_id = article_data['id']
    title = article_data['title']
    image_title = article_data['image_title']
    image_alt = article_data['image_alt']
    
    log.info(f"🔍 [{index+1}/50] Аудит статьи ID {post_id}")
    log.info(f"   Заголовок: {title}")
    log.info(f"   Ожидаемое ключевое слово: {expected_keyword}")
    log.info(f"   Название изображения: {image_title}")
    log.info(f"   Alt текст изображения: {image_alt}")
    
    # Проверки
    title_match = check_keyword_match(title, expected_keyword)
    image_title_match = check_keyword_match(image_title, expected_keyword)
    image_alt_match = check_keyword_match(image_alt, expected_keyword)
    
    # Определяем статус
    if title_match and (image_title_match or image_alt_match):
        status = "✅ СООТВЕТСТВУЕТ"
        log.info(f"   {status}")
        return {
            'post_id': post_id,
            'title': title,
            'expected_keyword': expected_keyword,
            'image_title': image_title,
            'image_alt': image_alt,
            'title_match': title_match,
            'image_title_match': image_title_match,
            'image_alt_match': image_alt_match,
            'status': 'OK',
            'issues': []
        }
    else:
        issues = []
        if not title_match:
            issues.append("Заголовок не соответствует ключевому слову")
        if not image_title_match and not image_alt_match:
            issues.append("Изображение не соответствует ключевому слову")
        
        status = "❌ НЕ СООТВЕТСТВУЕТ"
        log.warning(f"   {status}")
        for issue in issues:
            log.warning(f"   ⚠️ {issue}")
        
        return {
            'post_id': post_id,
            'title': title,
            'expected_keyword': expected_keyword,
            'image_title': image_title,
            'image_alt': image_alt,
            'title_match': title_match,
            'image_title_match': image_title_match,
            'image_alt_match': image_alt_match,
            'status': 'ERROR',
            'issues': issues
        }

def generate_audit_report(audit_results):
    """Генерация отчета по аудиту"""
    total_articles = len(audit_results)
    ok_articles = len([r for r in audit_results if r['status'] == 'OK'])
    error_articles = len([r for r in audit_results if r['status'] == 'ERROR'])
    
    log.info("=" * 80)
    log.info("📊 ОТЧЕТ ПО АУДИТУ SEO СТАТЕЙ")
    log.info("=" * 80)
    log.info(f"📈 Общее количество статей: {total_articles}")
    log.info(f"✅ Соответствуют критериям: {ok_articles}")
    log.info(f"❌ Требуют исправления: {error_articles}")
    log.info(f"📊 Процент соответствия: {(ok_articles/total_articles*100):.1f}%")
    
    if error_articles > 0:
        log.info("\n🚨 СТАТЬИ ТРЕБУЮЩИЕ ИСПРАВЛЕНИЯ:")
        log.info("-" * 50)
        
        for i, result in enumerate(audit_results, 1):
            if result['status'] == 'ERROR':
                log.warning(f"{i}. ID {result['post_id']}: {result['title']}")
                log.warning(f"   Ожидалось: {result['expected_keyword']}")
                log.warning(f"   Изображение: {result['image_title']} / {result['image_alt']}")
                for issue in result['issues']:
                    log.warning(f"   ❌ {issue}")
                log.warning("")
    
    return {
        'total': total_articles,
        'ok': ok_articles,
        'errors': error_articles,
        'percentage': (ok_articles/total_articles*100) if total_articles > 0 else 0,
        'error_details': [r for r in audit_results if r['status'] == 'ERROR']
    }

def main():
    """Основная функция аудита"""
    log.info("🔍 ЗАПУСК АУДИТА SEO СТАТЕЙ")
    log.info("Проверка соответствия: заголовок ↔ изображение ↔ ключевое слово")
    log.info(f"Время запуска: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    log.info("=" * 80)
    
    try:
        # Получаем данные
        articles = get_articles_from_project_db()
        posts = get_wordpress_posts_with_images()
        
        if not articles or not posts:
            log.error("Не удалось загрузить данные для аудита")
            return
        
        # Проверяем соответствие количества
        if len(articles) != len(EXPECTED_KEYWORDS):
            log.warning(f"Несоответствие количества: статьи={len(articles)}, ключевые слова={len(EXPECTED_KEYWORDS)}")
        
        if len(posts) != len(EXPECTED_KEYWORDS):
            log.warning(f"Несоответствие количества: посты={len(posts)}, ключевые слова={len(EXPECTED_KEYWORDS)}")
        
        # Проводим аудит каждой статьи
        audit_results = []
        
        for i, expected_keyword in enumerate(EXPECTED_KEYWORDS):
            if i < len(posts):
                article_data = posts[i]
                result = audit_article(article_data, expected_keyword, i)
                audit_results.append(result)
            else:
                log.warning(f"Статья {i+1} не найдена в WordPress")
        
        # Генерируем отчет
        report = generate_audit_report(audit_results)
        
        # Сохраняем детальный отчет в файл
        report_file = f"/var/www/fastuser/data/www/ecopackpro.ru/seo_audit_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.txt"
        
        with open(report_file, 'w', encoding='utf-8') as f:
            f.write("ОТЧЕТ ПО АУДИТУ SEO СТАТЕЙ\n")
            f.write("=" * 50 + "\n")
            f.write(f"Дата: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
            f.write(f"Всего статей: {report['total']}\n")
            f.write(f"Соответствуют: {report['ok']}\n")
            f.write(f"Ошибки: {report['errors']}\n")
            f.write(f"Процент соответствия: {report['percentage']:.1f}%\n\n")
            
            if report['error_details']:
                f.write("ДЕТАЛИ ОШИБОК:\n")
                f.write("-" * 30 + "\n")
                for detail in report['error_details']:
                    f.write(f"ID {detail['post_id']}: {detail['title']}\n")
                    f.write(f"Ожидалось: {detail['expected_keyword']}\n")
                    f.write(f"Изображение: {detail['image_title']} / {detail['image_alt']}\n")
                    for issue in detail['issues']:
                        f.write(f"❌ {issue}\n")
                    f.write("\n")
        
        log.info(f"📄 Детальный отчет сохранен: {report_file}")
        
        if report['errors'] == 0:
            log.info("🎉 ВСЕ СТАТЬИ СООТВЕТСТВУЮТ КРИТЕРИЯМ!")
        else:
            log.warning(f"⚠️ ТРЕБУЕТСЯ ИСПРАВЛЕНИЕ {report['errors']} СТАТЕЙ")
            
    except Exception as e:
        log.error(f"Критическая ошибка при аудите: {e}")

if __name__ == "__main__":
    main()

