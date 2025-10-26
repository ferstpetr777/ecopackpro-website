#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
🔧 ИСПРАВЛЕНИЕ БИТЫХ ССЫЛОК В ПОДВАЛЕ
Сайт: ecopackpro.ru
Цель: Создать недостающие страницы и исправить ссылки в подвале
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
        logging.FileHandler('/var/www/fastuser/data/www/ecopackpro.ru/fix_footer_links.log'),
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

class FooterLinksFixer:
    def __init__(self):
        self.db_config = DB_CONFIG
        
        # Статистика исправлений
        self.stats = {
            'pages_created': 0,
            'links_fixed': 0,
            'redirects_created': 0
        }
        
        # Битые ссылки и их заменители
        self.broken_links_fixes = {
            'contacts': {
                'title': 'Контакты для заказа упаковки',
                'slug': 'contact-us',
                'content': self.get_contacts_content(),
                'redirect_from': 'contacts'
            },
            'catalog': {
                'title': 'Каталог упаковочных решений',
                'slug': 'shop',
                'content': self.get_catalog_content(),
                'redirect_from': 'catalog'
            },
            'delivery': {
                'title': 'Информация о доставке и оплате',
                'slug': 'oplata-i-dostavka',
                'content': self.get_delivery_content(),
                'redirect_from': 'delivery'
            },
            'box-selection': {
                'title': 'Выбор коробок по размерам и типу товара',
                'slug': 'box-selection',
                'content': self.get_box_selection_content(),
                'redirect_from': None
            },
            'custom-boxes': {
                'title': 'Производство коробок на заказ',
                'slug': 'custom-boxes',
                'content': self.get_custom_boxes_content(),
                'redirect_from': None
            }
        }
    
    def connect_to_database(self):
        """Подключение к базе данных"""
        try:
            connection = mysql.connector.connect(**self.db_config)
            return connection
        except mysql.connector.Error as e:
            logging.error(f"❌ Ошибка подключения к БД: {e}")
            return None
    
    def get_contacts_content(self):
        """Контент для страницы контактов"""
        return """
<div class="contact-page">
    <h1>Контакты для заказа упаковки</h1>
    
    <div class="contact-info">
        <h2>📞 Контактные телефоны</h2>
        <p><strong>8 800 201 06 93</strong></p>
        
        <h3>Н.Новгород:</h3>
        <p>+7 (831) 212-44-57<br>
        +7 (920) 029-93-83</p>
        
        <h3>Казань:</h3>
        <p>+7 (843) 245-18-45<br>
        +7 (927) 421-42-44</p>
        
        <h2>✉️ Email</h2>
        <p>zakaz@plomba-nn.ru</p>
        
        <h2>🕒 Время работы</h2>
        <p>Пн-Пт: 9:00 - 18:00<br>
        Сб: 10:00 - 16:00<br>
        Вс: выходной</p>
        
        <h2>📍 Адреса офисов</h2>
        <p><strong>Н.Новгород:</strong> ул. Примерная, 123<br>
        <strong>Казань:</strong> ул. Примерная, 456</p>
    </div>
    
    <div class="order-process">
        <h2>🛒 Как сделать заказ</h2>
        <ol>
            <li>Свяжитесь с нами по телефону или email</li>
            <li>Опишите ваши потребности в упаковке</li>
            <li>Получите расчет стоимости</li>
            <li>Оформите заказ</li>
            <li>Получите упаковку с доставкой</li>
        </ol>
    </div>
</div>
"""
    
    def get_catalog_content(self):
        """Контент для страницы каталога"""
        return """
<div class="catalog-page">
    <h1>Каталог упаковочных решений</h1>
    
    <div class="catalog-categories">
        <h2>📦 Основные категории товаров</h2>
        
        <div class="category-grid">
            <div class="category-item">
                <h3>Курьерские пакеты</h3>
                <p>Надежная упаковка для интернет-магазинов</p>
                <a href="/product-category/kurer-pakety/">Посмотреть товары</a>
            </div>
            
            <div class="category-item">
                <h3>Коробки</h3>
                <p>Картонные коробки различных размеров</p>
                <a href="/product-category/korobki/">Посмотреть товары</a>
            </div>
            
            <div class="category-item">
                <h3>ZIP-LOCK пакеты</h3>
                <p>Пакеты с застежкой-бегунком</p>
                <a href="/product-category/zip-lock-paket-s-begunkom/">Посмотреть товары</a>
            </div>
            
            <div class="category-item">
                <h3>Пакеты с воздушной подушкой</h3>
                <p>Защита хрупких товаров</p>
                <a href="/product-category/konverty-s-vozdushnoj-podushkoj/">Посмотреть товары</a>
            </div>
            
            <div class="category-item">
                <h3>Воздушно-пузырьковая пленка</h3>
                <p>Материал для защиты товаров</p>
                <a href="/product-category/vozdushno-puzyrkovaya-plenka/">Посмотреть товары</a>
            </div>
            
            <div class="category-item">
                <h3>Термоэтикетки</h3>
                <p>Этикетки для маркировки</p>
                <a href="/product-category/termo-etiketka/">Посмотреть товары</a>
            </div>
        </div>
    </div>
    
    <div class="catalog-features">
        <h2>🎯 Преимущества нашего каталога</h2>
        <ul>
            <li>✅ Широкий ассортимент упаковочных материалов</li>
            <li>✅ Конкурентные цены</li>
            <li>✅ Быстрая доставка</li>
            <li>✅ Индивидуальный подход</li>
            <li>✅ Консультации специалистов</li>
        </ul>
    </div>
</div>
"""
    
    def get_delivery_content(self):
        """Контент для страницы доставки"""
        return """
<div class="delivery-page">
    <h1>Информация о доставке и оплате</h1>
    
    <div class="delivery-options">
        <h2>🚚 Способы доставки</h2>
        
        <div class="delivery-method">
            <h3>Курьерская доставка</h3>
            <p>Доставка по Н.Новгороду и Казани в течение 1-2 дней</p>
            <ul>
                <li>Стоимость: от 300 руб</li>
                <li>Время: 1-2 рабочих дня</li>
                <li>Зона доставки: в пределах города</li>
            </ul>
        </div>
        
        <div class="delivery-method">
            <h3>Почта России</h3>
            <p>Доставка по всей России</p>
            <ul>
                <li>Стоимость: от 200 руб</li>
                <li>Время: 3-7 рабочих дней</li>
                <li>Зона доставки: вся Россия</li>
            </ul>
        </div>
        
        <div class="delivery-method">
            <h3>Самовывоз</h3>
            <p>Забрать заказ самостоятельно</p>
            <ul>
                <li>Стоимость: бесплатно</li>
                <li>Время: в день заказа</li>
                <li>Адреса: Н.Новгород, Казань</li>
            </ul>
        </div>
    </div>
    
    <div class="payment-options">
        <h2>💳 Способы оплаты</h2>
        
        <div class="payment-method">
            <h3>Наличные при получении</h3>
            <p>Оплата курьеру или при самовывозе</p>
        </div>
        
        <div class="payment-method">
            <h3>Банковский перевод</h3>
            <p>Перевод на расчетный счет</p>
        </div>
        
        <div class="payment-method">
            <h3>Электронные платежи</h3>
            <p>Оплата через интернет-банк</p>
        </div>
    </div>
    
    <div class="delivery-terms">
        <h2>📋 Условия доставки</h2>
        <ul>
            <li>Минимальная сумма заказа: 1000 руб</li>
            <li>Бесплатная доставка при заказе от 5000 руб</li>
            <li>Возможность отслеживания отправления</li>
            <li>Страхование груза при необходимости</li>
        </ul>
    </div>
</div>
"""
    
    def get_box_selection_content(self):
        """Контент для страницы выбора коробок"""
        return """
<div class="box-selection-page">
    <h1>Выбор коробок по размерам и типу товара</h1>
    
    <div class="selection-guide">
        <h2>📏 Таблица размеров коробок</h2>
        
        <table class="size-table">
            <thead>
                <tr>
                    <th>Размер</th>
                    <th>Длина (мм)</th>
                    <th>Ширина (мм)</th>
                    <th>Высота (мм)</th>
                    <th>Применение</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>S</td>
                    <td>260</td>
                    <td>170</td>
                    <td>80</td>
                    <td>Документы, мелкие товары</td>
                </tr>
                <tr>
                    <td>M</td>
                    <td>350</td>
                    <td>250</td>
                    <td>150</td>
                    <td>Книги, одежда</td>
                </tr>
                <tr>
                    <td>L</td>
                    <td>450</td>
                    <td>350</td>
                    <td>200</td>
                    <td>Обувь, техника</td>
                </tr>
                <tr>
                    <td>XL</td>
                    <td>530</td>
                    <td>360</td>
                    <td>220</td>
                    <td>Крупная техника</td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <div class="selection-tips">
        <h2>💡 Советы по выбору</h2>
        
        <div class="tip-category">
            <h3>Для документов</h3>
            <p>Используйте коробки размера S или архивные коробки с крышкой</p>
        </div>
        
        <div class="tip-category">
            <h3>Для одежды</h3>
            <p>Коробки размера M-L, дополнительно используйте полиэтиленовые пакеты</p>
        </div>
        
        <div class="tip-category">
            <h3>Для техники</h3>
            <p>Коробки размера L-XL, обязательно пузырчатая пленка</p>
        </div>
        
        <div class="tip-category">
            <h3>Для хрупких товаров</h3>
            <p>Любой размер + воздушная подушка или пупырка</p>
        </div>
    </div>
    
    <div class="calculator">
        <h2>🧮 Калькулятор объема</h2>
        <p>Для точного выбора размера коробки используйте формулу:</p>
        <p><strong>Длина × Ширина × Высота = Объем товара</strong></p>
        <p>Выбирайте коробку с объемом на 20-30% больше объема товара</p>
    </div>
</div>
"""
    
    def get_custom_boxes_content(self):
        """Контент для страницы индивидуальных коробок"""
        return """
<div class="custom-boxes-page">
    <h1>Производство коробок на заказ</h1>
    
    <div class="custom-features">
        <h2>🎨 Возможности индивидуального производства</h2>
        
        <div class="feature-list">
            <div class="feature-item">
                <h3>Нестандартные размеры</h3>
                <p>Изготовление коробок любых размеров под ваши товары</p>
            </div>
            
            <div class="feature-item">
                <h3>Фирменный дизайн</h3>
                <p>Нанесение логотипа и брендинга на коробки</p>
            </div>
            
            <div class="feature-item">
                <h3>Специальные материалы</h3>
                <p>Гофрокартон различной плотности и цвета</p>
            </div>
            
            <div class="feature-item">
                <h3>Дополнительная обработка</h3>
                <p>Ламинирование, тиснение, высечка</p>
            </div>
        </div>
    </div>
    
    <div class="production-process">
        <h2>⚙️ Процесс производства</h2>
        
        <div class="process-steps">
            <div class="step">
                <h3>1. Консультация</h3>
                <p>Обсуждение требований и технических характеристик</p>
            </div>
            
            <div class="step">
                <h3>2. Разработка макета</h3>
                <p>Создание дизайна и технических чертежей</p>
            </div>
            
            <div class="step">
                <h3>3. Изготовление образца</h3>
                <p>Производство пробного экземпляра для утверждения</p>
            </div>
            
            <div class="step">
                <h3>4. Серийное производство</h3>
                <p>Изготовление заказанного количества коробок</p>
            </div>
        </div>
    </div>
    
    <div class="production-conditions">
        <h2>📋 Условия производства</h2>
        
        <div class="conditions-list">
            <div class="condition">
                <h3>Минимальный тираж</h3>
                <p>От 500 штук</p>
            </div>
            
            <div class="condition">
                <h3>Сроки изготовления</h3>
                <p>14-30 дней в зависимости от сложности</p>
            </div>
            
            <div class="condition">
                <h3>Стоимость</h3>
                <p>Рассчитывается индивидуально</p>
            </div>
            
            <div class="condition">
                <h3>Гарантия качества</h3>
                <p>Соответствие заявленным характеристикам</p>
            </div>
        </div>
    </div>
    
    <div class="contact-production">
        <h2>📞 Свяжитесь с нами</h2>
        <p>Для заказа индивидуального производства коробок:</p>
        <p><strong>Телефон:</strong> 8 800 201 06 93</p>
        <p><strong>Email:</strong> zakaz@plomba-nn.ru</p>
    </div>
</div>
"""
    
    def create_page(self, page_data):
        """Создание страницы в WordPress"""
        connection = self.connect_to_database()
        if not connection:
            return False
        
        cursor = connection.cursor()
        
        try:
            # Проверяем, существует ли уже страница с таким slug
            cursor.execute(
                "SELECT ID FROM wp_posts WHERE post_name = %s AND post_type = 'page'",
                (page_data['slug'],)
            )
            
            if cursor.fetchone():
                logging.info(f"ℹ️ Страница {page_data['slug']} уже существует")
                return True
            
            # Создаем новую страницу
            now = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
            
            cursor.execute("""
                INSERT INTO wp_posts 
                (post_author, post_date, post_date_gmt, post_content, post_title, post_excerpt, 
                 post_status, comment_status, ping_status, post_password, post_name, to_ping, 
                 pinged, post_modified, post_modified_gmt, post_content_filtered, post_parent, 
                 guid, menu_order, post_type, post_mime_type, comment_count) 
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, (
                1,  # post_author
                now,  # post_date
                now,  # post_date_gmt
                page_data['content'],  # post_content
                page_data['title'],  # post_title
                '',  # post_excerpt
                'publish',  # post_status
                'closed',  # comment_status
                'closed',  # ping_status
                '',  # post_password
                page_data['slug'],  # post_name
                '',  # to_ping
                '',  # pinged
                now,  # post_modified
                now,  # post_modified_gmt
                '',  # post_content_filtered
                0,  # post_parent
                f"https://ecopackpro.ru/{page_data['slug']}/",  # guid
                0,  # menu_order
                'page',  # post_type
                '',  # post_mime_type
                0  # comment_count
            ))
            
            page_id = cursor.lastrowid
            connection.commit()
            
            logging.info(f"✅ Создана страница: {page_data['title']} (ID: {page_id}, slug: {page_data['slug']})")
            self.stats['pages_created'] += 1
            
            return True
            
        except Exception as e:
            logging.error(f"❌ Ошибка создания страницы {page_data['slug']}: {e}")
            return False
        
        finally:
            connection.close()
    
    def create_redirect(self, from_slug, to_slug):
        """Создание редиректа в .htaccess"""
        if not from_slug:
            return True
        
        try:
            # Читаем текущий .htaccess
            with open('/var/www/fastuser/data/www/ecopackpro.ru/.htaccess', 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Добавляем редирект
            redirect_rule = f"Redirect 301 /{from_slug}/ https://ecopackpro.ru/{to_slug}/\n"
            
            # Проверяем, нет ли уже такого редиректа
            if f"/{from_slug}/" not in content:
                content += redirect_rule
                
                # Записываем обратно
                with open('/var/www/fastuser/data/www/ecopackpro.ru/.htaccess', 'w', encoding='utf-8') as f:
                    f.write(content)
                
                logging.info(f"✅ Создан редирект: /{from_slug}/ → /{to_slug}/")
                self.stats['redirects_created'] += 1
            else:
                logging.info(f"ℹ️ Редирект /{from_slug}/ уже существует")
            
            return True
            
        except Exception as e:
            logging.error(f"❌ Ошибка создания редиректа: {e}")
            return False
    
    def fix_all_footer_links(self):
        """Исправление всех битых ссылок в подвале"""
        logging.info("🔧 ИСПРАВЛЕНИЕ БИТЫХ ССЫЛОК В ПОДВАЛЕ")
        logging.info("=" * 50)
        
        start_time = datetime.now()
        
        for link_name, page_data in self.broken_links_fixes.items():
            logging.info(f"🔨 Обрабатываем: {link_name}")
            
            # Создаем страницу
            if self.create_page(page_data):
                logging.info(f"✅ Страница {link_name} создана успешно")
                self.stats['links_fixed'] += 1
            else:
                logging.error(f"❌ Ошибка создания страницы {link_name}")
            
            # Создаем редирект если нужно
            if page_data['redirect_from']:
                if self.create_redirect(page_data['redirect_from'], page_data['slug']):
                    logging.info(f"✅ Редирект для {link_name} создан")
                else:
                    logging.error(f"❌ Ошибка создания редиректа для {link_name}")
        
        # Финальная статистика
        end_time = datetime.now()
        duration = end_time - start_time
        
        logging.info("=" * 50)
        logging.info("📊 ИТОГОВАЯ СТАТИСТИКА ИСПРАВЛЕНИЙ")
        logging.info(f"⏱️ Время выполнения: {duration}")
        logging.info(f"📄 Создано страниц: {self.stats['pages_created']}")
        logging.info(f"🔗 Исправлено ссылок: {self.stats['links_fixed']}")
        logging.info(f"🔄 Создано редиректов: {self.stats['redirects_created']}")
        logging.info("=" * 50)
        logging.info("🎯 ИСПРАВЛЕНИЕ БИТЫХ ССЫЛОК В ПОДВАЛЕ ЗАВЕРШЕНО!")
        
        return True

if __name__ == "__main__":
    fixer = FooterLinksFixer()
    fixer.fix_all_footer_links()



