#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import mysql.connector
import re
from datetime import datetime
from bs4 import BeautifulSoup

# Конфигурация базы данных WordPress
DB_CONFIG = {
    'host': 'localhost',
    'user': 'm1shqamai2_worp6',
    'password': '9nUQkM*Q2cnvy379',
    'database': 'm1shqamai2_worp6'
}

# Список всех 50 статей (ID из аудита)
ARTICLE_IDS = [
    7907, 7908, 7909, 7910, 7911, 7912, 7913, 7914, 7915, 7916,
    7917, 7918, 7919, 7920, 7921, 7922, 7923, 7924, 7925, 7926,
    7927, 7928, 7929, 7930, 7931, 7932, 7933, 7934, 7935, 7936,
    7937, 7938, 7939, 7940, 7941, 7942, 7943, 7944, 7945, 7946,
    7947, 7948, 7949, 7950, 7951, 7952, 7953, 7954, 7955, 7956
]

class WordCountAnalyzer:
    def __init__(self):
        self.db_config = DB_CONFIG
        self.word_counts = []
        
    def connect_to_database(self):
        """Подключение к базе данных MySQL"""
        try:
            connection = mysql.connector.connect(**self.db_config)
            return connection
        except mysql.connector.Error as e:
            print(f"❌ Ошибка подключения к базе данных: {e}")
            return None
    
    def clean_html(self, html_content):
        """Очистка HTML и извлечение текста"""
        if not html_content:
            return ""
        
        # Используем BeautifulSoup для удаления HTML тегов
        soup = BeautifulSoup(html_content, 'html.parser')
        
        # Удаляем скрипты и стили
        for script in soup(["script", "style"]):
            script.decompose()
        
        # Получаем текст
        text = soup.get_text()
        
        # Удаляем множественные пробелы и переносы строк
        text = re.sub(r'\s+', ' ', text)
        
        return text.strip()
    
    def count_words(self, text):
        """Подсчет количества слов в тексте"""
        if not text:
            return 0
        
        # Разбиваем текст на слова (учитываем русский и английский языки)
        words = re.findall(r'\b[\w\-]+\b', text, re.UNICODE)
        
        # Фильтруем очень короткие "слова" (1-2 символа могут быть артефактами)
        meaningful_words = [w for w in words if len(w) >= 2]
        
        return len(meaningful_words)
    
    def get_article_data(self, post_id):
        """Получение данных статьи из базы данных"""
        connection = self.connect_to_database()
        if not connection:
            return None
        
        try:
            cursor = connection.cursor(dictionary=True)
            
            # Получаем данные поста
            cursor.execute("""
                SELECT ID, post_title, post_content, post_excerpt
                FROM wp_posts 
                WHERE ID = %s
            """, (post_id,))
            
            post_data = cursor.fetchone()
            
            if not post_data:
                return None
            
            # Получаем фокусное ключевое слово
            cursor.execute("""
                SELECT meta_value
                FROM wp_postmeta 
                WHERE post_id = %s 
                AND meta_key = '_yoast_wpseo_focuskw'
            """, (post_id,))
            
            focus_kw = cursor.fetchone()
            post_data['focus_keyword'] = focus_kw['meta_value'] if focus_kw else ''
            
            return post_data
            
        except mysql.connector.Error as e:
            print(f"❌ Ошибка получения данных для ID {post_id}: {e}")
            return None
        finally:
            connection.close()
    
    def analyze_article(self, post_id):
        """Анализ одной статьи"""
        article_data = self.get_article_data(post_id)
        
        if not article_data:
            return {
                'id': post_id,
                'title': 'НЕ НАЙДЕНА',
                'focus_keyword': '',
                'word_count': 0,
                'status': 'error'
            }
        
        # Извлекаем и очищаем текст
        content = article_data['post_content']
        clean_text = self.clean_html(content)
        
        # Подсчитываем слова
        word_count = self.count_words(clean_text)
        
        return {
            'id': post_id,
            'title': article_data['post_title'],
            'focus_keyword': article_data['focus_keyword'],
            'word_count': word_count,
            'status': 'success'
        }
    
    def analyze_all_articles(self):
        """Анализ всех 50 статей"""
        print("📊 ПОДСЧЕТ КОЛИЧЕСТВА СЛОВ В 50 СТАТЬЯХ")
        print("=" * 80)
        
        for i, post_id in enumerate(ARTICLE_IDS, 1):
            print(f"\n📋 {i}/50 Анализ статьи ID {post_id}...", end=" ")
            
            result = self.analyze_article(post_id)
            self.word_counts.append(result)
            
            if result['status'] == 'success':
                print(f"✅ {result['word_count']} слов")
            else:
                print(f"❌ Ошибка")
        
        return self.word_counts
    
    def print_detailed_report(self):
        """Вывод детального отчета"""
        print("\n" + "=" * 80)
        print("📊 ДЕТАЛЬНЫЙ ОТЧЕТ ПО КОЛИЧЕСТВУ СЛОВ")
        print("=" * 80)
        
        # Сортируем по количеству слов (от большего к меньшему)
        sorted_articles = sorted(self.word_counts, key=lambda x: x['word_count'], reverse=True)
        
        total_words = 0
        min_words = float('inf')
        max_words = 0
        
        print(f"\n{'№':<4} {'ID':<6} {'Слов':<8} {'Ключевое слово / Название статьи'}")
        print("-" * 80)
        
        for i, article in enumerate(sorted_articles, 1):
            if article['status'] == 'success':
                total_words += article['word_count']
                min_words = min(min_words, article['word_count'])
                max_words = max(max_words, article['word_count'])
                
                # Определяем статус по количеству слов
                if article['word_count'] >= 2000:
                    status_icon = "🟢"  # Отлично
                elif article['word_count'] >= 1500:
                    status_icon = "🟡"  # Хорошо
                elif article['word_count'] >= 1000:
                    status_icon = "🟠"  # Средне
                else:
                    status_icon = "🔴"  # Мало
                
                keyword = article['focus_keyword'] if article['focus_keyword'] else article['title']
                print(f"{i:<4} {article['id']:<6} {article['word_count']:<8} {status_icon} {keyword}")
        
        # Статистика
        avg_words = total_words / len(self.word_counts) if self.word_counts else 0
        
        print("\n" + "=" * 80)
        print("📈 ОБЩАЯ СТАТИСТИКА")
        print("=" * 80)
        print(f"📚 Всего статей: {len(self.word_counts)}")
        print(f"📝 Общее количество слов: {total_words:,}")
        print(f"📊 Среднее количество слов: {avg_words:.0f}")
        print(f"📉 Минимум слов в статье: {min_words}")
        print(f"📈 Максимум слов в статье: {max_words}")
        
        # Распределение по категориям
        excellent = sum(1 for a in self.word_counts if a['word_count'] >= 2000)
        good = sum(1 for a in self.word_counts if 1500 <= a['word_count'] < 2000)
        medium = sum(1 for a in self.word_counts if 1000 <= a['word_count'] < 1500)
        low = sum(1 for a in self.word_counts if a['word_count'] < 1000)
        
        print(f"\n📊 РАСПРЕДЕЛЕНИЕ ПО КАТЕГОРИЯМ:")
        print(f"🟢 Отлично (≥2000 слов): {excellent} статей")
        print(f"🟡 Хорошо (1500-1999 слов): {good} статей")
        print(f"🟠 Средне (1000-1499 слов): {medium} статей")
        print(f"🔴 Мало (<1000 слов): {low} статей")
        
        return sorted_articles
    
    def save_report_to_file(self, sorted_articles):
        """Сохранение отчета в файл"""
        timestamp = datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
        report_filename = f"ОТЧЕТ_КОЛИЧЕСТВО_СЛОВ_{timestamp}.md"
        
        with open(report_filename, 'w', encoding='utf-8') as f:
            f.write("# 📊 ОТЧЕТ: КОЛИЧЕСТВО СЛОВ В 50 СТАТЬЯХ\n\n")
            f.write(f"**Дата:** {datetime.now().strftime('%d.%m.%Y %H:%M:%S')}\n\n")
            
            # Общая статистика
            total_words = sum(a['word_count'] for a in self.word_counts)
            avg_words = total_words / len(self.word_counts) if self.word_counts else 0
            min_words = min(a['word_count'] for a in self.word_counts)
            max_words = max(a['word_count'] for a in self.word_counts)
            
            f.write("## 📈 ОБЩАЯ СТАТИСТИКА\n\n")
            f.write(f"- **Всего статей:** {len(self.word_counts)}\n")
            f.write(f"- **Общее количество слов:** {total_words:,}\n")
            f.write(f"- **Среднее количество слов:** {avg_words:.0f}\n")
            f.write(f"- **Минимум слов:** {min_words}\n")
            f.write(f"- **Максимум слов:** {max_words}\n\n")
            
            # Распределение
            excellent = sum(1 for a in self.word_counts if a['word_count'] >= 2000)
            good = sum(1 for a in self.word_counts if 1500 <= a['word_count'] < 2000)
            medium = sum(1 for a in self.word_counts if 1000 <= a['word_count'] < 1500)
            low = sum(1 for a in self.word_counts if a['word_count'] < 1000)
            
            f.write("## 📊 РАСПРЕДЕЛЕНИЕ ПО КАТЕГОРИЯМ\n\n")
            f.write(f"- 🟢 **Отлично (≥2000 слов):** {excellent} статей ({excellent/len(self.word_counts)*100:.1f}%)\n")
            f.write(f"- 🟡 **Хорошо (1500-1999 слов):** {good} статей ({good/len(self.word_counts)*100:.1f}%)\n")
            f.write(f"- 🟠 **Средне (1000-1499 слов):** {medium} статей ({medium/len(self.word_counts)*100:.1f}%)\n")
            f.write(f"- 🔴 **Мало (<1000 слов):** {low} статей ({low/len(self.word_counts)*100:.1f}%)\n\n")
            
            # Детальный список
            f.write("## 📋 ДЕТАЛЬНЫЙ СПИСОК (сортировка по количеству слов)\n\n")
            f.write("| № | ID | Слов | Статус | Ключевое слово / Название |\n")
            f.write("|---|-------|------|--------|---------------------------|\n")
            
            for i, article in enumerate(sorted_articles, 1):
                if article['word_count'] >= 2000:
                    status = "🟢 Отлично"
                elif article['word_count'] >= 1500:
                    status = "🟡 Хорошо"
                elif article['word_count'] >= 1000:
                    status = "🟠 Средне"
                else:
                    status = "🔴 Мало"
                
                keyword = article['focus_keyword'] if article['focus_keyword'] else article['title']
                f.write(f"| {i} | {article['id']} | {article['word_count']} | {status} | {keyword} |\n")
            
            f.write(f"\n---\n\n*Отчет создан автоматически {datetime.now().strftime('%d.%m.%Y в %H:%M:%S')}*\n")
        
        return report_filename

def main():
    """Основная функция"""
    print("=" * 80)
    print("📊 АНАЛИЗ КОЛИЧЕСТВА СЛОВ В 50 СТАТЬЯХ")
    print("=" * 80)
    print("Метод: Подсчет слов в содержимом статей из базы данных WordPress")
    print("=" * 80)
    
    analyzer = WordCountAnalyzer()
    
    # Анализируем все статьи
    analyzer.analyze_all_articles()
    
    # Выводим детальный отчет
    sorted_articles = analyzer.print_detailed_report()
    
    # Сохраняем отчет в файл
    report_file = analyzer.save_report_to_file(sorted_articles)
    
    print(f"\n📄 Отчет сохранен в файл: {report_file}")
    print(f"\n✅ АНАЛИЗ ЗАВЕРШЕН!")

if __name__ == "__main__":
    main()
