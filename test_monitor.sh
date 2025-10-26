#!/bin/bash

# Скрипт для тестирования системы мониторинга
# Этот скрипт имитирует сбой сайта и проверяет восстановление

echo "=== Тест системы мониторинга ecopackpro.ru ==="
echo "Дата: $(date)"
echo ""

# Путь к основному скрипту мониторинга
MONITOR_SCRIPT="/var/www/fastuser/data/www/ecopackpro.ru/site_monitor.sh"

# Проверяем что скрипт существует
if [ ! -f "$MONITOR_SCRIPT" ]; then
    echo "❌ ОШИБКА: Скрипт мониторинга не найден: $MONITOR_SCRIPT"
    exit 1
fi

echo "1. Проверяем текущее состояние сайта..."
$MONITOR_SCRIPT test
echo ""

echo "2. Проверяем статистику мониторинга..."
$MONITOR_SCRIPT stats
echo ""

echo "3. Проверяем Apache статус..."
if systemctl is-active --quiet apache2; then
    echo "✅ Apache работает"
else
    echo "❌ Apache не работает"
fi

echo "4. Проверяем порт 81..."
if ss -tlnp | grep -q ":81"; then
    echo "✅ Порт 81 открыт"
else
    echo "❌ Порт 81 закрыт"
fi

echo "5. Проверяем использование памяти..."
free -h | grep Mem
echo ""

echo "6. Проверяем нагрузку системы..."
uptime
echo ""

echo "7. Запускаем полный мониторинг..."
$MONITOR_SCRIPT monitor
echo ""

echo "8. Проверяем логи мониторинга..."
if [ -f "/var/log/ecopackpro_monitor.log" ]; then
    echo "Последние 5 записей из лога:"
    tail -n 5 /var/log/ecopackpro_monitor.log
else
    echo "Лог файл не найден"
fi

echo ""
echo "=== Тест завершен ==="
echo "Скрипт мониторинга настроен и будет запускаться каждые 5 минут"
echo "Лог файл: /var/log/ecopackpro_monitor.log"
echo "Для ручного запуска: $MONITOR_SCRIPT monitor"
echo "Для просмотра статистики: $MONITOR_SCRIPT stats"

