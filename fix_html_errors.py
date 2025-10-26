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
ARTICLE_ID = 7939 # ID статьи для исправления

class HTMLFixer:
    def __init__(self):
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
        self.article_id = ARTICLE_ID
        self.post_content = ""

    def get_article_content_from_db(self):
        """Получает содержимое статьи из базы данных."""
        print(f"🔄 Получаю содержимое статьи ID {self.article_id} из БД...")
        try:
            # Используем прямой запрос к MySQL для получения post_content
            # Это имитация, в реальном скрипте нужно использовать subprocess или ORM
            # Для данного контекста, я буду использовать ранее сохраненный файл /tmp/article_7939_content.html
            with open(f"/tmp/article_{self.article_id}_content.html", "r", encoding="utf-8") as f:
                self.post_content = f.read()
            print(f"✅ Содержимое статьи ID {self.article_id} получено.")
            return True
        except FileNotFoundError:
            print(f"❌ Ошибка: Файл /tmp/article_{self.article_id}_content.html не найден.")
            return False
        except Exception as e:
            print(f"❌ Ошибка при получении содержимого статьи из файла: {e}")
            return False

    def fix_html_structure(self, html_content):
        """Исправление структуры HTML согласно аудиту."""
        print("🔧 Применяю исправления HTML структуры...")

        # ОШИБКА №1: ЭКРАНИРОВАННЫЕ СИМВОЛЫ НОВОЙ СТРОКИ
        # Заменить \\n на реальные переносы строк
        html_content = html_content.replace('\\n', '\n')
        print("  - Исправлены экранированные символы новой строки.")

        # Используем BeautifulSoup для более надежного парсинга и манипуляций
        soup = BeautifulSoup(html_content, 'html.parser')

        # ПРОБЛЕМА №7: РАЗДЕЛ "ИСТОЧНИКИ" ПРИСУТСТВУЕТ
        # Удалить раздел "Источники"
        sources_heading = soup.find('h3', string=re.compile(r'📚\s*Источники'))
        if sources_heading:
            sources_block = sources_heading.find_parent('div', class_='wp-block-group')
            if sources_block:
                sources_block.decompose()
                print("  - Раздел 'Источники' удален.")
            else:
                # Если не нашли wp-block-group, попробуем найти ближайший div или section
                current_element = sources_heading
                while current_element and current_element.name != 'div' and current_element.name != 'section':
                    current_element = current_element.parent
                if current_element:
                    current_element.decompose()
                    print("  - Раздел 'Источники' удален (альтернативный метод).")
        
        # ОШИБКА №2, №3, №4: НЕПРАВИЛЬНАЯ СТРУКТУРА НАВИГАЦИИ, ПУСТЫЕ <p> ТЕГИ, НЕПРАВИЛЬНОЕ ЗАКРЫТИЕ БЛОКОВ
        # Исправить структуру навигационного блока (убрать <p> теги внутри)
        # Предполагаем, что навигационный блок имеет класс 'table-of-contents'
        toc_div = soup.find('div', class_='table-of-contents')
        if toc_div:
            print("  - Исправляю структуру навигационного блока...")
            for p_tag in toc_div.find_all('p'):
                # Если <p> содержит <a>, перемещаем <a> на уровень выше и удаляем <p>
                a_tag = p_tag.find('a')
                if a_tag:
                    p_tag.replace_with(a_tag)
                else:
                    # Если <p> пустой или содержит только пробелы, удаляем его
                    if not p_tag.get_text(strip=True):
                        p_tag.decompose()
            print("  - Структура навигационного блока исправлена.")

        # ОШИБКА №5: НЕКОРРЕКТНАЯ СТРУКТУРА СПИСКОВ
        # Вынести <hr/> за пределы абзацев
        for hr_tag in soup.find_all('hr'):
            if hr_tag.parent and hr_tag.parent.name == 'p':
                hr_tag.parent.insert_after(hr_tag)
                hr_tag.parent.decompose() # Удаляем теперь пустой <p>
                print("  - Исправлено расположение тега <hr/>.")
        
        # Удаление любых оставшихся пустых <p> тегов
        for p_tag in soup.find_all('p'):
            if not p_tag.get_text(strip=True) and not p_tag.find_all(True): # Проверяем, что нет дочерних тегов
                p_tag.decompose()
                # print("  - Удален пустой <p> тег.") # Может быть много, не выводим каждый раз

        # Возвращаем очищенный HTML
        return str(soup)

    def update_article(self, fixed_content):
        """Обновление статьи через WordPress API."""
        print(f"🚀 Обновляю статью ID {self.article_id} через WordPress API...")
        url = f"{WP_API_URL}/posts/{self.article_id}"

        data = {
            'content': fixed_content,
            'status': 'publish' # Убедимся, что статья остается опубликованной
        }

        try:
            response = requests.post(url, auth=self.auth, headers=self.headers, json=data, timeout=120)
            if response.status_code == 200:
                post = response.json()
                print(f"✅ Статья ID {self.article_id} успешно обновлена. Ссылка: {post['link']}")
                return True
            else:
                print(f"❌ Ошибка обновления статьи ID {self.article_id}: {response.status_code} - {response.text}")
                return False
        except requests.exceptions.RequestException as e:
            print(f"❌ Ошибка при запросе к API для статьи ID {self.article_id}: {e}")
            return False
        except Exception as e:
            print(f"❌ Неизвестная ошибка при обновлении статьи ID {self.article_id}: {e}")
            return False

    def run_fix(self):
        if not self.get_article_content_from_db():
            return False

        if not self.post_content:
            print("❌ Нет содержимого для исправления.")
            return False

        fixed_html = self.fix_html_structure(self.post_content)

        if self.update_article(fixed_html):
            print(f"🎉 Исправление статьи ID {self.article_id} завершено успешно!")
            return True
        else:
            print(f"⚠️ Исправление статьи ID {self.article_id} завершено с ошибками при обновлении.")
            return False

if __name__ == "__main__":
    fixer = HTMLFixer()
    fixer.run_fix()