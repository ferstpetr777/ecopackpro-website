#!/bin/bash

OUTPUT_FILE="/var/www/fastuser/data/www/ecopackpro.ru/РАЗМЕРЫ_САЙТА_РЕАЛЬНЫЕ.txt"

cat > "$OUTPUT_FILE" << 'ENDFILE'
================================================================================
              РАЗМЕРЫ ДИРЕКТОРИИ САЙТА ECOPACKPRO.RU
================================================================================

ENDFILE

echo "📊 ОБЩИЙ РАЗМЕР ДИРЕКТОРИИ:" >> "$OUTPUT_FILE"
du -sh /var/www/fastuser/data/www/ecopackpro.ru >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"

echo "================================================================================" >> "$OUTPUT_FILE"
echo "ТАБЛИЦА РАЗМЕРОВ ПОДПАПОК (ОТСОРТИРОВАНО ПО РАЗМЕРУ):" >> "$OUTPUT_FILE"
echo "================================================================================" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"

cd /var/www/fastuser/data/www/ecopackpro.ru
du -sh */ 2>/dev/null | sort -hr >> "$OUTPUT_FILE"

echo "" >> "$OUTPUT_FILE"
echo "================================================================================" >> "$OUTPUT_FILE"
echo "ДЕТАЛИЗАЦИЯ WP-CONTENT:" >> "$OUTPUT_FILE"
echo "================================================================================" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"

cd /var/www/fastuser/data/www/ecopackpro.ru/wp-content
du -sh */ 2>/dev/null | sort -hr >> "$OUTPUT_FILE"

echo "" >> "$OUTPUT_FILE"
echo "================================================================================" >> "$OUTPUT_FILE"
echo "ДЕТАЛИЗАЦИЯ WP-CONTENT/UPLOADS:" >> "$OUTPUT_FILE"
echo "================================================================================" >> "$OUTPUT_FILE"
echo "" >> "$OUTPUT_FILE"

cd /var/www/fastuser/data/www/ecopackpro.ru/wp-content/uploads
du -sh */ 2>/dev/null | sort -hr | head -20 >> "$OUTPUT_FILE"

echo "" >> "$OUTPUT_FILE"
echo "================================================================================" >> "$OUTPUT_FILE"
echo "Отчет создан: $(date '+%Y-%m-%d %H:%M:%S')" >> "$OUTPUT_FILE"
echo "================================================================================" >> "$OUTPUT_FILE"

chmod 644 "$OUTPUT_FILE"
cat "$OUTPUT_FILE"

