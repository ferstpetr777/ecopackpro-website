#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для исправления фирменных стилей - убираем синие цвета и исправляем размеры заголовков
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
        
        print(f"🎨 Исправление стилей WordPress: {self.api_url}")
    
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
    
    def fix_brand_styling(self, post_id):
        """
        Исправление фирменных стилей - правильные цвета и размеры заголовков
        """
        print(f"🎨 Исправление стилей поста ID: {post_id}")
        
        # Получаем текущий пост
        post = self.get_post(post_id)
        if not post:
            return False
        
        print(f"📝 Текущий заголовок: {post['title']['rendered']}")
        
        # ПРАВИЛЬНЫЕ фирменные цвета сайта bizfin-pro.ru (без синих!)
        brand_colors = {
            'primary_bg': '#FDFBF7',      # Светло-бежевый фон
            'text_color': '#333333',      # Темно-коричневый текст
            'accent_orange': '#FF8C00',   # Яркий оранжевый акцент
            'secondary_bg': '#FFFFFF',    # Белый фон для карточек
            'border_color': '#E0E0E0',    # Светло-серые границы
            'light_gray': '#F8F9FA',      # Светло-серый для фонов
            'success_green': '#28A745'    # Зеленый для успеха
        }
        
        # Фирменные шрифты
        brand_fonts = {
            'primary_font': 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            'heading_font': 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif',
            'accent_font': 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif'
        }
        
        # Получаем текущий контент
        current_content = post['content']['rendered']
        
        # Применяем исправленные стили
        styled_content = self.add_corrected_styles(current_content, brand_colors, brand_fonts)
        
        # Обновляем пост
        update_data = {
            'content': styled_content,
            'meta': {
                '_custom_css': self.generate_corrected_css(brand_colors, brand_fonts)
            }
        }
        
        try:
            response = requests.post(f"{self.api_url}/posts/{post_id}", 
                                   headers=self.headers, 
                                   json=update_data, 
                                   timeout=30)
            
            if response.status_code == 200:
                updated_post = response.json()
                print("✅ Стили успешно исправлены!")
                print(f"🔗 Обновленная статья: {updated_post['link']}")
                return True
            else:
                print(f"❌ Ошибка обновления: {response.status_code}")
                print(f"📝 Ответ: {response.text}")
                return False
                
        except Exception as e:
            print(f"❌ Ошибка: {e}")
            return False
    
    def add_corrected_styles(self, content, colors, fonts):
        """
        Добавление исправленных стилей к контенту
        """
        print("🎨 Применение исправленных стилей (без синих цветов)...")
        
        # Добавляем исправленные стили в начало контента
        corrected_styles = f"""
        <style>
        /* Исправленные фирменные стили BizFin Pro (БЕЗ СИНИХ ЦВЕТОВ) */
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
            font-size: 2.2em;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }}
        
        .article-header p {{
            font-size: 1.1em;
            opacity: 0.95;
            margin-bottom: 30px;
        }}
        
        .cta-block {{
            background: rgba(255,255,255,0.15);
            padding: 25px;
            border-radius: 12px;
            margin: 25px 0;
            backdrop-filter: blur(10px);
        }}
        
        .cta-block h3 {{
            color: white;
            margin-bottom: 15px;
            font-family: {fonts['heading_font']};
            font-size: 1.3em;
        }}
        
        .cta-button {{
            background: {colors['accent_orange']};
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 20px;
            font-size: 1em;
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
            padding: 35px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            border-left: 4px solid {colors['accent_orange']};
        }}
        
        .content-section h2 {{
            color: {colors['text_color']};
            font-family: {fonts['heading_font']};
            font-size: 1.8em;
            margin-bottom: 20px;
            font-weight: 700;
        }}
        
        .content-section h3 {{
            color: {colors['accent_orange']};
            font-family: {fonts['heading_font']};
            font-size: 1.3em;
            margin-bottom: 15px;
            font-weight: 600;
        }}
        
        .content-section p {{
            color: {colors['text_color']};
            font-size: 1.05em;
            line-height: 1.7;
            margin-bottom: 18px;
        }}
        
        .highlight-box {{
            background: linear-gradient(135deg, #FFF3E0 0%, #FFE0B2 100%);
            border: 2px solid {colors['accent_orange']};
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }}
        
        .highlight-box h3 {{
            color: {colors['accent_orange']};
            margin-bottom: 12px;
            font-size: 1.2em;
        }}
        
        .info-table {{
            background: {colors['secondary_bg']};
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
            margin: 20px 0;
        }}
        
        .info-table table {{
            width: 100%;
            border-collapse: collapse;
        }}
        
        .info-table th {{
            background: {colors['accent_orange']};
            color: white;
            padding: 12px;
            font-weight: 600;
            text-align: left;
            font-size: 0.95em;
        }}
        
        .info-table td {{
            padding: 12px;
            border-bottom: 1px solid {colors['border_color']};
            font-size: 0.9em;
        }}
        
        .info-table tr:nth-child(even) {{
            background: {colors['light_gray']};
        }}
        
        /* ИСПРАВЛЕННЫЙ FAQ БЛОК - АККУРАТНЫЕ ЗАГОЛОВКИ */
        .faq-section {{
            background: {colors['secondary_bg']};
            padding: 30px;
            border-radius: 12px;
            margin: 25px 0;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }}
        
        .faq-section h2 {{
            color: {colors['text_color']};
            font-family: {fonts['heading_font']};
            font-size: 1.6em;
            margin-bottom: 25px;
            font-weight: 700;
            text-align: center;
        }}
        
        .faq-item {{
            background: {colors['light_gray']};
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid {colors['border_color']};
        }}
        
        .faq-question {{
            background: {colors['accent_orange']};
            color: white;
            padding: 15px 20px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1em;
            transition: background 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }}
        
        .faq-question:hover {{
            background: #E67E00;
        }}
        
        .faq-question .faq-icon {{
            font-size: 1.1em;
            min-width: 20px;
        }}
        
        .faq-answer {{
            padding: 20px;
            color: {colors['text_color']};
            line-height: 1.6;
            font-size: 0.95em;
            background: {colors['secondary_bg']};
        }}
        
        .calculator-section {{
            background: linear-gradient(135deg, {colors['accent_orange']} 0%, #FF6B35 100%);
            color: white;
            padding: 35px;
            border-radius: 12px;
            margin: 25px 0;
        }}
        
        .calculator-section h3 {{
            color: white;
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.4em;
        }}
        
        .form-group {{
            margin-bottom: 18px;
        }}
        
        .form-group label {{
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: white;
            font-size: 0.95em;
        }}
        
        .form-group input,
        .form-group select {{
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            font-size: 0.95em;
            background: rgba(255,255,255,0.9);
        }}
        
        .result-box {{
            background: rgba(255,255,255,0.1);
            padding: 18px;
            border-radius: 8px;
            margin-top: 18px;
            text-align: center;
        }}
        
        .contact-form {{
            background: {colors['secondary_bg']};
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }}
        
        .contact-form h2 {{
            color: {colors['text_color']};
            text-align: center;
            margin-bottom: 25px;
            font-size: 1.6em;
        }}
        
        .form-row {{
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 18px;
            margin-bottom: 18px;
        }}
        
        .form-field {{
            margin-bottom: 18px;
        }}
        
        .form-field label {{
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: {colors['text_color']};
            font-size: 0.95em;
        }}
        
        .form-field input,
        .form-field select,
        .form-field textarea {{
            width: 100%;
            padding: 10px;
            border: 2px solid {colors['border_color']};
            border-radius: 6px;
            font-size: 0.95em;
            transition: border-color 0.3s ease;
        }}
        
        .form-field input:focus,
        .form-field select:focus,
        .form-field textarea:focus {{
            outline: none;
            border-color: {colors['accent_orange']};
        }}
        
        .expert-section {{
            background: {colors['light_gray']};
            padding: 25px;
            border-radius: 10px;
            border-left: 4px solid {colors['success_green']};
            margin: 25px 0;
        }}
        
        .expert-info {{
            display: flex;
            align-items: center;
            margin-bottom: 18px;
        }}
        
        .expert-avatar {{
            width: 50px;
            height: 50px;
            background: {colors['success_green']};
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 1.3em;
        }}
        
        .expert-details h3 {{
            color: {colors['text_color']};
            margin-bottom: 4px;
            font-size: 1.1em;
        }}
        
        .expert-details p {{
            color: #666;
            margin: 0;
            font-size: 0.9em;
        }}
        
        .stats-row {{
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #666;
            font-size: 0.85em;
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
                font-size: 1.8em;
            }}
            
            .content-section {{
                padding: 25px;
            }}
            
            .content-section h2 {{
                font-size: 1.5em;
            }}
            
            .faq-section h2 {{
                font-size: 1.4em;
            }}
            
            .form-row {{
                grid-template-columns: 1fr;
            }}
        }}
        </style>
        
        <div class="article-container">
        """
        
        # Добавляем исправленные стили в начало контента
        styled_content = corrected_styles + content + "</div>"
        
        return styled_content
    
    def generate_corrected_css(self, colors, fonts):
        """
        Генерация исправленного CSS для темы
        """
        return f"""
        /* Исправленные фирменные стили (БЕЗ СИНИХ ЦВЕТОВ) */
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
            border-radius: 20px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }}
        
        .wp-block-button__link:hover {{
            background: #E67E00;
            transform: translateY(-2px);
        }}
        
        /* Исправление размеров заголовков FAQ */
        .faq-section h3 {{
            font-size: 1em !important;
            font-weight: 600 !important;
            margin-bottom: 10px !important;
        }}
        """

