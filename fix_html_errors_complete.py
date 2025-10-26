#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import re
from bs4 import BeautifulSoup
import requests
from requests.auth import HTTPBasicAuth
import json
import time

# Настройки WordPress API
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"
ARTICLE_ID = 7939

class CompleteHTMLFixer:
    def __init__(self):
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
        self.article_id = ARTICLE_ID

    def get_article_content_from_db(self):
        """Получает содержимое статьи из базы данных."""
        print(f"🔄 Получаю содержимое статьи ID {self.article_id} из БД...")
        try:
            import subprocess
            result = subprocess.run([
                'mysql', '-u', 'm1shqamai2_worp6', 
                f'-p9nUQkM*Q2cnvy379', 'm1shqamai2_worp6', 
                '-e', f'SELECT post_content FROM wp_posts WHERE ID = {self.article_id};'
            ], capture_output=True, text=True, timeout=30)
            
            if result.returncode == 0:
                lines = result.stdout.strip().split('\n')
                if len(lines) > 1:
                    self.post_content = '\n'.join(lines[1:])  # Пропускаем заголовок
                    print(f"✅ Содержимое статьи ID {self.article_id} получено из БД.")
                    return True
            
            print(f"❌ Не удалось получить содержимое из БД.")
            return False
            
        except Exception as e:
            print(f"❌ Ошибка при получении содержимого статьи из БД: {e}")
            return False

    def fix_html_structure(self, html_content):
        """Полное исправление структуры HTML."""
        print("🔧 Применяю ПОЛНОЕ исправление HTML структуры...")
        
        # ЭТАП 1: Заменить ВСЕ экранированные символы \\n на реальные переносы строк
        original_length = len(html_content)
        html_content = html_content.replace('\\n', '\n')
        html_content = html_content.replace('\\\\n', '\n')  # Двойное экранирование
        print(f"  ✅ Исправлены экранированные символы новой строки (было {original_length} символов)")

        # ЭТАП 2: Парсинг через BeautifulSoup
        soup = BeautifulSoup(html_content, 'html.parser')
        
        # ЭТАП 3: Удаление раздела "Источники" полностью
        sources_removed = 0
        
        # Ищем заголовок "📚 Источники"
        sources_heading = soup.find('h2', string=re.compile(r'📚\s*Источники'))
        if sources_heading:
            # Найдем родительский блок и удалим его полностью
            parent = sources_heading.parent
            while parent and parent.name not in ['div', 'section', 'article']:
                parent = parent.parent
            
            if parent:
                # Удаляем весь блок от заголовка до следующего hr или конца
                current = sources_heading
                while current:
                    next_sibling = current.next_sibling
                    if current.name == 'hr' and current != sources_heading:
                        break
                    current.decompose()
                    sources_removed += 1
                    current = next_sibling
                    if not current or current.name in ['h1', 'h2', 'h3']:
                        break
                print(f"  ✅ Удален раздел 'Источники' ({sources_removed} элементов)")
        
        # ЭТАП 4: Исправление навигационного блока
        toc_fixes = 0
        toc_div = soup.find('div', class_='table-of-contents')
        if toc_div:
            # Удаляем все <p> теги внутри навигации
            for p_tag in toc_div.find_all('p'):
                if p_tag.find('a'):
                    # Если <p> содержит <a>, извлекаем <a> и заменяем <p>
                    a_tag = p_tag.find('a')
                    p_tag.replace_with(a_tag)
                    toc_fixes += 1
                else:
                    # Пустой <p> просто удаляем
                    p_tag.decompose()
                    toc_fixes += 1
            print(f"  ✅ Исправлена структура навигации ({toc_fixes} исправлений)")
        
        # ЭТАП 5: Удаление ВСЕХ пустых <p> тегов
        empty_p_count = 0
        for p_tag in soup.find_all('p'):
            text_content = p_tag.get_text(strip=True)
            if not text_content and not p_tag.find_all(True):
                p_tag.decompose()
                empty_p_count += 1
        print(f"  ✅ Удалено пустых <p> тегов: {empty_p_count}")
        
        # ЭТАП 6: Исправление структуры навигации в конце статьи
        nav_fixes = 0
        # Ищем блок навигации по статьям в конце
        nav_divs = soup.find_all('div', style=re.compile(r'background:\s*linear-gradient'))
        for nav_div in nav_divs:
            # Удаляем <p> теги внутри навигационных ссылок
            for p_tag in nav_div.find_all('p'):
                if p_tag.find('a'):
                    a_tag = p_tag.find('a')
                    p_tag.replace_with(a_tag)
                    nav_fixes += 1
                elif not p_tag.get_text(strip=True):
                    p_tag.decompose()
                    nav_fixes += 1
        print(f"  ✅ Исправлена навигация в конце статьи ({nav_fixes} исправлений)")
        
        # ЭТАП 7: Финальная очистка - удаление всех оставшихся проблемных элементов
        final_cleans = 0
        
        # Удаляем <hr/> теги внутри <p>
        for hr_tag in soup.find_all('hr'):
            if hr_tag.parent and hr_tag.parent.name == 'p':
                hr_tag.parent.insert_after(hr_tag)
                hr_tag.parent.decompose()
                final_cleans += 1
        
        # Удаляем пустые div с только пробелами
        for div_tag in soup.find_all('div'):
            if not div_tag.get_text(strip=True) and not div_tag.find_all(True):
                div_tag.decompose()
                final_cleans += 1
                
        print(f"  ✅ Финальная очистка ({final_cleans} элементов)")
        
        # Возвращаем очищенный HTML
        return str(soup)

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

    def run_complete_fix(self):
        """Запуск полного исправления."""
        print("="*80)
        print("🔧 ПОЛНОЕ ИСПРАВЛЕНИЕ HTML КОДА СТАТЬИ".center(80))
        print("="*80)
        
        if not self.get_article_content_from_db():
            return False

        if not self.post_content:
            print("❌ Нет содержимого для исправления.")
            return False

        print(f"📊 Исходный размер контента: {len(self.post_content)} символов")
        
        fixed_html = self.fix_html_structure(self.post_content)
        
        print(f"📊 Размер после исправлений: {len(fixed_html)} символов")

        if self.update_article(fixed_html):
            print("\n" + "="*80)
            print("🎉 ПОЛНОЕ ИСПРАВЛЕНИЕ ЗАВЕРШЕНО УСПЕШНО!".center(80))
            print("="*80)
            return True
        else:
            print("\n" + "="*80)
            print("⚠️ ИСПРАВЛЕНИЕ ЗАВЕРШЕНО С ОШИБКАМИ".center(80))
            print("="*80)
            return False

if __name__ == "__main__":
    fixer = CompleteHTMLFixer()
    fixer.run_complete_fix()
