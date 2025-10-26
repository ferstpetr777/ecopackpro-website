# 📊 ФИНАЛЬНАЯ РЕВИЗИЯ ОПУБЛИКОВАННЫХ СТАТЕЙ НА ECOPACKPRO.RU

## 📅 Дата и время: 2025-10-12 08:44:11

---

## ✅ РЕЗЮМЕ

- **Всего статей опубликовано:** 49
- **Связано с исходниками:** 43 статьи (87.8%)
- **Без связи с исходниками:** 6 статей (12.2%)
- **Резервные копии созданы:** ✅
- **Данные сохранены в БД проекта:** ✅

---

## 📁 СОЗДАННЫЕ ФАЙЛЫ

### Резервные копии WordPress:
1. `backup_published_articles_final_20251012_084242.sql` (2.3 MB) - таблица wp_posts
2. `backup_published_articles_meta_final_20251012_084305.sql` (61 KB) - таблица wp_postmeta

### Отчеты:
1. `published_articles_report_20251012_084411.json` - JSON отчет со всеми данными
2. `published_articles_urls_20251012_084411.txt` - Список URL всех статей

### База данных проекта:
- **Путь:** `/root/seo_project/SEO_ecopackpro/articles.db`
- **Таблица:** `published_articles`
- **Записей:** 49

---

## 🔗 СТРУКТУРА ТАБЛИЦЫ `published_articles`

```sql
CREATE TABLE published_articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    wp_post_id INTEGER UNIQUE NOT NULL,
    title TEXT NOT NULL,
    slug TEXT NOT NULL,
    url TEXT NOT NULL,
    post_date TEXT NOT NULL,
    post_modified TEXT NOT NULL,
    export_date TEXT NOT NULL,
    source_article_id INTEGER,
    FOREIGN KEY (source_article_id) REFERENCES articles(id)
)
```

---

## 📋 ПОЛНЫЙ СПИСОК ОПУБЛИКОВАННЫХ СТАТЕЙ