def main():
    """
    Основная функция для исправления стилей
    """
    print("🎨 WordPress Style Fixer - Исправление стилей")
    print("=" * 60)
    
    # Параметры подключения
    SITE_URL = "https://bizfin-pro.ru"
    USERNAME = "bizfin_pro_r"
    APP_PASSWORD = "U3Ep gU2T clRu FcwN QU6l Dsda"
    
    # ID опубликованной статьи
    POST_ID = 2067
    
    # Создаем экземпляр стилизатора
    styler = WordPressStyler(SITE_URL, USERNAME, APP_PASSWORD)
    
    # Исправляем стили
    success = styler.fix_brand_styling(POST_ID)
    
    if success:
        print("\n🎉 СТИЛИ УСПЕШНО ИСПРАВЛЕНЫ!")
        print("🔗 Обновленная статья: https://bizfin-pro.ru/tender-guarantee/")
        print("🎨 Исправления:")
        print("   ❌ Убраны все синие цвета")
        print("   📏 Исправлены размеры заголовков FAQ")
        print("   🎯 Применены правильные фирменные цвета")
        print("   ✨ Улучшена читаемость")
    else:
        print("\n❌ НЕ УДАЛОСЬ ИСПРАВИТЬ СТИЛИ!")
        print("🔧 Проверьте логи выше для диагностики проблемы")

if __name__ == "__main__":
    main()


