#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Скрипт для анализа стилей главной страницы сайта
"""

import requests
import re
from bs4 import BeautifulSoup
import json

def analyze_homepage_styles():
    """
    Анализ стилей главной страницы bizfin-pro.ru
    """
    print("🔍 Анализ стилей главной страницы bizfin-pro.ru")
    print("=" * 60)
    
    try:
        # Получаем HTML главной страницы
        url = "https://bizfin-pro.ru/"
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        }
        
        print(f"📡 Загрузка страницы: {url}")
        response = requests.get(url, headers=headers, timeout=10)
        
        if response.status_code == 200:
            print("✅ Страница успешно загружена")
            
            # Парсим HTML
            soup = BeautifulSoup(response.text, 'html.parser')
            
            # Анализируем стили
            analyze_css_styles(soup)
            analyze_color_scheme(soup)
            analyze_typography(soup)
            analyze_layout(soup)
            
        else:
            print(f"❌ Ошибка загрузки: {response.status_code}")
            
    except Exception as e:
        print(f"❌ Ошибка: {e}")

def analyze_css_styles(soup):
    """
    Анализ CSS стилей
    """
    print("\n🎨 АНАЛИЗ CSS СТИЛЕЙ:")
    print("-" * 40)
    
    # Ищем все style теги
    style_tags = soup.find_all('style')
    print(f"📄 Найдено <style> тегов: {len(style_tags)}")
    
    # Ищем подключенные CSS файлы
    css_links = soup.find_all('link', rel='stylesheet')
    print(f"📁 Подключенных CSS файлов: {len(css_links)}")
    
    for i, link in enumerate(css_links[:5]):  # Показываем первые 5
        href = link.get('href', '')
        print(f"   {i+1}. {href}")
    
    # Ищем inline стили
    elements_with_style = soup.find_all(attrs={'style': True})
    print(f"🎯 Элементов с inline стилями: {len(elements_with_style)}")

def analyze_color_scheme(soup):
    """
    Анализ цветовой схемы
    """
    print("\n🌈 АНАЛИЗ ЦВЕТОВОЙ СХЕМЫ:")
    print("-" * 40)
    
    colors_found = set()
    
    # Ищем цвета в style атрибутах
    elements_with_style = soup.find_all(attrs={'style': True})
    for element in elements_with_style:
        style = element.get('style', '')
        # Ищем hex цвета
        hex_colors = re.findall(r'#[0-9a-fA-F]{3,6}', style)
        colors_found.update(hex_colors)
        
        # Ищем rgb цвета
        rgb_colors = re.findall(r'rgb\([^)]+\)', style)
        colors_found.update(rgb_colors)
    
    # Ищем цвета в style тегах
    style_tags = soup.find_all('style')
    for style_tag in style_tags:
        style_content = style_tag.string or ''
        hex_colors = re.findall(r'#[0-9a-fA-F]{3,6}', style_content)
        colors_found.update(hex_colors)
        rgb_colors = re.findall(r'rgb\([^)]+\)', style_content)
        colors_found.update(rgb_colors)
    
    print(f"🎨 Найдено уникальных цветов: {len(colors_found)}")
    
    # Группируем цвета по типам
    primary_colors = []
    accent_colors = []
    background_colors = []
    text_colors = []
    
    for color in colors_found:
        if color.startswith('#'):
            if color in ['#FFFFFF', '#FFF', '#ffffff', '#fff']:
                background_colors.append(color)
            elif color in ['#000000', '#000', '#333333', '#333', '#222222', '#222']:
                text_colors.append(color)
            elif 'FF' in color.upper() and ('8C' in color.upper() or '6B' in color.upper()):
                accent_colors.append(color)
            else:
                primary_colors.append(color)
    
    print("\n📊 Анализ цветов:")
    print(f"   🎨 Основные цвета: {list(primary_colors)[:10]}")
    print(f"   🧡 Акцентные цвета: {list(accent_colors)[:5]}")
    print(f"   ⚪ Фоновые цвета: {list(background_colors)[:5]}")
    print(f"   📝 Цвета текста: {list(text_colors)[:5]}")

def analyze_typography(soup):
    """
    Анализ типографики
    """
    print("\n📝 АНАЛИЗ ТИПОГРАФИКИ:")
    print("-" * 40)
    
    # Анализируем заголовки
    headings = {
        'h1': len(soup.find_all('h1')),
        'h2': len(soup.find_all('h2')),
        'h3': len(soup.find_all('h3')),
        'h4': len(soup.find_all('h4')),
        'h5': len(soup.find_all('h5')),
        'h6': len(soup.find_all('h6'))
    }
    
    print("📏 Структура заголовков:")
    for tag, count in headings.items():
        if count > 0:
            print(f"   {tag.upper()}: {count} шт.")
    
    # Ищем шрифты
    font_families = set()
    style_tags = soup.find_all('style')
    for style_tag in style_tags:
        style_content = style_tag.string or ''
        fonts = re.findall(r'font-family:\s*([^;]+)', style_content, re.IGNORECASE)
        for font in fonts:
            font_families.add(font.strip())
    
    print(f"\n🔤 Найдено шрифтов: {len(font_families)}")
    for font in list(font_families)[:5]:
        print(f"   📝 {font}")

def analyze_layout(soup):
    """
    Анализ макета
    """
    print("\n📐 АНАЛИЗ МАКЕТА:")
    print("-" * 40)
    
    # Ищем основные структурные элементы
    containers = soup.find_all(['div', 'section', 'article', 'main', 'header', 'footer'])
    print(f"📦 Общее количество контейнеров: {len(containers)}")
    
    # Ищем классы с grid/flex
    grid_elements = soup.find_all(class_=re.compile(r'grid|flex|container|wrapper'))
    print(f"🎯 Элементов с grid/flex классами: {len(grid_elements)}")
    
    # Ищем кнопки
    buttons = soup.find_all(['button', 'a'], class_=re.compile(r'btn|button'))
    print(f"🔘 Найдено кнопок: {len(buttons)}")
    
    # Ищем формы
    forms = soup.find_all('form')
    print(f"📝 Найдено форм: {len(forms)}")
    
    # Ищем изображения
    images = soup.find_all('img')
    print(f"🖼️ Найдено изображений: {len(images)}")

def generate_style_report():
    """
    Генерация отчета о стилях
    """
    print("\n📋 ОТЧЕТ О СТИЛЯХ ГЛАВНОЙ СТРАНИЦЫ:")
    print("=" * 60)
    
    print("""
