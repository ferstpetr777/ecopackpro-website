#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для исправления HTML статьи 7939
"""

import mysql.connector
import re

# Настройки подключения к БД
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6',
    'charset': 'utf8mb4'
}

def fix_html(html):
    """Исправляет HTML код статьи"""
    
    # 1. Убираем все двойные и четверные обратные слэши
    html = html.replace('\\\\\\\\', '')
    html = html.replace('\\\\', '')
    
    # 2. Исправляем таблицы - убираем лишние <p> теги внутри таблиц
    # Паттерн: <p> внутри thead/tbody/tr
    html = re.sub(r'<thead>\s*<p>\\*\s*', '<thead>\n', html)
    html = re.sub(r'</thead>\s*<p>\\*\s*', '</thead>\n', html)
    html = re.sub(r'<tbody>\s*<p>\\*\s*', '<tbody>\n', html)
    html = re.sub(r'</tbody>\s*<p>\\*\s*', '</tbody>\n', html)
    html = re.sub(r'<tr>\s*<p>\\*\s*', '<tr>\n', html)
    html = re.sub(r'</tr>\s*<p>\\*\s*', '</tr>\n', html)
    html = re.sub(r'<th>([^<]+)</th>\s*<p>\\*\s*', r'<th>\1</th>\n', html)
    html = re.sub(r'<td>([^<]+)</td>\s*<p>\\*\s*', r'<td>\1</td>\n', html)
    
    # Убираем <p> перед и после </thead>, </tbody>, </table>
    html = re.sub(r'<p>\\*\s*</thead>', '</thead>', html)
    html = re.sub(r'<p>\\*\s*</tbody>', '</tbody>', html)
    html = re.sub(r'<p>\\*\s*</table>', '</table>', html)
    html = re.sub(r'</table>\s*<p>\\*\s*', '</table>\n\n', html)
    
    # 3. Убираем пустые <p>\\</p> и <p>\n</p>
    html = re.sub(r'<p>\\+\s*</p>', '', html)
    html = re.sub(r'<p>\s*</p>', '', html)
    
    # 4. Исправляем <br /> с обратными слэшами
    html = re.sub(r'\\+<br />', '<br>', html)
    html = re.sub(r'<br />\\+', '<br>', html)
    
    # 5. Убираем лишние \\ между текстом и тегами
    html = re.sub(r'\\+\n', '\n', html)
    html = re.sub(r'\n\\+', '\n', html)
    
    # 6. Исправляем закрытые теги списков
    html = re.sub(r'<p>\\\\\s*</ol>', '</ol>', html)
    html = re.sub(r'</ol>\s*<p>\\\\', '</ol>\n', html)
    
    # 7. Убираем \\ в конце строк внутри параграфов
    html = re.sub(r'\\+<br', '<br', html)
    
    # 8. Очищаем множественные пустые строки
    html = re.sub(r'\n\n+', '\n\n', html)
    
    return html

def main():
    print("=" * 70)
    print("ИСПРАВЛЕНИЕ HTML СТАТЬИ 7939")
    print("=" * 70)
    
    try:
        # Подключение к БД
        print("\n1. Подключение к базе данных...")
        conn = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor()
        print("✅ Подключено к БД")
        
        # Извлекаем текущий HTML
        print("\n2. Извлечение текущего HTML из БД...")
        cursor.execute("""
            SELECT post_content 
            FROM wp_posts 
            WHERE ID = 7939
        """)
        result = cursor.fetchone()
        
        if not result:
            print("❌ Статья ID=7939 не найдена!")
            return
        
        old_html = result[0]
        print(f"✅ Извлечено {len(old_html)} символов")
        
        # Показываем примеры ошибок
        print("\n3. Обнаруженные ошибки в HTML:")
        errors_count = old_html.count('\\\\\\\\')
        print(f"   - Четверные слэши (\\\\\\\\): {errors_count}")
        errors_count2 = old_html.count('\\\\')
        print(f"   - Двойные слэши (\\\\): {errors_count2}")
        errors_count3 = old_html.count('<p>\\\\')
        print(f"   - Пустые параграфы с слэшами: {errors_count3}")
        
        # Исправляем HTML
        print("\n4. Исправление HTML...")
        fixed_html = fix_html(old_html)
        print(f"✅ Исправлено. Новый размер: {len(fixed_html)} символов")
        
        # Показываем что исправлено
        print("\n5. Результат исправления:")
        errors_count_after = fixed_html.count('\\\\\\\\')
        print(f"   - Четверные слэши (\\\\\\\\): {errors_count_after}")
        errors_count2_after = fixed_html.count('\\\\')
        print(f"   - Двойные слэши (\\\\): {errors_count2_after}")
        
        # Обновляем в БД
        print("\n6. Обновление статьи в базе данных...")
        cursor.execute("""
            UPDATE wp_posts 
            SET post_content = %s,
                post_modified = NOW(),
                post_modified_gmt = NOW()
            WHERE ID = 7939
        """, (fixed_html,))
        
        conn.commit()
        print(f"✅ Статья обновлена в БД (затронуто строк: {cursor.rowcount})")
        
        # Закрываем подключение
        cursor.close()
        conn.close()
        
        print("\n" + "=" * 70)
        print("✅ СТАТЬЯ УСПЕШНО ИСПРАВЛЕНА!")
        print("=" * 70)
        print(f"\nПроверьте статью на сайте:")
        print("https://ecopackpro.ru/pochtovye-korobki-razmera-xl-530360220/")
        
    except Exception as e:
        print(f"\n❌ ОШИБКА: {e}")
        import traceback
        traceback.print_exc()

if __name__ == '__main__':
    main()

