#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector
from datetime import datetime

# Конфигурация базы данных WordPress
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

# 28 статей, требующих доработки мета-описаний
ARTICLES_TO_FIX = [
    7911, 7913, 7914, 7915, 7916, 7918, 7919, 7921, 7926, 7928, 7929, 7930,
    7932, 7933, 7934, 7935, 7936, 7937, 7938, 7939, 7940, 7941, 7943, 7944,
    7945, 7946, 7947, 7949
]

class DirectDBMetaDescriptionFixer:
    def __init__(self):
        self.db_config = DB_CONFIG
        self.fix_stats = {
            'total': 0,
            'success': 0,
            'failed': 0,
            'errors': []
        }
    
    def connect_to_database(self):
        """Подключение к базе данных MySQL"""
        try:
            connection = mysql.connector.connect(**self.db_config)
            return connection
        except mysql.connector.Error as e:
            print(f"❌ Ошибка подключения к базе данных: {e}")
            return None
    
    def get_article_meta(self, post_id):
        """Получение фокусного ключевого слова и текущего мета-описания"""
        connection = self.connect_to_database()
        if not connection:
            return None, None
        
        try:
            cursor = connection.cursor(dictionary=True)
            
            # Получаем фокусное ключевое слово и мета-описание
            cursor.execute("""
                SELECT meta_key, meta_value
                FROM wp_postmeta
                WHERE post_id = %s
                AND meta_key IN ('_yoast_wpseo_focuskw', '_yoast_wpseo_metadesc')
            """, (post_id,))
            
            meta_data = cursor.fetchall()
            meta_dict = {row['meta_key']: row['meta_value'] for row in meta_data}
            
            focus_keyword = meta_dict.get('_yoast_wpseo_focuskw', '')
            current_meta_description = meta_dict.get('_yoast_wpseo_metadesc', '')
            
            return focus_keyword, current_meta_description
            
        except mysql.connector.Error as e:
            print(f"❌ Ошибка получения мета данных для ID {post_id}: {e}")
            return None, None
        finally:
            connection.close()
    
    def update_meta_description_in_db(self, post_id, focus_keyword, new_meta_description):
        """Прямое обновление мета-описания в базе данных"""
        connection = self.connect_to_database()
        if not connection:
            return False
        
        try:
            cursor = connection.cursor()
            
            # Проверяем, существует ли мета запись
            cursor.execute("""
                SELECT meta_id
                FROM wp_postmeta
                WHERE post_id = %s AND meta_key = '_yoast_wpseo_metadesc'
            """, (post_id,))
            
            existing = cursor.fetchone()
            
            if existing:
                # Обновляем существующую запись
                cursor.execute("""
                    UPDATE wp_postmeta
                    SET meta_value = %s
                    WHERE post_id = %s AND meta_key = '_yoast_wpseo_metadesc'
                """, (new_meta_description, post_id))
            else:
                # Создаем новую запись
                cursor.execute("""
                    INSERT INTO wp_postmeta (post_id, meta_key, meta_value)
                    VALUES (%s, '_yoast_wpseo_metadesc', %s)
                """, (post_id, new_meta_description))
            
            connection.commit()
            return True
            
        except mysql.connector.Error as e:
            print(f"❌ Ошибка обновления мета-описания для ID {post_id}: {e}")
            connection.rollback()
            return False
        finally:
            connection.close()
    
    def fix_single_article_meta(self, post_id):
        """Исправление мета-описания одной статьи"""
        print(f"\n🔧 ИСПРАВЛЕНИЕ МЕТА-ОПИСАНИЯ ID {post_id}")
        print("-" * 60)
        
        # Получаем фокусное ключевое слово и текущее мета-описание
        focus_keyword, current_meta_description = self.get_article_meta(post_id)
        
        if not focus_keyword:
            print(f"❌ Отсутствует фокусное ключевое слово")
            return False, "Отсутствует фокусное ключевое слово"
        
        print(f"🎯 Фокусное ключевое слово: '{focus_keyword}'")
        print(f"📝 Текущее мета-описание: '{current_meta_description[:80]}...'")
        
        # Проверяем, начинается ли мета-описание с ключевого слова
        if current_meta_description.strip().lower().startswith(focus_keyword.lower()):
            print(f"✅ Мета-описание уже правильное!")
            return True, "Уже правильное"
        
        # Исправляем мета-описание
        new_meta_description = f"{focus_keyword} - {current_meta_description}"
        print(f"✨ Новое мета-описание: '{new_meta_description[:80]}...'")
        
        # Обновляем в базе данных
        if self.update_meta_description_in_db(post_id, focus_keyword, new_meta_description):
            print(f"✅ Мета-описание успешно обновлено в БД!")
            return True, "Успешно обновлено"
        else:
            print(f"❌ Ошибка обновления в БД")
            return False, "Ошибка обновления в БД"
    
    def fix_all_meta_descriptions(self):
        """Исправление всех 28 мета-описаний"""
        print("🔧 МАССОВОЕ ИСПРАВЛЕНИЕ МЕТА-ОПИСАНИЙ НАПРЯМУЮ В БД")
        print("=" * 80)
        print("Метод: Прямое обновление в MySQL базе данных")
        print("Критерий: Мета-описание должно начинаться с фокусного ключевого слова")
        print("=" * 80)
        
        self.fix_stats['total'] = len(ARTICLES_TO_FIX)
        
        for i, post_id in enumerate(ARTICLES_TO_FIX, 1):
            print(f"\n📋 {i}/{len(ARTICLES_TO_FIX)}")
            
            success, message = self.fix_single_article_meta(post_id)
            
            if success:
                self.fix_stats['success'] += 1
            else:
                self.fix_stats['failed'] += 1
                self.fix_stats['errors'].append(f"ID {post_id}: {message}")
        
        return self.fix_stats
    
    def print_fix_report(self):
        """Вывод отчета об исправлении"""
        print("\n" + "=" * 80)
        print("📊 ФИНАЛЬНЫЙ ОТЧЕТ ИСПРАВЛЕНИЯ МЕТА-ОПИСАНИЙ")
        print("=" * 80)
        
        print(f"📚 Всего статей: {self.fix_stats['total']}")
        print(f"✅ Успешно исправлено: {self.fix_stats['success']}")
        print(f"❌ Ошибки: {self.fix_stats['failed']}")
        
        if self.fix_stats['total'] > 0:
            success_rate = (self.fix_stats['success'] / self.fix_stats['total']) * 100
            print(f"📊 Процент успешности: {success_rate:.1f}%")
        
        if self.fix_stats['errors']:
            print(f"\n🚨 ОШИБКИ:")
            for error in self.fix_stats['errors']:
                print(f"  - {error}")
        
        # Сохраняем отчет в файл
        timestamp = datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
        report_filename = f"ОТЧЕТ_ИСПРАВЛЕНИЯ_МЕТА_ОПИСАНИЙ_БД_{timestamp}.md"
        
        with open(report_filename, 'w', encoding='utf-8') as f:
            f.write("# 🔧 ОТЧЕТ ИСПРАВЛЕНИЯ МЕТА-ОПИСАНИЙ (ПРЯМОЕ ОБНОВЛЕНИЕ БД)\n\n")
            f.write(f"**Дата:** {datetime.now().strftime('%d.%m.%Y %H:%M:%S')}\n\n")
            f.write(f"## 📊 СТАТИСТИКА\n\n")
            f.write(f"- **Всего статей:** {self.fix_stats['total']}\n")
            f.write(f"- **✅ Успешно исправлено:** {self.fix_stats['success']} ({success_rate:.1f}%)\n")
            f.write(f"- **❌ Ошибки:** {self.fix_stats['failed']}\n\n")
            
            if self.fix_stats['errors']:
                f.write(f"## 🚨 ОШИБКИ\n\n")
                for error in self.fix_stats['errors']:
                    f.write(f"- {error}\n")
        
        print(f"\n📄 Отчет сохранен в файл: {report_filename}")

def main():
    """Основная функция"""
    print("🔧 ИСПРАВЛЕНИЕ МЕТА-ОПИСАНИЙ НАПРЯМУЮ В БАЗЕ ДАННЫХ")
    print("=" * 80)
    
    fixer = DirectDBMetaDescriptionFixer()
    
    # Исправляем все мета-описания
    stats = fixer.fix_all_meta_descriptions()
    
    # Выводим отчет
    fixer.print_fix_report()
    
    if stats['success'] == stats['total']:
        print(f"\n🎉 ВСЕ МЕТА-ОПИСАНИЯ УСПЕШНО ИСПРАВЛЕНЫ!")
    else:
        print(f"\n⚠️  Требуется доработка {stats['failed']} статей")
    
    print(f"\n✅ ИСПРАВЛЕНИЕ ЗАВЕРШЕНО!")

if __name__ == "__main__":
    main()
