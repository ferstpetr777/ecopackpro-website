#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
🔍 ДЕБАГ СТРУКТУРЫ СТРАНИЦЫ
Сайт: ecopackpro.ru
Цель: Найти все ссылки на странице
"""

import requests
import re
from datetime import datetime
import logging

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s'
)

def debug_page_structure(url):
    """Анализ структуры страницы"""
    logging.info(f"🔍 Анализируем структуру страницы: {url}")
    
    session = requests.Session()
    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    })
    
    try:
        response = session.get(url, timeout=10)
        content = response.text
        
        logging.info(f"📊 Размер страницы: {len(content)} символов")
        
        # Ищем все ссылки на странице
        link_pattern = r'href="([^"]+)"'
        all_links = re.findall(link_pattern, content)
        
        logging.info(f"🔗 Всего ссылок на странице: {len(all_links)}")
        
        # Фильтруем внутренние ссылки
        internal_links = []
        external_links = []
        
        for link in all_links:
            if link.startswith('/') or 'ecopackpro.ru' in link:
                if not link.startswith('http'):
                    link = 'https://ecopackpro.ru' + link
                internal_links.append(link)
            elif link.startswith('http'):
                external_links.append(link)
        
        logging.info(f"🏠 Внутренних ссылок: {len(internal_links)}")
        logging.info(f"🌐 Внешних ссылок: {len(external_links)}")
        
        # Показываем все внутренние ссылки
        logging.info("📋 ВСЕ ВНУТРЕННИЕ ССЫЛКИ НА СТРАНИЦЕ:")
        for i, link in enumerate(internal_links, 1):
            logging.info(f"{i:3d}. {link}")
        
        # Ищем подвал разными способами
        logging.info("\n🔍 ПОИСК ПОДВАЛА РАЗНЫМИ СПОСОБАМИ:")
        
        # 1. Ищем footer
        footer_match = re.search(r'<footer[^>]*>(.*?)</footer>', content, re.DOTALL | re.IGNORECASE)
        if footer_match:
            footer_content = footer_match.group(1)
            logging.info(f"✅ Найден <footer>: {len(footer_content)} символов")
            
            # Ищем ссылки в footer
            footer_links = re.findall(link_pattern, footer_content)
            logging.info(f"🔗 Ссылок в footer: {len(footer_links)}")
            for link in footer_links:
                logging.info(f"   - {link}")
        else:
            logging.info("❌ <footer> не найден")
        
        # 2. Ищем класс footer
        footer_class_match = re.search(r'<div[^>]*class="[^"]*footer[^"]*"[^>]*>(.*?)</div>', content, re.DOTALL | re.IGNORECASE)
        if footer_class_match:
            footer_content = footer_class_match.group(1)
            logging.info(f"✅ Найден div с классом footer: {len(footer_content)} символов")
            
            footer_links = re.findall(link_pattern, footer_content)
            logging.info(f"🔗 Ссылок в div.footer: {len(footer_links)}")
            for link in footer_links:
                logging.info(f"   - {link}")
        else:
            logging.info("❌ div с классом footer не найден")
        
        # 3. Ищем в конце страницы (последние 3000 символов)
        end_content = content[-3000:]
        logging.info(f"📄 Анализируем конец страницы (последние 3000 символов)")
        
        end_links = re.findall(link_pattern, end_content)
        logging.info(f"🔗 Ссылок в конце страницы: {len(end_links)}")
        for link in end_links:
            logging.info(f"   - {link}")
        
        # 4. Ищем навигационные ссылки
        nav_patterns = [
            r'<nav[^>]*>(.*?)</nav>',
            r'<div[^>]*class="[^"]*nav[^"]*"[^>]*>(.*?)</div>',
            r'<div[^>]*class="[^"]*menu[^"]*"[^>]*>(.*?)</div>'
        ]
        
        for pattern in nav_patterns:
            nav_match = re.search(pattern, content, re.DOTALL | re.IGNORECASE)
            if nav_match:
                nav_content = nav_match.group(1)
                nav_links = re.findall(link_pattern, nav_content)
                if nav_links:
                    logging.info(f"🧭 Навигационные ссылки ({pattern}): {len(nav_links)}")
                    for link in nav_links:
                        logging.info(f"   - {link}")
        
        # 5. Сохраняем HTML для анализа
        with open('/var/www/fastuser/data/www/ecopackpro.ru/page_content.html', 'w', encoding='utf-8') as f:
            f.write(content)
        logging.info("💾 HTML страницы сохранен в page_content.html")
        
        return internal_links
        
    except Exception as e:
        logging.error(f"❌ Ошибка анализа страницы: {e}")
        return []

if __name__ == "__main__":
    url = "https://ecopackpro.ru/korobki-dlya-otpravki/"
    debug_page_structure(url)



