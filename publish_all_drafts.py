#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
from requests.auth import HTTPBasicAuth
import json
import time
from datetime import datetime

# Настройки WordPress API
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"

class DraftPublisher:
    def __init__(self):
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
        self.results = []
        self.start_time = datetime.now()
    
    def publish_article(self, post_id, article_title):
        """Публикация одной статьи"""
        url = f"{WP_API_URL}/posts/{post_id}"
        
        data = {
            'status': 'publish'
        }
        
        try:
            response = requests.post(url, auth=self.auth, headers=self.headers, json=data, timeout=30)
            
            if response.status_code == 200:
                post = response.json()
                return {
                    'id': post_id,
                    'title': article_title,
                    'status': 'success',
                    'url': post['link'],
                    'slug': post['slug']
                }
            else:
                return {
                    'id': post_id,
                    'title': article_title,
                    'status': 'failed',
                    'error': f"HTTP {response.status_code}: {response.text[:100]}"
                }
                
        except Exception as e:
            return {
                'id': post_id,
                'title': article_title,
                'status': 'error',
                'error': str(e)[:100]
            }
    
    def publish_all_drafts(self):
        """Публикация всех статей-черновиков"""
        print("=" * 80)
        print("📰 ПУБЛИКАЦИЯ ВСЕХ СТАТЕЙ-ЧЕРНОВИКОВ")
        print("=" * 80)
        
        # Список всех статей из базы данных (исключаем старую статью 7152)
        articles = [
            (7907, "Курьерские пакеты: полный гид по выбору и применению"),
            (7908, "Почтовые коробки: размеры, виды, цены и где купить"),
            (7909, "Зип пакеты"),
            (7910, "Zip lock пакеты с бегунком: удобное хранение продуктов"),
            (7911, "Конверты с воздушной подушкой для хрупких товаров"),
            (7912, "Конверты с воздушной прослойкой для документов"),
            (7913, "Крафтовые пакеты с воздушной подушкой для бизнеса: как выбрать оптимал"),
            (7914, "Курьерские пакеты прозрачные"),
            (7915, "Курьерские пакеты номерные"),
            (7916, "Курьерские пакеты черно-белые"),
            (7917, "Курьерские пакеты с карманом"),
            (7918, "Zip lock пакеты матовые"),
            (7919, "Zip lock пакеты оптом"),
            (7920, "Крафтовые конверты"),
            (7921, "Пузырчатые пакеты ВПП"),
            (7922, "Коробки для почты"),
            (7923, "Коробки для отправки"),
            (7924, "Самоклеящиеся карманы"),
            (7925, "Антимагнитная пломба"),
            (7926, "Наклейка пломба антимагнит"),
            (7927, "Пломбиратор для бочек"),
            (7928, "Номерные пломбы наклейки"),
            (7929, "Zip lock пакеты с белой полосой"),
            (7930, "Белые крафт пакеты с пузырчатой плёнкой"),
            (7931, "Прозрачные zip lock пакеты"),
            (7932, "Купить курьерские пакеты с номерным штрих-кодом"),
            (7933, "Заказать прозрачные курьерские пакеты оптом"),
            (7934, "Курьерские пакеты черно-белые с карманом цена"),
            (7935, "Матовые zip lock пакеты с бегунком 10×15"),
            (7936, "Купить оптом zip lock пакеты матовые 30 мкм"),
            (7937, "Крафт конверты с воздушной подушкой F/3"),
            (7938, "Почтовые коробки размера S 260×170×80"),
            (7939, "Почтовые коробки размера XL 530×360×220"),
            (7940, "Купить самоклеящиеся карманы SD для документов"),
            (7941, "Антимагнитные наклейки для водяных счётчиков"),
            (7942, "Антимагнитная пломба цена за 100 штук"),
            (7943, "Пломбиратор для евробочек 2 дюйма"),
            (7944, "Инструмент для опломбирования бочек ¾ дюйма"),
            (7945, "Курьерские пакеты черно-белые без логотипа А4"),
            (7946, "Курьерские пакеты прозрачные для одежды"),
            (7947, "Курьерские пакеты для маркетплейсов Ozon"),
            (7948, "Почтовые коробки с логотипом на заказ"),
            (7949, "Зип пакеты с бегунком купить Москва"),
            (7950, "Матовые zip lock пакеты для чая"),
            (7951, "Zip lock пакеты с подвесом"),
            (7952, "Белые крафт-пакеты с пузырчатой плёнкой оптом"),
            (7953, "Плоские конверты с воздушной подушкой для документов"),
            (7954, "Пакеты из воздушно-пузырьковой плёнки оптом"),
            (7955, "Антимагнитные пломбы для газовых счётчиков"),
            (7956, "Самоклеящиеся карманы для транспортных накладных")
        ]
        
        success_count = 0
        failed_count = 0
        
        for i, (post_id, title) in enumerate(articles, 1):
            print(f"[{i:2d}/50] Публикация статьи ID {post_id}")
            print(f"    📝 {title}")
            
            result = self.publish_article(post_id, title)
            self.results.append(result)
            
            if result['status'] == 'success':
                print(f"    ✅ Опубликована: {result['url']}")
                success_count += 1
            else:
                print(f"    ❌ Ошибка: {result['error']}")
                failed_count += 1
            
            # Пауза между запросами
            time.sleep(1)
        
        # Генерируем отчет
        self.generate_report(success_count, failed_count)
        
        return success_count, failed_count
    
    def generate_report(self, success_count, failed_count):
        """Генерация отчета"""
        end_time = datetime.now()
        duration = end_time - self.start_time
        
        print("\n" + "=" * 80)
        print("📊 ИТОГОВЫЙ ОТЧЕТ ПУБЛИКАЦИИ")
        print("=" * 80)
        print(f"⏱️  Время выполнения: {duration}")
        print(f"✅ Успешно опубликовано: {success_count}")
        print(f"❌ Ошибок: {failed_count}")
        print(f"📝 Всего статей: {len(self.results)}")
        
        # Сохраняем детальный отчет
        report_filename = f"publish_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        with open(report_filename, 'w', encoding='utf-8') as f:
            json.dump({
                'timestamp': datetime.now().isoformat(),
                'duration': str(duration),
                'summary': {
                    'total': len(self.results),
                    'success': success_count,
                    'failed': failed_count
                },
                'results': self.results
            }, f, ensure_ascii=False, indent=2)
        
        print(f"\n📄 Детальный отчет сохранен: {report_filename}")
        
        # Выводим успешные статьи
        if success_count > 0:
            print(f"\n✅ ОПУБЛИКОВАННЫЕ СТАТЬИ ({success_count}):")
            print("=" * 80)
            for result in self.results:
                if result['status'] == 'success':
                    print(f"{result['id']:4d}. {result['title']}")
                    print(f"     🔗 {result['url']}")
                    print()
        
        # Выводим проблемные статьи
        if failed_count > 0:
            print(f"\n❌ СТАТЬИ С ОШИБКАМИ ({failed_count}):")
            for result in self.results:
                if result['status'] != 'success':
                    print(f"   ID {result['id']}: {result['title']} - {result['error']}")

def main():
    """Основная функция"""
    publisher = DraftPublisher()
    success, failed = publisher.publish_all_drafts()
    
    if success > 0:
        print(f"\n🎉 ПУБЛИКАЦИЯ ЗАВЕРШЕНА! Опубликовано {success} статей")
    else:
        print("\n❌ НЕ УДАЛОСЬ ОПУБЛИКОВАТЬ НИ ОДНУ СТАТЬЮ")

if __name__ == "__main__":
    main()
