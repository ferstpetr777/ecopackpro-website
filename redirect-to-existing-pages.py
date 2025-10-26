#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
🔄 ПЕРЕНАПРАВЛЕНИЕ БИТЫХ ССЫЛОК НА СУЩЕСТВУЮЩИЕ СТАТЬИ
Сайт: ecopackpro.ru
Цель: Настроить редиректы битых ссылок на существующие качественные статьи
"""

import mysql.connector
import requests
from datetime import datetime
import logging

# Настройка логирования
logging.basicConfig(
    level=logging.INFO,
    format='%(asctime)s - %(levelname)s - %(message)s',
    handlers=[
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/redirect_to_existing.log'),
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

class RedirectToExistingPages:
    def __init__(self):
        self.db_config = DB_CONFIG
        
        # Маппинг битых ссылок на существующие статьи
        self.redirect_mapping = {
            'contacts': '/contact-us/',  # Существующая страница контактов
            'catalog': '/shop/',        # Существующий магазин
            'delivery': '/oplata-i-dostavka/',  # Существующая страница доставки
            'box-selection': '/korobki-dlya-otpravki/',  # Качественная статья о коробках
            'custom-boxes': '/upakovka-s-logotipom/'  # Качественная статья об упаковке с логотипом
        }
        
        # Статистика
        self.stats = {
            'redirects_updated': 0,
            'existing_pages_found': 0
        }
    
    def connect_to_database(self):
        """Подключение к базе данных"""
        try:
            connection = mysql.connector.connect(**self.db_config)
            return connection
        except mysql.connector.Error as e:
            logging.error(f"❌ Ошибка подключения к БД: {e}")
            return None
    
    def get_existing_pages(self):
        """Получение списка существующих страниц"""
        connection = self.connect_to_database()
        if not connection:
            return []
        
        cursor = connection.cursor()
        
        # Получаем все опубликованные страницы и статьи
        cursor.execute("""
            SELECT ID, post_title, post_name, post_type 
            FROM wp_posts 
            WHERE post_status = 'publish' 
            AND post_type IN ('page', 'post')
            ORDER BY post_type, post_title
        """)
        
        pages = cursor.fetchall()
        connection.close()
        
        logging.info(f"📊 Найдено существующих страниц и статей: {len(pages)}")
        
        # Показываем список существующих страниц
        logging.info("📋 СУЩЕСТВУЮЩИЕ СТРАНИЦЫ И СТАТЬИ:")
        for page_id, title, slug, post_type in pages:
            logging.info(f"   {post_type}: {title} (/{slug}/)")
        
        return pages
    
    def update_htaccess_redirects(self):
        """Обновление редиректов в .htaccess"""
        logging.info("🔄 Обновляем редиректы в .htaccess...")
        
        try:
            # Читаем текущий .htaccess
            with open('/var/www/fastuser/data/www/ecopackpro.ru/.htaccess', 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Удаляем старые редиректы для этих ссылок
            old_redirects = [
                'Redirect 301 /contacts/',
                'Redirect 301 /catalog/',
                'Redirect 301 /delivery/',
                'Redirect 301 /box-selection/',
                'Redirect 301 /custom-boxes/'
            ]
            
            for old_redirect in old_redirects:
                # Удаляем строки с этими редиректами
                lines = content.split('\n')
                content = '\n'.join([line for line in lines if old_redirect not in line])
            
            # Добавляем новые редиректы
            new_redirects = []
            for broken_link, target_page in self.redirect_mapping.items():
                redirect_rule = f"Redirect 301 /{broken_link}/ https://ecopackpro.ru{target_page}"
                new_redirects.append(redirect_rule)
                logging.info(f"✅ Настроен редирект: /{broken_link}/ → {target_page}")
            
            # Добавляем новые редиректы в конец файла
            if new_redirects:
                content += "\n# Redirects for broken footer links\n"
                content += "\n".join(new_redirects)
                content += "\n"
            
            # Записываем обновленный .htaccess
            with open('/var/www/fastuser/data/www/ecopackpro.ru/.htaccess', 'w', encoding='utf-8') as f:
                f.write(content)
            
            self.stats['redirects_updated'] = len(self.redirect_mapping)
            logging.info(f"✅ Обновлено {len(self.redirect_mapping)} редиректов в .htaccess")
            
            return True
            
        except Exception as e:
            logging.error(f"❌ Ошибка обновления .htaccess: {e}")
            return False
    
    def test_redirects(self):
        """Тестирование редиректов"""
        logging.info("🧪 Тестируем редиректы...")
        
        session = requests.Session()
        session.headers.update({
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        })
        
        for broken_link, target_page in self.redirect_mapping.items():
            test_url = f"https://ecopackpro.ru/{broken_link}/"
            
            try:
                response = session.get(test_url, timeout=10, allow_redirects=True)
                
                if response.status_code == 200:
                    if target_page.replace('/', '') in response.url:
                        logging.info(f"✅ Редирект работает: {broken_link} → {response.url}")
                    else:
                        logging.warning(f"⚠️ Редирект не работает: {broken_link} → {response.url}")
                else:
                    logging.error(f"❌ Ошибка редиректа {broken_link}: {response.status_code}")
                    
            except Exception as e:
                logging.error(f"❌ Ошибка тестирования {broken_link}: {e}")
    
    def run_redirect_setup(self):
        """Запуск настройки редиректов"""
        logging.info("🔄 НАСТРОЙКА РЕДИРЕКТОВ НА СУЩЕСТВУЮЩИЕ СТРАНИЦЫ")
        logging.info("=" * 60)
        
        start_time = datetime.now()
        
        # 1. Получаем список существующих страниц
        existing_pages = self.get_existing_pages()
        self.stats['existing_pages_found'] = len(existing_pages)
        
        # 2. Обновляем редиректы в .htaccess
        if self.update_htaccess_redirects():
            logging.info("✅ Редиректы в .htaccess обновлены успешно")
        else:
            logging.error("❌ Ошибка обновления редиректов")
            return False
        
        # 3. Тестируем редиректы
        self.test_redirects()
        
        # Финальная статистика
        end_time = datetime.now()
        duration = end_time - start_time
        
        logging.info("=" * 60)
        logging.info("📊 ИТОГОВАЯ СТАТИСТИКА")
        logging.info(f"⏱️ Время выполнения: {duration}")
        logging.info(f"📄 Найдено существующих страниц: {self.stats['existing_pages_found']}")
        logging.info(f"🔄 Обновлено редиректов: {self.stats['redirects_updated']}")
        logging.info("=" * 60)
        
        # Показываем маппинг редиректов
        logging.info("🎯 НАСТРОЕННЫЕ РЕДИРЕКТЫ:")
        for broken_link, target_page in self.redirect_mapping.items():
            logging.info(f"   /{broken_link}/ → {target_page}")
        
        logging.info("🎉 НАСТРОЙКА РЕДИРЕКТОВ ЗАВЕРШЕНА!")
        
        return True

if __name__ == "__main__":
    redirector = RedirectToExistingPages()
    redirector.run_redirect_setup()



