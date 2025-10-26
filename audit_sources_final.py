#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import requests
from requests.auth import HTTPBasicAuth
import json
from datetime import datetime

# Настройки WordPress API
WP_API_URL = "https://ecopackpro.ru/wp-json/wp/v2"
WP_USERNAME = "rtep1976@me.com"
WP_APP_PASSWORD = "7EKI VWpH 96dg VI3H ovlI hI4E"

class SourcesAuditor:
    def __init__(self):
        self.auth = HTTPBasicAuth(WP_USERNAME, WP_APP_PASSWORD)
        self.headers = {'Content-Type': 'application/json'}
        self.results = []
    
    def get_post(self, post_id):
        """Получение поста через API"""
        url = f"{WP_API_URL}/posts/{post_id}"
        try:
            response = requests.get(url, auth=self.auth, headers=self.headers, timeout=15)
            if response.status_code == 200:
                return response.json()
            else:
                print(f"❌ Ошибка получения поста {post_id}: {response.status_code}")
                return None
        except Exception as e:
            print(f"❌ Ошибка получения поста {post_id}: {e}")
            return None
    
    def check_sources_in_article(self, post_id, article_title):
        """Проверка наличия источников в статье"""
        post = self.get_post(post_id)
        
        if not post:
            return {
                'id': post_id,
                'title': article_title,
                'status': 'error',
                'reason': 'Не удалось получить пост',
                'has_sources': False,
                'sources_count': 0
            }
        
        content = post['content']['rendered']
        
        # Проверяем наличие раздела источников
        has_sources = '📚 Источники' in content
        
        if has_sources:
            # Подсчитываем количество ссылок
            sources_count = content.count('<a href=')
            
            return {
                'id': post_id,
                'title': article_title,
                'status': 'success',
                'reason': 'Источники найдены',
                'has_sources': True,
                'sources_count': sources_count
            }
        else:
            return {
                'id': post_id,
                'title': article_title,
                'status': 'missing',
                'reason': 'Источники не найдены',
                'has_sources': False,
                'sources_count': 0
            }
    
    def audit_all_articles(self):
        """Аудит всех статей"""
        print("=" * 80)
        print("📊 ФИНАЛЬНЫЙ АУДИТ РАЗДЕЛА ИСТОЧНИКОВ")
        print("=" * 80)
        
        # Список всех статей
        articles = [
            (7907, "курьерские пакеты"),
            (7908, "почтовые коробки"),
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
        missing_count = 0
        error_count = 0
        
        for i, (post_id, title) in enumerate(articles, 1):
            print(f"[{i:2d}/50] Проверка статьи ID {post_id}")
            
            result = self.check_sources_in_article(post_id, title)
            self.results.append(result)
            
            if result['status'] == 'success':
                success_count += 1
                print(f"    ✅ Источники найдены ({result['sources_count']} ссылок)")
            elif result['status'] == 'missing':
                missing_count += 1
                print(f"    ❌ Источники НЕ найдены")
            else:
                error_count += 1
                print(f"    ⚠️  Ошибка: {result['reason']}")
        
        # Генерируем отчет
        self.generate_report(success_count, missing_count, error_count)
        
        return success_count, missing_count, error_count
    
    def generate_report(self, success_count, missing_count, error_count):
        """Генерация отчета"""
        print("\n" + "=" * 80)
        print("📊 ИТОГОВЫЙ ОТЧЕТ АУДИТА")
        print("=" * 80)
        print(f"✅ Статей с источниками: {success_count}")
        print(f"❌ Статей без источников: {missing_count}")
        print(f"⚠️  Статей с ошибками: {error_count}")
        print(f"📝 Всего проверено статей: {len(self.results)}")
        
        # Сохраняем детальный отчет
        report_filename = f"final_sources_audit_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
        
        with open(report_filename, 'w', encoding='utf-8') as f:
            json.dump({
                'timestamp': datetime.now().isoformat(),
                'summary': {
                    'total': len(self.results),
                    'with_sources': success_count,
                    'without_sources': missing_count,
                    'errors': error_count
                },
                'results': self.results
            }, f, ensure_ascii=False, indent=2)
        
        print(f"\n📄 Детальный отчет сохранен: {report_filename}")
        
        # Выводим статьи без источников
        if missing_count > 0:
            print("\n❌ СТАТЬИ БЕЗ ИСТОЧНИКОВ:")
            for result in self.results:
                if result['status'] == 'missing':
                    print(f"   ID {result['id']}: {result['title']}")
        
        # Выводим статьи с ошибками
        if error_count > 0:
            print("\n⚠️  СТАТЬИ С ОШИБКАМИ:")
            for result in self.results:
                if result['status'] == 'error':
                    print(f"   ID {result['id']}: {result['title']} - {result['reason']}")
        
        # Выводим успешные статьи
        if success_count > 0:
            print(f"\n✅ СТАТЬИ С ИСТОЧНИКАМИ ({success_count}):")
            for result in self.results:
                if result['status'] == 'success':
                    print(f"   ID {result['id']}: {result['title']} ({result['sources_count']} ссылок)")
        
        # Создаем markdown отчет
        self.create_markdown_report(success_count, missing_count, error_count)
    
    def create_markdown_report(self, success_count, missing_count, error_count):
        """Создание markdown отчета"""
        report_filename = f"ФИНАЛЬНЫЙ_ОТЧЕТ_АУДИТА_ИСТОЧНИКОВ_{datetime.now().strftime('%Y%m%d_%H%M%S')}.md"
        
        with open(report_filename, 'w', encoding='utf-8') as f:
            f.write(f"""# 📊 ФИНАЛЬНЫЙ ОТЧЕТ АУДИТА РАЗДЕЛА ИСТОЧНИКОВ

**Дата проведения:** {datetime.now().strftime('%d.%m.%Y %H:%M:%S')}

## 📈 СВОДНАЯ СТАТИСТИКА

| Категория | Количество | Процент |
|-----------|------------|---------|
| ✅ Статей с источниками | {success_count} | {(success_count/len(self.results)*100):.1f}% |
| ❌ Статей без источников | {missing_count} | {(missing_count/len(self.results)*100):.1f}% |
| ⚠️ Статей с ошибками | {error_count} | {(error_count/len(self.results)*100):.1f}% |
| 📝 **Всего статей** | **{len(self.results)}** | **100%** |

## 🎯 РЕЗУЛЬТАТ

""")
            
            if success_count == len(self.results):
                f.write("**🎉 ВСЕ СТАТЬИ УСПЕШНО СОДЕРЖАТ РАЗДЕЛ ИСТОЧНИКОВ!**\n\n")
            elif success_count > 0:
                f.write(f"**✅ {success_count} из {len(self.results)} статей содержат раздел источников**\n\n")
            
            # Детальные результаты
            f.write("## 📋 ДЕТАЛЬНЫЕ РЕЗУЛЬТАТЫ\n\n")
            
            # Статьи с источниками
            if success_count > 0:
                f.write("### ✅ Статьи с источниками\n\n")
                for result in self.results:
                    if result['status'] == 'success':
                        f.write(f"- **ID {result['id']}:** {result['title']} ({result['sources_count']} ссылок)\n")
                f.write("\n")
            
            # Статьи без источников
            if missing_count > 0:
                f.write("### ❌ Статьи без источников\n\n")
                for result in self.results:
                    if result['status'] == 'missing':
                        f.write(f"- **ID {result['id']}:** {result['title']}\n")
                f.write("\n")
            
            # Статьи с ошибками
            if error_count > 0:
                f.write("### ⚠️ Статьи с ошибками\n\n")
                for result in self.results:
                    if result['status'] == 'error':
                        f.write(f"- **ID {result['id']}:** {result['title']} - {result['reason']}\n")
                f.write("\n")
            
            f.write(f"""## 📊 ЗАКЛЮЧЕНИЕ

Аудит разделов источников завершен. Все статьи проверены на наличие раздела "📚 Источники" с кликабельными ссылками на внешние ресурсы.

**Общий результат:** {success_count}/{len(self.results)} статей содержат раздел источников.

---
*Отчет сгенерирован автоматически {datetime.now().strftime('%d.%m.%Y в %H:%M:%S')}*
""")
        
        print(f"📄 Markdown отчет сохранен: {report_filename}")

def main():
    """Основная функция"""
    auditor = SourcesAuditor()
    success, missing, error = auditor.audit_all_articles()
    
    if success == 50:
        print("\n🎉 ИДЕАЛЬНЫЙ РЕЗУЛЬТАТ! Все 50 статей содержат раздел источников!")
    elif success > 0:
        print(f"\n✅ АУДИТ ЗАВЕРШЕН! {success} из 50 статей содержат раздел источников")
    else:
        print("\n❌ НИ ОДНА СТАТЬЯ НЕ СОДЕРЖИТ РАЗДЕЛ ИСТОЧНИКОВ")

if __name__ == "__main__":
    main()
