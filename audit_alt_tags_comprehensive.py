#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector
import re
from datetime import datetime

# Список ключевых слов для поиска статей
KEYWORDS = [
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

def connect_to_database():
    """Подключение к базе данных"""
    return mysql.connector.connect(
        host='localhost',
        user='m1shqamai2_worp6',
        password='9nUQkM*Q2cnvy379',
        database='m1shqamai2_worp6',
        charset='utf8mb4'
    )

def find_articles_by_keywords(cursor):
    """Поиск статей по ключевым словам"""
    articles = []
    
    for i, keyword in enumerate(KEYWORDS, 1):
        # Ищем статьи по заголовку
        cursor.execute("""
            SELECT ID, post_title, post_status 
            FROM wp_posts 
            WHERE post_type = 'post' 
            AND post_status = 'draft' 
            AND post_title LIKE %s
            ORDER BY ID
        """, (f'%{keyword}%',))
        
        results = cursor.fetchall()
        if results:
            result = results[0]  # Берем первый результат
            articles.append({
                'number': i,
                'keyword': keyword,
                'id': result[0],
                'title': result[1],
                'status': result[2]
            })
            print(f"✅ {i:2d}. ID {result[0]:4d}: {result[1]}")
        else:
            articles.append({
                'number': i,
                'keyword': keyword,
                'id': None,
                'title': 'НЕ НАЙДЕНА',
                'status': 'NOT_FOUND'
            })
            print(f"❌ {i:2d}. НЕ НАЙДЕНА: {keyword}")
    
    return articles

def extract_images_from_content(content):
    """Извлечение всех изображений из контента"""
    # Паттерны для поиска изображений
    patterns = [
        r'<img[^>]*>',  # Обычные img теги
        r'<figure[^>]*>.*?</figure>',  # Figure блоки
    ]
    
    images = []
    
    for pattern in patterns:
        matches = re.finditer(pattern, content, re.DOTALL | re.IGNORECASE)
        for match in matches:
            img_html = match.group()
            images.append({
                'html': img_html,
                'start': match.start(),
                'end': match.end()
            })
    
    return images

def analyze_image_alt(img_html, article_title, keyword):
    """Анализ alt-атрибута изображения"""
    # Извлечение alt атрибута
    alt_match = re.search(r'alt\s*=\s*["\']([^"\']*)["\']', img_html, re.IGNORECASE)
    current_alt = alt_match.group(1) if alt_match else ""
    
    # Определение корректного alt-тега
    correct_alt = keyword.lower()
    
    # Проверка корректности
    is_correct = current_alt.lower() == correct_alt
    
    return {
        'current_alt': current_alt,
        'correct_alt': correct_alt,
        'is_correct': is_correct,
        'needs_fix': not is_correct or not current_alt
    }

def fix_image_alt(img_html, correct_alt):
    """Исправление alt-атрибута изображения"""
    # Если alt уже есть, заменяем его
    if re.search(r'alt\s*=', img_html, re.IGNORECASE):
        fixed_html = re.sub(
            r'alt\s*=\s*["\'][^"\']*["\']',
            f'alt="{correct_alt}"',
            img_html,
            flags=re.IGNORECASE
        )
    else:
        # Если alt нет, добавляем его в img тег
        fixed_html = re.sub(
            r'(<img[^>]*?)(/?>)',
            rf'\1 alt="{correct_alt}"\2',
            img_html,
            flags=re.IGNORECASE
        )
    
    return fixed_html

def audit_article_alt_tags(cursor, article):
    """Аудит alt-тегов в конкретной статье"""
    if not article['id']:
        return {
            'total_images': 0,
            'images_with_alt': 0,
            'images_correct_alt': 0,
            'images_to_fix': 0,
            'fixed_images': []
        }
    
    # Получаем контент статьи
    cursor.execute("SELECT post_content FROM wp_posts WHERE ID = %s", (article['id'],))
    result = cursor.fetchone()
    if not result:
        return None
    
    content = result[0]
    
    # Извлекаем изображения
    images = extract_images_from_content(content)
    
    total_images = len(images)
    images_with_alt = 0
    images_correct_alt = 0
    images_to_fix = 0
    fixed_images = []
    
    new_content = content
    
    print(f"\n📋 Статья {article['number']:2d}: {article['title']}")
    print(f"   ID: {article['id']}, Ключевое слово: {article['keyword']}")
    print(f"   Найдено изображений: {total_images}")
    
    # Анализируем каждое изображение
    for i, img in enumerate(images, 1):
        analysis = analyze_image_alt(img['html'], article['title'], article['keyword'])
        
        print(f"   🖼️  Изображение {i}:")
        print(f"      Текущий alt: '{analysis['current_alt']}'")
        print(f"      Корректный alt: '{analysis['correct_alt']}'")
        print(f"      Статус: {'✅ Корректный' if analysis['is_correct'] else '❌ Требует исправления'}")
        
        if analysis['current_alt']:
            images_with_alt += 1
        
        if analysis['is_correct']:
            images_correct_alt += 1
        else:
            images_to_fix += 1
            
            # Исправляем alt-тег
            fixed_html = fix_image_alt(img['html'], analysis['correct_alt'])
            new_content = new_content.replace(img['html'], fixed_html)
            
            fixed_images.append({
                'image_number': i,
                'old_alt': analysis['current_alt'],
                'new_alt': analysis['correct_alt']
            })
            
            print(f"      🔧 Исправлено: '{analysis['current_alt']}' → '{analysis['correct_alt']}'")
    
    # Если были исправления, обновляем статью
    if fixed_images:
        cursor.execute("UPDATE wp_posts SET post_content = %s WHERE ID = %s", (new_content, article['id']))
        print(f"   💾 Статья обновлена в базе данных")
    
    return {
        'total_images': total_images,
        'images_with_alt': images_with_alt,
        'images_correct_alt': images_correct_alt,
        'images_to_fix': images_to_fix,
        'fixed_images': fixed_images
    }

def main():
    """Основная функция аудита"""
    print("🔍 АУДИТ ALT-ТЕГОВ ВО ВСЕХ 50 СТАТЬЯХ")
    print("=" * 60)
    
    # Подключение к БД
    conn = connect_to_database()
    cursor = conn.cursor()
    
    try:
        # Поиск статей
        print("\n📚 ПОИСК СТАТЕЙ ПО КЛЮЧЕВЫМ СЛОВАМ:")
        articles = find_articles_by_keywords(cursor)
        
        # Аудит каждой статьи
        print("\n🔍 АУДИТ ALT-ТЕГОВ:")
        print("=" * 60)
        
        total_stats = {
            'total_articles': 0,
            'articles_found': 0,
            'total_images': 0,
            'images_with_alt': 0,
            'images_correct_alt': 0,
            'images_to_fix': 0,
            'fixed_images': 0
        }
        
        for article in articles:
            total_stats['total_articles'] += 1
            
            if article['id']:
                total_stats['articles_found'] += 1
                
                stats = audit_article_alt_tags(cursor, article)
                if stats:
                    total_stats['total_images'] += stats['total_images']
                    total_stats['images_with_alt'] += stats['images_with_alt']
                    total_stats['images_correct_alt'] += stats['images_correct_alt']
                    total_stats['images_to_fix'] += stats['images_to_fix']
                    total_stats['fixed_images'] += len(stats['fixed_images'])
        
        # Сохранение изменений
        conn.commit()
        
        # Итоговый отчет
        print("\n" + "=" * 60)
        print("📊 ИТОГОВЫЙ ОТЧЕТ ПО АУДИТУ ALT-ТЕГОВ")
        print("=" * 60)
        
        print(f"📚 Всего статей в списке: {total_stats['total_articles']}")
        print(f"✅ Найдено статей: {total_stats['articles_found']}")
        print(f"❌ Не найдено статей: {total_stats['total_articles'] - total_stats['articles_found']}")
        print()
        print(f"🖼️  Всего изображений: {total_stats['total_images']}")
        print(f"📝 С alt-тегами: {total_stats['images_with_alt']}")
        print(f"✅ С корректными alt-тегами: {total_stats['images_correct_alt']}")
        print(f"❌ Требовали исправления: {total_stats['images_to_fix']}")
        print(f"🔧 Исправлено: {total_stats['fixed_images']}")
        
        if total_stats['total_images'] > 0:
            correct_percentage = (total_stats['images_correct_alt'] / total_stats['total_images']) * 100
            print(f"📊 Процент корректных alt-тегов: {correct_percentage:.1f}%")
        
        print("\n✅ АУДИТ ЗАВЕРШЕН УСПЕШНО!")
        
    except Exception as e:
        print(f"❌ Ошибка: {e}")
        conn.rollback()
    
    finally:
        cursor.close()
        conn.close()

if __name__ == "__main__":
    main()
