#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import subprocess
import json
from datetime import datetime

# Список всех 50 статей для восстановления
articles_data = [
    {"id": 7907, "title": "Курьерские пакеты: полный гид по выбору и применению", "slug": "kurierskie-pakety"},
    {"id": 7908, "title": "Почтовые коробки: размеры, виды, цены и где купить", "slug": "pochtovye-korobki"},
    {"id": 7909, "title": "Зип пакеты", "slug": "zip-pakety"},
    {"id": 7910, "title": "Zip lock пакеты с бегунком: удобное хранение продуктов", "slug": "zip-lock-pakety-s-begunkom"},
    {"id": 7911, "title": "Конверты с воздушной подушкой для хрупких товаров", "slug": "konverty-s-vozdushnoy-podushkoy"},
    {"id": 7912, "title": "Конверты с воздушной прослойкой для документов", "slug": "konverty-s-vozdushnoy-prosloykoy"},
    {"id": 7913, "title": "Крафтовые пакеты с воздушной подушкой для бизнеса: как выбрать оптимал", "slug": "kraftovye-pakety-s-vozdushnoy-podushkoy"},
    {"id": 7914, "title": "Курьерские пакеты прозрачные", "slug": "kurerskie-pakety-prozrachnye"},
    {"id": 7915, "title": "Курьерские пакеты номерные", "slug": "kurerskie-pakety-nomernye"},
    {"id": 7916, "title": "Курьерские пакеты черно-белые", "slug": "kurerskie-pakety-cherno-belye"},
    {"id": 7917, "title": "Курьерские пакеты с карманом", "slug": "kurerskie-pakety-s-karmanom"},
    {"id": 7918, "title": "Zip lock пакеты матовые", "slug": "zip-lock-pakety-matovye"},
    {"id": 7919, "title": "Zip lock пакеты оптом", "slug": "zip-lock-pakety-optom"},
    {"id": 7920, "title": "Крафтовые конверты", "slug": "kraftovye-konverty"},
    {"id": 7921, "title": "Пузырчатые пакеты ВПП", "slug": "puzyrchatye-pakety-vpp"},
    {"id": 7922, "title": "Коробки для почты", "slug": "korobki-dlya-pochty"},
    {"id": 7923, "title": "Коробки для отправки", "slug": "korobki-dlya-otpravki"},
    {"id": 7924, "title": "Самоклеящиеся карманы", "slug": "samokleyaschiesya-karmany"},
    {"id": 7925, "title": "Антимагнитная пломба", "slug": "antimagnitnaya-plomba"},
    {"id": 7926, "title": "Наклейка пломба антимагнит", "slug": "nakleyka-plomba-antimagnit"},
    {"id": 7927, "title": "Пломбиратор для бочек", "slug": "plombirator-dlya-bochek"},
    {"id": 7928, "title": "Номерные пломбы наклейки", "slug": "nomernye-plomby-nakleyki"},
    {"id": 7929, "title": "Zip lock пакеты с белой полосой", "slug": "zip-lock-pakety-s-beloy-polosoy"},
    {"id": 7930, "title": "Белые крафт пакеты с пузырчатой плёнкой", "slug": "belye-kraft-pakety-s-puzyrchatoy-plyonkoy"},
    {"id": 7931, "title": "Прозрачные zip lock пакеты", "slug": "prozrachnye-zip-lock-pakety"},
    {"id": 7932, "title": "Купить курьерские пакеты с номерным штрих-кодом", "slug": "kupit-kurerskie-pakety-s-nomernym-shtrih-kodom"},
    {"id": 7933, "title": "Заказать прозрачные курьерские пакеты оптом", "slug": "zakazat-prozrachnye-kurerskie-pakety-optom"},
    {"id": 7933, "title": "Курьерские пакеты черно-белые с карманом цена", "slug": "kurerskie-pakety-cherno-belye-s-karmanom-tsena"},
    {"id": 7935, "title": "Матовые zip lock пакеты с бегунком 10×15", "slug": "matovye-zip-lock-pakety-s-begunkom-1015"},
    {"id": 7936, "title": "Купить оптом zip lock пакеты матовые 30 мкм", "slug": "kupit-optom-zip-lock-pakety-matovye-30-mkm"},
    {"id": 7937, "title": "Крафт конверты с воздушной подушкой F/3", "slug": "kraft-konverty-s-vozdushnoy-podushkoy-f3"},
    {"id": 7938, "title": "Почтовые коробки размера S 260×170×80", "slug": "pochtovye-korobki-razmera-s-26017080"},
    {"id": 7939, "title": "Почтовые коробки размера XL 530×360×220", "slug": "pochtovye-korobki-razmera-xl-530360220"},
    {"id": 7940, "title": "Купить самоклеящиеся карманы SD для документов", "slug": "kupit-samokleyaschiesya-karmany-sd-dlya-dokumentov"},
    {"id": 7941, "title": "Антимагнитные наклейки для водяных счётчиков", "slug": "antimagnitnye-nakleyki-dlya-vodyanyh-schyotchikov"},
    {"id": 7942, "title": "Антимагнитная пломба цена за 100 штук", "slug": "antimagnitnaya-plomba-tsena-za-100-shtuk"},
    {"id": 7943, "title": "Пломбиратор для евробочек 2 дюйма", "slug": "plombirator-dlya-evrobochek-2-dyuyma"},
    {"id": 7944, "title": "Инструмент для опломбирования бочек ¾ дюйма", "slug": "instrument-dlya-oplombirovaniya-bochek-dyuyma"},
    {"id": 7945, "title": "Курьерские пакеты черно-белые без логотипа А4", "slug": "kurerskie-pakety-cherno-belye-bez-logotipa-a4"},
    {"id": 7946, "title": "Курьерские пакеты прозрачные для одежды", "slug": "kurerskie-pakety-prozrachnye-dlya-odezhdy"},
    {"id": 7947, "title": "Курьерские пакеты для маркетплейсов Ozon", "slug": "kurerskie-pakety-dlya-marketpleysov-ozon"},
    {"id": 7948, "title": "Почтовые коробки с логотипом на заказ", "slug": "pochtovye-korobki-s-logotipom-na-zakaz"},
    {"id": 7949, "title": "Зип пакеты с бегунком купить Москва", "slug": "zip-pakety-s-begunkom-kupit-moskva"},
    {"id": 7950, "title": "Матовые zip lock пакеты для чая", "slug": "matovye-zip-lock-pakety-dlya-chaya"},
    {"id": 7951, "title": "Zip lock пакеты с подвесом", "slug": "zip-lock-pakety-s-podvesom"},
    {"id": 7952, "title": "Белые крафт-пакеты с пузырчатой плёнкой оптом", "slug": "belye-kraft-pakety-s-puzyrchatoy-plyonkoy-optom"},
    {"id": 7953, "title": "Плоские конверты с воздушной подушкой для документов", "slug": "ploskie-konverty-s-vozdushnoy-podushkoy-dlya-dokumentov"},
    {"id": 7954, "title": "Пакеты из воздушно-пузырьковой плёнки оптом", "slug": "pakety-iz-vozdushno-puzyrkovoy-plyonki-optom"},
    {"id": 7955, "title": "Антимагнитные пломбы для газовых счётчиков", "slug": "antimagnitnye-plomby-dlya-gazovyh-schyotchikov"},
    {"id": 7956, "title": "Самоклеящиеся карманы для транспортных накладных", "slug": "samokleyaschiesya-karmany-dlya-transportnyh-nakladnyh"}
]

