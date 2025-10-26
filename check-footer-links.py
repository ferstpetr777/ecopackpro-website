#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
🔍 ПРОВЕРКА ССЫЛОК В ПОДВАЛЕ САЙТА
Сайт: ecopackpro.ru
Цель: Проверить ссылки в футере сайта
"""

import requests
import re
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
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/footer_links_check.log'),
        logging.StreamHandler()
    ]
)

class FooterLinksChecker:
    def __init__(self):
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
    
    def get_page_content(self, url):
        """Получение содержимого страницы"""
        try:
            response = self.session.get(url, timeout=10)
            return response.text
        except Exception as e:
            logging.error(f"❌ Ошибка получения страницы {url}: {e}")
            return None
    
    def extract_footer_links(self, url):
        """Извлечение ссылок из подвала страницы"""
        logging.info(f"🔍 Извлекаем ссылки из подвала страницы: {url}")
        
        content = self.get_page_content(url)
        if not content:
            return []
        
        # Ищем подвал сайта (footer)
        footer_patterns = [
            r'<footer[^>]*>(.*?)</footer>',
            r'<div[^>]*class="[^"]*footer[^"]*"[^>]*>(.*?)</div>',
            r'<div[^>]*id="[^"]*footer[^"]*"[^>]*>(.*?)</div>',
            r'<!-- footer -->(.*?)<!-- /footer -->',
            r'<!-- Подвал -->(.*?)<!-- /Подвал -->'
        ]
        
        footer_content = ""
        for pattern in footer_patterns:
            match = re.search(pattern, content, re.DOTALL | re.IGNORECASE)
            if match:
                footer_content = match.group(1)
                logging.info(f"✅ Найден подвал по паттерну: {pattern}")
                break
        
        if not footer_content:
            # Если не нашли footer, ищем в конце страницы
            logging.warning("⚠️ Подвал не найден, ищем ссылки в конце страницы")
            # Берем последние 2000 символов страницы
            footer_content = content[-2000:]
        
        # Извлекаем все ссылки из подвала
        link_pattern = r'href="([^"]+)"'
        links = re.findall(link_pattern, footer_content)
        
        # Фильтруем только внутренние ссылки
        internal_links = []
        for link in links:
            if link.startswith('/') or 'ecopackpro.ru' in link:
                if not link.startswith('http'):
                    link = 'https://ecopackpro.ru' + link
                internal_links.append(link)
        
        logging.info(f"📊 Найдено ссылок в подвале: {len(internal_links)}")
        return internal_links
    
    def check_single_link(self, link):
        """Проверка одной ссылки"""
        try:
            response = self.session.get(link, timeout=10, allow_redirects=True)
            
            result = {
                'url': link,
                'status_code': response.status_code,
                'final_url': response.url,
                'redirect_count': len(response.history),
                'response_time': response.elapsed.total_seconds(),
                'error': None
            }
            
            with self.lock:
                if response.status_code == 200:
                    self.stats['working_links'] += 1
                    logging.info(f"✅ {link} - {response.status_code} ({result['response_time']:.2f}s)")
                elif 300 <= response.status_code < 400:
                    self.stats['redirects'] += 1
                    logging.warning(f"🔄 {link} - {response.status_code} (редирект на {response.url})")
                else:
                    self.stats['broken_links'] += 1
                    logging.error(f"❌ {link} - {response.status_code}")
            
            return result
            
        except requests.exceptions.Timeout:
            with self.lock:
                self.stats['timeouts'] += 1
            logging.error(f"⏰ {link} - Timeout")
            return {
                'url': link,
                'status_code': 'Timeout',
                'error': 'Timeout'
            }
            
        except requests.exceptions.RequestException as e:
            with self.lock:
                self.stats['errors'] += 1
            logging.error(f"💥 {link} - Error: {str(e)}")
            return {
                'url': link,
                'status_code': 'Error',
                'error': str(e)
            }
    
    def check_links_concurrent(self, links, max_workers=10):
        """Проверка ссылок параллельно"""
        logging.info(f"🚀 Начинаем проверку {len(links)} ссылок из подвала...")
        
        with concurrent.futures.ThreadPoolExecutor(max_workers=max_workers) as executor:
            future_to_link = {
                executor.submit(self.check_single_link, link): link 
                for link in links
            }
            
            for future in concurrent.futures.as_completed(future_to_link):
                try:
                    result = future.result()
                    self.results.append(result)
                except Exception as e:
                    logging.error(f"❌ Ошибка при проверке ссылки: {e}")
    
    def generate_footer_report(self):
        """Генерация отчета о проверке ссылок в подвале"""
        logging.info("📋 Генерируем отчет о проверке ссылок в подвале...")
        
        working_links = [r for r in self.results if r['status_code'] == 200]
        broken_links = [r for r in self.results if r['status_code'] != 200 and r['status_code'] != 'Timeout' and r['status_code'] != 'Error']
        redirect_links = [r for r in self.results if isinstance(r['status_code'], int) and 300 <= r['status_code'] < 400]
        error_links = [r for r in self.results if r['status_code'] in ['Timeout', 'Error']]
        
        report = f"""
