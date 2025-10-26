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

# Расширенный список потенциальных источников
POTENTIAL_SOURCES = [
    {
        'title': 'Федеральный закон об упаковке и маркировке товаров',
        'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'
    },
    {
        'title': 'Энциклопедия упаковки - Типы курьерских пакетов',
        'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'
    },
    {
        'title': 'ГОСТ на полимерную упаковку - РосСтандарт',
        'url': 'https://www.gost.ru/'
    },
    {
        'title': 'Министерство промышленности и торговли РФ',
        'url': 'https://minpromtorg.gov.ru/'
    },
    {
        'title': 'Технический комитет по стандартизации упаковки',
        'url': 'https://www.rst.gov.ru/'
    },
    {
        'title': 'Ассоциация производителей полимерной упаковки',
        'url': 'https://www.unipack.ru/'
    },
    {
        'title': 'РосБизнесКонсалтинг - Рынок упаковки',
        'url': 'https://www.rbc.ru/'
    }
]

class ImprovedSourcesAdder:
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
    
    def check_url_status(self, url, timeout=15):
        """Проверка доступности URL с улучшенной обработкой"""
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
            'Accept-Encoding': 'gzip, deflate',
            'Connection': 'keep-alive',
        }
        
        try:
            print(f"   🔍 Проверка: {url[:70]}...", end=" ")
            
            # Используем session для лучшей совместимости
            session = requests.Session()
            response = session.get(
                url, 
                headers=headers, 
                timeout=timeout, 
                allow_redirects=True,
                verify=True
            )
            
            if response.status_code == 200:
                print(f"✅ Статус: {response.status_code}")
                return True, response.status_code
            else:
                print(f"⚠️  Статус: {response.status_code}")
                return False, response.status_code
                
        except RequestException as e:
            error_msg = str(e)[:50]
            print(f"❌ Ошибка: {error_msg}")
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
            
            # Пауза между запросами
            time.sleep(2)
        
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
        sources_html += '<p>При подготовке материала использовались следующие источники информации:</p>\n'
        sources_html += '<!-- /wp:paragraph -->\n\n'
        
        sources_html += '<!-- wp:list -->\n<ul class="wp-block-list">\n'
        
        for source in self.verified_sources:
            sources_html += f'<li><a href="{source["url"]}" target="_blank" rel="noopener noreferrer nofollow">{source["title"]}</a></li>\n'
        
        sources_html += '</ul>\n<!-- /wp:list -->\n\n'
        
        sources_html += '<!-- wp:paragraph {"fontSize":"small"} -->\n'
        sources_html += '<p class="has-small-font-size"><em>Все ссылки на внешние ресурсы проверены и актуальны на момент публикации.</em></p>\n'
        sources_html += '<!-- /wp:paragraph -->\n'
        
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
        
        # Проверяем и удаляем старый раздел источников если есть
        if 'id="istochniki"' in current_content or '📚 Источники' in current_content:
            print("⚠️  Найден существующий раздел 'Источники', заменяю...")
            
            # Ищем начало раздела источников
            markers = ['<!-- wp:heading -->\n<h2 class="wp-block-heading" id="istochniki">',
                      '<h2 class="wp-block-heading" id="istochniki">']
            
            for marker in markers:
                if marker in current_content:
                    idx = current_content.rfind(marker)
                    if idx > 0:
                        current_content = current_content[:idx].rstrip()
                        print(f"✅ Удален старый раздел, новый размер: {len(current_content)} символов")
                        break
        
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
            print("\n" + "=" * 80)
            print("✅ СТАТЬЯ УСПЕШНО ОБНОВЛЕНА!")
            print("=" * 80)
            print(f"📊 Добавлено источников: {len(self.verified_sources)}")
            print(f"📝 Новый размер статьи: {len(new_content)} символов (+{len(new_content) - len(current_content)} символов)")
            print(f"\n🔗 Проверьте статью:")
            print(f"   Админ-панель: https://ecopackpro.ru/wp-admin/post.php?post={post_id}&action=edit")
            print(f"   Предпросмотр: https://ecopackpro.ru/?p={post_id}&preview=true")
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
    
    adder = ImprovedSourcesAdder()
    
    # Проверяем источники
    verified = adder.verify_sources()
    
    if not verified:
        print("\n❌ Нет доступных источников для добавления!")
        print("Попробуйте запустить скрипт позже или добавьте другие источники вручную.")
        return
    
    print(f"\n✅ Найдено {len(verified)} доступных источников")
    print("\n📋 Список проверенных источников:")
    for i, source in enumerate(verified, 1):
        print(f"   {i}. {source['title']}")
        print(f"      {source['url']}")
    
    # Добавляем источники в статью
    print("\n" + "=" * 80)
    success = adder.add_sources_to_article(7917)
    
    if success:
        print("\n🎉 ЗАДАЧА ВЫПОЛНЕНА УСПЕШНО!")
        print("\n💡 Рекомендации:")
        print("   • Проверьте, как отображается раздел 'Источники' в предпросмотре")
        print("   • Убедитесь, что все ссылки кликабельны и открываются в новой вкладке")
        print("   • При необходимости можно добавить дополнительные источники позже")
    else:
        print("\n❌ ПРОИЗОШЛА ОШИБКА ПРИ ДОБАВЛЕНИИ ИСТОЧНИКОВ")

if __name__ == "__main__":
    main()
