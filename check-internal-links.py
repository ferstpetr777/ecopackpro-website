#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
🔍 ПРОВЕРКА ВНУТРЕННИХ ССЫЛОК НА КОД ОТВЕТА 200
Сайт: ecopackpro.ru
Цель: Проверить доступность всех созданных внутренних ссылок
"""

import mysql.connector
import requests
import time
from datetime import datetime
import logging
from urllib.parse import urlparse
import concurrent.futures
from threading import Lock

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/links_check.log'),
        logging.StreamHandler()
    ]
)

# Конфигурация базы данных
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

class InternalLinksChecker:
    def __init__(self):
        self.db_config = DB_CONFIG
        self.session = requests.Session()
        self.session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        })
        
        # Статистика проверки
        self.stats = {
            'total_links': 0,
            'working_links': 0,
            'broken_links': 0,
            'redirects': 0,
            'timeouts': 0,
            'errors': 0
        }
        
        self.lock = Lock()
        self.results = []
    
    def connect_to_database(self):
        """Подключение к базе данных"""
        try:
            connection = mysql.connector.connect(**self.db_config)
            return connection
        except mysql.connector.Error as e:
            logging.error(f"❌ Ошибка подключения к БД: {e}")
            return None
    
    def extract_internal_links(self):
        """Извлечение всех внутренних ссылок из статей"""
        logging.info("🔍 Извлекаем внутренние ссылки из статей...")
        
        connection = self.connect_to_database()
        if not connection:
            return []
        
        cursor = connection.cursor()
        
        # Ищем все внутренние ссылки в контенте статей
        cursor.execute("""
            SELECT ID, post_title, post_content 
            FROM wp_posts 
            WHERE post_status = 'publish' 
            AND post_type = 'post'
            AND post_content LIKE '%href="https://ecopackpro.ru/%'
        """)
        
        articles = cursor.fetchall()
        internal_links = []
        
        import re
        
        for article_id, title, content in articles:
            # Ищем все ссылки на ecopackpro.ru
            link_pattern = r'href="(https://ecopackpro\.ru/[^"]+)"'
            links = re.findall(link_pattern, content)
            
            for link in links:
                internal_links.append({
                    'url': link,
                    'source_article_id': article_id,
                    'source_title': title
                })
        
        connection.close()
        
        self.stats['total_links'] = len(internal_links)
        logging.info(f"📊 Найдено внутренних ссылок: {len(internal_links)}")
        
        return internal_links
    
    def check_single_link(self, link_info):
        """Проверка одной ссылки"""
        url = link_info['url']
        source_title = link_info['source_title']
        
        try:
            # Проверяем ссылку с таймаутом
            response = self.session.get(url, timeout=10, allow_redirects=True)
            
            result = {
                'url': url,
                'status_code': response.status_code,
                'source_title': source_title,
                'final_url': response.url,
                'redirect_count': len(response.history),
                'response_time': response.elapsed.total_seconds(),
                'error': None
            }
            
            with self.lock:
                if response.status_code == 200:
                    self.stats['working_links'] += 1
                    logging.info(f"✅ {url} - {response.status_code} ({result['response_time']:.2f}s)")
                elif 300 <= response.status_code < 400:
                    self.stats['redirects'] += 1
                    logging.warning(f"🔄 {url} - {response.status_code} (редирект на {response.url})")
                else:
                    self.stats['broken_links'] += 1
                    logging.error(f"❌ {url} - {response.status_code}")
            
            return result
            
        except requests.exceptions.Timeout:
            with self.lock:
                self.stats['timeouts'] += 1
            logging.error(f"⏰ {url} - Timeout")
            return {
                'url': url,
                'status_code': 'Timeout',
                'source_title': source_title,
                'error': 'Timeout'
            }
            
        except requests.exceptions.RequestException as e:
            with self.lock:
                self.stats['errors'] += 1
            logging.error(f"💥 {url} - Error: {str(e)}")
            return {
                'url': url,
                'status_code': 'Error',
                'source_title': source_title,
                'error': str(e)
            }
    
    def check_links_concurrent(self, links, max_workers=10):
        """Проверка ссылок параллельно"""
        logging.info(f"🚀 Начинаем проверку {len(links)} ссылок (параллельно, {max_workers} потоков)...")
        
        with concurrent.futures.ThreadPoolExecutor(max_workers=max_workers) as executor:
            # Отправляем все задачи
            future_to_link = {
                executor.submit(self.check_single_link, link): link 
                for link in links
            }
            
            # Собираем результаты
            for future in concurrent.futures.as_completed(future_to_link):
                try:
                    result = future.result()
                    self.results.append(result)
                except Exception as e:
                    logging.error(f"❌ Ошибка при проверке ссылки: {e}")
    
    def generate_links_report(self):
        """Генерация отчета о проверке ссылок"""
        logging.info("📋 Генерируем отчет о проверке ссылок...")
        
        # Анализируем результаты
        working_links = [r for r in self.results if r['status_code'] == 200]
        broken_links = [r for r in self.results if r['status_code'] != 200 and r['status_code'] != 'Timeout' and r['status_code'] != 'Error']
        redirect_links = [r for r in self.results if isinstance(r['status_code'], int) and 300 <= r['status_code'] < 400]
        error_links = [r for r in self.results if r['status_code'] in ['Timeout', 'Error']]
        
        report = f"""
# 🔍 ОТЧЕТ О ПРОВЕРКЕ ВНУТРЕННИХ ССЫЛОК
**Сайт:** ecopackpro.ru  
**Дата проверки:** {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}

## 📊 ОБЩАЯ СТАТИСТИКА