def run_wp_command(command):
    """Выполняет команду WordPress CLI"""
    try:
        result = subprocess.run(command, shell=True, capture_output=True, text=True, cwd='/var/www/fastuser/data/www/ecopackpro.ru')
        return result.returncode == 0, result.stdout, result.stderr
    except Exception as e:
        return False, "", str(e)

def restore_article(article_id, title, slug):
    """Восстанавливает статью"""
    # Сначала проверим, существует ли статья
    success, stdout, stderr = run_wp_command(f"wp post get {article_id} --field=post_status --allow-root")
    
    if success and stdout.strip() == "publish":
        print(f"✅ Статья {article_id} уже опубликована")
        return True
    
    # Восстанавливаем статью
    success, stdout, stderr = run_wp_command(f"wp post update {article_id} --post_status=publish --post_title='{title}' --post_name='{slug}' --allow-root")
    
    if success:
        print(f"✅ Статья {article_id} восстановлена: {title[:50]}...")
        return True
    else:
        print(f"❌ Ошибка восстановления статьи {article_id}: {stderr}")
        return False

def main():
    print("🔄 Восстановление 50 статей EcopackPro...")
    print("=" * 80)
    
    restored_count = 0
    failed_count = 0
    results = []
    
    for article in articles_data:
        article_id = article["id"]
        title = article["title"]
        slug = article["slug"]
        
        success = restore_article(article_id, title, slug)
        
        if success:
            restored_count += 1
        else:
            failed_count += 1
            
        results.append({
            "id": article_id,
            "title": title,
            "slug": slug,
            "restored": success,
            "timestamp": datetime.now().isoformat()
        })
    
    print("=" * 80)
    print(f"📊 ИТОГИ ВОССТАНОВЛЕНИЯ:")
    print(f"✅ Восстановлено: {restored_count}")
    print(f"❌ Ошибки: {failed_count}")
    print(f"📈 Процент успеха: {(restored_count/50)*100:.1f}%")
    
    # Сохраняем результаты
    report_file = f"restore_articles_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
    with open(report_file, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)
    
    print(f"📄 Отчёт сохранён: {report_file}")
    
    return results

if __name__ == "__main__":
    main()



