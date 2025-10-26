#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
from requests.auth import HTTPBasicAuth
import time

# Настройки WordPress API
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKIVWpH96dgVI3HovlIhI4E"

# Список статей с неправильными мета-описаниями
ARTICLES_TO_FIX = [
    7913, 7915, 7926, 7928, 7929, 7930, 7932, 7934, 7938, 7939, 
    7941, 7943, 7944, 7945, 7946, 7947, 7949
]

class MetaDescriptionFixer:
    def __init__(self):
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
    
    def fix_meta_description(self, post_id):
        """Исправление мета-описания для одной статьи"""
        print(f"\n🔧 Исправление мета-описания ID {post_id}")
        print("-" * 50)
        
        try:
            # Получаем текущий пост
            response = requests.get(
                f"{WP_API_URL}/posts/{post_id}",
                auth=self.auth,
                headers=self.headers,
                timeout=60
            )
            
            if response.status_code != 200:
                print(f"❌ Ошибка получения поста: {response.status_code}")
                return False
            
            post_data = response.json()
            post_title = post_data['title']['rendered']
            
            # Получаем текущие мета данные
            meta = post_data.get('meta', {})
            focus_keyword = meta.get('_yoast_wpseo_focuskw', '')
            current_meta_description = meta.get('_yoast_wpseo_metadesc', '')
            
            print(f"📄 Заголовок: {post_title}")
            print(f"🎯 Фокусное ключевое слово: {focus_keyword}")
            
            if not focus_keyword:
                print("❌ Отсутствует фокусное ключевое слово")
                return False
            
            # Создаем правильное мета-описание, которое начинается с фокусного ключевого слова
            if current_meta_description and not current_meta_description.lower().startswith(focus_keyword.lower()):
                # Если мета-описание не начинается с ключевого слова, исправляем его
                new_meta_description = f"{focus_keyword} - {current_meta_description}"
                print(f"📝 Исправленное мета-описание: {new_meta_description[:80]}...")
            else:
                print("✅ Мета-описание уже правильное")
                return True
            
            # Обновляем только мета-описание
            update_data = {
                'meta': {
                    '_yoast_wpseo_metadesc': new_meta_description
                }
            }
            
            # Обновляем пост
            update_response = requests.post(
                f"{WP_API_URL}/posts/{post_id}",
                auth=self.auth,
                headers=self.headers,
                json=update_data,
                timeout=60
            )
            
            if update_response.status_code == 200:
                print("✅ Мета-описание успешно исправлено!")
                return True
            else:
                print(f"❌ Ошибка обновления: {update_response.status_code} - {update_response.text}")
                return False
                
        except Exception as e:
            print(f"❌ Ошибка: {e}")
            return False
    
    def fix_all_meta_descriptions(self):
        """Исправление мета-описаний для всех статей"""
        print("🔧 ИСПРАВЛЕНИЕ МЕТА-ОПИСАНИЙ")
        print("=" * 60)
        print("Исправляем мета-описания, чтобы они начинались с фокусного ключевого слова")
        print("=" * 60)
        
        success_count = 0
        failed_count = 0
        
        for i, post_id in enumerate(ARTICLES_TO_FIX, 1):
            print(f"\n📋 {i}/{len(ARTICLES_TO_FIX)}")
            
            if self.fix_meta_description(post_id):
                success_count += 1
            else:
                failed_count += 1
            
            # Небольшая пауза между запросами
            if i < len(ARTICLES_TO_FIX):
                time.sleep(1)
        
        print(f"\n" + "=" * 60)
        print("📊 ИТОГОВЫЙ ОТЧЕТ")
        print("=" * 60)
        print(f"📚 Всего статей: {len(ARTICLES_TO_FIX)}")
        print(f"✅ Успешно исправлено: {success_count}")
        print(f"❌ Ошибки: {failed_count}")
        
        if success_count == len(ARTICLES_TO_FIX):
            print(f"\n🎉 ВСЕ МЕТА-ОПИСАНИЯ УСПЕШНО ИСПРАВЛЕНЫ!")
        else:
            print(f"\n⚠️  Требуется доработка {failed_count} статей")

def main():
    """Основная функция"""
    fixer = MetaDescriptionFixer()
    fixer.fix_all_meta_descriptions()

if __name__ == "__main__":
    main()
