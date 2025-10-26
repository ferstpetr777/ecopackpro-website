#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import subprocess
import json
import requests
from datetime import datetime

# Данные для подключения к базе данных из wp-config.php
DB_NAME = 'm1shqamai2_worp6'
DB_USER = 'm1shqamai2_worp6'
DB_PASSWORD = '9nUQkM*Q2cnvy379'
DB_HOST = 'localhost'

def run_mysql_command(sql_command):
    """Выполняет SQL команду через mysql CLI"""
    try:
        cmd = f"mysql -u {DB_USER} -p'{DB_PASSWORD}' {DB_NAME} -e \"{sql_command}\""
        result = subprocess.run(cmd, shell=True, capture_output=True, text=True, cwd='/var/www/fastuser/data/www/ecopackpro.ru')
        return result.returncode == 0, result.stdout, result.stderr
    except Exception as e:
        return False, "", str(e)

def restore_articles_from_backup():
    """Восстанавливает статьи из SQL бэкапа"""
    print("🔄 Восстановление статей из SQL бэкапа...")
    
    # Сначала создаем резервную копию текущих статей
    backup_cmd = f"mysqldump -u {DB_USER} -p'{DB_PASSWORD}' {DB_NAME} wp_posts --where=\"post_type='post' AND post_status='publish'\" > current_articles_backup_{datetime.now().strftime('%Y%m%d_%H%M%S')}.sql"
    
    result = subprocess.run(backup_cmd, shell=True, capture_output=True, text=True, cwd='/var/www/fastuser/data/www/ecopackpro.ru')
    success = result.returncode == 0
    stdout = result.stdout
    stderr = result.stderr
    
    if not success:
        print(f"⚠️ Предупреждение: не удалось создать резервную копию текущих статей: {stderr}")
    
    # Восстанавливаем статьи из бэкапа
    restore_cmd = f"mysql -u {DB_USER} -p'{DB_PASSWORD}' {DB_NAME} < backup_published_articles_final_20251012_084242.sql"
    
    result = subprocess.run(restore_cmd, shell=True, capture_output=True, text=True, cwd='/var/www/fastuser/data/www/ecopackpro.ru')
    success = result.returncode == 0
    stdout = result.stdout
    stderr = result.stderr
    
    if success:
        print("✅ Статьи восстановлены из SQL бэкапа")
        return True
    else:
        print(f"❌ Ошибка восстановления из SQL бэкапа: {stderr}")
        return False

def update_article_permalinks():
    """Обновляет permalink'и статей"""
    print("🔄 Обновление permalink'ов статей...")
    
    # Обновляем permalink'и для всех статей с ID 7907-7956
    for article_id in range(7907, 7957):
        update_sql = f"""
        UPDATE wp_posts 
        SET post_status = 'publish',
            post_modified = NOW(),
            post_modified_gmt = UTC_TIMESTAMP()
        WHERE ID = {article_id} AND post_type = 'post';
        """
        
        success, stdout, stderr = run_mysql_command(update_sql)
        
        if success:
            print(f"✅ Статья {article_id} обновлена")
        else:
            print(f"⚠️ Предупреждение для статьи {article_id}: {stderr}")

def flush_rewrite_rules():
    """Очищает правила перезаписи URL"""
    print("🔄 Обновление правил перезаписи URL...")
    
    flush_cmd = "wp rewrite flush --allow-root"
    result = subprocess.run(flush_cmd, shell=True, capture_output=True, text=True, cwd='/var/www/fastuser/data/www/ecopackpro.ru')
    success = result.returncode == 0
    stdout = result.stdout
    stderr = result.stderr
    
    if success:
        print("✅ Правила перезаписи URL обновлены")
    else:
        print(f"❌ Ошибка обновления правил: {stderr}")

