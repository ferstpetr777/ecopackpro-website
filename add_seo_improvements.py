#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
ДОБАВЛЕНИЕ SEO УЛУЧШЕНИЙ В СТАТЬИ
1. Исходящие ссылки в конце статей
2. Ключевая фраза во вступлении
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
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/seo_improvements.log'),
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

def add_keyword_to_intro(content, keyword):
    """Добавить ключевое слово во вступление статьи"""
    try:
        soup = BeautifulSoup(content, 'html.parser')
        
        # Ищем первый параграф после заголовка H1
        h1_tag = soup.find('h1')
        if not h1_tag:
            return content
        
        # Ищем первый параграф после H1
        first_p = None
        for element in h1_tag.find_next_siblings():
            if element.name == 'p' and element.get_text().strip():
                first_p = element
                break
        
        if first_p:
            # Проверяем, есть ли уже ключевое слово в первом абзаце
            first_p_text = first_p.get_text().lower()
            keyword_lower = keyword.lower()
            
            if keyword_lower not in first_p_text:
                # Добавляем ключевое слово в начало первого абзаца
                current_text = first_p.get_text()
                new_text = f"{keyword.capitalize()} - это {current_text.lower()}"
                first_p.string = new_text
                log.info(f"✅ Добавлено ключевое слово во вступление: '{keyword}'")
            else:
                log.info(f"ℹ️ Ключевое слово уже присутствует во вступлении: '{keyword}'")
        else:
            log.warning(f"⚠️ Не найден первый параграф для добавления ключевого слова")
        
        return str(soup)
        
    except Exception as e:
        log.error(f"Ошибка добавления ключевого слова во вступление: {e}")
        return content

def add_outgoing_links(content, keyword):
    """Добавить исходящие ссылки в конце статьи"""
    try:
        soup = BeautifulSoup(content, 'html.parser')
        
        # Определяем релевантные ссылки на основе ключевого слова
        outgoing_links = generate_outgoing_links(keyword)
        
        # Создаем HTML для исходящих ссылок
        links_html = f"""
        <div class="outgoing-links-section" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin: 30px 0; font-family: 'Roboto', sans-serif;">
            <h3 style="color: #495057; margin-top: 0; margin-bottom: 15px; font-size: 1.2em; font-weight: 600;">Полезные ссылки</h3>
            <ul style="margin: 0; padding-left: 20px; color: #6c757d;">
                {''.join([f'<li style="margin-bottom: 8px;"><a href="{link["url"]}" target="_blank" rel="noopener" style="color: #007bff; text-decoration: none; font-weight: 500;">{link["text"]}</a></li>' for link in outgoing_links])}
            </ul>
            <p style="margin: 15px 0 0 0; font-size: 0.9em; color: #6c757d; font-style: italic;">Эти ссылки помогут вам глубже изучить тему и найти дополнительную информацию.</p>
        </div>
        """
        
        # Добавляем ссылки перед навигацией или в конец контента
        nav_div = soup.find('div', class_='article-navigation')
        if nav_div:
            # Вставляем перед навигацией
            links_soup = BeautifulSoup(links_html, 'html.parser')
            nav_div.insert_before(links_soup)
        else:
            # Добавляем в конец контента
            soup.append(BeautifulSoup(links_html, 'html.parser'))
        
        log.info(f"✅ Добавлены исходящие ссылки: {len(outgoing_links)} ссылок")
        return str(soup)
        
    except Exception as e:
        log.error(f"Ошибка добавления исходящих ссылок: {e}")
        return content

def generate_outgoing_links(keyword):
    """Генерирует релевантные исходящие ссылки на основе ключевого слова"""
    
    # Базовые ссылки для упаковочной тематики
    base_links = [
        {
            "text": "Официальный сайт производителя упаковочных материалов",
            "url": "https://ecopackpro.ru"
        },
        {
            "text": "Каталог упаковочных решений",
            "url": "https://ecopackpro.ru/catalog"
        },
        {
            "text": "Информация о доставке и оплате",
            "url": "https://ecopackpro.ru/delivery"
        },
        {
            "text": "Контакты для заказа",
            "url": "https://ecopackpro.ru/contacts"
        }
    ]
    
    # Специфичные ссылки на основе ключевого слова
    specific_links = []
    
    if "пакет" in keyword.lower():
        specific_links.extend([
            {
                "text": "Виды упаковочных пакетов и их применение",
                "url": "https://ecopackpro.ru/packaging-types"
            },
            {
                "text": "Стандарты упаковки для разных отраслей",
                "url": "https://ecopackpro.ru/packaging-standards"
            }
        ])
    
    if "коробк" in keyword.lower():
        specific_links.extend([
            {
                "text": "Выбор коробок по размерам и типу товара",
                "url": "https://ecopackpro.ru/box-selection"
            },
            {
                "text": "Производство коробок на заказ",
                "url": "https://ecopackpro.ru/custom-boxes"
            }
        ])
    
    if "конверт" in keyword.lower():
        specific_links.extend([
            {
                "text": "Типы конвертов для документов и писем",
                "url": "https://ecopackpro.ru/envelope-types"
            },
            {
                "text": "Почтовые требования к конвертам",
                "url": "https://ecopackpro.ru/postal-requirements"
            }
        ])
    
    if "пломб" in keyword.lower() or "антимагнит" in keyword.lower():
        specific_links.extend([
            {
                "text": "Типы пломб и их применение",
                "url": "https://ecopackpro.ru/seal-types"
            },
            {
                "text": "Требования к пломбированию",
                "url": "https://ecopackpro.ru/sealing-requirements"
            }
        ])
    
    # Объединяем базовые и специфичные ссылки
    all_links = base_links + specific_links[:2]  # Берем максимум 2 специфичные ссылки
    
    return all_links

