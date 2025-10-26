#!/bin/bash
#
# Скрипт автоматической очистки базы данных WordPress
# Запускается еженедельно через cron
#

DB_NAME="m1shqamai2_worp6"
DB_USER="m1shqamai2_worp6"
DB_PASS="9nUQkM*Q2cnvy379"
DB_HOST="localhost"
LOG_FILE="/var/www/fastuser/data/www/ecopackpro.ru/db_cleanup.log"

echo "=== База данных - автоматическая очистка $(date) ===" >> "$LOG_FILE"

# 1. Удаление старых ревизий (оставляем только последние 5 для каждого поста)
echo "Удаление старых ревизий..." >> "$LOG_FILE"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF >> "$LOG_FILE" 2>&1
DELETE FROM wp_posts 
WHERE post_type = 'revision' 
AND ID NOT IN (
    SELECT * FROM (
        SELECT ID FROM wp_posts 
        WHERE post_type = 'revision' 
        ORDER BY post_modified DESC 
        LIMIT 100
    ) AS keep_revisions
);
EOF

# 2. Удаление auto-draft постов старше 7 дней
echo "Удаление auto-draft постов..." >> "$LOG_FILE"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF >> "$LOG_FILE" 2>&1
DELETE FROM wp_posts 
WHERE post_status = 'auto-draft' 
AND post_modified < DATE_SUB(NOW(), INTERVAL 7 DAY);
EOF

# 3. Удаление осиротевших postmeta
echo "Удаление осиротевших postmeta..." >> "$LOG_FILE"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF >> "$LOG_FILE" 2>&1
DELETE pm FROM wp_postmeta pm 
LEFT JOIN wp_posts p ON pm.post_id = p.ID 
WHERE p.ID IS NULL;
EOF

# 4. Очистка старых задач Action Scheduler (старше 30 дней)
echo "Очистка Action Scheduler..." >> "$LOG_FILE"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF >> "$LOG_FILE" 2>&1
DELETE FROM wp_actionscheduler_actions 
WHERE status IN ('complete', 'failed') 
AND scheduled_date_gmt < DATE_SUB(NOW(), INTERVAL 30 DAY);

DELETE FROM wp_actionscheduler_logs 
WHERE action_id NOT IN (SELECT action_id FROM wp_actionscheduler_actions);
EOF

# 5. Удаление устаревших transients
echo "Удаление устаревших transients..." >> "$LOG_FILE"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF >> "$LOG_FILE" 2>&1
DELETE FROM wp_options 
WHERE option_name LIKE '_transient_%' 
OR option_name LIKE '_site_transient_%';
EOF

# 6. Удаление старых сессий WooCommerce (старше 2 дней)
echo "Очистка сессий WooCommerce..." >> "$LOG_FILE"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF >> "$LOG_FILE" 2>&1
DELETE FROM wp_woocommerce_sessions 
WHERE session_expiry < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 2 DAY));
EOF

# 7. Оптимизация основных таблиц
echo "Оптимизация таблиц..." >> "$LOG_FILE"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF >> "$LOG_FILE" 2>&1
OPTIMIZE TABLE wp_posts, wp_postmeta, wp_options, 
wp_actionscheduler_actions, wp_actionscheduler_logs, 
wp_woocommerce_sessions;
EOF

# Вывод итоговой статистики
echo "Получение статистики..." >> "$LOG_FILE"
mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" <<EOF >> "$LOG_FILE" 2>&1
SELECT ROUND(SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS 'DB Size (MB)' 
FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = '$DB_NAME';
EOF

echo "=== Очистка завершена $(date) ===" >> "$LOG_FILE"
echo "" >> "$LOG_FILE"




