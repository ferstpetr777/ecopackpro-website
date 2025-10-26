#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import base64
import json
from datetime import datetime
from bs4 import BeautifulSoup
import re

# Конфигурация WordPress API
WORDPRESS_URL = "https://ecopackpro.ru"
APPLICATION_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"
USERNAME = "rtep1976@me.com"

# Список ключевых слов для поиска статей
KEYWORDS = [
    "курьерские пакеты",
    "почтовые коробки", 
    "зип пакеты",
    "zip lock пакеты с бегунком",
    "конверты с воздушной подушкой",
    "конверты с воздушной прослойкой",
    "крафтовые пакеты с воздушной подушкой",
    "курьерские пакеты прозрачные",
    "курьерские пакеты номерные",
    "курьерские пакеты черно-белые",
    "курьерские пакеты с карманом",
    "zip lock пакеты матовые",
    "zip lock пакеты оптом",
    "крафтовые конверты",
    "пузырчатые пакеты ВПП",
    "коробки для почты",
    "коробки для отправки",
    "самоклеящиеся карманы",
    "антимагнитная пломба",
    "наклейка пломба антимагнит",
    "пломбиратор для бочек",
    "номерные пломбы наклейки",
    "zip lock пакеты с белой полосой",
    "белые крафт пакеты с пузырчатой плёнкой",
    "прозрачные zip lock пакеты",
    "купить курьерские пакеты с номерным штрих-кодом",
    "заказать прозрачные курьерские пакеты оптом",
    "курьерские пакеты черно-белые с карманом цена",
    "матовые zip lock пакеты с бегунком 10×15",
    "купить оптом zip lock пакеты матовые 30 мкм",
    "крафт конверты с воздушной подушкой F/3",
    "почтовые коробки размера S 260×170×80",
    "почтовые коробки размера XL 530×360×220",
    "купить самоклеящиеся карманы SD для документов",
    "антимагнитные наклейки для водяных счётчиков",
    "антимагнитная пломба цена за 100 штук",
    "пломбиратор для евробочек 2 дюйма",
    "инструмент для опломбирования бочек ¾ дюйма",
    "курьерские пакеты черно-белые без логотипа А4",
    "курьерские пакеты прозрачные для одежды",
    "курьерские пакеты для маркетплейсов Ozon",
    "почтовые коробки с логотипом на заказ",
    "зип пакеты с бегунком купить Москва",
    "матовые zip lock пакеты для чая",
    "zip lock пакеты с подвесом",
    "белые крафт-пакеты с пузырчатой плёнкой оптом",
    "плоские конверты с воздушной подушкой для документов",
    "пакеты из воздушно-пузырьковой плёнки оптом",
    "антимагнитные пломбы для газовых счётчиков",
    "самоклеящиеся карманы для транспортных накладных"
]

