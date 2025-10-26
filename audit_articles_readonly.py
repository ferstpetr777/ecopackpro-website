#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
БЕЗОПАСНЫЙ АУДИТ СТАТЕЙ - ТОЛЬКО ЧТЕНИЕ, БЕЗ ИЗМЕНЕНИЙ!
"""
import mysql.connector
import re
from html.parser import HTMLParser

# Список ключевых слов для проверки
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

class ImageExtractor(HTMLParser):
    """Парсер для извлечения изображений из HTML"""
    def __init__(self):
        super().__init__()
        self.images = []
    
    def handle_starttag(self, tag, attrs):
        if tag == 'img':
            img_data = dict(attrs)
            self.images.append(img_data)

def connect_db():
    """Подключение к БД"""
    return mysql.connector.connect(
        host='localhost',
        user='m1shqamai2_worp6',
        password='9nUQkM*Q2cnvy379',
        database='m1shqamai2_worp6',
        charset='utf8mb4'
    )

def get_article_info(cursor, post_id):
    """Получить полную информацию о статье"""
    # Основная информация о посте
    cursor.execute("""
        SELECT ID, post_title, post_content, post_status
        FROM wp_posts
        WHERE ID = %s
    """, (post_id,))
    post = cursor.fetchone()
    
    if not post:
        return None
    
    # Featured Image ID
    cursor.execute("""
        SELECT meta_value
        FROM wp_postmeta
        WHERE post_id = %s AND meta_key = '_thumbnail_id'
    """, (post_id,))
    featured_img_result = cursor.fetchone()
    featured_img_id = featured_img_result[0] if featured_img_result else None
    
    # Информация о Featured Image
    featured_img_info = None
    if featured_img_id:
        cursor.execute("""
            SELECT post_title, guid
            FROM wp_posts
            WHERE ID = %s
        """, (featured_img_id,))
        img_post = cursor.fetchone()
        
        # Alt текст изображения
        cursor.execute("""
            SELECT meta_value
            FROM wp_postmeta
            WHERE post_id = %s AND meta_key = '_wp_attachment_image_alt'
        """, (featured_img_id,))
        alt_result = cursor.fetchone()
        
        featured_img_info = {
            'id': featured_img_id,
            'title': img_post[0] if img_post else '',
            'url': img_post[1] if img_post else '',
            'alt': alt_result[0] if alt_result else ''
        }
    
    # Извлекаем изображения из контента
    parser = ImageExtractor()
    parser.feed(post[2])
    content_images = parser.images
    
    return {
        'id': post[0],
        'title': post[1],
        'content': post[2],
        'status': post[3],
        'featured_image': featured_img_info,
        'content_images': content_images
    }

def check_keyword_match(title, keyword):
    """Проверка соответствия заголовка ключевому слову"""
    title_lower = title.lower().strip()
    keyword_lower = keyword.lower().strip()
    
    # Точное совпадение
    if title_lower == keyword_lower:
        return 'exact'
    
    # Содержит ключевое слово
    if keyword_lower in title_lower:
        return 'contains'
    
    # Похожее (удаляем знаки препинания для сравнения)
    title_clean = re.sub(r'[^\w\s]', '', title_lower)
    keyword_clean = re.sub(r'[^\w\s]', '', keyword_lower)
    if keyword_clean in title_clean:
        return 'similar'
    
    return 'no_match'

def main():
    print("=" * 80)
    print("АУДИТ СТАТЕЙ ECOPACKPRO.RU - БЕЗОПАСНЫЙ РЕЖИМ (ТОЛЬКО ЧТЕНИЕ)")
    print("=" * 80)
    print()
    
    conn = connect_db()
    cursor = conn.cursor()
    
    # Получаем статьи 7907-7956
    cursor.execute("""
        SELECT ID, post_title
        FROM wp_posts
        WHERE ID BETWEEN 7907 AND 7956
        AND post_type = 'post'
        ORDER BY ID
    """)
    
    articles = cursor.fetchall()
    print(f"✅ Найдено статей: {len(articles)}")
    print()
    
    results = []
    issues_count = 0
    
    for idx, (post_id, post_title) in enumerate(articles, 1):
        print(f"\n{'='*80}")
        print(f"СТАТЬЯ #{idx}: ID {post_id}")
        print(f"{'='*80}")
        
        # Получаем полную информацию
        info = get_article_info(cursor, post_id)
        if not info:
            continue
        
        print(f"📝 Заголовок: {info['title']}")
        print(f"📊 Статус: {info['status']}")
        print(f"📏 Длина контента: {len(info['content'])} символов")
        
        # Проверяем соответствие ключевому слову
        keyword = KEYWORDS[idx-1] if idx <= len(KEYWORDS) else None
        if keyword:
            match_type = check_keyword_match(info['title'], keyword)
            print(f"\n🔍 Ключевое слово: {keyword}")
            if match_type == 'exact':
                print(f"   ✅ ТОЧНОЕ СОВПАДЕНИЕ")
            elif match_type == 'contains':
                print(f"   ⚠️  Заголовок содержит ключевое слово")
            elif match_type == 'similar':
                print(f"   ⚠️  Похоже на ключевое слово")
            else:
                print(f"   ❌ НЕ СОВПАДАЕТ!")
                issues_count += 1
        
        # Проверяем Featured Image
        print(f"\n🖼️  ГЛАВНОЕ ИЗОБРАЖЕНИЕ (Featured Image):")
        if info['featured_image']:
            fi = info['featured_image']
            print(f"   ID: {fi['id']}")
            print(f"   Title: {fi['title']}")
            print(f"   Alt: {fi['alt'] if fi['alt'] else '❌ НЕТ ALT-ТЕГА!'}")
            
            if not fi['alt']:
                issues_count += 1
            else:
                # Проверяем соответствие alt ключевому слову
                if keyword:
                    alt_match = check_keyword_match(fi['alt'], keyword)
                    if alt_match in ['exact', 'contains', 'similar']:
                        print(f"   ✅ Alt соответствует ключевому слову")
                    else:
                        print(f"   ⚠️  Alt НЕ содержит ключевое слово")
        else:
            print(f"   ❌ НЕТ ГЛАВНОГО ИЗОБРАЖЕНИЯ!")
            issues_count += 1
        
        # Проверяем изображения в контенте
        print(f"\n📷 ИЗОБРАЖЕНИЯ В КОНТЕНТЕ: {len(info['content_images'])}")
        if info['content_images']:
            for i, img in enumerate(info['content_images'][:5], 1):  # Показываем первые 5
                src = img.get('src', '')
                alt = img.get('alt', '')
                print(f"   {i}. {src[:60]}...")
                if alt:
                    print(f"      Alt: {alt[:60]}...")
                else:
                    print(f"      ❌ НЕТ ALT-ТЕГА!")
                    issues_count += 1
            
            if len(info['content_images']) > 5:
                print(f"   ... и ещё {len(info['content_images']) - 5} изображений")
        
        # Проверяем видимость Featured Image в контенте
        if info['featured_image'] and info['content_images']:
            featured_url = info['featured_image']['url']
            featured_id = str(info['featured_image']['id'])
            
            found_in_content = False
            for img in info['content_images']:
                img_src = img.get('src', '')
                if featured_id in img_src or featured_url in img_src:
                    found_in_content = True
                    break
            
            if found_in_content:
                print(f"\n   ✅ Главное изображение ВИДНО в контенте")
            else:
                print(f"\n   ⚠️  Главное изображение НЕ НАЙДЕНО в контенте")
        
        results.append({
            'id': post_id,
            'title': info['title'],
            'keyword': keyword,
            'match': match_type if keyword else 'n/a',
            'featured_img': info['featured_image'] is not None,
            'featured_alt': bool(info['featured_image'] and info['featured_image']['alt']),
            'content_images_count': len(info['content_images']),
            'images_without_alt': sum(1 for img in info['content_images'] if not img.get('alt'))
        })
    
    # ФИНАЛЬНЫЙ ОТЧЕТ
    print(f"\n\n{'='*80}")
    print("📊 ФИНАЛЬНЫЙ ОТЧЕТ")
    print(f"{'='*80}\n")
    
    print(f"Всего статей проверено: {len(results)}")
    print(f"Всего проблем найдено: {issues_count}")
    print()
    
    # Статистика по проблемам
    no_featured = sum(1 for r in results if not r['featured_img'])
    no_featured_alt = sum(1 for r in results if r['featured_img'] and not r['featured_alt'])
    keyword_mismatch = sum(1 for r in results if r['match'] == 'no_match')
    images_no_alt_total = sum(r['images_without_alt'] for r in results)
    
    print("ПРОБЛЕМЫ:")
    print(f"  ❌ Статей без главного изображения: {no_featured}")
    print(f"  ❌ Главных изображений без alt: {no_featured_alt}")
    print(f"  ❌ Несовпадений заголовков с ключевыми словами: {keyword_mismatch}")
    print(f"  ❌ Изображений в контенте без alt: {images_no_alt_total}")
    print()
    
    # Сохраняем отчет
    with open('audit_report_readonly.txt', 'w', encoding='utf-8') as f:
        f.write("ДЕТАЛЬНЫЙ ОТЧЕТ АУДИТА\n")
        f.write("=" * 80 + "\n\n")
        for r in results:
            f.write(f"ID: {r['id']}\n")
            f.write(f"Заголовок: {r['title']}\n")
            f.write(f"Ключевое слово: {r['keyword']}\n")
            f.write(f"Совпадение: {r['match']}\n")
            f.write(f"Главное изображение: {'Да' if r['featured_img'] else 'Нет'}\n")
            f.write(f"Alt у главного: {'Да' if r['featured_alt'] else 'Нет'}\n")
            f.write(f"Изображений в контенте: {r['content_images_count']}\n")
            f.write(f"Без alt: {r['images_without_alt']}\n")
            f.write("\n" + "-" * 80 + "\n\n")
    
    print("✅ Детальный отчет сохранен: audit_report_readonly.txt")
    print()
    print("⚠️  ВАЖНО: Никакие изменения НЕ БЫЛИ внесены в базу данных!")
    print("    Это был ТОЛЬКО аналитический аудит.")
    
    cursor.close()
    conn.close()

if __name__ == '__main__':
    main()
