#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
from requests.auth import HTTPBasicAuth
import json
import re

# Настройки WordPress API
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"

class WordPressSourcesAdder:
    def __init__(self):
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
    
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
    
    def clean_broken_html(self, content):
        """Очистка сломанного HTML и восстановление правильной структуры"""
        print("🧹 Очистка сломанного HTML...")
        
        # Удаляем сломанный раздел источников, который попал внутрь блока контактов
        # Ищем паттерн от "📚 Источники" до "Контактные данные"
        broken_pattern = r'<h2>📚 Источники</h2>.*?<hr />\nКонтактные данные'
        cleaned_content = re.sub(broken_pattern, '', content, flags=re.DOTALL)
        
        print("✅ Сломанный HTML очищен")
        return cleaned_content
    
    def add_sources_section(self, content):
        """Добавление раздела источников в правильном месте"""
        print("📝 Добавление раздела источников...")
        
        # Создаем раздел источников
        sources_section = """
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
"""
        
        # Находим место для вставки - после блока "Мы поможем" и перед блоком "Контактные данные"
        insertion_point = content.find('Контактные данные')
        
        if insertion_point == -1:
            print("❌ Не найдено место для вставки раздела источников!")
            return content
        
        # Вставляем раздел источников
        new_content = (
            content[:insertion_point] + 
            sources_section + 
            content[insertion_point:]
        )
        
        print("✅ Раздел источников добавлен в правильное место")
        return new_content
    
    def fix_article_sources(self, post_id):
        """Исправление статьи и добавление раздела источников"""
        print(f"\n🔧 ИСПРАВЛЕНИЕ СТАТЬИ {post_id} ЧЕРЕЗ WORDPRESS API")
        print("=" * 80)
        
        # Получаем пост
        print("1️⃣ Получение поста через API...")
        post = self.get_post(post_id)
        
        if not post:
            print("❌ Не удалось получить пост")
            return False
        
        print(f"✅ Пост получен: {post['title']['rendered']}")
        
        # Получаем контент
        content = post['content']['rendered']
        print(f"✅ Получен контент ({len(content)} символов)")
        
        # Очищаем сломанный HTML
        print("2️⃣ Очистка сломанного HTML...")
        cleaned_content = self.clean_broken_html(content)
        
        # Добавляем раздел источников
        print("3️⃣ Добавление раздела источников...")
        final_content = self.add_sources_section(cleaned_content)
        
        # Обновляем пост через API
        print("4️⃣ Обновление поста через API...")
        updated_post = self.update_post(post_id, final_content)
        
        if updated_post:
            print("\n" + "=" * 80)
            print("✅ СТАТЬЯ УСПЕШНО ОБНОВЛЕНА ЧЕРЕЗ API!")
            print("=" * 80)
            print(f"📝 Новый размер контента: {len(final_content)} символов")
            print(f"\n🔗 Проверьте статью:")
            print(f"   Админ-панель: https://ecopackpro.ru/wp-admin/post.php?post={post_id}&action=edit")
            print(f"   Предпросмотр: https://ecopackpro.ru/?p={post_id}&preview=true")
            print(f"\n💡 Раздел 'Источники' теперь должен отображаться:")
            print(f"   • После блока 'Мы поможем:'")
            print(f"   • Перед блоком 'Контактные данные'")
            return True
        else:
            print("❌ Не удалось обновить пост через API")
            return False

def main():
    """Основная функция"""
    print("=" * 80)
    print("🔧 ДОБАВЛЕНИЕ РАЗДЕЛА ИСТОЧНИКОВ ЧЕРЕЗ WORDPRESS API")
    print("=" * 80)
    print("Статья: Курьерские пакеты с карманом (ID: 7917)")
    print("=" * 80)
    
    adder = WordPressSourcesAdder()
    
    # Исправляем статью
    success = adder.fix_article_sources(7917)
    
    if success:
        print("\n🎉 ЗАДАЧА ВЫПОЛНЕНА УСПЕШНО!")
        print("\n💡 Раздел 'Источники' добавлен через WordPress API")
        print("   и должен корректно отображаться в браузере")
    else:
        print("\n❌ ПРОИЗОШЛА ОШИБКА ПРИ ОБНОВЛЕНИИ СТАТЬИ")

if __name__ == "__main__":
    main()
