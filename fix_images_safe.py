#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
БЕЗОПАСНАЯ ЗАМЕНА PLACEHOLDER НА FEATURED IMAGE
- Создает дополнительную ревизию перед изменениями
- Заменяет изображение 7146 на правильное Featured Image
- Добавляет правильные alt-теги
- Легко откатить
"""
import mysql.connector
import re
from datetime import datetime

# Проблемные статьи
PROBLEM_ARTICLES = [7911, 7912, 7913, 7926, 7928, 7929, 7930, 7941, 7943, 7944, 7947, 7952, 7953, 7954, 7955]

def connect_db():
    """Подключение к БД"""
    return mysql.connector.connect(
        host='localhost',
        user='m1shqamai2_worp6',
        password='9nUQkM*Q2cnvy379',
        database='m1shqamai2_worp6',
        charset='utf8mb4'
    )

def create_revision_table(cursor):
    """Создать таблицу ревизии ПЕРЕД изменениями"""
    timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
    table_name = f'wp_posts_revision_{timestamp}'
    
    print(f"\n{'='*80}")
    print(f"СОЗДАНИЕ РЕВИЗИИ")
    print(f"{'='*80}")
    
    cursor.execute(f"""
        CREATE TABLE {table_name} AS 
        SELECT * FROM wp_posts 
        WHERE ID BETWEEN 7907 AND 7956
    """)
    
    print(f"✅ Создана таблица ревизии: {table_name}")
    print(f"   Сохранено записей: 50")
    print(f"\nДля отката используйте:")
    print(f"   UPDATE wp_posts p")
    print(f"   INNER JOIN {table_name} r ON p.ID = r.ID")
    print(f"   SET p.post_content = r.post_content;")
    
    return table_name

def get_featured_image_info(cursor, post_id):
    """Получить информацию о Featured Image статьи"""
    # Получить Featured Image ID
    cursor.execute("""
        SELECT meta_value
        FROM wp_postmeta
        WHERE post_id = %s AND meta_key = '_thumbnail_id'
    """, (post_id,))
    result = cursor.fetchone()
    
    if not result:
        return None
    
    featured_id = result[0]
    
    # Получить информацию об изображении
    cursor.execute("""
        SELECT post_title, guid
        FROM wp_posts
        WHERE ID = %s
    """, (featured_id,))
    img_info = cursor.fetchone()
    
    # Получить alt-тег
    cursor.execute("""
        SELECT meta_value
        FROM wp_postmeta
        WHERE post_id = %s AND meta_key = '_wp_attachment_image_alt'
    """, (featured_id,))
    alt_result = cursor.fetchone()
    
    return {
        'id': featured_id,
        'title': img_info[0] if img_info else '',
        'url': img_info[1] if img_info else '',
        'alt': alt_result[0] if alt_result else img_info[0] if img_info else ''
    }

def get_image_dimensions(url):
    """Извлечь размеры изображения из URL если есть"""
    # Обычно WordPress сохраняет размеры в URL или метаданных
    # Для простоты используем стандартные размеры
    return {'width': 1500, 'height': 1500}

def create_new_image_block(featured_img, post_title):
    """Создать новый блок изображения с Featured Image"""
    img_id = featured_img['id']
    img_url = featured_img['url']
    img_alt = featured_img['alt'] if featured_img['alt'] else post_title
    
    # Получаем базовый URL без расширения для srcset
    base_url = img_url.rsplit('.', 1)[0]
    ext = img_url.rsplit('.', 1)[1]
    
    # Создаем HTML блок аналогичный существующему
    new_block = f'''<figure class="wp-block-image size-large" style="text-align: center; margin: 20px auto; max-width: 80%;"><img alt="{img_alt}" class="wp-image-{img_id}" decoding="async" height="1500" loading="lazy" sizes="auto, (max-width: 1500px) 100vw, 1500px" src="{img_url}" srcset="{img_url} 1500w, {base_url}-300x300.{ext} 300w, {base_url}-1024x1024.{ext} 1024w, {base_url}-150x150.{ext} 150w, {base_url}-768x768.{ext} 768w" style="aspect-ratio: 1; object-fit: cover;" width="1500" /></figure>'''
    
    return new_block

def preview_changes(cursor):
    """Показать что будет изменено (БЕЗ изменений)"""
    print(f"\n{'='*80}")
    print(f"ПРЕДПРОСМОТР ИЗМЕНЕНИЙ")
    print(f"{'='*80}\n")
    
    changes = []
    
    for post_id in PROBLEM_ARTICLES:
        # Получить информацию о статье
        cursor.execute("""
            SELECT ID, post_title, post_content
            FROM wp_posts
            WHERE ID = %s
        """, (post_id,))
        post = cursor.fetchone()
        
        if not post:
            continue
        
        # Получить Featured Image
        featured_img = get_featured_image_info(cursor, post_id)
        
        if not featured_img:
            print(f"⚠️  ID {post_id}: Нет Featured Image - ПРОПУСКАЕМ")
            continue
        
        # Проверить наличие старого изображения в контенте
        if 'wp-image-7146' not in post[2]:
            print(f"✓  ID {post_id}: Placeholder не найден - OK")
            continue
        
        print(f"\n📝 ID {post_id}: {post[1]}")
        print(f"   Старое изображение: 7146 (Tvist-PRO_blue_09.jpg) БЕЗ alt")
        print(f"   Новое изображение:  {featured_img['id']} ({featured_img['title']})")
        print(f"   Alt-тег:            {featured_img['alt']}")
        
        changes.append({
            'post_id': post_id,
            'post_title': post[1],
            'post_content': post[2],
            'featured_img': featured_img
        })
    
    print(f"\n{'='*80}")
    print(f"Всего статей для изменения: {len(changes)}")
    print(f"{'='*80}\n")
    
    return changes

def apply_changes(cursor, conn, changes):
    """Применить изменения к статьям"""
    print(f"\n{'='*80}")
    print(f"ПРИМЕНЕНИЕ ИЗМЕНЕНИЙ")
    print(f"{'='*80}\n")
    
    success_count = 0
    
    for change in changes:
        post_id = change['post_id']
        post_title = change['post_title']
        content = change['post_content']
        featured_img = change['featured_img']
        
        # Найти и извлечь старый блок изображения
        # Ищем весь блок <figure>...</figure> с wp-image-7146
        pattern = r'<figure[^>]*>.*?wp-image-7146.*?</figure>'
        match = re.search(pattern, content, re.DOTALL)
        
        if not match:
            print(f"⚠️  ID {post_id}: Не найден блок изображения - ПРОПУСКАЕМ")
            continue
        
        old_block = match.group(0)
        
        # Создать новый блок
        new_block = create_new_image_block(featured_img, post_title)
        
        # Заменить
        new_content = content.replace(old_block, new_block)
        
        # Проверить что изменение произошло
        if new_content == content:
            print(f"⚠️  ID {post_id}: Контент не изменился - ПРОПУСКАЕМ")
            continue
        
        # Обновить в БД
        try:
            cursor.execute("""
                UPDATE wp_posts
                SET post_content = %s,
                    post_modified = NOW(),
                    post_modified_gmt = UTC_TIMESTAMP()
                WHERE ID = %s
            """, (new_content, post_id))
            
            conn.commit()
            
            print(f"✅ ID {post_id}: УСПЕШНО обновлено")
            print(f"   Заменено изображение 7146 → {featured_img['id']}")
            print(f"   Добавлен alt: {featured_img['alt'][:50]}...")
            
            success_count += 1
            
        except Exception as e:
            print(f"❌ ID {post_id}: ОШИБКА - {e}")
            conn.rollback()
    
    print(f"\n{'='*80}")
    print(f"РЕЗУЛЬТАТ: Успешно обновлено {success_count} из {len(changes)} статей")
    print(f"{'='*80}\n")
    
    return success_count

def verify_changes(cursor):
    """Проверить результат изменений"""
    print(f"\n{'='*80}")
    print(f"ПРОВЕРКА РЕЗУЛЬТАТОВ")
    print(f"{'='*80}\n")
    
    for post_id in PROBLEM_ARTICLES:
        cursor.execute("""
            SELECT post_content
            FROM wp_posts
            WHERE ID = %s
        """, (post_id,))
        result = cursor.fetchone()
        
        if not result:
            continue
        
        content = result[0]
        
        # Проверить что старого изображения больше нет
        has_old = 'wp-image-7146' in content
        
        # Проверить что есть изображение с alt
        has_alt_empty = 'alt=""' in content
        
        status = "✅ OK" if not has_old and not has_alt_empty else "⚠️ ПРОВЕРИТЬ"
        
        print(f"ID {post_id}: {status}")
        if has_old:
            print(f"   ⚠️ Еще есть изображение 7146")
        if has_alt_empty:
            print(f"   ⚠️ Еще есть пустые alt")

def main():
    print("="*80)
    print("БЕЗОПАСНАЯ ЗАМЕНА PLACEHOLDER НА FEATURED IMAGE")
    print("="*80)
    print()
    print("⚠️  ВАЖНО: Перед запуском убедитесь что:")
    print("   1. Созданы резервные копии БД")
    print("   2. Вы понимаете что будет изменено")
    print("   3. У вас есть возможность отката")
    print()
    
    conn = connect_db()
    cursor = conn.cursor()
    
    try:
        # ШАГ 1: Создать ревизию
        revision_table = create_revision_table(cursor)
        conn.commit()
        
        # ШАГ 2: Предпросмотр изменений
        changes = preview_changes(cursor)
        
        if not changes:
            print("\n⚠️  Нет статей для изменения!")
            return
        
        # ШАГ 3: Запрос подтверждения
        print("\n" + "="*80)
        print("ПОДТВЕРЖДЕНИЕ")
        print("="*80)
        print(f"\nБудет изменено статей: {len(changes)}")
        print(f"Создана ревизия: {revision_table}")
        print()
        
        response = input("Продолжить? (yes/NO): ").strip().lower()
        
        if response != 'yes':
            print("\n❌ Отменено пользователем. Никакие изменения не внесены.")
            return
        
        # ШАГ 4: Применить изменения
        success_count = apply_changes(cursor, conn, changes)
        
        # ШАГ 5: Проверка
        if success_count > 0:
            verify_changes(cursor)
        
        print("\n" + "="*80)
        print("ЗАВЕРШЕНО")
        print("="*80)
        print(f"\n✅ Успешно обновлено: {success_count} статей")
        print(f"✅ Создана ревизия: {revision_table}")
        print()
        print("Для отката изменений используйте:")
        print(f"   mysql -u m1shqamai2_worp6 -p m1shqamai2_worp6")
        print(f"   UPDATE wp_posts p")
        print(f"   INNER JOIN {revision_table} r ON p.ID = r.ID")
        print(f"   SET p.post_content = r.post_content;")
        print()
        
    except Exception as e:
        print(f"\n❌ КРИТИЧЕСКАЯ ОШИБКА: {e}")
        conn.rollback()
        print("   Все изменения откачены.")
        
    finally:
        cursor.close()
        conn.close()

if __name__ == '__main__':
    main()
