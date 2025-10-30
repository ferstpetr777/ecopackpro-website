#!/bin/bash
# Скрипт для очистки устаревшего Nginx microcache
# Автор: System Admin
# Дата: 2025-10-30

CACHE_DIR="/var/cache/nginx/microcache"
LOG_FILE="/var/www/fastuser/data/www/ecopackpro.ru/nginx_cache_cleanup.log"
MAX_SIZE_MB=400  # Максимальный размер кэша в MB (из конфига 500M, оставляем запас)

# Функция логирования
log_message() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" >> "$LOG_FILE"
}

# Проверка размера кэша
CURRENT_SIZE=$(du -sm "$CACHE_DIR" 2>/dev/null | cut -f1)

if [ -z "$CURRENT_SIZE" ]; then
    log_message "ERROR: Не удалось определить размер кэша"
    exit 1
fi

log_message "Текущий размер кэша: ${CURRENT_SIZE}MB"

# Если кэш превышает максимальный размер, очищаем старые файлы
if [ "$CURRENT_SIZE" -gt "$MAX_SIZE_MB" ]; then
    log_message "WARNING: Кэш превышает лимит (${CURRENT_SIZE}MB > ${MAX_SIZE_MB}MB), начинаем очистку"
    
    # Удаляем файлы старше 15 минут (inactive=15m в конфиге)
    DELETED=$(find "$CACHE_DIR" -type f -mmin +15 -delete -print 2>/dev/null | wc -l)
    
    NEW_SIZE=$(du -sm "$CACHE_DIR" 2>/dev/null | cut -f1)
    log_message "Удалено файлов: $DELETED, новый размер: ${NEW_SIZE}MB"
else
    log_message "OK: Размер кэша в пределах нормы"
fi

# Проверка и исправление прав доступа
if [ "$(stat -c '%U:%G' "$CACHE_DIR")" != "www-data:www-data" ]; then
    log_message "WARNING: Исправление прав доступа на $CACHE_DIR"
    chown -R www-data:www-data "$CACHE_DIR"
fi

exit 0

