#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Скрипт для связывания ВСЕХ 50 опубликованных статей с исходниками в articles.db
"""

import sqlite3
import mysql.connector
import re

# Путь к базе данных проекта
PROJECT_DB_PATH = '/root/seo_project/SEO_ecopackpro/articles.db'

# Параметры подключения к MySQL (WordPress)
WP_DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

# Эталонный список из 50 ключевых слов
KEYWORDS_LIST = [
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

def normalize_keyword(keyword):
    """Нормализует ключевое слово для поиска"""
    # Убираем лишние пробелы и дефисы в начале
    keyword = keyword.strip().lstrip('-').strip()
    # Приводим к нижнему регистру
    keyword = keyword.lower()
    return keyword

def get_all_wp_articles():
    """Получает все опубликованные статьи из WordPress"""
    conn = mysql.connector.connect(**WP_DB_CONFIG)
    cursor = conn.cursor(dictionary=True)
    
    query = """
    SELECT 
        ID,
        post_title,
        post_name,
        post_date,
        post_modified
    FROM wp_posts
    WHERE post_status = 'publish' 
    AND post_type = 'post'
    AND ID >= 7907
    ORDER BY ID
    """
    
    cursor.execute(query)
    articles = cursor.fetchall()
    
    cursor.close()
    conn.close()
    
    return articles

def get_all_source_articles():
    """Получает все исходные статьи из articles.db"""
    conn = sqlite3.connect(PROJECT_DB_PATH)
    cursor = conn.cursor()
    
    cursor.execute("""
    SELECT id, keyword, title
    FROM articles
    ORDER BY id
    """)
    
    sources = {}
    for row in cursor.fetchall():
        source_id, keyword, title = row
        normalized_keyword = normalize_keyword(keyword)
        sources[normalized_keyword] = source_id
    
    conn.close()
    return sources

def link_all_articles():
    """Связывает все 50 статей с исходниками"""
    print("\n" + "="*120)
    print("🔗 СВЯЗЫВАНИЕ ВСЕХ 50 СТАТЕЙ С ИСХОДНИКАМИ".center(120))
    print("="*120 + "\n")
    
    # Получаем статьи из WordPress
    print("📥 Загружаю статьи из WordPress...")
    wp_articles = get_all_wp_articles()
    print(f"✅ Получено {len(wp_articles)} статей из WordPress\n")
    
    # Получаем исходники
    print("📥 Загружаю исходники из articles.db...")
    sources = get_all_source_articles()
    print(f"✅ Получено {len(sources)} исходников\n")
    
    # Очищаем и пересоздаем таблицу
    print("🗑️  Очищаю старые данные из published_articles...")
    conn = sqlite3.connect(PROJECT_DB_PATH)
    cursor = conn.cursor()
    cursor.execute("DELETE FROM published_articles WHERE wp_post_id >= 7907")
    conn.commit()
    print("✅ Старые данные удалены\n")
    
    print("="*120)
    print(f"{'№':<4} {'WP ID':<7} {'ИСХОДНИК':<12} {'НАЗВАНИЕ':<80}")
    print("="*120)
    
    linked_count = 0
    not_found_count = 0
    export_date = '2025-10-12 09:10:00'
    
    for idx, article in enumerate(wp_articles, 1):
        wp_id = article['ID']
        title = article['post_title']
        slug = article['post_name']
        url = f"https://ecopackpro.ru/{slug}/"
        post_date = str(article['post_date'])
        post_modified = str(article['post_modified'])
        
        # Нормализуем название для поиска
        normalized_title = normalize_keyword(title)
        # Убираем все после двоеточия
        normalized_title = re.sub(r':.*$', '', normalized_title).strip()
        
        # Ищем по эталонному списку ключевых слов
        source_id = None
        matched_keyword = None
        
        # Сначала пытаемся найти точное совпадение
        for keyword in KEYWORDS_LIST:
            normalized_keyword = normalize_keyword(keyword)
            if normalized_keyword in normalized_title or normalized_title in normalized_keyword:
                if normalized_keyword in sources:
                    source_id = sources[normalized_keyword]
                    matched_keyword = keyword
                    break
        
        # Если не нашли, ищем частичное совпадение
        if not source_id:
            for keyword in KEYWORDS_LIST:
                normalized_keyword = normalize_keyword(keyword)
                # Разбиваем на слова и проверяем совпадение ключевых слов
                title_words = set(normalized_title.split())
                keyword_words = set(normalized_keyword.split())
                
                # Если хотя бы 70% слов совпадают
                if len(title_words) > 0 and len(keyword_words) > 0:
                    common_words = title_words & keyword_words
                    similarity = len(common_words) / max(len(title_words), len(keyword_words))
                    
                    if similarity >= 0.7:
                        if normalized_keyword in sources:
                            source_id = sources[normalized_keyword]
                            matched_keyword = keyword
                            break
        
        # Если все еще не нашли, ищем в БД по keyword/title напрямую
        if not source_id:
            conn_check = sqlite3.connect(PROJECT_DB_PATH)
            cursor_check = conn_check.cursor()
            
            cursor_check.execute("""
            SELECT id, keyword
            FROM articles
            WHERE LOWER(keyword) = ? OR LOWER(title) = ?
            """, (normalized_title, normalized_title))
            
            result = cursor_check.fetchone()
            if result:
                source_id = result[0]
                matched_keyword = result[1]
            
            conn_check.close()
        
        # Сохраняем в БД
        cursor.execute("""
        INSERT INTO published_articles 
        (wp_post_id, title, slug, url, post_date, post_modified, export_date, source_article_id, http_status, last_checked)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        """, (wp_id, title, slug, url, post_date, post_modified, export_date, source_id, 200, export_date))
        
        if source_id:
            linked_count += 1
            status_icon = "✅"
            source_text = f"ID {source_id}"
        else:
            not_found_count += 1
            status_icon = "❌"
            source_text = "НЕ НАЙДЕН"
        
        display_title = title[:77] + "..." if len(title) > 80 else title
        print(f"{idx:<4} {wp_id:<7} {status_icon} {source_text:<10} {display_title}")
        
        if matched_keyword and source_id:
            print(f"     🔗 Связано с: '{matched_keyword}'")
    
    conn.commit()
    print("="*120)
    
    print(f"\n✅ Сохранено в БД: {linked_count + not_found_count} статей")
    print(f"✅ Связано с исходниками: {linked_count}")
    print(f"❌ Не найдено исходников: {not_found_count}\n")
    
    # Если есть несвязанные, ищем причину
    if not_found_count > 0:
        print("="*120)
        print("🔍 АНАЛИЗ НЕСВЯЗАННЫХ СТАТЕЙ".center(120))
        print("="*120 + "\n")
        
        cursor.execute("""
        SELECT wp_post_id, title
        FROM published_articles
        WHERE source_article_id IS NULL
        ORDER BY wp_post_id
        """)
        
        unlinked = cursor.fetchall()
        
        for wp_id, title in unlinked:
            print(f"❌ WP ID {wp_id}: {title}")
            normalized = normalize_keyword(title)
            normalized = re.sub(r':.*$', '', normalized).strip()
            print(f"   🔍 Нормализованное название: '{normalized}'")
            
            # Ищем похожие ключевые слова
            print(f"   🔍 Поиск похожих ключевых слов...")
            for keyword in KEYWORDS_LIST:
                normalized_keyword = normalize_keyword(keyword)
                if any(word in normalized for word in normalized_keyword.split() if len(word) > 3):
                    print(f"      ⚠️  Похоже на: '{keyword}'")
            print()
    
    conn.close()
    
    return linked_count, not_found_count

def create_manual_links():
    """Создает ручные связи для проблемных статей"""
    print("="*120)
    print("🔧 СОЗДАНИЕ РУЧНЫХ СВЯЗЕЙ ДЛЯ ПРОБЛЕМНЫХ СТАТЕЙ".center(120))
    print("="*120 + "\n")
    
    # Вручную создаем маппинг для статей, которые не нашлись автоматически
    manual_mappings = {
        # WP_ID: keyword_from_list
        7911: "конверты с воздушной подушкой",
        7912: "конверты с воздушной прослойкой",
        7913: "крафтовые пакеты с воздушной подушкой",
        7921: "пузырчатые пакеты ВПП",
        7923: "коробки для отправки",
        7945: "курьерские пакеты черно-белые без логотипа А4",
        7949: "зип пакеты с бегунком купить Москва",
    }
    
    conn = sqlite3.connect(PROJECT_DB_PATH)
    cursor = conn.cursor()
    
    updated_count = 0
    
    for wp_id, keyword in manual_mappings.items():
        normalized_keyword = normalize_keyword(keyword)
        
        # Ищем source_id по ключевому слову
        cursor.execute("""
        SELECT id, keyword
        FROM articles
        WHERE LOWER(keyword) = ? OR LOWER(title) = ?
        """, (normalized_keyword, normalized_keyword))
        
        result = cursor.fetchone()
        
        if result:
            source_id, source_keyword = result
            
            # Обновляем связь
            cursor.execute("""
            UPDATE published_articles
            SET source_article_id = ?
            WHERE wp_post_id = ?
            """, (source_id, wp_id))
            
            if cursor.rowcount > 0:
                updated_count += 1
                print(f"✅ WP ID {wp_id}: связана с исходником ID {source_id} ('{source_keyword}')")
        else:
            print(f"❌ WP ID {wp_id}: исходник '{keyword}' не найден в БД")
    
    conn.commit()
    conn.close()
    
    print(f"\n✅ Обновлено ручных связей: {updated_count}\n")
    return updated_count

def verify_all_50():
    """Проверяет, что все 50 статей связаны"""
    print("="*120)
    print("📊 ФИНАЛЬНАЯ ПРОВЕРКА: ВСЕ 50 СТАТЕЙ".center(120))
    print("="*120 + "\n")
    
    conn = sqlite3.connect(PROJECT_DB_PATH)
    cursor = conn.cursor()
    
    cursor.execute("""
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN source_article_id IS NOT NULL THEN 1 ELSE 0 END) as linked,
        SUM(CASE WHEN source_article_id IS NULL THEN 1 ELSE 0 END) as unlinked
    FROM published_articles
    WHERE wp_post_id >= 7907
    """)
    
    total, linked, unlinked = cursor.fetchone()
    
    print(f"📝 Всего статей в БД: {total}")
    print(f"✅ Связано с исходниками: {linked} ({linked*100//total if total > 0 else 0}%)")
    print(f"❌ Без связи: {unlinked}")
    
    # Показываем все статьи
    cursor.execute("""
    SELECT 
        pa.wp_post_id,
        pa.title,
        pa.url,
        pa.source_article_id,
        a.keyword as source_keyword
    FROM published_articles pa
    LEFT JOIN articles a ON pa.source_article_id = a.id
    WHERE pa.wp_post_id >= 7907
    ORDER BY pa.wp_post_id
    """)
    
    results = cursor.fetchall()
    
    print("\n" + "="*120)
    print(f"{'№':<4} {'WP ID':<7} {'ИСХОДНИК':<15} {'НАЗВАНИЕ':<80}")
    print("="*120)
    
    for idx, (wp_id, title, url, source_id, source_keyword) in enumerate(results, 1):
        if source_id:
            status = f"✅ ID {source_id}"
        else:
            status = "❌ НЕТ"
        
        display_title = title[:77] + "..." if len(title) > 80 else title
        print(f"{idx:<4} {wp_id:<7} {status:<15} {display_title}")
    
    print("="*120 + "\n")
    
    conn.close()
    
    return total, linked, unlinked

def main():
    print("\n" + "="*120)
    print("🚀 СВЯЗЫВАНИЕ ВСЕХ 50 СТАТЕЙ С ИСХОДНИКАМИ - СТАРТ".center(120))
    print("="*120)
    
    # Первый проход - автоматическое связывание
    linked, not_found = link_all_articles()
    
    # Если есть несвязанные - создаем ручные связи
    if not_found > 0:
        print(f"\n⚠️  Обнаружено {not_found} несвязанных статей. Создаю ручные связи...\n")
        manual_linked = create_manual_links()
        linked += manual_linked
        not_found -= manual_linked
    
    # Финальная проверка
    total, final_linked, final_unlinked = verify_all_50()
    
    # Итоговое сообщение
    print("="*120)
    print("📊 ИТОГОВЫЙ РЕЗУЛЬТАТ".center(120))
    print("="*120)
    print(f"📝 Всего статей: {total}")
    print(f"✅ Связано с исходниками: {final_linked} ({final_linked*100//total if total > 0 else 0}%)")
    print(f"❌ Без связи: {final_unlinked}")
    
    if final_unlinked == 0 and total == 50:
        print("\n" + "="*120)
        print("🎉 УСПЕХ! ВСЕ 50 СТАТЕЙ СВЯЗАНЫ С ИСХОДНИКАМИ!".center(120))
        print("="*120)
        return True
    else:
        print("\n" + "="*120)
        print(f"⚠️  ВНИМАНИЕ! Не все статьи связаны. Требуется дополнительный анализ.".center(120))
        print("="*120)
        return False

if __name__ == "__main__":
    success = main()
    exit(0 if success else 1)