class FeaturedImageAuditor:
    def __init__(self, url, username, app_password):
        self.url = url.rstrip('/')
        self.username = username
        self.app_password = app_password
        
        # Создание заголовков для аутентификации
        credentials = f"{username}:{app_password}"
        encoded_credentials = base64.b64encode(credentials.encode()).decode()
        
        self.headers = {
            'Authorization': f'Basic {encoded_credentials}',
            'Content-Type': 'application/json',
            'User-Agent': 'WordPress-API-Client/1.0'
        }
    
    def test_connection(self):
        """Тестирование подключения к WordPress API"""
        try:
            response = requests.get(
                f"{self.url}/wp-json/wp/v2/users/me",
                headers=self.headers,
                timeout=30
            )
            
            if response.status_code == 200:
                user_data = response.json()
                print(f"✅ Подключение успешно! Пользователь: {user_data.get('name', 'Unknown')}")
                return True
            else:
                print(f"❌ Ошибка подключения: {response.status_code} - {response.text}")
                return False
                
        except Exception as e:
            print(f"❌ Ошибка подключения: {e}")
            return False
    
    def search_posts_by_title(self, keyword, status='draft'):
        """Поиск постов по заголовку"""
        try:
            params = {
                'search': keyword,
                'status': status,
                'per_page': 10
            }
            
            response = requests.get(
                f"{self.url}/wp-json/wp/v2/posts",
                headers=self.headers,
                params=params,
                timeout=30
            )
            
            if response.status_code == 200:
                posts = response.json()
                # Фильтруем по точному соответствию в заголовке
                matching_posts = []
                for post in posts:
                    if keyword.lower() in post['title']['rendered'].lower():
                        matching_posts.append(post)
                return matching_posts
            else:
                print(f"❌ Ошибка поиска постов: {response.status_code}")
                return []
                
        except Exception as e:
            print(f"❌ Ошибка поиска постов: {e}")
            return []
    
    def get_featured_image_info(self, post_id):
        """Получение информации о главном изображении"""
        try:
            # Получаем информацию о главном изображении через API
            response = requests.get(
                f"{self.url}/wp-json/wp/v2/posts/{post_id}",
                headers=self.headers,
                params={'_embed': 'wp:featuredmedia'},
                timeout=30
            )
            
            if response.status_code == 200:
                post_data = response.json()
                featured_media = post_data.get('_embedded', {}).get('wp:featuredmedia', [])
                
                if featured_media:
                    media_info = featured_media[0]
                    return {
                        'id': media_info.get('id'),
                        'url': media_info.get('source_url'),
                        'alt': media_info.get('alt_text', ''),
                        'title': media_info.get('title', {}).get('rendered', ''),
                        'caption': media_info.get('caption', {}).get('rendered', '')
                    }
                else:
                    return None
            else:
                print(f"❌ Ошибка получения главного изображения: {response.status_code}")
                return None
                
        except Exception as e:
            print(f"❌ Ошибка получения главного изображения: {e}")
            return None
    
    def get_post_content(self, post_id):
        """Получение содержимого поста"""
        try:
            response = requests.get(
                f"{self.url}/wp-json/wp/v2/posts/{post_id}",
                headers=self.headers,
                timeout=30
            )
            
            if response.status_code == 200:
                post_data = response.json()
                return post_data.get('content', {}).get('rendered', '')
            else:
                print(f"❌ Ошибка получения контента: {response.status_code}")
                return ''
                
        except Exception as e:
            print(f"❌ Ошибка получения контента: {e}")
            return ''
    
    def extract_images_from_content(self, content):
        """Извлечение всех изображений из контента"""
        try:
            soup = BeautifulSoup(content, 'html.parser')
            images = []
            
            for img_tag in soup.find_all('img'):
                img_info = {
                    'src': img_tag.get('src', ''),
                    'alt': img_tag.get('alt', ''),
                    'class': img_tag.get('class', []),
                    'title': img_tag.get('title', ''),
                    'parent_tag': img_tag.parent.name if img_tag.parent else 'unknown'
                }
                images.append(img_info)
            
            return images
            
        except Exception as e:
            print(f"❌ Ошибка извлечения изображений: {e}")
            return []
    
    def get_first_visible_image(self, content):
        """Получение первого визуально отображаемого изображения"""
        try:
            soup = BeautifulSoup(content, 'html.parser')
            
            # Ищем первое изображение в figure блоке (обычно это главное изображение статьи)
            figure_img = soup.find('figure', class_='wp-block-image')
            if figure_img:
                img_tag = figure_img.find('img')
                if img_tag:
                    return {
                        'src': img_tag.get('src', ''),
                        'alt': img_tag.get('alt', ''),
                        'class': img_tag.get('class', []),
                        'location': 'figure_block'
                    }
            
            # Если не найдено в figure, ищем первое изображение в контенте
            first_img = soup.find('img')
            if first_img:
                return {
                    'src': first_img.get('src', ''),
                    'alt': first_img.get('alt', ''),
                    'class': first_img.get('class', []),
                    'location': 'first_image'
                }
            
            return None
            
        except Exception as e:
            print(f"❌ Ошибка поиска первого изображения: {e}")
            return None
    
    def compare_images(self, featured_img, visible_img):
        """Сравнение главного изображения с визуально отображаемым"""
        if not featured_img or not visible_img:
            return False, "Одно из изображений отсутствует"
        
        # Извлекаем имя файла из URL
        featured_filename = featured_img['url'].split('/')[-1] if featured_img['url'] else ''
        visible_filename = visible_img['src'].split('/')[-1] if visible_img['src'] else ''
        
        # Сравниваем имена файлов
        if featured_filename == visible_filename:
            return True, "Изображения совпадают"
        
        # Проверяем, не является ли визуальное изображение placeholder'ом
        placeholder_indicators = ['Tvist-PRO', 'placeholder', 'default']
        is_placeholder = any(indicator in visible_img['src'] for indicator in placeholder_indicators)
        
        if is_placeholder:
            return False, f"Визуально отображается placeholder: {visible_filename}"
        
        return False, f"Разные изображения. Главное: {featured_filename}, Визуальное: {visible_filename}"

