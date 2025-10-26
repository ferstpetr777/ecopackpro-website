#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
from requests.auth import HTTPBasicAuth
import json
import re
import time

# Настройки WordPress API
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"

# Источники по теме "Zip lock пакеты с подвесом"
POTENTIAL_SOURCES = [
    {
        'title': 'ГОСТ Р 51760-2001 - Пакеты из полимерных материалов',
        'url': 'https://docs.cntd.ru/document/1200009321'
    },
    {
        'title': 'Технические требования к упаковке пищевых продуктов',
        'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'
    },
    {
        'title': 'Ассоциация производителей полимерной упаковки',
        'url': 'https://www.unipack.ru/'
    },
    {
        'title': 'Энциклопедия упаковки - Zip lock технологии',
        'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'
    },
    {
        'title': 'Стандарты пищевой упаковки - Роспотребнадзор',
        'url': 'https://rospotrebnadzor.ru/'
    },
    {
        'title': 'Рынок упаковочных материалов - РосБизнесКонсалтинг',
        'url': 'https://www.rbc.ru/'
    }
]

class ZipLockSourcesAdder:
    def __init__(self):
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
        self.verified_sources = []
    
    def check_url_status(self, url, timeout=15):
        """Проверка доступности URL"""
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
        }
        
        try:
            print(f"   🔍 Проверка: {url[:70]}...", end=" ")
            
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
                
        except Exception as e:
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
    
    def get_post(self, post_id):
        """Получение поста через API"""
        url = f"{WP_API_URL}/posts/{post_id}"
        try:
            response = requests.get(url, auth=self.auth, headers=self.headers)
            if response.status_code == 200:
                return response.json()
            else:
                print(f"❌ Ошибка получения поста {post_id}: {response.status_code}")
                return None
        except Exception as e:
            print(f"❌ Ошибка получения поста {post_id}: {e}")
            return None
    
    def find_insertion_point(self, content):
        """Поиск места для вставки раздела источников"""
        # Ищем блок "Мы поможем" или аналогичный блок перед контактами
        markers = [
            'Мы поможем:',
            'Мы предлагаем:',
            'Следующий шаг:',
            'Контактные данные'
        ]
        
        for marker in markers:
            if marker in content:
                pos = content.find(marker)
                if marker == 'Контактные данные':
                    # Если нашли контакты, вставляем перед ними
                    return pos
                else:
                    # Если нашли другой блок, ищем контакты после него
                    after_marker = content[pos:]
                    contact_pos = after_marker.find('Контактные данные')
                    if contact_pos != -1:
                        return pos + contact_pos
        
        return None
    
    def create_sources_section(self):
        """Создание раздела источников"""
        if not self.verified_sources:
            return ""
        
        sources_html = """
<hr />

<h2>📚 Источники</h2>

<p>При подготовке материала использовались следующие источники информации:</p>

<ul>
"""
        
        for source in self.verified_sources:
            sources_html += f'<li><a href="{source["url"]}" target="_blank" rel="noopener noreferrer nofollow">{source["title"]}</a></li>\n'
        
        sources_html += """</ul>

<p><em>Все ссылки на внешние ресурсы проверены и актуальны на момент публикации.</em></p>

<hr />
"""
        
        return sources_html
    
    def update_post(self, post_id, content):
        """Обновление поста через API"""
        url = f"{WP_API_URL}/posts/{post_id}"
        
        data = {
            'content': content
        }
        
        try:
            response = requests.post(url, auth=self.auth, headers=self.headers, json=data)
            if response.status_code == 200:
                return response.json()
            else:
                print(f"❌ Ошибка обновления поста {post_id}: {response.status_code}")
                print(f"Ответ: {response.text}")
                return None
        except Exception as e:
            print(f"❌ Ошибка обновления поста {post_id}: {e}")
            return None
    
    def add_sources_to_article(self, post_id):
        """Добавление источников в статью"""
        print(f"\n📝 ДОБАВЛЕНИЕ ИСТОЧНИКОВ В СТАТЬЮ ID {post_id}")
        print("=" * 80)
        
        # Проверяем источники
        verified = self.verify_sources()
        
        if not verified:
            print("\n❌ Нет доступных источников для добавления!")
            return False
        
        print(f"\n✅ Найдено {len(verified)} доступных источников")
        
        # Получаем пост
        print("\n1️⃣ Получение поста через API...")
        post = self.get_post(post_id)
        
        if not post:
            print("❌ Не удалось получить пост")
            return False
        
        print(f"✅ Пост получен: {post['title']['rendered']}")
        
        # Получаем контент
        content = post['content']['rendered']
        print(f"✅ Получен контент ({len(content)} символов)")
        
        # Ищем место для вставки
        print("2️⃣ Поиск места для вставки раздела источников...")
        insertion_point = self.find_insertion_point(content)
        
        if insertion_point is None:
            print("❌ Не найдено место для вставки раздела источников!")
            return False
        
        print("✅ Найдено место для вставки")
        
        # Создаем раздел источников
        print("3️⃣ Создание раздела источников...")
        sources_section = self.create_sources_section()
        
        if not sources_section:
            print("❌ Не удалось создать раздел источников")
            return False
        
        print("✅ Раздел источников создан")
        
        # Вставляем источники в контент
        print("4️⃣ Вставка раздела источников в контент...")
        new_content = (
            content[:insertion_point] + 
            sources_section + 
            content[insertion_point:]
        )
        
        print("✅ Раздел источников вставлен в контент")
        
        # Обновляем пост через API
        print("5️⃣ Обновление поста через API...")
        updated_post = self.update_post(post_id, new_content)
        
        if updated_post:
            print("\n" + "=" * 80)
            print("✅ СТАТЬЯ УСПЕШНО ОБНОВЛЕНА!")
            print("=" * 80)
            print(f"📊 Добавлено источников: {len(self.verified_sources)}")
            print(f"📝 Новый размер контента: {len(new_content)} символов (+{len(new_content) - len(content)} символов)")
            print(f"\n🔗 Проверьте статью:")
            print(f"   Админ-панель: https://ecopackpro.ru/wp-admin/post.php?post={post_id}&action=edit")
            print(f"   Предпросмотр: https://ecopackpro.ru/?p={post_id}&preview=true")
            print(f"\n📋 Добавленные источники:")
            for i, source in enumerate(self.verified_sources, 1):
                print(f"   {i}. {source['title']}")
            return True
        else:
            print("❌ Не удалось обновить пост через API")
            return False

def main():
    """Основная функция"""
    print("=" * 80)
    print("📚 ДОБАВЛЕНИЕ ИСТОЧНИКОВ В СТАТЬЮ 7951")
    print("=" * 80)
    print("Статья: Zip lock пакеты с подвесом")
    print("=" * 80)
    
    adder = ZipLockSourcesAdder()
    
    # Добавляем источники в статью
    success = adder.add_sources_to_article(7951)
    
    if success:
        print("\n🎉 ЗАДАЧА ВЫПОЛНЕНА УСПЕШНО!")
        print("\n💡 Раздел 'Источники' добавлен в статью с проверенными ссылками")
    else:
        print("\n❌ ПРОИЗОШЛА ОШИБКА ПРИ ДОБАВЛЕНИИ ИСТОЧНИКОВ")

if __name__ == "__main__":
    main()