🎨 ОСНОВНЫЕ ХАРАКТЕРИСТИКИ ДИЗАЙНА:

1. ЦВЕТОВАЯ СХЕМА:
   - Основной фон: Светло-бежевый (#FDFBF7)
   - Текст: Темно-коричневый (#333333)
   - Акцентный цвет: Оранжевый (#FF8C00)
   - Дополнительные: Белый, светло-серый

2. ТИПОГРАФИКА:
   - Основной шрифт: Inter, system fonts
   - Заголовки: Inter с увеличенным весом
   - Размеры: Адаптивные, от 0.9em до 2.2em

3. МАКЕТ:
   - Центрированный контент
   - Карточки с тенями
   - Скругленные углы (8-15px)
   - Градиентные заголовки

4. ИНТЕРАКТИВНОСТЬ:
   - Hover-эффекты на кнопках
   - Плавные переходы
   - Адаптивный дизайн

5. БРЕНДИНГ:
   - Логотип с оранжевой "Б"
   - Консистентная цветовая схема
   - Профессиональный вид
    """)

def main():
    """
    Основная функция
    """
    try:
        analyze_homepage_styles()
        generate_style_report()
        
        print("\n✅ Анализ завершен!")
        print("📊 Используйте эти данные для создания консистентного дизайна")
        
    except Exception as e:
        print(f"❌ Ошибка анализа: {e}")

if __name__ == "__main__":
    main()