def main():
    """Основная функция аудита"""
    print("🔍 АУДИТ СООТВЕТСТВИЯ ГЛАВНЫХ ИЗОБРАЖЕНИЙ")
    print("=" * 80)
    
    # Создание экземпляра аудитора
    auditor = FeaturedImageAuditor(WORDPRESS_URL, USERNAME, APPLICATION_PASSWORD)
    
    # Тестирование подключения
    print("\n🔍 Тестирование подключения к WordPress API...")
    if not auditor.test_connection():
        print("❌ Не удалось подключиться к WordPress API")
        return
    
    # Статистика
    stats = {
        'total_keywords': len(KEYWORDS),
        'articles_found': 0,
        'articles_with_featured': 0,
        'articles_without_featured': 0,
        'matches_correct': 0,
        'matches_incorrect': 0,
        'placeholder_issues': 0,
        'missing_images': 0
    }
    
    # Детальные результаты
    detailed_results = []
    
    print("\n🔍 Начало аудита статей...")
    print("=" * 80)
    
    for i, keyword in enumerate(KEYWORDS, 1):
        print(f"\n📋 {i:2d}. Поиск: {keyword}")
        
        # Поиск статей по ключевому слову
        posts = auditor.search_posts_by_title(keyword)
        
        if posts:
            for post in posts:
                post_id = post['id']
                post_title = post['title']['rendered']
                post_url = post['link']
                
                print(f"   📄 ID {post_id}: {post_title}")
                
                stats['articles_found'] += 1
                
                # Получение информации о главном изображении
                featured_img = auditor.get_featured_image_info(post_id)
                
                if featured_img:
                    stats['articles_with_featured'] += 1
                    print(f"   🖼️  Главное изображение: {featured_img['url'].split('/')[-1]}")
                    print(f"   📝 Alt текст: {featured_img['alt']}")
                    
                    # Получение контента статьи
                    content = auditor.get_post_content(post_id)
                    
                    # Поиск первого визуально отображаемого изображения
                    visible_img = auditor.get_first_visible_image(content)
                    
                    if visible_img:
                        # Сравнение изображений
                        is_match, reason = auditor.compare_images(featured_img, visible_img)
                        
                        result = {
                            'post_id': post_id,
                            'title': post_title,
                            'url': post_url,
                            'featured_image': featured_img,
                            'visible_image': visible_img,
                            'is_match': is_match,
                            'reason': reason
                        }
                        
                        detailed_results.append(result)
                        
                        if is_match:
                            stats['matches_correct'] += 1
                            print(f"   ✅ Соответствует: {reason}")
                        else:
                            stats['matches_incorrect'] += 1
                            print(f"   ❌ Не соответствует: {reason}")
                            
                            if 'placeholder' in reason.lower():
                                stats['placeholder_issues'] += 1
                    else:
                        stats['missing_images'] += 1
                        print(f"   ❌ Визуальное изображение не найдено")
                        
                        result = {
                            'post_id': post_id,
                            'title': post_title,
                            'url': post_url,
                            'featured_image': featured_img,
                            'visible_image': None,
                            'is_match': False,
                            'reason': 'Визуальное изображение не найдено в контенте'
                        }
                        detailed_results.append(result)
                else:
                    stats['articles_without_featured'] += 1
                    print(f"   ❌ Главное изображение не установлено")
                    
                    result = {
                        'post_id': post_id,
                        'title': post_title,
                        'url': post_url,
                        'featured_image': None,
                        'visible_image': None,
                        'is_match': False,
                        'reason': 'Главное изображение не установлено'
                    }
                    detailed_results.append(result)
        else:
            print(f"   ❌ Статьи не найдены")
    
    # Итоговый отчет
    print("\n" + "=" * 80)
    print("📊 ИТОГОВЫЙ ОТЧЕТ АУДИТА")
    print("=" * 80)
    
    print(f"📚 Всего ключевых слов: {stats['total_keywords']}")
    print(f"📄 Найдено статей: {stats['articles_found']}")
    print(f"🖼️  Статей с главным изображением: {stats['articles_with_featured']}")
    print(f"❌ Статей без главного изображения: {stats['articles_without_featured']}")
    print(f"✅ Правильных соответствий: {stats['matches_correct']}")
    print(f"❌ Неправильных соответствий: {stats['matches_incorrect']}")
    print(f"🔄 Проблем с placeholder: {stats['placeholder_issues']}")
    print(f"📭 Отсутствующих изображений: {stats['missing_images']}")
    
    if stats['articles_with_featured'] > 0:
        match_rate = (stats['matches_correct'] / stats['articles_with_featured']) * 100
        print(f"📊 Процент соответствий: {match_rate:.1f}%")
    
    # Детальный отчет о проблемах
    print("\n" + "=" * 80)
    print("🚨 ПРОБЛЕМНЫЕ СТАТЬИ")
    print("=" * 80)
    
    problem_articles = [r for r in detailed_results if not r['is_match']]
    
    if problem_articles:
        for i, article in enumerate(problem_articles, 1):
            print(f"\n{i}. ID {article['post_id']}: {article['title']}")
            print(f"   🔗 URL: {article['url']}")
            print(f"   ❌ Проблема: {article['reason']}")
            
            if article['featured_image']:
                print(f"   🖼️  Главное: {article['featured_image']['url'].split('/')[-1]}")
            
            if article['visible_image']:
                print(f"   👁️  Визуальное: {article['visible_image']['src'].split('/')[-1]}")
    else:
        print("✅ Проблемных статей не найдено!")
    
    # Сохранение детального отчета
    report_data = {
        'timestamp': datetime.now().isoformat(),
        'stats': stats,
        'detailed_results': detailed_results
    }
    
    report_filename = f"audit_featured_images_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
    
    try:
        with open(report_filename, 'w', encoding='utf-8') as f:
            json.dump(report_data, f, ensure_ascii=False, indent=2)
        print(f"\n📁 Детальный отчет сохранен: {report_filename}")
    except Exception as e:
        print(f"❌ Ошибка сохранения отчета: {e}")
    
    print(f"\n✅ Аудит завершен!")

if __name__ == "__main__":
    main()
