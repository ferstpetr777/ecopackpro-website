#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для применения фирменных цветов и шрифтов к статье
"""

import requests
import json
import base64
from datetime import datetime

class WordPressStyler:
    def __init__(self, site_url, username, app_password):
        """
        Инициализация подключения к WordPress API
        """
        self.site_url = site_url.rstrip('/')
        self.api_url = f"{self.site_url}/wp-json/wp/v2"
        self.username = username
        self.app_password = app_password
        
        # Создаем заголовки для аутентификации
        credentials = f"{username}:{app_password}"
        token = base64.b64encode(credentials.encode()).decode('utf-8')
        
        self.headers = {
            'Authorization': f'Basic {token}',
            'Content-Type': 'application/json',
            'User-Agent': 'WordPress-Styler/1.0'
        }
        
        print(f"🎨 Подключение к WordPress API для стилизации: {self.api_url}")
    
    def get_post(self, post_id):
        """
        Получение поста по ID
        """
        try:
            response = requests.get(f"{self.api_url}/posts/{post_id}", headers=self.headers, timeout=10)
            if response.status_code == 200:
                return response.json()
            else:
                print(f"❌ Ошибка получения поста: {response.status_code}")
                return None
        except Exception as e:
            print(f"❌ Ошибка: {e}")
            return None
    
    def apply_brand_styling(self, post_id):
        """
        Применение фирменных стилей к статье
        """
        print(f"🎨 Применение фирменных стилей к посту ID: {post_id}")
        
        # Получаем текущий пост
        post = self.get_post(post_id)
        if not post:
            return False
        
        print(f"📝 Текущий заголовок: {post['title']['rendered']}")
        
        # Фирменные цвета сайта bizfin-pro.ru
        brand_colors = {
            'primary_bg': '#FDFBF7',      # Светло-бежевый фон
            'text_color': '#333333',      # Темно-коричневый текст
            'accent_orange': '#FF8C00',   # Яркий оранжевый акцент
            'secondary_bg': '#FFFFFF',    # Белый фон для карточек
            'border_color': '#E0E0E0'     # Светло-серые границы
        }
        
        # Фирменные шрифты
        brand_fonts = {
            'primary_font': 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            'heading_font': 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            'accent_font': 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
        }
        
        # Получаем текущий контент
        current_content = post['content']['rendered']
        
        # Применяем фирменные стили
        styled_content = self.add_brand_styles(current_content, brand_colors, brand_fonts)
        
        # Обновляем пост
        update_data = {
            'content': styled_content,
            'meta': {
                '_custom_css': self.generate_custom_css(brand_colors, brand_fonts)
            }
        }
        
        try:
            response = requests.post(f"{self.api_url}/posts/{post_id}", 
                                   headers=self.headers, 
                                   json=update_data, 
                                   timeout=30)
            
            if response.status_code == 200:
                updated_post = response.json()
                print("✅ Фирменные стили успешно применены!")
                print(f"🔗 Обновленная статья: {updated_post['link']}")
                return True
            else:
                print(f"❌ Ошибка обновления: {response.status_code}")
                print(f"📝 Ответ: {response.text}")
                return False
                
        except Exception as e:
            print(f"❌ Ошибка: {e}")
            return False
    
    def add_brand_styles(self, content, colors, fonts):
        """
        Добавление фирменных стилей к контенту
        """
        print("🎨 Применение фирменных цветов и шрифтов...")
        
        # Добавляем стили в начало контента
        brand_styles = f"""
        <style>
        /* Фирменные стили BizFin Pro */
        .article-container {{
            background-color: {colors['primary_bg']};
            font-family: {fonts['primary_font']};
            color: {colors['text_color']};
            line-height: 1.6;
            padding: 40px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }}
        
        .article-header {{
            background: linear-gradient(135deg, {colors['accent_orange']} 0%, #FF6B35 100%);
            color: white;
            padding: 60px 40px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(255, 140, 0, 0.3);
        }}
        
        .article-header h1 {{
            font-family: {fonts['heading_font']};
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }}
        
        .article-header p {{
            font-size: 1.2em;
            opacity: 0.95;
            margin-bottom: 30px;
        }}
        
        .cta-block {{
            background: rgba(255,255,255,0.15);
            padding: 30px;
            border-radius: 15px;
            margin: 30px 0;
            backdrop-filter: blur(10px);
        }}
        
        .cta-block h3 {{
            color: white;
            margin-bottom: 15px;
            font-family: {fonts['heading_font']};
        }}
        
        .cta-button {{
            background: {colors['accent_orange']};
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.4);
        }}
        
        .cta-button:hover {{
            background: #E67E00;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 0, 0.6);
        }}
        
        .content-section {{
            background: {colors['secondary_bg']};
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-left: 5px solid {colors['accent_orange']};
        }}
        
        .content-section h2 {{
            color: {colors['text_color']};
            font-family: {fonts['heading_font']};
            font-size: 2em;
            margin-bottom: 25px;
            font-weight: 700;
        }}
        
        .content-section h3 {{
            color: {colors['accent_orange']};
            font-family: {fonts['heading_font']};
            font-size: 1.5em;
            margin-bottom: 20px;
            font-weight: 600;
        }}
        
        .content-section p {{
            color: {colors['text_color']};
            font-size: 1.1em;
            line-height: 1.7;
            margin-bottom: 20px;
        }}
        
        .highlight-box {{
            background: linear-gradient(135deg, #FFF3E0 0%, #FFE0B2 100%);
            border: 2px solid {colors['accent_orange']};
            border-radius: 10px;
            padding: 25px;
            margin: 25px 0;
        }}
        
        .highlight-box h3 {{
            color: {colors['accent_orange']};
            margin-bottom: 15px;
        }}
        
        .info-table {{
            background: {colors['secondary_bg']};
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            margin: 25px 0;
        }}
        
        .info-table table {{
            width: 100%;
            border-collapse: collapse;
        }}
        
        .info-table th {{
            background: {colors['accent_orange']};
            color: white;
            padding: 15px;
            font-weight: 600;
            text-align: left;
        }}
        
        .info-table td {{
            padding: 15px;
            border-bottom: 1px solid {colors['border_color']};
        }}
        
        .info-table tr:nth-child(even) {{
            background: #FAFAFA;
        }}
        
        .faq-item {{
            background: {colors['secondary_bg']};
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }}
        
        .faq-question {{
            background: {colors['accent_orange']};
            color: white;
            padding: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s ease;
        }}
        
        .faq-question:hover {{
            background: #E67E00;
        }}
        
        .faq-answer {{
            padding: 20px;
            color: {colors['text_color']};
            line-height: 1.6;
        }}
        
        .calculator-section {{
            background: linear-gradient(135deg, {colors['accent_orange']} 0%, #FF6B35 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin: 30px 0;
        }}
        
        .calculator-section h3 {{
            color: white;
            text-align: center;
            margin-bottom: 30px;
        }}
        
        .form-group {{
            margin-bottom: 20px;
        }}
        
        .form-group label {{
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: white;
        }}
        
        .form-group input,
        .form-group select {{
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            background: rgba(255,255,255,0.9);
        }}
        
        .result-box {{
            background: rgba(255,255,255,0.1);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
            text-align: center;
        }}
        
        .contact-form {{
            background: {colors['secondary_bg']};
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }}
        
        .contact-form h2 {{
            color: {colors['text_color']};
            text-align: center;
            margin-bottom: 30px;
        }}
        
        .form-row {{
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }}
        
        .form-field {{
            margin-bottom: 20px;
        }}
        
        .form-field label {{
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: {colors['text_color']};
        }}
        
        .form-field input,
        .form-field select,
        .form-field textarea {{
            width: 100%;
            padding: 12px;
            border: 2px solid {colors['border_color']};
            border-radius: 8px;
            font-size: 1em;
            transition: border-color 0.3s ease;
        }}
        
        .form-field input:focus,
        .form-field select:focus,
        .form-field textarea:focus {{
            outline: none;
            border-color: {colors['accent_orange']};
        }}
        
        .expert-section {{
            background: #F8F9FA;
            padding: 30px;
            border-radius: 10px;
            border-left: 5px solid #28A745;
            margin: 30px 0;
        }}
        
        .expert-info {{
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }}
        
        .expert-avatar {{
            width: 60px;
            height: 60px;
            background: #28A745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            color: white;
            font-size: 1.5em;
        }}
        
        .expert-details h3 {{
            color: {colors['text_color']};
            margin-bottom: 5px;
        }}
        
        .expert-details p {{
            color: #666;
            margin: 0;
        }}
        
        .stats-row {{
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #666;
            font-size: 0.9em;
        }}
        
        /* Адаптивность */
        @media (max-width: 768px) {{
            .article-container {{
                padding: 20px 10px;
            }}
            
            .article-header {{
                padding: 40px 20px;
            }}
            
            .article-header h1 {{
                font-size: 2em;
            }}
            
            .content-section {{
                padding: 25px;
            }}
            
            .form-row {{
                grid-template-columns: 1fr;
            }}
        }}
        </style>
        
        <div class="article-container">
        """
        
        # Добавляем стили в начало контента
        styled_content = brand_styles + content + "</div>"
        
        return styled_content
    
    def generate_custom_css(self, colors, fonts):
        """
        Генерация дополнительного CSS для темы
        """
        return f"""
        /* Дополнительные фирменные стили */
        body {{
            background-color: {colors['primary_bg']};
            font-family: {fonts['primary_font']};
        }}
        
        .entry-content {{
            background: transparent;
        }}
        
        .entry-title {{
            color: {colors['text_color']};
            font-family: {fonts['heading_font']};
        }}
        
        .wp-block-button__link {{
            background: {colors['accent_orange']};
            border-radius: 25px;
            padding: 15px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }}
        
        .wp-block-button__link:hover {{
            background: #E67E00;
            transform: translateY(-2px);
        }}
        """

def main():
    """
    Основная функция для применения фирменных стилей
    """
    print("🎨 WordPress Brand Styler - Применение фирменных стилей")
    print("=" * 60)
    
    # Параметры подключения
    SITE_URL = "https://bizfin-pro.ru"
    USERNAME = "bizfin_pro_r"
    APP_PASSWORD = "U3Ep gU2T clRu FcwN QU6l Dsda"
    
    # ID опубликованной статьи
    POST_ID = 2067
    
    # Создаем экземпляр стилизатора
    styler = WordPressStyler(SITE_URL, USERNAME, APP_PASSWORD)
    
    # Применяем фирменные стили
    success = styler.apply_brand_styling(POST_ID)
    
    if success:
        print("\n🎉 ФИРМЕННЫЕ СТИЛИ УСПЕШНО ПРИМЕНЕНЫ!")
        print("🔗 Обновленная статья: https://bizfin-pro.ru/tender-guarantee/")
        print("🎨 Применены:")
        print("   📊 Фирменные цвета BizFin Pro")
        print("   🔤 Фирменные шрифты")
        print("   🎯 Адаптивный дизайн")
        print("   ✨ Интерактивные элементы")
    else:
        print("\n❌ НЕ УДАЛОСЬ ПРИМЕНИТЬ СТИЛИ!")
        print("🔧 Проверьте логи выше для диагностики проблемы")

if __name__ == "__main__":
    main()


