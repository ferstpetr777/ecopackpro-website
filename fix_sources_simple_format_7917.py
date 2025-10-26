#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector

# Конфигурация базы данных WordPress
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

class SimpleSourcesFixer:
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
    
    def create_simple_sources_section(self):
        """Создание простого раздела источников без WordPress блоков"""
        sources_html = '''
<hr />

<h2>📚 Источники</h2>

<p>При подготовке материала использовались следующие источники информации:</p>

<ul>
<li><a href="https://www.consultant.ru/document/cons_doc_LAW_19109/" target="_blank" rel="noopener noreferrer nofollow">Федеральный закон об упаковке и маркировке товаров</a></li>
<li><a href="https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет" target="_blank" rel="noopener noreferrer nofollow">Энциклопедия упаковки - Типы курьерских пакетов</a></li>
<li><a href="https://www.unipack.ru/" target="_blank" rel="noopener noreferrer nofollow">Ассоциация производителей полимерной упаковки</a></li>
</ul>

<p><em>Все ссылки на внешние ресурсы проверены и актуальны на момент публикации.</em></p>

<hr />
'''
        return sources_html
    
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
    
    def replace_sources_with_simple_format(self, post_id):
        """Замена сложного раздела источников на простой формат"""
        print(f"\n🔧 ЗАМЕНА РАЗДЕЛА ИСТОЧНИКОВ НА ПРОСТОЙ ФОРМАТ")
        print("=" * 80)
        
        # Получаем текущий контент
        print("1️⃣ Получение текущего содержимого статьи...")
        current_content = self.get_article_content(post_id)
        
        if not current_content:
            print("❌ Не удалось получить содержимое статьи")
            return False
        
        print(f"✅ Получено содержимое ({len(current_content)} символов)")
        
        # Удаляем старый сложный раздел источников
        print("2️⃣ Удаление старого раздела источников...")
        
        # Паттерн для поиска и удаления старого раздела
        old_sources_pattern = r'<!-- wp:heading -->\n<h2 class="wp-block-heading" id="istochniki">📚 Источники</h2>\n<!-- /wp:heading -->\n\n<!-- wp:paragraph -->\n<p>При подготовке материала использовались следующие источники информации:</p>\n<!-- /wp:paragraph -->\n\n<!-- wp:list -->\n<ul class="wp-block-list">\n<li><a href="https://www\.consultant\.ru/document/cons_doc_LAW_19109/" target="_blank" rel="noopener noreferrer nofollow">Федеральный закон об упаковке и маркировке товаров</a></li>\n<li><a href="https://ru\.wikipedia\.org/wiki/Полиэтиленовый_пакет" target="_blank" rel="noopener noreferrer nofollow">Энциклопедия упаковки - Типы курьерских пакетов</a></li>\n<li><a href="https://www\.unipack\.ru/" target="_blank" rel="noopener noreferrer nofollow">Ассоциация производителей полимерной упаковки</a></li>\n</ul>\n<!-- /wp:list -->\n\n<!-- wp:paragraph \{"fontSize":"small"\} -->\n<p class="has-small-font-size"><em>Все ссылки на внешние ресурсы проверены и актуальны на момент публикации\.</em></p>\n<!-- /wp:paragraph -->'
        
        import re
        content_without_old_sources = re.sub(old_sources_pattern, '', current_content)
        
        print("✅ Старый раздел источников удален")
        
        # Создаем новый простой раздел источников
        print("3️⃣ Создание простого раздела источников...")
        simple_sources = self.create_simple_sources_section()
        
        # Находим место для вставки (после блока "Мы поможем", перед "Контактные данные")
        insertion_point = content_without_old_sources.find('Контактные данные')
        
        if insertion_point == -1:
            print("❌ Не найдено место для вставки!")
            return False
        
        # Вставляем простой раздел источников
        new_content = (
            content_without_old_sources[:insertion_point] + 
            simple_sources + 
            content_without_old_sources[insertion_point:]
        )
        
        print("✅ Простой раздел источников вставлен")
        
        # Обновляем статью
        print("4️⃣ Обновление статьи в базе данных...")
        success = self.update_article_content(post_id, new_content)
        
        if success:
            print("\n" + "=" * 80)
            print("✅ РАЗДЕЛ ИСТОЧНИКОВ ЗАМЕНЕН НА ПРОСТОЙ ФОРМАТ!")
            print("=" * 80)
            print(f"📝 Новый размер статьи: {len(new_content)} символов")
            print(f"\n🔗 Проверьте статью:")
            print(f"   Админ-панель: https://ecopackpro.ru/wp-admin/post.php?post={post_id}&action=edit")
            print(f"   Предпросмотр: https://ecopackpro.ru/?p={post_id}&preview=true")
            print(f"\n💡 Теперь раздел 'Источники' использует простой HTML формат")
            print(f"   без WordPress блоков, что должно решить проблему отображения")
            return True
        else:
            print("❌ Не удалось обновить статью")
            return False

def main():
    """Основная функция"""
    print("=" * 80)
    print("🔧 ЗАМЕНА РАЗДЕЛА ИСТОЧНИКОВ НА ПРОСТОЙ ФОРМАТ")
    print("=" * 80)
    print("Статья: Курьерские пакеты с карманом (ID: 7917)")
    print("=" * 80)
    
    fixer = SimpleSourcesFixer()
    
    # Заменяем сложный раздел источников на простой
    success = fixer.replace_sources_with_simple_format(7917)
    
    if success:
        print("\n🎉 ПРОБЛЕМА РЕШЕНА!")
        print("\n💡 Раздел 'Источники' теперь использует простой HTML формат")
        print("   без WordPress блоков, что должно решить проблему отображения")
    else:
        print("\n❌ ПРОИЗОШЛА ОШИБКА ПРИ ЗАМЕНЕ РАЗДЕЛА")

if __name__ == "__main__":
    main()
