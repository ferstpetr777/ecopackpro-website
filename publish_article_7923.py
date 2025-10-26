#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Скрипт для публикации статьи 7923 через WordPress REST API
"""

import requests
from requests.auth import HTTPBasicAuth
import json
from datetime import datetime

# WordPress API credentials
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"

ARTICLE_ID = 7923

def publish_article():
    """Публикует статью через WordPress REST API"""
    print("\n" + "="*100)
    print("📰 ПУБЛИКАЦИЯ СТАТЬИ ID 7923 ЧЕРЕЗ WORDPRESS REST API".center(100))
    print("="*100 + "\n")
    
    auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
    headers = {'Content-Type': 'application/json'}
    
    # Получаем текущие данные статьи
    print(f"📥 Получаю данные статьи ID {ARTICLE_ID}...")
    url = f"{WP_API_URL}/posts/{ARTICLE_ID}"
    response = requests.get(url, auth=auth, headers=headers, timeout=30)
    
    if response.status_code == 200:
        post_data = response.json()
        print(f"✅ Статья получена:")
        print(f"   📝 Название: {post_data['title']['rendered']}")
        print(f"   📊 Текущий статус: {post_data['status']}")
        print(f"   🔗 Slug: {post_data['slug']}\n")
    else:
        print(f"❌ Ошибка получения статьи: {response.status_code}")
        return False
    
    # Обновляем статус на publish
    print(f"🚀 Публикую статью ID {ARTICLE_ID}...")
    update_data = {
        "status": "publish"
    }
    
    response = requests.post(url, auth=auth, headers=headers, json=update_data, timeout=60)
    
    if response.status_code == 200:
        updated_post = response.json()
        print(f"✅ Статья успешно опубликована!")
        print(f"   📝 Название: {updated_post['title']['rendered']}")
        print(f"   📊 Статус: {updated_post['status']}")
        print(f"   🔗 URL: {updated_post['link']}")
        print(f"   📅 Дата публикации: {updated_post['date']}\n")
        
        # Проверяем доступность
        print("🌐 Проверяю доступность статьи...")
        import time
        time.sleep(2)
        
        check_response = requests.get(updated_post['link'], timeout=10)
        if check_response.status_code == 200:
            print(f"✅ Статья доступна! HTTP 200")
            print(f"   Размер страницы: {len(check_response.content)} байт\n")
        else:
            print(f"⚠️  HTTP статус: {check_response.status_code}\n")
        
        return True
    else:
        print(f"❌ Ошибка публикации: {response.status_code}")
        print(f"   {response.text}\n")
        return False

def check_sitemap():
    """Проверяет наличие статьи в sitemap"""
    print("="*100)
    print("🗺️  ПРОВЕРКА SITEMAP".center(100))
    print("="*100 + "\n")
    
    print("🔄 Очищаю кэш sitemap...")
    import mysql.connector
    
    WP_DB_CONFIG = {
        'host': 'localhost',
        'user': 'm1shqamai2_worp6',
        'password': '9nUQkM*Q2cnvy379',
        'database': 'm1shqamai2_worp6'
    }
    
    conn = mysql.connector.connect(**WP_DB_CONFIG)
    cursor = conn.cursor()
    cursor.execute("DELETE FROM wp_options WHERE option_name LIKE '%wpseo%cache%'")
    cursor.execute("DELETE FROM wp_options WHERE option_name = 'wpseo_sitemap_cache'")
    conn.commit()
    cursor.close()
    conn.close()
    
    print("✅ Кэш sitemap очищен\n")
    
    # Запрашиваем sitemap для регенерации
    print("🔄 Запрашиваю sitemap для регенерации...")
    response = requests.get('https://ecopackpro.ru/post-sitemap.xml', timeout=30)
    if response.status_code == 200:
        print("✅ Sitemap регенерирован!\n")
        
        # Проверяем наличие статьи
        if 'korobki-dlya-otpravki' in response.text:
            print("✅ Статья ID 7923 найдена в sitemap!")
            print("   🔗 https://ecopackpro.ru/korobki-dlya-otpravki/\n")
            return True
        else:
            print("⚠️  Статья пока не добавлена в sitemap (может потребоваться время)\n")
            return False
    else:
        print(f"❌ Ошибка получения sitemap: {response.status_code}\n")
        return False

def main():
    start_time = datetime.now()
    
    if publish_article():
        import time
        time.sleep(3)
        check_sitemap()
    
    end_time = datetime.now()
    duration = end_time - start_time
    
    print("="*100)
    print("📊 ИТОГОВЫЙ ОТЧЕТ".center(100))
    print("="*100)
    print(f"⏱️  Время выполнения: {duration}")
    print(f"✅ Статья ID 7923 'Коробки для отправки' опубликована")
    print(f"🔗 URL: https://ecopackpro.ru/korobki-dlya-otpravki/")
    print("="*100)

if __name__ == "__main__":
    main()