# 🔍 ОТЧЕТ О ПРОВЕРКЕ ССЫЛОК В ПОДВАЛЕ САЙТА
**Сайт:** ecopackpro.ru  
**Дата проверки:** {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}

## 📊 ОБЩАЯ СТАТИСТИКА

- **Всего проверено ссылок в подвале:** {self.stats['total_links']}
- **✅ Работающих ссылок (200):** {self.stats['working_links']} ({self.stats['working_links']/self.stats['total_links']*100:.1f}%)
- **❌ Неработающих ссылок:** {self.stats['broken_links']} ({self.stats['broken_links']/self.stats['total_links']*100:.1f}%)
- **🔄 Редиректов:** {self.stats['redirects']} ({self.stats['redirects']/self.stats['total_links']*100:.1f}%)
- **⏰ Таймаутов:** {self.stats['timeouts']}
- **💥 Ошибок:** {self.stats['errors']}

## ✅ РАБОТАЮЩИЕ ССЫЛКИ В ПОДВАЛЕ ({len(working_links)})

"""
        
        for link in working_links:
            report += f"- ✅ [{link['url']}]({link['url']}) - {link['response_time']:.2f}s\n"
        
        report += f"""
## ❌ БИТЫЕ ССЫЛКИ В ПОДВАЛЕ ({len(broken_links)})

"""
        
        for link in broken_links:
            report += f"- ❌ [{link['url']}]({link['url']}) - код {link['status_code']}\n"
        
        if redirect_links:
            report += f"""
## 🔄 РЕДИРЕКТЫ В ПОДВАЛЕ ({len(redirect_links)})

"""
            for link in redirect_links:
                report += f"- 🔄 [{link['url']}]({link['url']}) → [{link['final_url']}]({link['final_url']}) - код {link['status_code']}\n"
        
        if error_links:
            report += f"""
## 💥 ОШИБКИ И ТАЙМАУТЫ В ПОДВАЛЕ ({len(error_links)})

"""
            for link in error_links:
                report += f"- 💥 [{link['url']}]({link['url']}) - {link['error']}\n"
        
        report += f"""
## 🎯 РЕКОМЕНДАЦИИ

"""
        
        if self.stats['broken_links'] > 0:
            report += f"1. **Исправить {self.stats['broken_links']} битых ссылок в подвале** - это критично для SEO и UX\n"
        
        if self.stats['redirects'] > 0:
            report += f"2. **Оптимизировать {self.stats['redirects']} редиректов в подвале** - заменить на прямые ссылки\n"
        
        if self.stats['timeouts'] > 0:
            report += f"3. **Проверить {self.stats['timeouts']} ссылок с таймаутами в подвале** - возможно, проблемы с сервером\n"
        
        success_rate = self.stats['working_links'] / self.stats['total_links'] * 100 if self.stats['total_links'] > 0 else 0
        report += f"4. **Общий показатель успешности ссылок в подвале: {success_rate:.1f}%** - {'отлично' if success_rate > 95 else 'хорошо' if success_rate > 80 else 'требует улучшения'}\n"
        
        report += f"""
---
*Отчет сгенерирован автоматически системой проверки ссылок в подвале*
"""
        
        # Сохраняем отчет
        report_filename = f"/var/www/fastuser/data/www/ecopackpro.ru/footer_links_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.md"
        with open(report_filename, 'w', encoding='utf-8') as f:
            f.write(report)
        
        logging.info(f"✅ Отчет сохранен: {report_filename}")
        return report_filename
    
    def check_footer_links(self, url):
        """Запуск проверки ссылок в подвале"""
        logging.info("🔍 ЗАПУСК ПРОВЕРКИ ССЫЛОК В ПОДВАЛЕ САЙТА")
        logging.info("=" * 50)
        
        start_time = datetime.now()
        
        # 1. Извлекаем ссылки из подвала
        links = self.extract_footer_links(url)
        if not links:
            logging.error("❌ Ссылки в подвале не найдены!")
            return False
        
        self.stats['total_links'] = len(links)
        
        # 2. Проверяем ссылки
        self.check_links_concurrent(links, max_workers=10)
        
        # 3. Генерируем отчет
        report_file = self.generate_footer_report()
        
        # Финальная статистика
        end_time = datetime.now()
        duration = end_time - start_time
        
        logging.info("=" * 50)
        logging.info("📊 ИТОГОВАЯ СТАТИСТИКА ПРОВЕРКИ ПОДВАЛА")
        logging.info(f"⏱️ Время выполнения: {duration}")
        logging.info(f"🔗 Всего ссылок в подвале: {self.stats['total_links']}")
        logging.info(f"✅ Работающих: {self.stats['working_links']} ({self.stats['working_links']/self.stats['total_links']*100:.1f}%)")
        logging.info(f"❌ Неработающих: {self.stats['broken_links']} ({self.stats['broken_links']/self.stats['total_links']*100:.1f}%)")
        logging.info(f"📄 Отчет: {report_file}")
        logging.info("=" * 50)
        
        return True

if __name__ == "__main__":
    # Проверяем ссылки в подвале конкретной страницы
    url = "https://ecopackpro.ru/korobki-dlya-otpravki/"
    checker = FooterLinksChecker()
    checker.check_footer_links(url)



