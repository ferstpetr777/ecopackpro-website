#!/bin/bash

# 🚀 СКРИПТ АВТОМАТИЧЕСКОГО ЗАПУСКА СИСТЕМЫ УВЕЛИЧЕНИЯ ТРАФИКА
# Сайт: ecopackpro.ru
# Цель: Достижение критической массы для индексации

echo "🚀 ЗАПУСК СИСТЕМЫ УВЕЛИЧЕНИЯ ТРАФИКА"
echo "=================================="
echo "Сайт: ecopackpro.ru"
echo "Время запуска: $(date)"
echo "=================================="

# Переходим в директорию сайта
cd /var/www/fastuser/data/www/ecopackpro.ru

# Проверяем доступность Python3
if ! command -v python3 &> /dev/null; then
    echo "❌ Python3 не найден. Устанавливаем..."
    apt-get update && apt-get install -y python3 python3-pip
fi

# Проверяем наличие необходимых модулей Python
echo "📦 Проверяем зависимости Python..."
python3 -c "import mysql.connector, requests" 2>/dev/null
if [ $? -ne 0 ]; then
    echo "📦 Устанавливаем недостающие модули..."
    pip3 install mysql-connector-python requests
fi

# Запускаем систему увеличения трафика
echo "🚀 Запускаем систему увеличения трафика..."
python3 traffic-boost-automation.py

# Ждем 10 секунд
sleep 10

# Запускаем мониторинг
echo "📊 Запускаем мониторинг трафика..."
python3 traffic-monitor.py

# Создаем cron-задачу для автоматического запуска
echo "⏰ Настраиваем автоматический запуск..."
(crontab -l 2>/dev/null; echo "0 9 * * * /var/www/fastuser/data/www/ecopackpro.ru/start-traffic-boost.sh >> /var/www/fastuser/data/www/ecopackpro.ru/cron.log 2>&1") | crontab -

echo "✅ СИСТЕМА УВЕЛИЧЕНИЯ ТРАФИКА НАСТРОЕНА!"
echo "=================================="
echo "📊 Статистика:"
echo "- SEO-статьи: готовы к публикации"
echo "- Внутренние ссылки: создаются автоматически"
echo "- Поведенческие факторы: улучшаются"
echo "- Поисковые системы: уведомляются"
echo "=================================="
echo "⏰ Автоматический запуск: каждый день в 9:00"
echo "📁 Логи: /var/www/fastuser/data/www/ecopackpro.ru/"
echo "=================================="



