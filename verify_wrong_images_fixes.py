#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
import base64
import time
from datetime import datetime

# Конфигурация WordPress API
WORDPRESS_URL = "https://ecopackpro.ru"
APPLICATION_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"
USERNAME = "rtep1976@me.com"

# Список всех исправленных статей с неправильными изображениями
WRONG_IMAGES_ARTICLES = [7915, 7917, 7932, 7934, 7938, 7939, 7945, 7946, 7947, 7948, 7949, 7956]

class WrongImagesVerificationChecker:
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
        
        # Статистика проверки
        self.verification_stats = {
            'total': 0,
            'verified': 0,
            'failed': 0,
            'errors': []
        }
    
    def get_post_data(self, post_id):
        """Получение данных поста включая главное изображение"""
        try:
            response = requests.get(
                f"{self.url}/wp-json/wp/v2/posts/{post_id}",
                headers=self.headers,
                params={'_embed': 'wp:featuredmedia'},
                timeout=30
            )
            
            if response.status_code == 200:
                return response.json()
            else:
                return None
                
        except Exception as e:
            return None
    
    def get_featured_image_info(self, post_data):
        """Извлечение информации о главном изображении"""
        featured_media = post_data.get('_embedded', {}).get('wp:featuredmedia', [])
        
        if featured_media:
            media_info = featured_media[0]
            return {
                'id': media_info.get('id'),
                'url': media_info.get('source_url'),
                'alt': media_info.get('alt_text', ''),
            }
        return None
    
    def verify_single_article(self, post_id):
        """Проверка одной статьи"""
        try:
            post_data = self.get_post_data(post_id)
            if not post_data:
                return False, "Ошибка получения данных"
            
            post_title = post_data.get('title', {}).get('rendered', '')
            post_content = post_data.get('content', {}).get('rendered', '')
            
            # Получаем информацию о главном изображении
            featured_img = self.get_featured_image_info(post_data)
            if not featured_img:
                return False, "Главное изображение не найдено"
            
            featured_filename = featured_img['url'].split('/')[-1]
            
            # Проверяем, есть ли неправильные изображения (кроме правильного главного)
            wrong_images_count = 0
            correct_images_count = 0
            
            # Подсчитываем изображения в контенте
            from bs4 import BeautifulSoup
            soup = BeautifulSoup(post_content, 'html.parser')
            
            for img_tag in soup.find_all('img'):
                src = img_tag.get('src', '')
                img_filename = src.split('/')[-1] if src else ''
                
                if img_filename:
                    if img_filename == featured_filename:
                        correct_images_count += 1
                    elif 'Tvist-PRO' not in img_filename:  # Исключаем placeholder'ы
                        wrong_images_count += 1
            
            if wrong_images_count == 0 and correct_images_count > 0:
                return True, f"✅ Исправлено. Правильных изображений: {correct_images_count}"
            elif wrong_images_count > 0:
                return False, f"❌ Остались неправильные изображения: {wrong_images_count}"
            else:
                return False, "❌ Не найдены правильные изображения"
                
        except Exception as e:
            return False, f"❌ Ошибка проверки: {str(e)}"
    
    def verify_all_articles(self):
        """Проверка всех исправленных статей"""
        print("🔍 ПРОВЕРКА СТАТЕЙ С НЕПРАВИЛЬНЫМИ ИЗОБРАЖЕНИЯМИ")
        print("=" * 60)
        
        self.verification_stats['total'] = len(WRONG_IMAGES_ARTICLES)
        
        verified_articles = []
        failed_articles = []
        
        for i, post_id in enumerate(WRONG_IMAGES_ARTICLES, 1):
            print(f"\n📋 {i}/{len(WRONG_IMAGES_ARTICLES)} Проверка ID {post_id}")
            
            is_verified, message = self.verify_single_article(post_id)
            
            if is_verified:
                print(f"✅ {message}")
                verified_articles.append(post_id)
                self.verification_stats['verified'] += 1
            else:
                print(f"❌ {message}")
                failed_articles.append(post_id)
                self.verification_stats['failed'] += 1
                self.verification_stats['errors'].append(f"ID {post_id}: {message}")
            
            # Небольшая пауза между запросами
            if i < len(WRONG_IMAGES_ARTICLES):
                time.sleep(0.5)
        
        return verified_articles, failed_articles
    
    def print_verification_report(self, verified_articles, failed_articles):
        """Вывод отчета о проверке"""
        print("\n" + "=" * 60)
        print("📊 ОТЧЕТ О ПРОВЕРКЕ ИСПРАВЛЕНИЙ")
        print("=" * 60)
        
        print(f"📚 Всего проверено статей: {self.verification_stats['total']}")
        print(f"✅ Успешно исправлены: {self.verification_stats['verified']}")
        print(f"❌ Требуют доработки: {self.verification_stats['failed']}")
        
        if self.verification_stats['total'] > 0:
            success_rate = (self.verification_stats['verified'] / self.verification_stats['total']) * 100
            print(f"📊 Процент успешности: {success_rate:.1f}%")
        
        if verified_articles:
            print(f"\n✅ УСПЕШНО ИСПРАВЛЕННЫЕ СТАТЬИ:")
            print("-" * 50)
            for post_id in verified_articles:
                print(f"  https://ecopackpro.ru/?p={post_id}&preview=true")
        
        if failed_articles:
            print(f"\n❌ СТАТЬИ ТРЕБУЮЩИЕ ДОРАБОТКИ:")
            print("-" * 50)
            for post_id in failed_articles:
                print(f"  https://ecopackpro.ru/?p={post_id}&preview=true")
        
        if self.verification_stats['errors']:
            print(f"\n🚨 ОШИБКИ:")
            for error in self.verification_stats['errors']:
                print(f"  - {error}")

def main():
    """Основная функция"""
    print("🔍 ПРОВЕРКА СТАТЕЙ С НЕПРАВИЛЬНЫМИ ИЗОБРАЖЕНИЯМИ")
    print("=" * 60)
    
    # Создание экземпляра проверяющего
    checker = WrongImagesVerificationChecker(WORDPRESS_URL, USERNAME, APPLICATION_PASSWORD)
    
    # Проверка всех статей
    verified_articles, failed_articles = checker.verify_all_articles()
    
    # Вывод отчета
    checker.print_verification_report(verified_articles, failed_articles)
    
    if len(failed_articles) == 0:
        print(f"\n🎉 ВСЕ СТАТЬИ С НЕПРАВИЛЬНЫМИ ИЗОБРАЖЕНИЯМИ УСПЕШНО ИСПРАВЛЕНЫ!")
    else:
        print(f"\n⚠️  Требуется доработка {len(failed_articles)} статей")

if __name__ == "__main__":
    main()
