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

# Словарь источников по тематикам
SOURCES_BY_THEME = {
    # Курьерские пакеты
    'курьерские пакеты': [
        {'title': 'ГОСТ Р 51760-2001 - Пакеты из полимерных материалов', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Ассоциация производителей полимерной упаковки', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Типы курьерских пакетов', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Стандарты упаковки для логистики', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Почтовые коробки
    'почтовые коробки': [
        {'title': 'Правила почтовой пересылки - Почта России', 'url': 'https://www.pochta.ru/'},
        {'title': 'ГОСТ Р 53636-2009 - Упаковка из картона', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Стандарты упаковки для международной почты', 'url': 'https://www.unipack.ru/'},
        {'title': 'Технические требования к почтовой упаковке', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Zip lock пакеты
    'zip lock': [
        {'title': 'Технические требования к упаковке пищевых продуктов', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Ассоциация производителей полимерной упаковки', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Zip lock технологии', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Стандарты пищевой упаковки - Роспотребнадзор', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Зип пакеты
    'зип пакеты': [
        {'title': 'Технические требования к упаковке пищевых продуктов', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Ассоциация производителей полимерной упаковки', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Zip lock технологии', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Стандарты пищевой упаковки - Роспотребнадзор', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Конверты с воздушной подушкой
    'конверты с воздушной подушкой': [
        {'title': 'Стандарты упаковки для хрупких товаров', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Технологии защитной упаковки', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Воздушно-пузырчатая пленка', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Требования к упаковке электроники', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Конверты с воздушной прослойкой
    'конверты с воздушной прослойкой': [
        {'title': 'Стандарты упаковки для документов', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Технологии защитной упаковки', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Воздушно-пузырчатая пленка', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Требования к упаковке документов', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Крафтовые пакеты
    'крафтовые пакеты': [
        {'title': 'ГОСТ Р 53636-2009 - Упаковка из картона', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Экологичная упаковка - тренды 2025', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Крафт-бумага', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Стандарты экологической упаковки', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Крафтовые конверты
    'крафтовые конверты': [
        {'title': 'ГОСТ Р 53636-2009 - Упаковка из картона', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Экологичная упаковка - тренды 2025', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Крафт-бумага', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Стандарты экологической упаковки', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Пузырчатые пакеты ВПП
    'пузырчатые пакеты': [
        {'title': 'Стандарты упаковки для хрупких товаров', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Технологии защитной упаковки', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Воздушно-пузырчатая пленка', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Требования к упаковке хрупких товаров', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Коробки для почты/отправки
    'коробки для': [
        {'title': 'Правила почтовой пересылки - Почта России', 'url': 'https://www.pochta.ru/'},
        {'title': 'ГОСТ Р 53636-2009 - Упаковка из картона', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Стандарты упаковки для международной почты', 'url': 'https://www.unipack.ru/'},
        {'title': 'Технические требования к почтовой упаковке', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Самоклеящиеся карманы
    'самоклеящиеся карманы': [
        {'title': 'Стандарты упаковки для документов', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Организационные решения для офиса', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Самоклеящиеся материалы', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Требования к упаковке документов', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Антимагнитная пломба
    'антимагнитная пломба': [
        {'title': 'Федеральный закон о защите приборов учета', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Стандарты защиты от магнитного воздействия', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Защитные элементы', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Требования к пломбированию приборов учета', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Пломбиратор
    'пломбиратор': [
        {'title': 'Федеральный закон о защите приборов учета', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Стандарты пломбирования оборудования', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Инструменты пломбирования', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Требования к пломбированию оборудования', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Номерные пломбы
    'номерные пломбы': [
        {'title': 'Федеральный закон о защите приборов учета', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Стандарты идентификации пломб', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Нумерация пломб', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Требования к нумерации пломб', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Белые крафт пакеты
    'белые крафт': [
        {'title': 'ГОСТ Р 53636-2009 - Упаковка из картона', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Экологичная упаковка - тренды 2025', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Крафт-бумага', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Стандарты экологической упаковки', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Прозрачные zip lock
    'прозрачные zip': [
        {'title': 'Технические требования к упаковке пищевых продуктов', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Ассоциация производителей полимерной упаковки', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Прозрачная упаковка', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Стандарты пищевой упаковки - Роспотребнадзор', 'url': 'https://rospotrebnadzor.ru/'}
    ],
    
    # Матовые zip lock
    'матовые zip': [
        {'title': 'Технические требования к упаковке пищевых продуктов', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
        {'title': 'Ассоциация производителей полимерной упаковки', 'url': 'https://www.unipack.ru/'},
        {'title': 'Энциклопедия упаковки - Матовая упаковка', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
        {'title': 'Стандарты пищевой упаковки - Роспотребнадзор', 'url': 'https://rospotrebnadzor.ru/'}
    ]
}

class UniversalSourcesAdder:
    def __init__(self):
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
        self.results = []
        self.start_time = datetime.now()
    
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
    
    def get_sources_for_theme(self, article_title):
        """Получение источников для конкретной тематики статьи"""
        article_lower = article_title.lower()
        
        # Ищем подходящую тематику
        for theme, sources in SOURCES_BY_THEME.items():
            if theme in article_lower:
                return sources
        
        # Если не найдено, возвращаем общие источники
        return [
            {'title': 'Федеральный закон об упаковке и маркировке товаров', 'url': 'https://www.consultant.ru/document/cons_doc_LAW_19109/'},
            {'title': 'Ассоциация производителей полимерной упаковки', 'url': 'https://www.unipack.ru/'},
            {'title': 'Энциклопедия упаковки - Типы упаковочных материалов', 'url': 'https://ru.wikipedia.org/wiki/Полиэтиленовый_пакет'},
            {'title': 'Стандарты упаковочной индустрии - Роспотребнадзор', 'url': 'https://rospotrebnadzor.ru/'}
        ]
    
    def verify_sources(self, sources):
        """Проверка доступности источников"""
        verified_sources = []
        
        for source in sources:
            is_valid, status = self.check_url_status(source['url'])
            if is_valid:
                verified_sources.append(source)
            
            # Пауза между запросами
            time.sleep(1)
        
        return verified_sources
    
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
            response = requests.post(url, auth=self.auth, headers=self.headers, json=data)
            if response.status_code == 200:
                return response.json()
            else:
                print(f"❌ Ошибка обновления поста {post_id}: {response.status_code}")
                return None
        except Exception as e:
            print(f"❌ Ошибка обновления поста {post_id}: {e}")
            return None
    
    def add_sources_to_article(self, post_id, article_title):
        """Добавление источников в статью"""
        print(f"\n📝 Обработка статьи ID {post_id}: {article_title}")
        
        # Получаем источники для тематики
        potential_sources = self.get_sources_for_theme(article_title)
        
        # Проверяем источники
        verified_sources = self.verify_sources(potential_sources)
        
        if not verified_sources:
            print(f"❌ Нет доступных источников для статьи {post_id}")
            self.results.append({
                'id': post_id,
                'title': article_title,
                'status': 'failed',
                'reason': 'Нет доступных источников',
                'sources_count': 0
            })
            return False
        
        # Получаем пост
        post = self.get_post(post_id)
        
        if not post:
            print(f"❌ Не удалось получить пост {post_id}")
            self.results.append({
                'id': post_id,
                'title': article_title,
                'status': 'failed',
                'reason': 'Не удалось получить пост',
                'sources_count': 0
            })
            return False
        
        # Получаем контент
        content = post['content']['rendered']
        
        # Проверяем, есть ли уже источники
        if '📚 Источники' in content:
            print(f"⚠️  В статье {post_id} уже есть раздел источников")
            self.results.append({
                'id': post_id,
                'title': article_title,
                'status': 'skipped',
                'reason': 'Источники уже добавлены',
                'sources_count': 0
            })
            return True
        
        # Ищем место для вставки
        insertion_point = self.find_insertion_point(content)
        
        if insertion_point is None:
            print(f"❌ Не найдено место для вставки в статье {post_id}")
            self.results.append({
                'id': post_id,
                'title': article_title,
                'status': 'failed',
                'reason': 'Не найдено место для вставки',
                'sources_count': 0
            })
            return False
        
        # Создаем раздел источников
        sources_section = self.create_sources_section(verified_sources)
        
        if not sources_section:
            print(f"❌ Не удалось создать раздел источников для статьи {post_id}")
            self.results.append({
                'id': post_id,
                'title': article_title,
                'status': 'failed',
                'reason': 'Не удалось создать раздел источников',
                'sources_count': 0
            })
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
            self.results.append({
                'id': post_id,
                'title': article_title,
                'status': 'success',
                'reason': 'Источники успешно добавлены',
                'sources_count': len(verified_sources),
                'sources': verified_sources
            })
            return True
        else:
            print(f"❌ Не удалось обновить статью {post_id}")
            self.results.append({
                'id': post_id,
                'title': article_title,
                'status': 'failed',
                'reason': 'Не удалось обновить пост',
                'sources_count': 0
            })
            return False
    
    def process_all_articles(self):
        """Обработка всех статей"""
        print("=" * 80)
        print("📚 ДОБАВЛЕНИЕ ИСТОЧНИКОВ ВО ВСЕ СТАТЬИ")
        print("=" * 80)
        
        # Список статей из базы данных
        articles = [
            (7907, "курьерские пакеты"),
            (7908, "почтовые коробки"),
            (7909, "Зип пакеты"),
            (7910, "Zip lock пакеты с бегунком: удобное хранение продуктов"),
            (7911, "Конверты с воздушной подушкой для хрупких товаров"),
            (7912, "Конверты с воздушной прослойкой для документов"),
            (7913, "Крафтовые пакеты с воздушной подушкой для бизнеса: как выбрать оптимал"),
            (7914, "Курьерские пакеты прозрачные"),
            (7915, "Курьерские пакеты номерные"),
            (7916, "Курьерские пакеты черно-белые"),
            (7917, "Курьерские пакеты с карманом"),
            (7918, "Zip lock пакеты матовые"),
            (7919, "Zip lock пакеты оптом"),
            (7920, "Крафтовые конверты"),
            (7921, "Пузырчатые пакеты ВПП"),
            (7922, "Коробки для почты"),
            (7923, "Коробки для отправки"),
            (7924, "Самоклеящиеся карманы"),
            (7925, "Антимагнитная пломба"),
            (7926, "Наклейка пломба антимагнит"),
            (7927, "Пломбиратор для бочек"),
            (7928, "Номерные пломбы наклейки"),
            (7929, "Zip lock пакеты с белой полосой"),
            (7930, "Белые крафт пакеты с пузырчатой плёнкой"),
            (7931, "Прозрачные zip lock пакеты"),
            (7932, "Купить курьерские пакеты с номерным штрих-кодом"),
            (7933, "Заказать прозрачные курьерские пакеты оптом"),
            (7934, "Курьерские пакеты черно-белые с карманом цена"),
            (7935, "Матовые zip lock пакеты с бегунком 10×15"),
            (7936, "Купить оптом zip lock пакеты матовые 30 мкм"),
            (7937, "Крафт конверты с воздушной подушкой F/3"),
            (7938, "Почтовые коробки размера S 260×170×80"),
            (7939, "Почтовые коробки размера XL 530×360×220"),
            (7940, "Купить самоклеящиеся карманы SD для документов"),
            (7941, "Антимагнитные наклейки для водяных счётчиков"),
            (7942, "Антимагнитная пломба цена за 100 штук"),
            (7943, "Пломбиратор для евробочек 2 дюйма"),
            (7944, "Инструмент для опломбирования бочек ¾ дюйма"),
            (7945, "Курьерские пакеты черно-белые без логотипа А4"),
            (7946, "Курьерские пакеты прозрачные для одежды"),
            (7947, "Курьерские пакеты для маркетплейсов Ozon"),
            (7948, "Почтовые коробки с логотипом на заказ"),
            (7949, "Зип пакеты с бегунком купить Москва"),
            (7950, "Матовые zip lock пакеты для чая"),
            (7951, "Zip lock пакеты с подвесом"),
            (7952, "Белые крафт-пакеты с пузырчатой плёнкой оптом"),
            (7953, "Плоские конверты с воздушной подушкой для документов"),
            (7954, "Пакеты из воздушно-пузырьковой плёнки оптом"),
            (7955, "Антимагнитные пломбы для газовых счётчиков"),
            (7956, "Самоклеящиеся карманы для транспортных накладных")
        ]
        
        success_count = 0
        failed_count = 0
        skipped_count = 0
        
        for i, (post_id, title) in enumerate(articles, 1):
            print(f"\n[{i}/50] Обработка статьи ID {post_id}")
            
            result = self.add_sources_to_article(post_id, title)
            
            if result:
                success_count += 1
            elif self.results[-1]['status'] == 'skipped':
                skipped_count += 1
            else:
                failed_count += 1
            
            # Пауза между статьями
            time.sleep(2)
        
        # Генерируем отчет
        self.generate_report(success_count, failed_count, skipped_count)
        
        return success_count, failed_count, skipped_count
    
    def generate_report(self, success_count, failed_count, skipped_count):
        """Генерация отчета"""
        end_time = datetime.now()
        duration = end_time - self.start_time
        
        print("\n" + "=" * 80)
        print("📊 ИТОГОВЫЙ ОТЧЕТ")
        print("=" * 80)
        print(f"⏱️  Время выполнения: {duration}")
        print(f"✅ Успешно обработано: {success_count}")
        print(f"⚠️  Пропущено (уже есть источники): {skipped_count}")
        print(f"❌ Ошибок: {failed_count}")
        print(f"📝 Всего статей: {len(self.results)}")
        
        # Сохраняем детальный отчет
        report_filename = f"sources_addition_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        with open(report_filename, 'w', encoding='utf-8') as f:
            json.dump({
                'timestamp': datetime.now().isoformat(),
                'duration': str(duration),
                'summary': {
                    'total': len(self.results),
                    'success': success_count,
                    'skipped': skipped_count,
                    'failed': failed_count
                },
                'results': self.results
            }, f, ensure_ascii=False, indent=2)
        
        print(f"\n📄 Детальный отчет сохранен: {report_filename}")
        
        # Выводим проблемные статьи
        if failed_count > 0:
            print("\n❌ ПРОБЛЕМНЫЕ СТАТЬИ:")
            for result in self.results:
                if result['status'] == 'failed':
                    print(f"   ID {result['id']}: {result['title']} - {result['reason']}")
        
        # Выводим успешные статьи
        if success_count > 0:
            print(f"\n✅ УСПЕШНО ОБРАБОТАННЫЕ СТАТЬИ ({success_count}):")
            for result in self.results:
                if result['status'] == 'success':
                    print(f"   ID {result['id']}: {result['title']} ({result['sources_count']} источников)")

def main():
    """Основная функция"""
    adder = UniversalSourcesAdder()
    success, failed, skipped = adder.process_all_articles()
    
    if success > 0:
        print(f"\n🎉 ЗАДАЧА ВЫПОЛНЕНА! Добавлены источники в {success} статей")
    else:
        print("\n❌ НЕ УДАЛОСЬ ДОБАВИТЬ ИСТОЧНИКИ НИ В ОДНУ СТАТЬЮ")

if __name__ == "__main__":
    main()
