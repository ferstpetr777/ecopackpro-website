#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
🔍 ПРОВЕРКА КОНКРЕТНЫХ ССЫЛОК ИЗ ПОДВАЛА
Сайт: ecopackpro.ru
Цель: Проверить конкретные ссылки из подвала
"""

import requests
import re
from datetime import datetime
import logging
import concurrent.futures
from threading import Lock

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/footer_specific_check.log'),
        logging.StreamHandler()
    ]
)

class FooterSpecificChecker:
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
        
        # Ссылки из подвала, которые нужно проверить
        self.footer_links = [
            'https://ecopackpro.ru',
            'https://ecopackpro.ru/catalog',
            'https://ecopackpro.ru/delivery', 
            'https://ecopackpro.ru/contacts',
            'https://ecopackpro.ru/box-selection',
            'https://ecopackpro.ru/custom-boxes'
        ]
    
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
    
    def check_footer_links(self):
        """Проверка ссылок из подвала"""
        logging.info("🔍 ПРОВЕРКА ССЫЛОК ИЗ ПОДВАЛА САЙТА")
        logging.info("=" * 50)
        
        start_time = datetime.now()
        
        self.stats['total_links'] = len(self.footer_links)
        
        logging.info(f"🚀 Начинаем проверку {len(self.footer_links)} ссылок из подвала...")
        
        with concurrent.futures.ThreadPoolExecutor(max_workers=10) as executor:
            future_to_link = {
                executor.submit(self.check_single_link, link): link 
                for link in self.footer_links
            }
            
            for future in concurrent.futures.as_completed(future_to_link):
                try:
                    result = future.result()
                    self.results.append(result)
                except Exception as e:
                    logging.error(f"❌ Ошибка при проверке ссылки: {e}")
        
        # Генерируем отчет
        self.generate_report()
        
        # Финальная статистика
        end_time = datetime.now()
        duration = end_time - start_time
        
        logging.info("=" * 50)
        logging.info("📊 ИТОГОВАЯ СТАТИСТИКА ПРОВЕРКИ ПОДВАЛА")
        logging.info(f"⏱️ Время выполнения: {duration}")
        logging.info(f"🔗 Всего ссылок в подвале: {self.stats['total_links']}")
        logging.info(f"✅ Работающих: {self.stats['working_links']} ({self.stats['working_links']/self.stats['total_links']*100:.1f}%)")
        logging.info(f"❌ Неработающих: {self.stats['broken_links']} ({self.stats['broken_links']/self.stats['total_links']*100:.1f}%)")
        logging.info("=" * 50)
        
        return True
    
    def generate_report(self):
        """Генерация отчета"""
        logging.info("📋 Генерируем отчет о проверке ссылок из подвала...")
        
        working_links = [r for r in self.results if r['status_code'] == 200]
        broken_links = [r for r in self.results if r['status_code'] != 200 and r['status_code'] != 'Timeout' and r['status_code'] != 'Error']
        redirect_links = [r for r in self.results if isinstance(r['status_code'], int) and 300 <= r['status_code'] < 400]
        error_links = [r for r in self.results if r['status_code'] in ['Timeout', 'Error']]
        
        report = f"""
# 🔍 ОТЧЕТ О ПРОВЕРКЕ ССЫЛОК ИЗ ПОДВАЛА САЙТА
**Сайт:** ecopackpro.ru  
**Дата проверки:** {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}

## 📊 ОБЩАЯ СТАТИСТИКА

- **Всего проверено ссылок в подвале:** {self.stats['total_links']}
- **✅ Работающих ссылок (200):** {self.stats['working_links']} ({self.stats['working_links']/self.stats['total_links']*100:.1f}%)
- **❌ Неработающих ссылок:** {self.stats['broken_links']} ({self.stats['broken_links']/self.stats['total_links']*100:.1f}%)
- **🔄 Редиректов:** {self.stats['redirects']} ({self.stats['redirects']/self.stats['total_links']*100:.1f}%)
- **⏰ Таймаутов:** {self.stats['timeouts']}
- **💥 Ошибок:** {self.stats['errors']}

## ✅ РАБОТАЮЩИЕ ССЫЛКИ ИЗ ПОДВАЛА ({len(working_links)})

"""
        
        for link in working_links:
            report += f"- ✅ [{link['url']}]({link['url']}) - {link['response_time']:.2f}s\n"
        
        report += f"""
## ❌ БИТЫЕ ССЫЛКИ ИЗ ПОДВАЛА ({len(broken_links)})

"""
        
        for link in broken_links:
            report += f"- ❌ [{link['url']}]({link['url']}) - код {link['status_code']}\n"
        
        if redirect_links:
            report += f"""
## 🔄 РЕДИРЕКТЫ ИЗ ПОДВАЛА ({len(redirect_links)})

"""
            for link in redirect_links:
                report += f"- 🔄 [{link['url']}]({link['url']}) → [{link['final_url']}]({link['final_url']}) - код {link['status_code']}\n"
        
        if error_links:
            report += f"""
## 💥 ОШИБКИ И ТАЙМАУТЫ ИЗ ПОДВАЛА ({len(error_links)})

"""
            for link in error_links:
                report += f"- 💥 [{link['url']}]({link['url']}) - {link['error']}\n"
        
        report += f"""
## 🎯 РЕКОМЕНДАЦИИ

"""
        
        if self.stats['broken_links'] > 0:
            report += f"1. **Исправить {self.stats['broken_links']} битых ссылок в подвале** - это критично для SEO и UX\n"
            report += f"2. **Создать недостающие страницы** или настроить редиректы\n"
        
        if self.stats['redirects'] > 0:
            report += f"3. **Оптимизировать {self.stats['redirects']} редиректов в подвале** - заменить на прямые ссылки\n"
        
        success_rate = self.stats['working_links'] / self.stats['total_links'] * 100 if self.stats['total_links'] > 0 else 0
        report += f"4. **Общий показатель успешности ссылок в подвале: {success_rate:.1f}%** - {'отлично' if success_rate > 95 else 'хорошо' if success_rate > 80 else 'требует улучшения'}\n"
        
        report += f"""
---
*Отчет сгенерирован автоматически системой проверки ссылок из подвала*
"""
        
        # Сохраняем отчет
        report_filename = f"/var/www/fastuser/data/www/ecopackpro.ru/footer_specific_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.md"
        with open(report_filename, 'w', encoding='utf-8') as f:
            f.write(report)
        
        logging.info(f"✅ Отчет сохранен: {report_filename}")
        return report_filename

if __name__ == "__main__":
    checker = FooterSpecificChecker()
    checker.check_footer_links()