def check_articles_status():
    """Проверяет статус всех 50 статей"""
    print("🔍 Проверка статуса статей...")
    
    articles = [
        {"id": 7907, "title": "Курьерские пакеты: полный гид по выбору и применению", "url": "https://ecopackpro.ru/kurierskie-pakety/"},
        {"id": 7908, "title": "Почтовые коробки: размеры, виды, цены и где купить", "url": "https://ecopackpro.ru/pochtovye-korobki/"},
        {"id": 7909, "title": "Зип пакеты", "url": "https://ecopackpro.ru/zip-pakety/"},
        {"id": 7910, "title": "Zip lock пакеты с бегунком: удобное хранение продуктов", "url": "https://ecopackpro.ru/zip-lock-pakety-s-begunkom/"},
        {"id": 7911, "title": "Конверты с воздушной подушкой для хрупких товаров", "url": "https://ecopackpro.ru/konverty-s-vozdushnoy-podushkoy/"},
        {"id": 7912, "title": "Конверты с воздушной прослойкой для документов", "url": "https://ecopackpro.ru/konverty-s-vozdushnoy-prosloykoy/"},
        {"id": 7913, "title": "Крафтовые пакеты с воздушной подушкой для бизнеса: как выбрать оптимал", "url": "https://ecopackpro.ru/kraftovye-pakety-s-vozdushnoy-podushkoy/"},
        {"id": 7914, "title": "Курьерские пакеты прозрачные", "url": "https://ecopackpro.ru/kurerskie-pakety-prozrachnye/"},
        {"id": 7915, "title": "Курьерские пакеты номерные", "url": "https://ecopackpro.ru/kurerskie-pakety-nomernye/"},
        {"id": 7916, "title": "Курьерские пакеты черно-белые", "url": "https://ecopackpro.ru/kurerskie-pakety-cherno-belye/"},
        {"id": 7917, "title": "Курьерские пакеты с карманом", "url": "https://ecopackpro.ru/kurerskie-pakety-s-karmanom/"},
        {"id": 7918, "title": "Zip lock пакеты матовые", "url": "https://ecopackpro.ru/zip-lock-pakety-matovye/"},
        {"id": 7919, "title": "Zip lock пакеты оптом", "url": "https://ecopackpro.ru/zip-lock-pakety-optom/"},
        {"id": 7920, "title": "Крафтовые конверты", "url": "https://ecopackpro.ru/kraftovye-konverty/"},
        {"id": 7921, "title": "Пузырчатые пакеты ВПП", "url": "https://ecopackpro.ru/puzyrchatye-pakety-vpp/"},
        {"id": 7922, "title": "Коробки для почты", "url": "https://ecopackpro.ru/korobki-dlya-pochty/"},
        {"id": 7923, "title": "Коробки для отправки", "url": "https://ecopackpro.ru/korobki-dlya-otpravki/"},
        {"id": 7924, "title": "Самоклеящиеся карманы", "url": "https://ecopackpro.ru/samokleyaschiesya-karmany/"},
        {"id": 7925, "title": "Антимагнитная пломба", "url": "https://ecopackpro.ru/antimagnitnaya-plomba/"},
        {"id": 7926, "title": "Наклейка пломба антимагнит", "url": "https://ecopackpro.ru/nakleyka-plomba-antimagnit/"},
        {"id": 7927, "title": "Пломбиратор для бочек", "url": "https://ecopackpro.ru/plombirator-dlya-bochek/"},
        {"id": 7928, "title": "Номерные пломбы наклейки", "url": "https://ecopackpro.ru/nomernye-plomby-nakleyki/"},
        {"id": 7929, "title": "Zip lock пакеты с белой полосой", "url": "https://ecopackpro.ru/zip-lock-pakety-s-beloy-polosoy/"},
        {"id": 7930, "title": "Белые крафт пакеты с пузырчатой плёнкой", "url": "https://ecopackpro.ru/belye-kraft-pakety-s-puzyrchatoy-plyonkoy/"},
        {"id": 7931, "title": "Прозрачные zip lock пакеты", "url": "https://ecopackpro.ru/%d0%bf%d1%80%d0%be%d0%b7%d1%80%d0%b0%d1%87%d0%bd%d1%8b%d0%b5-zip-lock-%d0%bf%d0%b0%d0%ba%d0%b5%d1%82%d1%8b/"},
        {"id": 7932, "title": "Купить курьерские пакеты с номерным штрих-кодом", "url": "https://ecopackpro.ru/kupit-kurerskie-pakety-s-nomernym-shtrih-kodom/"},
        {"id": 7933, "title": "Заказать прозрачные курьерские пакеты оптом", "url": "https://ecopackpro.ru/zakazat-prozrachnye-kurerskie-pakety-optom/"},
        {"id": 7934, "title": "Курьерские пакеты черно-белые с карманом цена", "url": "https://ecopackpro.ru/kurerskie-pakety-cherno-belye-s-karmanom-tsena/"},
        {"id": 7935, "title": "Матовые zip lock пакеты с бегунком 10×15", "url": "https://ecopackpro.ru/matovye-zip-lock-pakety-s-begunkom-1015/"},
        {"id": 7936, "title": "Купить оптом zip lock пакеты матовые 30 мкм", "url": "https://ecopackpro.ru/kupit-optom-zip-lock-pakety-matovye-30-mkm/"},
        {"id": 7937, "title": "Крафт конверты с воздушной подушкой F/3", "url": "https://ecopackpro.ru/kraft-konverty-s-vozdushnoy-podushkoy-f3/"},
        {"id": 7938, "title": "Почтовые коробки размера S 260×170×80", "url": "https://ecopackpro.ru/pochtovye-korobki-razmera-s-26017080/"},
        {"id": 7939, "title": "Почтовые коробки размера XL 530×360×220", "url": "https://ecopackpro.ru/pochtovye-korobki-razmera-xl-530360220/"},
        {"id": 7940, "title": "Купить самоклеящиеся карманы SD для документов", "url": "https://ecopackpro.ru/kupit-samokleyaschiesya-karmany-sd-dlya-dokumentov/"},
        {"id": 7941, "title": "Антимагнитные наклейки для водяных счётчиков", "url": "https://ecopackpro.ru/antimagnitnye-nakleyki-dlya-vodyanyh-schyotchikov/"},
        {"id": 7942, "title": "Антимагнитная пломба цена за 100 штук", "url": "https://ecopackpro.ru/antimagnitnaya-plomba-tsena-za-100-shtuk/"},
        {"id": 7943, "title": "Пломбиратор для евробочек 2 дюйма", "url": "https://ecopackpro.ru/plombirator-dlya-evrobochek-2-dyuyma/"},
        {"id": 7944, "title": "Инструмент для опломбирования бочек ¾ дюйма", "url": "https://ecopackpro.ru/instrument-dlya-oplombirovaniya-bochek-dyuyma/"},
        {"id": 7945, "title": "Курьерские пакеты черно-белые без логотипа А4", "url": "https://ecopackpro.ru/kurerskie-pakety-cherno-belye-bez-logotipa-a4/"},
        {"id": 7946, "title": "Курьерские пакеты прозрачные для одежды", "url": "https://ecopackpro.ru/kurerskie-pakety-prozrachnye-dlya-odezhdy/"},
        {"id": 7947, "title": "Курьерские пакеты для маркетплейсов Ozon", "url": "https://ecopackpro.ru/kurerskie-pakety-dlya-marketpleysov-ozon/"},
        {"id": 7948, "title": "Почтовые коробки с логотипом на заказ", "url": "https://ecopackpro.ru/pochtovye-korobki-s-logotipom-na-zakaz/"},
        {"id": 7949, "title": "Зип пакеты с бегунком купить Москва", "url": "https://ecopackpro.ru/zip-pakety-s-begunkom-kupit-moskva/"},
        {"id": 7950, "title": "Матовые zip lock пакеты для чая", "url": "https://ecopackpro.ru/matovye-zip-lock-pakety-dlya-chaya/"},
        {"id": 7951, "title": "Zip lock пакеты с подвесом", "url": "https://ecopackpro.ru/zip-lock-pakety-s-podvesom/"},
        {"id": 7952, "title": "Белые крафт-пакеты с пузырчатой плёнкой оптом", "url": "https://ecopackpro.ru/belye-kraft-pakety-s-puzyrchatoy-plyonkoy-optom/"},
        {"id": 7953, "title": "Плоские конверты с воздушной подушкой для документов", "url": "https://ecopackpro.ru/ploskie-konverty-s-vozdushnoy-podushkoy-dlya-dokumentov/"},
        {"id": 7954, "title": "Пакеты из воздушно-пузырьковой плёнки оптом", "url": "https://ecopackpro.ru/pakety-iz-vozdushno-puzyrkovoy-plyonki-optom/"},
        {"id": 7955, "title": "Антимагнитные пломбы для газовых счётчиков", "url": "https://ecopackpro.ru/antimagnitnye-plomby-dlya-gazovyh-schyotchikov/"},
        {"id": 7956, "title": "Самоклеящиеся карманы для транспортных накладных", "url": "https://ecopackpro.ru/samokleyaschiesya-karmany-dlya-transportnyh-nakladnyh/"}
    ]
    
    success_count = 0
    error_count = 0
    results = []
    
    for article in articles:
        try:
            response = requests.get(article["url"], timeout=10, allow_redirects=True)
            status = response.status_code
            status_icon = "✅" if status == 200 else "❌"
            
            if status == 200:
                success_count += 1
            else:
                error_count += 1
                
            result = {
                "id": article["id"],
                "title": article["title"],
                "url": article["url"],
                "status": status,
                "timestamp": datetime.now().isoformat()
            }
            results.append(result)
            
            print(f"{status_icon} ID {article['id']:4d} | {status:3d} | {article['title'][:50]}...")
            
        except Exception as e:
            error_count += 1
            print(f"❌ ID {article['id']:4d} | ERR | {article['title'][:50]}... - {str(e)}")
    
    print("=" * 80)
    print(f"📊 ИТОГИ ПРОВЕРКИ:")
    print(f"✅ Успешно (200): {success_count}")
    print(f"❌ Ошибки: {error_count}")
    print(f"📈 Процент успеха: {(success_count/50)*100:.1f}%")
    
    # Сохраняем результаты
    report_file = f"final_articles_restoration_report_{datetime.now().strftime('%Y%m%d_%H%M%S')}.json"
    with open(report_file, 'w', encoding='utf-8') as f:
        json.dump(results, f, ensure_ascii=False, indent=2)
    
    print(f"📄 Отчёт сохранён: {report_file}")
    
    return success_count == 50

def main():
    print("🚀 ВОССТАНОВЛЕНИЕ 50 СТАТЕЙ ИЗ АРХИВА ECOPACKPRO")
    print("=" * 80)
    
    # Шаг 1: Восстановление из SQL бэкапа
    if not restore_articles_from_backup():
        print("❌ Не удалось восстановить статьи из SQL бэкапа")
        return False
    
    # Шаг 2: Обновление permalink'ов
    update_article_permalinks()
    
    # Шаг 3: Обновление правил перезаписи
    flush_rewrite_rules()
    
    # Шаг 4: Проверка статуса
    print("\n" + "=" * 80)
    print("🔍 ФИНАЛЬНАЯ ПРОВЕРКА СТАТУСА СТАТЕЙ")
    print("=" * 80)
    
    success = check_articles_status()
    
    if success:
        print("\n🎉 ВСЕ 50 СТАТЕЙ УСПЕШНО ВОССТАНОВЛЕНЫ И ДОСТУПНЫ!")
    else:
        print("\n⚠️ Некоторые статьи требуют дополнительного внимания")
    
    return success

if __name__ == "__main__":
    main()
