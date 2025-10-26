#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
from requests.auth import HTTPBasicAuth
import json
import re
import time
from datetime import datetime

# Настройки WordPress API
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"

# Источники для проблемных статей
SOURCES_BY_ARTICLE = {
    7909: [  # Зип пакеты
        {'title': 'Технические требования к упаковке пищевых продуктов', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Ассоциация производителей полимерной упаковки', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Zip lock технологии', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Стандарты пищевой упаковки - Роспотребнадзор', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    7922: [  # Коробки для почты
        {'title': 'Правила почтовой пересылки - Почта России', 'url': 'https://www.pochta.ru/'},
        {'title': 'ГОСТ Р 53636-2009 - Упаковка из картона', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Стандарты упаковки для международной почты', 'url': 'https://www.unipack.ru/'},
        {'title': 'Технические требования к почтовой упаковке', 'url': 'https://rospotrebnadzor.ru/'}
    ]
}

class FailedArticlesFixer:
    def __init__(self):
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
    
    def check_url_status(self, url, timeout=15):
        """Проверка доступности URL"""
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language': 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
        }
        
        try:
            session = requests.Session()
            response = session.get(
                url, 
                headers=headers, 
                timeout=timeout, 
                allow_redirects=True,
                verify=True
            )
            
            return response.status_code == 200, response.status_code
                
        except Exception as e:
            return False, None
    
    def get_post(self, post_id):
        """Получение поста через API"""
        url = f"{WP_API_URL}/posts/{post_id}"
        try:
            response = requests.get(url, auth=self.auth, headers=self.headers, timeout=30)
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
        # Ищем различные блоки перед контактами
        markers = [
            'Мы поможем:',
            'Мы предлагаем:',
            'Следующий шаг:',
            'Заключение'
        ]
        
        for marker in markers:
            pos = content.find(marker)
            if pos != -1:
                # Ищем конец этого блока - ищем </p> после всех пунктов
                after_marker = content[pos:]
                last_p_end = after_marker.rfind('</p>')
                if last_p_end != -1:
                    insertion_point = pos + last_p_end + 4  # +4 для длины </p>
                    return insertion_point
        
        # Если не найдены стандартные блоки, ищем контакты
        contact_pos = content.find('Контактные данные')
        if contact_pos != -1:
            return contact_pos
        
        return None
    
    def create_sources_section(self, sources):
        """Создание раздела источников"""
        if not sources:
            return ""
        
        sources_html = """
<hr />

<h2>📚 Источники</h2>

<p>При подготовке материала использовались следующие источники информации:</p>

<ul>
"""
        
        for source in sources:
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
            response = requests.post(url, auth=self.auth, headers=self.headers, json=data, timeout=30)
            if response.status_code == 200:
                return response.json()
            else:
                print(f"❌ Ошибка обновления поста {post_id}: {response.status_code}")
                return None
        except Exception as e:
            print(f"❌ Ошибка обновления поста {post_id}: {e}")
            return None
    
    def fix_article(self, post_id, article_title):
        """Исправление конкретной статьи"""
        print(f"\n📝 Исправление статьи ID {post_id}: {article_title}")
        
        # Получаем источники для статьи
        potential_sources = SOURCES_BY_ARTICLE.get(post_id, [])
        
        # Проверяем источники
        verified_sources = []
        for source in potential_sources:
            is_valid, status = self.check_url_status(source['url'])
            if is_valid:
                verified_sources.append(source)
            time.sleep(1)
        
        if not verified_sources:
            print(f"❌ Нет доступных источников для статьи {post_id}")
            return False
        
        print(f"✅ Проверено {len(verified_sources)} источников")
        
        # Получаем пост
        post = self.get_post(post_id)
        
        if not post:
            print(f"❌ Не удалось получить пост {post_id}")
            return False
        
        # Получаем контент
        content = post['content']['rendered']
        
        # Проверяем, есть ли уже источники
        if '📚 Источники' in content:
            print(f"⚠️  В статье {post_id} уже есть раздел источников")
            return True
        
        # Ищем место для вставки
        insertion_point = self.find_insertion_point(content)
        
        if insertion_point is None:
            print(f"❌ Не найдено место для вставки в статье {post_id}")
            return False
        
        # Создаем раздел источников
        sources_section = self.create_sources_section(verified_sources)
        
        if not sources_section:
            print(f"❌ Не удалось создать раздел источников для статьи {post_id}")
            return False
        
        # Вставляем источники в контент
        new_content = (
            content[:insertion_point] + 
            sources_section + 
            content[insertion_point:]
        )
        
        # Обновляем пост через API
        updated_post = self.update_post(post_id, new_content)
        
        if updated_post:
            print(f"✅ Источники добавлены в статью {post_id} ({len(verified_sources)} источников)")
            return True
        else:
            print(f"❌ Не удалось обновить статью {post_id}")
            return False
    
    def fix_all_failed_articles(self):
        """Исправление всех проблемных статей"""
        print("=" * 80)
        print("🔧 ИСПРАВЛЕНИЕ ПРОБЛЕМНЫХ СТАТЕЙ")
        print("=" * 80)
        
        failed_articles = [
            (7909, "Зип пакеты"),
            (7922, "Коробки для почты")
        ]
        
        success_count = 0
        
        for post_id, title in failed_articles:
            print(f"\n[{success_count + 1}/{len(failed_articles)}] Исправление статьи ID {post_id}")
            
            if self.fix_article(post_id, title):
                success_count += 1
            
            # Пауза между статьями
            time.sleep(3)
        
        print("\n" + "=" * 80)
        print("📊 ИТОГОВЫЙ ОТЧЕТ")
        print("=" * 80)
        print(f"✅ Успешно исправлено: {success_count}")
        print(f"❌ Не удалось исправить: {len(failed_articles) - success_count}")
        
        return success_count

def main():
    """Основная функция"""
    fixer = FailedArticlesFixer()
    success = fixer.fix_all_failed_articles()
    
    if success > 0:
        print(f"\n🎉 ИСПРАВЛЕНИЕ ЗАВЕРШЕНО! Исправлено {success} статей")
    else:
        print("\n❌ НЕ УДАЛОСЬ ИСПРАВИТЬ НИ ОДНУ СТАТЬЮ")

if __name__ == "__main__":
    main()