| № | WP ID | НАЗВАНИЕ | URL | ИСХОДНИК |
|---|-------|----------|-----|----------|
| 1 | 7907 | Курьерские пакеты: полный гид по выбору и применению | [Ссылка](https://ecopackpro.ru/kurierskie-pakety/) | ✅ ID 55 |
| 2 | 7908 | Почтовые коробки: размеры, виды, цены и где купить | [Ссылка](https://ecopackpro.ru/pochtovye-korobki/) | ✅ ID 56 |
| 3 | 7909 | Зип пакеты | [Ссылка](https://ecopackpro.ru/zip-pakety/) | ✅ ID 57 |
| 4 | 7910 | Zip lock пакеты с бегунком | [Ссылка](https://ecopackpro.ru/zip-lock-pakety-s-begunkom/) | ✅ ID 58 |
| 5 | 7911 | Конверты с воздушной подушкой для хрупких товаров | [Ссылка](https://ecopackpro.ru/konverty-s-vozdushnoy-podushkoy/) | ❌ |
| 6 | 7912 | Конверты с воздушной прослойкой для документов | [Ссылка](https://ecopackpro.ru/konverty-s-vozdushnoy-prosloykoy/) | ❌ |
| 7 | 7913 | Крафтовые пакеты с воздушной подушкой для бизнеса | [Ссылка](https://ecopackpro.ru/kraftovye-pakety-s-vozdushnoy-podushkoy/) | ❌ |
| 8 | 7914 | Курьерские пакеты прозрачные | [Ссылка](https://ecopackpro.ru/kurerskie-pakety-prozrachnye/) | ✅ ID 113 |
| 9 | 7915 | Курьерские пакеты номерные | [Ссылка](https://ecopackpro.ru/kurerskie-pakety-nomernye/) | ✅ ID 114 |
| 10 | 7916 | Курьерские пакеты черно-белые | [Ссылка](https://ecopackpro.ru/kurerskie-pakety-cherno-belye/) | ✅ ID 115 |
| 11 | 7917 | Курьерские пакеты с карманом | [Ссылка](https://ecopackpro.ru/kurerskie-pakety-s-karmanom/) | ✅ ID 116 |
| 12 | 7918 | Zip lock пакеты матовые | [Ссылка](https://ecopackpro.ru/zip-lock-pakety-matovye/) | ✅ ID 117 |
| 13 | 7919 | Zip lock пакеты оптом | [Ссылка](https://ecopackpro.ru/zip-lock-pakety-optom/) | ✅ ID 118 |
| 14 | 7920 | Крафтовые конверты | [Ссылка](https://ecopackpro.ru/kraftovye-konverty/) | ✅ ID 119 |
| 15 | 7921 | Пузырчатые пакеты ВПП | [Ссылка](https://ecopackpro.ru/puzyrchatye-pakety-vpp/) | ❌ |
| 16 | 7922 | Коробки для почты | [Ссылка](https://ecopackpro.ru/korobki-dlya-pochty/) | ✅ ID 121 |
| 17 | 7924 | Самоклеящиеся карманы | [Ссылка](https://ecopackpro.ru/samokleyaschiesya-karmany/) | ✅ ID 123 |
| 18 | 7925 | Антимагнитная пломба | [Ссылка](https://ecopackpro.ru/antimagnitnaya-plomba/) | ✅ ID 124 |
| 19 | 7926 | Наклейка пломба антимагнит | [Ссылка](https://ecopackpro.ru/nakleyka-plomba-antimagnit/) | ✅ ID 125 |
| 20 | 7927 | Пломбиратор для бочек | [Ссылка](https://ecopackpro.ru/plombirator-dlya-bochek/) | ✅ ID 126 |
| 21 | 7928 | Номерные пломбы наклейки | [Ссылка](https://ecopackpro.ru/nomernye-plomby-nakleyki/) | ✅ ID 127 |
| 22 | 7929 | Zip lock пакеты с белой полосой | [Ссылка](https://ecopackpro.ru/zip-lock-pakety-s-beloy-polosoy/) | ✅ ID 128 |
| 23 | 7930 | Белые крафт пакеты с пузырчатой плёнкой | [Ссылка](https://ecopackpro.ru/belye-kraft-pakety-s-puzyrchatoy-plyonkoy/) | ✅ ID 129 |
| 24 | 7931 | Прозрачные zip lock пакеты | [Ссылка](https://ecopackpro.ru/%d0%bf%d1%80%d0%be%d0%b7%d1%80%d0%b0%d1%87%d0%bd%d1%8b%d0%b5-zip-lock-%d0%bf%d0%b0%d0%ba%d0%b5%d1%82%d1%8b/) | ✅ ID 130 |
| 25 | 7932 | Купить курьерские пакеты с номерным штрих-кодом | [Ссылка](https://ecopackpro.ru/kupit-kurerskie-pakety-s-nomernym-shtrih-kodom/) | ✅ ID 131 |
| 26 | 7933 | Заказать прозрачные курьерские пакеты оптом | [Ссылка](https://ecopackpro.ru/zakazat-prozrachnye-kurerskie-pakety-optom/) | ✅ ID 132 |
| 27 | 7934 | Курьерские пакеты черно-белые с карманом цена | [Ссылка](https://ecopackpro.ru/kurerskie-pakety-cherno-belye-s-karmanom-tsena/) | ✅ ID 133 |
| 28 | 7935 | Матовые zip lock пакеты с бегунком 10×15 | [Ссылка](https://ecopackpro.ru/matovye-zip-lock-pakety-s-begunkom-1015/) | ✅ ID 134 |
| 29 | 7936 | Купить оптом zip lock пакеты матовые 30 мкм | [Ссылка](https://ecopackpro.ru/kupit-optom-zip-lock-pakety-matovye-30-mkm/) | ✅ ID 135 |
| 30 | 7937 | Крафт конверты с воздушной подушкой F/3 | [Ссылка](https://ecopackpro.ru/kraft-konverty-s-vozdushnoy-podushkoy-f3/) | ✅ ID 136 |
| 31 | 7938 | Почтовые коробки размера S 260×170×80 | [Ссылка](https://ecopackpro.ru/pochtovye-korobki-razmera-s-26017080/) | ✅ ID 137 |
| 32 | 7939 | Почтовые коробки размера XL 530×360×220 | [Ссылка](https://ecopackpro.ru/pochtovye-korobki-razmera-xl-530360220/) | ✅ ID 138 |
| 33 | 7940 | Купить самоклеящиеся карманы SD для документов | [Ссылка](https://ecopackpro.ru/kupit-samokleyaschiesya-karmany-sd-dlya-dokumentov/) | ✅ ID 139 |
| 34 | 7941 | Антимагнитные наклейки для водяных счётчиков | [Ссылка](https://ecopackpro.ru/antimagnitnye-nakleyki-dlya-vodyanyh-schyotchikov/) | ✅ ID 140 |
| 35 | 7942 | Антимагнитная пломба цена за 100 штук | [Ссылка](https://ecopackpro.ru/antimagnitnaya-plomba-tsena-za-100-shtuk/) | ✅ ID 141 |
| 36 | 7943 | Пломбиратор для евробочек 2 дюйма | [Ссылка](https://ecopackpro.ru/plombirator-dlya-evrobochek-2-dyuyma/) | ✅ ID 142 |
| 37 | 7944 | Инструмент для опломбирования бочек ¾ дюйма | [Ссылка](https://ecopackpro.ru/instrument-dlya-oplombirovaniya-bochek-dyuyma/) | ✅ ID 143 |
| 38 | 7945 | Курьерские пакеты черно-белые без логотипа А4 | [Ссылка](https://ecopackpro.ru/kurerskie-pakety-cherno-belye-bez-logotipa-a4/) | ❌ |
| 39 | 7946 | Курьерские пакеты прозрачные для одежды | [Ссылка](https://ecopackpro.ru/kurerskie-pakety-prozrachnye-dlya-odezhdy/) | ✅ ID 145 |
| 40 | 7947 | Курьерские пакеты для маркетплейсов Ozon | [Ссылка](https://ecopackpro.ru/kurerskie-pakety-dlya-marketpleysov-ozon/) | ✅ ID 146 |
| 41 | 7948 | Почтовые коробки с логотипом на заказ | [Ссылка](https://ecopackpro.ru/pochtovye-korobki-s-logotipom-na-zakaz/) | ✅ ID 147 |
| 42 | 7949 | Зип пакеты с бегунком купить Москва | [Ссылка](https://ecopackpro.ru/zip-pakety-s-begunkom-kupit-moskva/) | ❌ |
| 43 | 7950 | Матовые zip lock пакеты для чая | [Ссылка](https://ecopackpro.ru/matovye-zip-lock-pakety-dlya-chaya/) | ✅ ID 149 |
| 44 | 7951 | Zip lock пакеты с подвесом | [Ссылка](https://ecopackpro.ru/zip-lock-pakety-s-podvesom/) | ✅ ID 150 |
| 45 | 7952 | Белые крафт-пакеты с пузырчатой плёнкой оптом | [Ссылка](https://ecopackpro.ru/belye-kraft-pakety-s-puzyrchatoy-plyonkoy-optom/) | ✅ ID 151 |
| 46 | 7953 | Плоские конверты с воздушной подушкой для документов | [Ссылка](https://ecopackpro.ru/ploskie-konverty-s-vozdushnoy-podushkoy-dlya-dokumentov/) | ✅ ID 152 |
| 47 | 7954 | Пакеты из воздушно-пузырьковой плёнки оптом | [Ссылка](https://ecopackpro.ru/pakety-iz-vozdushno-puzyrkovoy-plyonki-optom/) | ✅ ID 153 |
| 48 | 7955 | Антимагнитные пломбы для газовых счётчиков | [Ссылка](https://ecopackpro.ru/antimagnitnye-plomby-dlya-gazovyh-schyotchikov/) | ✅ ID 154 |
| 49 | 7956 | Самоклеящиеся карманы для транспортных накладных | [Ссылка](https://ecopackpro.ru/samokleyaschiesya-karmany-dlya-transportnyh-nakladnyh/) | ✅ ID 155 |

---

## ⚠️ СТАТЬИ БЕЗ СВЯЗИ С ИСХОДНИКАМИ

1. **WP ID 7911** - Конверты с воздушной подушкой для хрупких товаров
2. **WP ID 7912** - Конверты с воздушной прослойкой для документов
3. **WP ID 7913** - Крафтовые пакеты с воздушной подушкой для бизнеса
4. **WP ID 7921** - Пузырчатые пакеты ВПП
5. **WP ID 7945** - Курьерские пакеты черно-белые без логотипа А4
6. **WP ID 7949** - Зип пакеты с бегунком купить Москва

**Причина:** Названия в WordPress содержат дополнительные части (например, "для хрупких товаров", "для бизнеса"), которые не были в исходных ключевых словах.

---

## 📊 SQL ЗАПРОСЫ ДЛЯ РАБОТЫ С ДАННЫМИ

### Получить все опубликованные статьи с исходниками:
```sql
SELECT 
    pa.wp_post_id,
    pa.title,
    pa.url,
    pa.post_date,
    a.keyword AS source_keyword,
    a.id AS source_id
FROM published_articles pa
LEFT JOIN articles a ON pa.source_article_id = a.id
ORDER BY pa.wp_post_id;
```

### Получить статьи без связи с исходниками:
```sql
SELECT 
    wp_post_id,
    title,
    url
FROM published_articles
WHERE source_article_id IS NULL;
```

### Откат к этой ревизии (если потребуется):
```bash
# Откат wp_posts:
mysql -u m1shqamai2_worp6 -p'9nUQkM*Q2cnvy379' m1shqamai2_worp6 < backup_published_articles_final_20251012_084242.sql

# Откат wp_postmeta:
mysql -u m1shqamai2_worp6 -p'9nUQkM*Q2cnvy379' m1shqamai2_worp6 < backup_published_articles_meta_final_20251012_084305.sql
```

---

## 🎉 ИТОГИ

✅ **Финальная ревизия завершена успешно!**

- Все 49 статей опубликованы на сайте https://ecopackpro.ru
- Резервные копии созданы и сохранены
- Данные о публикации сохранены в БД проекта `/root/seo_project/SEO_ecopackpro/articles.db`
- 43 статьи (87.8%) связаны с исходными материалами
- Создан полный список URL всех опубликованных статей
- Возможность отката к этой ревизии сохранена

---

**Дата создания отчета:** 2025-10-12 08:44:11  
**Автор:** SEO Content Management System  
**Проект:** EcoPackPro.ru Content Publication

