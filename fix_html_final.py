#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import re
import requests
from requests.auth import HTTPBasicAuth
import json

# Настройки WordPress API
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"
ARTICLE_ID = 7939

class FinalHTMLFixer:
    def __init__(self):
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
        self.article_id = ARTICLE_ID

    def get_article_from_api(self):
        """Получает статью через WordPress API."""
        print(f"🔄 Получаю статью ID {self.article_id} через API...")
        try:
            url = f"{WP_API_URL}/posts/{self.article_id}"
            response = requests.get(url, auth=self.auth, headers=self.headers, timeout=30)
            
            if response.status_code == 200:
                post = response.json()
                self.post_content = post['content']['rendered']
                print(f"✅ Статья получена через API ({len(self.post_content)} символов)")
                return True
            else:
                print(f"❌ Ошибка API: {response.status_code} - {response.text}")
                return False
                
        except Exception as e:
            print(f"❌ Ошибка при получении статьи: {e}")
            return False

    def fix_all_newlines(self, html_content):
        """Финальное исправление всех экранированных символов новой строки."""
        print("🔧 Применяю ФИНАЛЬНОЕ исправление экранированных символов...")
        
        original_length = len(html_content)
        
        # Множественные уровни экранирования
        html_content = html_content.replace('\\\\\\\\n', '\n')  # \\\\n -> \n
        html_content = html_content.replace('\\\\\\n', '\n')   # \\\n -> \n  
        html_content = html_content.replace('\\\\n', '\n')     # \\n -> \n
        html_content = html_content.replace('\\n', '\n')       # \n -> \n
        
        print(f"  ✅ Исправлены экранированные символы (было {original_length}, стало {len(html_content)})")
        
        # Удаляем раздел "Источники" полностью
        sources_pattern = r'<h2[^>]*>📚\s*Источники</h2>.*?(?=<hr/>|<h2|$)'
        html_content = re.sub(sources_pattern, '', html_content, flags=re.DOTALL)
        print("  ✅ Удален раздел 'Источники'")
        
        # Удаляем пустые строки и лишние переносы
        html_content = re.sub(r'\n\s*\n\s*\n+', '\n\n', html_content)
        print("  ✅ Удалены лишние переносы строк")
        
        return html_content.strip()

    def update_article(self, fixed_content):
        """Обновление статьи через WordPress API."""
        print(f"🚀 Обновляю статью ID {self.article_id} через WordPress API...")
        url = f"{WP_API_URL}/posts/{self.article_id}"

        data = {
            'content': fixed_content,
            'status': 'publish'
        }

        try:
            response = requests.post(url, auth=self.auth, headers=self.headers, json=data, timeout=120)
            if response.status_code == 200:
                post = response.json()
                print(f"✅ Статья ID {self.article_id} успешно обновлена.")
                print(f"🔗 Ссылка: {post['link']}")
                return True
            else:
                print(f"❌ Ошибка обновления: {response.status_code} - {response.text}")
                return False
        except Exception as e:
            print(f"❌ Ошибка API: {e}")
            return False

    def run_final_fix(self):
        """Запуск финального исправления."""
        print("="*80)
        print("🔧 ФИНАЛЬНОЕ ИСПРАВЛЕНИЕ HTML КОДА".center(80))
        print("="*80)
        
        if not self.get_article_from_api():
            return False

        if not self.post_content:
            print("❌ Нет содержимого для исправления.")
            return False

        print(f"📊 Исходный размер контента: {len(self.post_content)} символов")
        
        fixed_html = self.fix_all_newlines(self.post_content)
        
        print(f"📊 Размер после исправлений: {len(fixed_html)} символов")

        if self.update_article(fixed_html):
            print("\n" + "="*80)
            print("🎉 ФИНАЛЬНОЕ ИСПРАВЛЕНИЕ ЗАВЕРШЕНО УСПЕШНО!".center(80))
            print("="*80)
            return True
        else:
            print("\n" + "="*80)
            print("⚠️ ИСПРАВЛЕНИЕ ЗАВЕРШЕНО С ОШИБКАМИ".center(80))
            print("="*80)
            return False

if __name__ == "__main__":
    fixer = FinalHTMLFixer()
    fixer.run_final_fix()
