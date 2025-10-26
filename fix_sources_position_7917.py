#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector
import re

# Конфигурация базы данных WordPress
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

class SourcesPositionFixer:
    def __init__(self):
        self.db_config = DB_CONFIG
    
    def connect_to_database(self):
        """Подключение к базе данных MySQL"""
        try:
            connection = mysql.connector.connect(**self.db_config)
            return connection
        except mysql.connector.Error as e:
            print(f"❌ Ошибка подключения к базе данных: {e}")
            return None
    
    def get_article_content(self, post_id):
        """Получение текущего содержимого статьи"""
        connection = self.connect_to_database()
        if not connection:
            return None
        
        try:
            cursor = connection.cursor(dictionary=True)
            
            cursor.execute("""
                SELECT post_content
                FROM wp_posts 
                WHERE ID = %s
            """, (post_id,))
            
            result = cursor.fetchone()
            return result['post_content'] if result else None
            
        except mysql.connector.Error as e:
            print(f"❌ Ошибка получения контента: {e}")
            return None
        finally:
            connection.close()
    
    def extract_sources_section(self, content):
        """Извлечение раздела источников из контента"""
        # Ищем раздел источников от начала до конца
        sources_pattern = r'(<!-- wp:heading -->\n<h2 class="wp-block-heading" id="istochniki">📚 Источники</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>При подготовке материала использовались следующие источники информации:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:list -->\n<ul class="wp-block-list">\n<li><a href="https://www\.consultant\.ru/document/cons_doc_LAW_19109/" target="_blank" rel="noopener noreferrer nofollow">Федеральный закон об упаковке и маркировке товаров</a></li>\n<li><a href="https://ru\.wikipedia\.org/wiki/Полиэтиленовый_пакет" target="_blank" rel="noopener noreferrer nofollow">Энциклопедия упаковки - Типы курьерских пакетов</a></li>\n<li><a href="https://www\.unipack\.ru/" target="_blank" rel="noopener noreferrer nofollow">Ассоциация производителей полимерной упаковки</a></li>\n</ul>\n<!-- /wp:list -->\n\n<!-- wp:paragraph \{"fontSize":"small"\} -->\n<p class="has-small-font-size"><em>Все ссылки на внешние ресурсы проверены и актуальны на момент публикации\.</em></p>\n<!-- /wp:paragraph -->)'
        
        match = re.search(sources_pattern, content)
        if match:
            return match.group(1)
        return None
    
    def find_insertion_point(self, content):
        """Находит место для вставки раздела источников - после блока 'Мы поможем' и перед блоком 'Контактные данные'"""
        # Ищем блок "Мы поможем:" и следующий за ним контент до блока "Контактные данные"
        pattern = r'(<strong>Мы поможем:</strong><br />\n&#8211; Подобрать размер кармана под ваши накладные<br />\n&#8211; Рассчитать ROI внедрения<br />\n&#8211; Предоставить тестовые образцы \(50 шт\)<br />\n&#8211; Организовать регулярные поставки</p>\n<hr />\n)(<div style="\n    background: #f8f9fa;)'
        
        match = re.search(pattern, content, re.DOTALL)
        if match:
            return match.end(1)  # Возвращаем позицию после первого блока, перед вторым
        return None
    
    def update_article_content(self, post_id, new_content):
        """Обновление содержимого статьи"""
        connection = self.connect_to_database()
        if not connection:
            return False
        
        try:
            cursor = connection.cursor()
            
            cursor.execute("""
                UPDATE wp_posts
                SET post_content = %s,
                    post_modified = NOW(),
                    post_modified_gmt = UTC_TIMESTAMP()
                WHERE ID = %s
            """, (new_content, post_id))
            
            connection.commit()
            print(f"✅ Обновлено строк: {cursor.rowcount}")
            return True
            
        except mysql.connector.Error as e:
            print(f"❌ Ошибка обновления контента: {e}")
            connection.rollback()
            return False
        finally:
            connection.close()
    
    def fix_sources_position(self, post_id):
        """Исправление позиции раздела источников"""
        print(f"\n🔧 ИСПРАВЛЕНИЕ ПОЗИЦИИ РАЗДЕЛА ИСТОЧНИКОВ В СТАТЬЕ ID {post_id}")
        print("=" * 80)
        
        # Получаем текущий контент
        print("1️⃣ Получение текущего содержимого статьи...")
        current_content = self.get_article_content(post_id)
        
        if not current_content:
            print("❌ Не удалось получить содержимое статьи")
            return False
        
        print(f"✅ Получено содержимое ({len(current_content)} символов)")
        
        # Извлекаем раздел источников
        print("2️⃣ Извлечение раздела источников...")
        sources_section = self.extract_sources_section(current_content)
        
        if not sources_section:
            print("❌ Раздел источников не найден в статье!")
            return False
        
        print("✅ Раздел источников найден и извлечен")
        
        # Удаляем старый раздел источников
        print("3️⃣ Удаление раздела источников из текущей позиции...")
        content_without_sources = re.sub(
            r'<!-- wp:heading -->\n<h2 class="wp-block-heading" id="istochniki">📚 Источники</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>При подготовке материала использовались следующие источники информации:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:list -->\n<ul class="wp-block-list">\n<li><a href="https://www\.consultant\.ru/document/cons_doc_LAW_19109/" target="_blank" rel="noopener noreferrer nofollow">Федеральный закон об упаковке и маркировке товаров</a></li>\n<li><a href="https://ru\.wikipedia\.org/wiki/Полиэтиленовый_пакет" target="_blank" rel="noopener noreferrer nofollow">Энциклопедия упаковки - Типы курьерских пакетов</a></li>\n<li><a href="https://www\.unipack\.ru/" target="_blank" rel="noopener noreferrer nofollow">Ассоциация производителей полимерной упаковки</a></li>\n</ul>\n<!-- /wp:list -->\n\n<!-- wp:paragraph \{"fontSize":"small"\} -->\n<p class="has-small-font-size"><em>Все ссылки на внешние ресурсы проверены и актуальны на момент публикации\.</em></p>\n<!-- /wp:paragraph -->',
            '',
            current_content
        )
        
        print("✅ Раздел источников удален из старой позиции")
        
        # Находим правильное место для вставки
        print("4️⃣ Поиск правильного места для вставки...")
        insertion_point = self.find_insertion_point(content_without_sources)
        
        if insertion_point is None:
            print("❌ Не найдено место для вставки раздела источников!")
            return False
        
        print("✅ Найдено место для вставки (после блока 'Мы поможем', перед блоком 'Контактные данные')")
        
        # Вставляем раздел источников в правильное место
        print("5️⃣ Вставка раздела источников в правильную позицию...")
        new_content = (
            content_without_sources[:insertion_point] + 
            '\n\n' + sources_section + '\n\n' + 
            content_without_sources[insertion_point:]
        )
        
        print("✅ Раздел источников вставлен в правильную позицию")
        
        # Обновляем статью
        print("6️⃣ Обновление статьи в базе данных...")
        success = self.update_article_content(post_id, new_content)
        
        if success:
            print("\n" + "=" * 80)
            print("✅ ПОЗИЦИЯ РАЗДЕЛА ИСТОЧНИКОВ ИСПРАВЛЕНА!")
            print("=" * 80)
            print(f"📝 Новый размер статьи: {len(new_content)} символов")
            print(f"\n🔗 Проверьте статью:")
            print(f"   Админ-панель: https://ecopackpro.ru/wp-admin/post.php?post={post_id}&action=edit")
            print(f"   Предпросмотр: https://ecopackpro.ru/?p={post_id}&preview=true")
            print(f"\n📋 Теперь раздел 'Источники' должен отображаться:")
            print(f"   • После блока 'Мы поможем:'")
            print(f"   • Перед блоком 'Контактные данные'")
            return True
        else:
            print("❌ Не удалось обновить статью")
            return False

def main():
    """Основная функция"""
    print("=" * 80)
    print("🔧 ИСПРАВЛЕНИЕ ПОЗИЦИИ РАЗДЕЛА ИСТОЧНИКОВ")
    print("=" * 80)
    print("Статья: Курьерские пакеты с карманом (ID: 7917)")
    print("=" * 80)
    
    fixer = SourcesPositionFixer()
    
    # Исправляем позицию раздела источников
    success = fixer.fix_sources_position(7917)
    
    if success:
        print("\n🎉 ЗАДАЧА ВЫПОЛНЕНА УСПЕШНО!")
        print("\n💡 Теперь раздел 'Источники' должен отображаться в правильном месте:")
        print("   1. После блока 'Мы поможем:'")
        print("   2. Перед блоком 'Контактные данные'")
        print("   3. Перед блоком навигации по статьям")
    else:
        print("\n❌ ПРОИЗОШЛА ОШИБКА ПРИ ИСПРАВЛЕНИИ ПОЗИЦИИ")

if __name__ == "__main__":
    main()
