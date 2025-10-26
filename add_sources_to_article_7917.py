#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector
import requests
from requests.exceptions import RequestException
import time

# Конфигурация базы данных WordPress
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

# Потенциальные источники для статьи о курьерских пакетах с карманом
POTENTIAL_SOURCES = [
    {
        'title': 'ГОСТ Р 51760-2001 - Пакеты и мешки из полимерных материалов',
        'url': 'https://docs.cntd.ru/document/1200009321'
    },
    {
        'title': 'Правила оформления почтовых отправлений - Почта России',
        'url': 'https://www.pochta.ru/support/post-rules'
    },
    {
        'title': 'Требования к упаковке товаров - Wildberries',
        'url': 'https://www.wildberries.ru/services/prodavayte-na-wildberries'
    },
    {
        'title': 'Стандарты упаковки для маркетплейсов - Ozon',
        'url': 'https://docs.ozon.ru/global/packaging/'
    },
    {
        'title': 'Технический регламент Таможенного союза ТР ТС 005/2011',
        'url': 'https://docs.cntd.ru/document/902307617'
    },
    {
        'title': 'Федеральный закон об упаковке и маркировке',
        'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'
    }
]

class SourcesAdder:
    def __init__(self):
        self.db_config = DB_CONFIG
        self.verified_sources = []
    
    def connect_to_database(self):
        """Подключение к базе данных MySQL"""
        try:
            connection = mysql.connector.connect(**self.db_config)
            return connection
        except mysql.connector.Error as e:
            print(f"❌ Ошибка подключения к базе данных: {e}")
            return None
    
    def check_url_status(self, url, timeout=10):
        """Проверка доступности URL (статус 200)"""
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        }
        
        try:
            print(f"   🔍 Проверка: {url[:60]}...", end=" ")
            response = requests.get(url, headers=headers, timeout=timeout, allow_redirects=True)
            
            if response.status_code == 200:
                print(f"✅ Статус: {response.status_code}")
                return True, response.status_code
            else:
                print(f"❌ Статус: {response.status_code}")
                return False, response.status_code
                
        except RequestException as e:
            print(f"❌ Ошибка: {str(e)[:50]}")
            return False, None
    
    def verify_sources(self):
        """Проверка всех потенциальных источников"""
        print("🔍 ПРОВЕРКА ДОСТУПНОСТИ ИСТОЧНИКОВ")
        print("=" * 80)
        
        for i, source in enumerate(POTENTIAL_SOURCES, 1):
            print(f"\n{i}. {source['title']}")
            
            is_valid, status = self.check_url_status(source['url'])
            
            if is_valid:
                self.verified_sources.append(source)
            
            # Небольшая пауза между запросами
            time.sleep(1)
        
        print(f"\n✅ Проверено источников: {len(POTENTIAL_SOURCES)}")
        print(f"✅ Доступных источников: {len(self.verified_sources)}")
        
        return self.verified_sources
    
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
    
    def create_sources_section(self):
        """Создание HTML раздела с источниками"""
        if not self.verified_sources:
            return ""
        
        sources_html = '\n\n<!-- wp:heading -->\n'
        sources_html += '<h2 class="wp-block-heading" id="istochniki">📚 Источники</h2>\n'
        sources_html += '<!-- /wp:heading -->\n\n'
        
        sources_html += '<!-- wp:paragraph -->\n'
        sources_html += '<p>При подготовке статьи использовались следующие источники:</p>\n'
        sources_html += '<!-- /wp:paragraph -->\n\n'
        
        sources_html += '<!-- wp:list -->\n<ul>\n'
        
        for source in self.verified_sources:
            sources_html += f'<li><a href="{source["url"]}" target="_blank" rel="noopener noreferrer">{source["title"]}</a></li>\n'
        
        sources_html += '</ul>\n<!-- /wp:list -->\n'
        
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
            return True
            
        except mysql.connector.Error as e:
            print(f"❌ Ошибка обновления контента: {e}")
            connection.rollback()
            return False
        finally:
            connection.close()
    
    def add_sources_to_article(self, post_id):
        """Добавление раздела источников в статью"""
        print(f"\n📝 ДОБАВЛЕНИЕ ИСТОЧНИКОВ В СТАТЬЮ ID {post_id}")
        print("=" * 80)
        
        # Получаем текущий контент
        print("1️⃣ Получение текущего содержимого статьи...")
        current_content = self.get_article_content(post_id)
        
        if not current_content:
            print("❌ Не удалось получить содержимое статьи")
            return False
        
        print(f"✅ Получено содержимое ({len(current_content)} символов)")
        
        # Проверяем, нет ли уже раздела источников
        if 'id="istochniki"' in current_content or '📚 Источники' in current_content:
            print("⚠️  Раздел 'Источники' уже существует в статье!")
            user_input = input("Хотите заменить его? (yes/no): ").lower()
            if user_input != 'yes':
                print("❌ Отменено пользователем")
                return False
            
            # Удаляем старый раздел источников (простой подход)
            # Ищем последнее вхождение заголовка и удаляем всё после него до конца
            if '📚 Источники' in current_content:
                idx = current_content.rfind('<!-- wp:heading -->')
                if idx > 0 and '📚 Источники' in current_content[idx:]:
                    current_content = current_content[:idx].rstrip()
        
        # Создаём раздел источников
        print("2️⃣ Создание раздела источников...")
        sources_section = self.create_sources_section()
        
        if not sources_section:
            print("❌ Нет проверенных источников для добавления")
            return False
        
        print(f"✅ Создан раздел с {len(self.verified_sources)} источниками")
        
        # Добавляем источники в конец статьи
        new_content = current_content.rstrip() + sources_section
        
        # Обновляем статью
        print("3️⃣ Обновление статьи в базе данных...")
        success = self.update_article_content(post_id, new_content)
        
        if success:
            print("✅ Статья успешно обновлена!")
            print(f"\n📊 ДОБАВЛЕНО ИСТОЧНИКОВ: {len(self.verified_sources)}")
            print(f"📝 Новый размер статьи: {len(new_content)} символов")
            print(f"\n🔗 Проверьте статью: https://ecopackpro.ru/wp-admin/post.php?post={post_id}&action=edit")
            return True
        else:
            print("❌ Не удалось обновить статью")
            return False

def main():
    """Основная функция"""
    print("=" * 80)
    print("📚 ДОБАВЛЕНИЕ ИСТОЧНИКОВ В СТАТЬЮ 7917")
    print("=" * 80)
    print("Статья: Курьерские пакеты с карманом")
    print("=" * 80)
    
    adder = SourcesAdder()
    
    # Проверяем источники
    verified = adder.verify_sources()
    
    if not verified:
        print("\n❌ Нет доступных источников для добавления!")
        return
    
    print(f"\n✅ Найдено {len(verified)} доступных источников")
    print("\nСписок проверенных источников:")
    for i, source in enumerate(verified, 1):
        print(f"{i}. {source['title']}")
    
    # Добавляем источники в статью
    print("\n" + "=" * 80)
    success = adder.add_sources_to_article(7917)
    
    if success:
        print("\n🎉 ИСТОЧНИКИ УСПЕШНО ДОБАВЛЕНЫ В СТАТЬЮ!")
    else:
        print("\n❌ НЕ УДАЛОСЬ ДОБАВИТЬ ИСТОЧНИКИ")

if __name__ == "__main__":
    main()