- **Всего проверено ссылок:** {self.stats['total_links']}
- **✅ Работающих ссылок (200):** {self.stats['working_links']} ({self.stats['working_links']/self.stats['total_links']*100:.1f}%)
- **❌ Неработающих ссылок:** {self.stats['broken_links']} ({self.stats['broken_links']/self.stats['total_links']*100:.1f}%)
- **🔄 Редиректов:** {self.stats['redirects']} ({self.stats['redirects']/self.stats['total_links']*100:.1f}%)
- **⏰ Таймаутов:** {self.stats['timeouts']}
- **💥 Ошибок:** {self.stats['errors']}

## ✅ РАБОТАЮЩИЕ ССЫЛКИ ({len(working_links)})

"""
        
        for link in working_links[:20]:  # Показываем первые 20
            report += f"- ✅ [{link['url']}]({link['url']}) - {link['response_time']:.2f}s\n"
        
        if len(working_links) > 20:
            report += f"... и еще {len(working_links) - 20} работающих ссылок\n"
        
        report += f"""
## ❌ НЕРАБОТАЮЩИЕ ССЫЛКИ ({len(broken_links)})

"""
        
        for link in broken_links:
            report += f"- ❌ [{link['url']}]({link['url']}) - код {link['status_code']}\n"
        
        report += f"""
## 🔄 РЕДИРЕКТЫ ({len(redirect_links)})

"""
        
        for link in redirect_links:
            report += f"- 🔄 [{link['url']}]({link['url']}) → [{link['final_url']}]({link['final_url']}) - код {link['status_code']}\n"
        
        if error_links:
            report += f"""
## 💥 ОШИБКИ И ТАЙМАУТЫ ({len(error_links)})

"""
            for link in error_links:
                report += f"- 💥 [{link['url']}]({link['url']}) - {link['error']}\n"
        
        # Анализ по статьям-источникам
        from collections import defaultdict
        articles_stats = defaultdict(lambda: {'total': 0, 'working': 0, 'broken': 0})
        
        for result in self.results:
            source = result['source_title']
            articles_stats[source]['total'] += 1
            if result['status_code'] == 200:
                articles_stats[source]['working'] += 1
            else:
                articles_stats[source]['broken'] += 1
        
        report += f"""
## 📈 АНАЛИЗ ПО СТАТЬЯМ-ИСТОЧНИКАМ

"""
        
        for article, stats in sorted(articles_stats.items(), key=lambda x: x[1]['total'], reverse=True)[:10]:
            success_rate = stats['working'] / stats['total'] * 100 if stats['total'] > 0 else 0
            report += f"- **{article}:** {stats['working']}/{stats['total']} ссылок ({success_rate:.1f}% работающих)\n"
        
        report += f"""
## 🎯 РЕКОМЕНДАЦИИ

"""
        
        if self.stats['broken_links'] > 0:
            report += f"1. **Исправить {self.stats['broken_links']} неработающих ссылок** - это критично для SEO\n"
        
        if self.stats['redirects'] > 0:
            report += f"2. **Оптимизировать {self.stats['redirects']} редиректов** - заменить на прямые ссылки\n"
        
        if self.stats['timeouts'] > 0:
            report += f"3. **Проверить {self.stats['timeouts']} ссылок с таймаутами** - возможно, проблемы с сервером\n"
        
        success_rate = self.stats['working_links'] / self.stats['total_links'] * 100 if self.stats['total_links'] > 0 else 0
        report += f"4. **Общий показатель успешности: {success_rate:.1f}%** - {'отлично' if success_rate > 95 else 'хорошо' if success_rate > 80 else 'требует улучшения'}\n"
        
        report += f"""
---
*Отчет сгенерирован автоматически системой проверки внутренних ссылок*
"""
        
        # Сохраняем отчет
        report_filename = f"/var/www/fastuser/data/www/ecopackpro.ru/links_check_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.md"
        with open(report_filename, 'w', encoding='utf-8') as f:
            f.write(report)
        
        logging.info(f"✅ Отчет сохранен: {report_filename}")
        return report_filename
    
    def run_links_check(self):
        """Запуск полной проверки ссылок"""
        logging.info("🔍 ЗАПУСК ПРОВЕРКИ ВНУТРЕННИХ ССЫЛОК")
        logging.info("=" * 50)
        
        start_time = datetime.now()
        
        # 1. Извлекаем внутренние ссылки
        links = self.extract_internal_links()
        if not links:
            logging.error("❌ Внутренние ссылки не найдены!")
            return False
        
        # 2. Проверяем ссылки параллельно
        self.check_links_concurrent(links, max_workers=15)
        
        # 3. Генерируем отчет
        report_file = self.generate_links_report()
        
        # Финальная статистика
        end_time = datetime.now()
        duration = end_time - start_time
        
        logging.info("=" * 50)
        logging.info("📊 ИТОГОВАЯ СТАТИСТИКА ПРОВЕРКИ")
        logging.info(f"⏱️ Время выполнения: {duration}")
        logging.info(f"🔗 Всего ссылок: {self.stats['total_links']}")
        logging.info(f"✅ Работающих: {self.stats['working_links']} ({self.stats['working_links']/self.stats['total_links']*100:.1f}%)")
        logging.info(f"❌ Неработающих: {self.stats['broken_links']} ({self.stats['broken_links']/self.stats['total_links']*100:.1f}%)")
        logging.info(f"🔄 Редиректов: {self.stats['redirects']}")
        logging.info(f"📄 Отчет: {report_file}")
        logging.info("=" * 50)
        
        return True

if __name__ == "__main__":
    # Создаем и запускаем проверку
    checker = InternalLinksChecker()
    checker.run_links_check()