def update_article_content(post_id, new_content):
    """Обновить контент статьи в базе данных"""
    try:
        conn = get_db_connection()
        if not conn:
            return False
        
        cursor = conn.cursor()
        
        cursor.execute("""
            UPDATE wp_posts
            SET post_content = %s
            WHERE ID = %s
        """, (new_content, post_id))
        
        conn.commit()
        cursor.close()
        conn.close()
        
        log.info(f"✅ Контент статьи {post_id} обновлен")
        return True
        
    except Exception as e:
        log.error(f"Ошибка обновления контента статьи {post_id}: {e}")
        return False

def main():
    """Основная функция"""
    log.info("🚀 ДОБАВЛЕНИЕ SEO УЛУЧШЕНИЙ В СТАТЬИ")
    log.info(f"Время: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
    log.info("=" * 60)
    
    stats = {
        'revisions_saved': 0,
        'articles_updated': 0,
        'intro_improvements': 0,
        'links_added': 0,
        'errors': 0
    }
    
    # Обрабатываем каждую статью
    for i, (post_id, keyword) in enumerate(ARTICLES_DATA.items(), 1):
        log.info(f"\n🔧 [{i}/{len(ARTICLES_DATA)}] Обработка статьи ID {post_id}")
        log.info(f"   Ключевое слово: '{keyword}'")
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
            
            # 2. Получаем текущий контент
            conn = get_db_connection()
            if not conn:
                stats['errors'] += 1
                continue
            
            cursor = conn.cursor()
            cursor.execute("""
                SELECT post_content
                FROM wp_posts
                WHERE ID = %s
            """, (post_id,))
            
            result = cursor.fetchone()
            cursor.close()
            conn.close()
            
            if not result:
                log.error(f"Не удалось получить контент статьи {post_id}")
                stats['errors'] += 1
                continue
            
            current_content = result[0]
            
            # 3. Добавляем ключевое слово во вступление
            log.info("📝 Улучшение вступления...")
            updated_content = add_keyword_to_intro(current_content, keyword)
            if updated_content != current_content:
                stats['intro_improvements'] += 1
            
            # 4. Добавляем исходящие ссылки
            log.info("🔗 Добавление исходящих ссылок...")
            final_content = add_outgoing_links(updated_content, keyword)
            if final_content != updated_content:
                stats['links_added'] += 1
            
            # 5. Обновляем статью в базе данных
            log.info("💾 Обновление статьи...")
            if update_article_content(post_id, final_content):
                stats['articles_updated'] += 1
                log.info(f"✅ Статья {post_id} успешно обновлена")
            else:
                stats['errors'] += 1
            
        except Exception as e:
            log.error(f"❌ Ошибка обработки статьи {post_id}: {e}")
            stats['errors'] += 1
    
    # Итоговая статистика
    log.info("\n" + "=" * 60)
    log.info("📊 ИТОГОВАЯ СТАТИСТИКА")
    log.info("=" * 60)
    log.info(f"📦 Ревизий сохранено: {stats['revisions_saved']}")
    log.info(f"📝 Статей обновлено: {stats['articles_updated']}")
    log.info(f"🔤 Улучшений вступления: {stats['intro_improvements']}")
    log.info(f"🔗 Добавлено исходящих ссылок: {stats['links_added']}")
    log.info(f"❌ Ошибок: {stats['errors']}")
    log.info("=" * 60)
    
    if stats['errors'] == 0:
        log.info("🎉 ВСЕ SEO УЛУЧШЕНИЯ ВНЕСЕНЫ УСПЕШНО!")
    else:
        log.warning(f"⚠️ Завершено с {stats['errors']} ошибками")
    
    log.info(f"Время завершения: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")

if __name__ == "__main__":
    main()

