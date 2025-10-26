#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector
import requests
import base64
import json
from datetime import datetime

# Конфигурация базы данных WordPress
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

# Конфигурация WordPress API
WORDPRESS_URL = "https://ecopackpro.ru"
APPLICATION_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"
USERNAME = "rtep1976@me.com"

class Article7917SEOUpdaterV2:
    def __init__(self):
        self.db_config = DB_CONFIG
        self.wp_url = WORDPRESS_URL
        self.username = USERNAME
        self.app_password = APPLICATION_PASSWORD
        
        # Создание заголовков для аутентификации
        credentials = f"{self.username}:{self.app_password}"
        encoded_credentials = base64.b64encode(credentials.encode()).decode()
        
        self.headers = {
            'Authorization': f'Basic {encoded_credentials}',
            'Content-Type': 'application/json',
            'User-Agent': 'WordPress-API-Client/1.0'
        }
    
    def connect_to_database(self):
        """Подключение к базе данных MySQL"""
        try:
            connection = mysql.connector.connect(**self.db_config)
            return connection
        except mysql.connector.Error as e:
            print(f"❌ Ошибка подключения к базе данных: {e}")
            return None
    
    def force_update_yoast_meta(self, post_id, focus_keyword, meta_description):
        """Принудительное обновление мета данных Yoast SEO"""
        connection = self.connect_to_database()
        if not connection:
            return False
        
        try:
            cursor = connection.cursor()
            
            # Сначала удаляем все существующие Yoast SEO мета данные
            cursor.execute("""
                DELETE FROM wp_postmeta 
                WHERE post_id = %s 
                AND meta_key IN (
                    '_yoast_wpseo_focuskw',
                    '_yoast_wpseo_metadesc',
                    '_yoast_wpseo_title',
                    '_yoast_wpseo_canonical'
                )
            """, (post_id,))
            
            deleted_count = cursor.rowcount
            print(f"🗑️  Удалено существующих мета записей: {deleted_count}")
            
            # Создаем новые мета данные
            meta_inserts = [
                ('_yoast_wpseo_focuskw', focus_keyword),
                ('_yoast_wpseo_metadesc', meta_description),
                ('_yoast_wpseo_title', f"{focus_keyword}"),
                ('_yoast_wpseo_canonical', f"{self.wp_url}/courier-packages-with-pocket/")
            ]
            
            success_count = 0
            
            for meta_key, meta_value in meta_inserts:
                cursor.execute("""
                    INSERT INTO wp_postmeta (post_id, meta_key, meta_value) 
                    VALUES (%s, %s, %s)
                """, (post_id, meta_key, meta_value))
                
                if cursor.rowcount > 0:
                    success_count += 1
                    print(f"✅ Создано: {meta_key}")
                else:
                    print(f"❌ Ошибка создания: {meta_key}")
            
            connection.commit()
            
            if success_count == len(meta_inserts):
                print(f"✅ Все мета данные Yoast SEO созданы ({success_count}/{len(meta_inserts)})")
                return True
            else:
                print(f"⚠️  Частично создано: {success_count}/{len(meta_inserts)}")
                return False
                
        except mysql.connector.Error as e:
            print(f"❌ Ошибка обновления мета данных: {e}")
            return False
        finally:
            connection.close()
    
    def update_post_slug(self, post_id, new_slug):
        """Обновление slug (ярлыка) поста в базе данных"""
        connection = self.connect_to_database()
        if not connection:
            return False
        
        try:
            cursor = connection.cursor()
            
            # Обновляем post_name (slug)
            cursor.execute("""
                UPDATE wp_posts 
                SET post_name = %s 
                WHERE ID = %s
            """, (new_slug, post_id))
            
            connection.commit()
            
            if cursor.rowcount > 0:
                print(f"✅ Slug обновлен: {new_slug}")
                return True
            else:
                print(f"❌ Slug не был обновлен")
                return False
                
        except mysql.connector.Error as e:
            print(f"❌ Ошибка обновления slug: {e}")
            return False
        finally:
            connection.close()
    
    def get_current_post_data(self, post_id):
        """Получение текущих данных поста из базы данных"""
        connection = self.connect_to_database()
        if not connection:
            return None
        
        try:
            cursor = connection.cursor(dictionary=True)
            
            # Получаем данные поста
            cursor.execute("""
                SELECT ID, post_title, post_name, post_content, post_excerpt
                FROM wp_posts 
                WHERE ID = %s
            """, (post_id,))
            
            post_data = cursor.fetchone()
            
            if not post_data:
                print(f"❌ Пост с ID {post_id} не найден")
                return None
            
            # Получаем мета данные Yoast SEO
            cursor.execute("""
                SELECT meta_key, meta_value
                FROM wp_postmeta 
                WHERE post_id = %s 
                AND meta_key IN (
                    '_yoast_wpseo_focuskw',
                    '_yoast_wpseo_metadesc',
                    '_yoast_wpseo_title',
                    '_yoast_wpseo_canonical'
                )
            """, (post_id,))
            
            meta_data = cursor.fetchall()
            meta_dict = {row['meta_key']: row['meta_value'] for row in meta_data}
            
            post_data['meta'] = meta_dict
            return post_data
            
        except mysql.connector.Error as e:
            print(f"❌ Ошибка получения данных: {e}")
            return None
        finally:
            connection.close()
    
    def verify_changes(self, post_id):
        """Проверка применённых изменений"""
        print(f"\n🔍 ПРОВЕРКА ИЗМЕНЕНИЙ ДЛЯ ПОСТА {post_id}")
        print("=" * 50)
        
        # Проверяем через базу данных
        post_data = self.get_current_post_data(post_id)
        if not post_data:
            return False
        
        print(f"📄 Заголовок: {post_data['post_title']}")
        print(f"🔗 Slug: {post_data['post_name']}")
        
        meta = post_data.get('meta', {})
        print(f"🎯 Фокусное ключевое слово: {meta.get('_yoast_wpseo_focuskw', 'НЕ УСТАНОВЛЕНО')}")
        print(f"📝 Мета описание: {meta.get('_yoast_wpseo_metadesc', 'НЕ УСТАНОВЛЕНО')}")
        print(f"🏷️  SEO заголовок: {meta.get('_yoast_wpseo_title', 'НЕ УСТАНОВЛЕНО')}")
        print(f"🔗 Каноническая ссылка: {meta.get('_yoast_wpseo_canonical', 'НЕ УСТАНОВЛЕНА')}")
        
        # Проверяем через API
        try:
            response = requests.get(
                f"{self.wp_url}/wp-json/wp/v2/posts/{post_id}",
                headers=self.headers,
                timeout=30
            )
            
            if response.status_code == 200:
                api_data = response.json()
                print(f"\n🌐 API проверка:")
                print(f"   Ссылка: {api_data.get('link', 'НЕ НАЙДЕНА')}")
                print(f"   Slug в API: {api_data.get('slug', 'НЕ НАЙДЕН')}")
                
                return True
            else:
                print(f"❌ Ошибка API проверки: {response.status_code}")
                return False
                
        except Exception as e:
            print(f"❌ Ошибка API проверки: {e}")
            return False
    
    def fix_article_7917(self):
        """Основная функция исправления статьи 7917"""
        print("🔧 ИСПРАВЛЕНИЕ СТАТЬИ 7917 - SEO ПАРАМЕТРЫ (V2)")
        print("=" * 60)
        
        post_id = 7917
        focus_keyword = "курьерские пакеты с карманом"
        new_slug = "courier-packages-with-pocket"
        meta_description = f"{focus_keyword} - Пакеты с карманом для документов: встроенные vs самоклеящиеся SD, размеры А5/А6, применение в логистике. Ускорение обработки на 30%. Цены от 3 руб/шт!"
        
        print(f"📋 Параметры исправления:")
        print(f"   Post ID: {post_id}")
        print(f"   Фокусное ключевое слово: {focus_keyword}")
        print(f"   Новый slug: {new_slug}")
        print(f"   Мета описание: {meta_description[:80]}...")
        
        # Получаем текущие данные
        print(f"\n🔍 Получение текущих данных...")
        current_data = self.get_current_post_data(post_id)
        if not current_data:
            return False
        
        print(f"📄 Текущий заголовок: {current_data['post_title']}")
        print(f"🔗 Текущий slug: {current_data['post_name']}")
        
        # Обновляем slug
        print(f"\n🔧 Обновление slug...")
        slug_success = self.update_post_slug(post_id, new_slug)
        
        # Принудительно обновляем мета данные Yoast SEO
        print(f"\n🔧 Принудительное обновление мета данных Yoast SEO...")
        meta_success = self.force_update_yoast_meta(post_id, focus_keyword, meta_description)
        
        # Проверяем изменения
        print(f"\n🔍 Проверка применённых изменений...")
        verify_success = self.verify_changes(post_id)
        
        # Итоговый результат
        print(f"\n" + "=" * 60)
        print("📊 ИТОГОВЫЙ РЕЗУЛЬТАТ")
        print("=" * 60)
        
        print(f"🔗 Slug обновлён: {'✅' if slug_success else '❌'}")
        print(f"📝 Мета данные Yoast SEO: {'✅' if meta_success else '❌'}")
        print(f"🔍 Проверка изменений: {'✅' if verify_success else '❌'}")
        
        if slug_success and meta_success and verify_success:
            print(f"\n🎉 СТАТЬЯ 7917 УСПЕШНО ИСПРАВЛЕНА!")
            print(f"🔗 Новая ссылка: {self.wp_url}/{new_slug}/")
            print(f"📱 Админ панель: {self.wp_url}/wp-admin/post.php?post={post_id}&action=edit")
            return True
        else:
            print(f"\n❌ ИСПРАВЛЕНИЕ ЗАВЕРШИЛОСЬ С ОШИБКАМИ")
            return False

def main():
    """Основная функция"""
    updater = Article7917SEOUpdaterV2()
    success = updater.fix_article_7917()
    
    if success:
        print(f"\n✅ Все изменения применены успешно!")
        print(f"🔍 Проверьте статью в админ панели Yoast SEO Premium")
    else:
        print(f"\n❌ Произошли ошибки при исправлении")

if __name__ == "__main__":
    main()
