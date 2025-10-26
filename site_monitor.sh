#!/bin/bash

# Скрипт мониторинга и автовосстановления сайта ecopackpro.ru
# Автор: AI Assistant
# Дата: $(date)

# Конфигурация
SITE_URL="https://ecopackpro.ru"
SITE_NAME="ecopackpro.ru"
LOG_FILE="/var/log/ecopackpro_monitor.log"
STATUS_FILE="/tmp/ecopackpro_status"
MAX_RETRIES=3
RETRY_DELAY=10

# Функция логирования
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Функция проверки доступности сайта
check_site_availability() {
    local response_code
    local response_time
    
    # Проверяем HTTP статус код
    response_code=$(curl -s -o /dev/null -w "%{http_code}" --connect-timeout 10 --max-time 30 "$SITE_URL" 2>/dev/null)
    
    if [ "$response_code" = "200" ]; then
        # Дополнительная проверка - убеждаемся что контент загружается
        response_time=$(curl -s -o /dev/null -w "%{time_total}" --connect-timeout 10 --max-time 30 "$SITE_URL" 2>/dev/null)
        
        if [ $? -eq 0 ] && [ -n "$response_time" ]; then
            echo "200:$response_time"
            return 0
        else
            echo "TIMEOUT:0"
            return 1
        fi
    else
        echo "$response_code:0"
        return 1
    fi
}

# Функция проверки Apache
check_apache_status() {
    if systemctl is-active --quiet apache2; then
        return 0
    else
        return 1
    fi
}

# Функция проверки порта 81
check_apache_port() {
    if ss -tlnp | grep -q ":81"; then
        return 0
    else
        return 1
    fi
}

# Функция перезапуска Apache
restart_apache() {
    log_message "Попытка перезапуска Apache..."
    
    # Останавливаем Apache
    systemctl stop apache2
    sleep 5
    
    # Запускаем Apache
    systemctl start apache2
    sleep 10
    
    # Проверяем что Apache запустился
    if check_apache_status; then
        log_message "Apache успешно перезапущен"
        return 0
    else
        log_message "ОШИБКА: Не удалось запустить Apache"
        return 1
    fi
}

# Функция восстановления сайта
recover_site() {
    local retry_count=0
    
    log_message "Начинаем восстановление сайта $SITE_NAME"
    
    while [ $retry_count -lt $MAX_RETRIES ]; do
        retry_count=$((retry_count + 1))
        log_message "Попытка восстановления $retry_count/$MAX_RETRIES"
        
        # Проверяем Apache
        if ! check_apache_status; then
            log_message "Apache не работает, перезапускаем..."
            restart_apache
        elif ! check_apache_port; then
            log_message "Apache работает, но порт 81 недоступен, перезапускаем..."
            restart_apache
        else
            log_message "Apache работает нормально"
        fi
        
        # Ждем немного и проверяем сайт
        sleep $RETRY_DELAY
        
        if check_site_availability > /dev/null 2>&1; then
            log_message "Сайт $SITE_NAME успешно восстановлен"
            echo "RECOVERED" > "$STATUS_FILE"
            return 0
        fi
        
        log_message "Сайт все еще недоступен, повторяем через $RETRY_DELAY секунд..."
        sleep $RETRY_DELAY
    done
    
    log_message "КРИТИЧЕСКАЯ ОШИБКА: Не удалось восстановить сайт после $MAX_RETRIES попыток"
    echo "FAILED" > "$STATUS_FILE"
    return 1
}

# Основная функция мониторинга
monitor_site() {
    local check_result
    local http_code
    local response_time
    local current_status
    
    log_message "Проверка доступности сайта $SITE_NAME"
    
    # Проверяем доступность сайта
    check_result=$(check_site_availability)
    http_code=$(echo "$check_result" | cut -d: -f1)
    response_time=$(echo "$check_result" | cut -d: -f2)
    
    # Читаем предыдущий статус
    if [ -f "$STATUS_FILE" ]; then
        current_status=$(cat "$STATUS_FILE")
    else
        current_status="UNKNOWN"
    fi
    
    if [ "$http_code" = "200" ]; then
        log_message "✅ Сайт доступен (HTTP $http_code, время ответа: ${response_time}s)"
        echo "ONLINE" > "$STATUS_FILE"
        
        # Если сайт был недоступен, логируем восстановление
        if [ "$current_status" != "ONLINE" ]; then
            log_message "🎉 Сайт восстановлен после сбоя"
        fi
        
    else
        log_message "❌ Сайт недоступен (HTTP $http_code, время ответа: ${response_time}s)"
        
        # Если сайт был доступен, начинаем восстановление
        if [ "$current_status" = "ONLINE" ]; then
            log_message "🚨 Обнаружен сбой сайта, начинаем восстановление..."
            recover_site
        else
            log_message "⚠️  Сайт все еще недоступен, продолжаем попытки восстановления..."
            recover_site
        fi
    fi
    
    # Дополнительная диагностика при проблемах
    if [ "$http_code" != "200" ]; then
        log_message "Диагностика системы:"
        
        # Проверяем Apache
        if check_apache_status; then
            log_message "  - Apache: ✅ Работает"
        else
            log_message "  - Apache: ❌ Не работает"
        fi
        
        # Проверяем порт
        if check_apache_port; then
            log_message "  - Порт 81: ✅ Открыт"
        else
            log_message "  - Порт 81: ❌ Закрыт"
        fi
        
        # Проверяем память
        local mem_usage=$(free | grep Mem | awk '{printf "%.1f", $3/$2 * 100.0}')
        log_message "  - Использование памяти: ${mem_usage}%"
        
        # Проверяем нагрузку
        local load_avg=$(uptime | awk -F'load average:' '{print $2}' | awk '{print $1}' | sed 's/,//')
        log_message "  - Нагрузка системы: $load_avg"
    fi
}

# Функция показа статистики
show_stats() {
    if [ -f "$LOG_FILE" ]; then
        echo "=== Статистика мониторинга $SITE_NAME ==="
        echo "Последние 10 записей:"
        tail -n 10 "$LOG_FILE"
        echo ""
        echo "Количество проверок за последние 24 часа:"
        grep "$(date '+%Y-%m-%d')" "$LOG_FILE" | wc -l
        echo ""
        echo "Количество сбоев за последние 24 часа:"
        grep "$(date '+%Y-%m-%d')" "$LOG_FILE" | grep -c "❌\|ОШИБКА\|КРИТИЧЕСКАЯ"
    else
        echo "Лог файл не найден: $LOG_FILE"
    fi
}

# Обработка аргументов командной строки
case "$1" in
    "monitor")
        monitor_site
        ;;
    "stats")
        show_stats
        ;;
    "test")
        log_message "Тестовая проверка сайта $SITE_NAME"
        check_result=$(check_site_availability)
        http_code=$(echo "$check_result" | cut -d: -f1)
        response_time=$(echo "$check_result" | cut -d: -f2)
        echo "Результат: HTTP $http_code, время ответа: ${response_time}s"
        ;;
    "recover")
        recover_site
        ;;
    *)
        echo "Использование: $0 {monitor|stats|test|recover}"
        echo ""
        echo "Команды:"
        echo "  monitor  - Запустить мониторинг (для cron)"
        echo "  stats    - Показать статистику"
        echo "  test     - Тестовая проверка"
        echo "  recover  - Принудительное восстановление"
        exit 1
        ;;
esac

