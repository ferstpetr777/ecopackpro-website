#!/usr/bin/env python3
import subprocess
import os
from datetime import datetime

def run_command(cmd):
    """Run shell command and return output"""
    try:
        result = subprocess.run(cmd, shell=True, capture_output=True, text=True, timeout=120)
        return result.stdout.strip()
    except Exception as e:
        return f"Error: {str(e)}"

def main():
    root_path = "/var/www/fastuser/data/www/ecopackpro.ru"
    output_file = f"{root_path}/РАЗМЕРЫ_САЙТА_РЕАЛЬНЫЕ.txt"
    
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write("=" * 80 + "\n")
        f.write("              РАЗМЕРЫ ДИРЕКТОРИИ САЙТА ECOPACKPRO.RU\n")
        f.write("=" * 80 + "\n\n")
        
        # Total size
        f.write("📊 ОБЩИЙ РАЗМЕР ДИРЕКТОРИИ:\n")
        total_size = run_command(f"du -sh {root_path}")
        f.write(total_size + "\n\n")
        
        f.write("=" * 80 + "\n")
        f.write("ТАБЛИЦА РАЗМЕРОВ ПОДПАПОК (ОТСОРТИРОВАНО ПО РАЗМЕРУ):\n")
        f.write("=" * 80 + "\n\n")
        
        # Subdirectories
        subdirs = run_command(f"cd {root_path} && du -sh */ 2>/dev/null | sort -hr")
        f.write(subdirs + "\n\n")
        
        f.write("=" * 80 + "\n")
        f.write("ДЕТАЛИЗАЦИЯ WP-CONTENT:\n")
        f.write("=" * 80 + "\n\n")
        
        # wp-content
        wp_content = run_command(f"cd {root_path}/wp-content && du -sh */ 2>/dev/null | sort -hr")
        f.write(wp_content + "\n\n")
        
        f.write("=" * 80 + "\n")
        f.write("ДЕТАЛИЗАЦИЯ WP-CONTENT/UPLOADS (ТОП-20):\n")
        f.write("=" * 80 + "\n\n")
        
        # uploads
        uploads = run_command(f"cd {root_path}/wp-content/uploads && du -sh */ 2>/dev/null | sort -hr | head -20")
        f.write(uploads + "\n\n")
        
        f.write("=" * 80 + "\n")
        f.write(f"Отчет создан: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
        f.write("=" * 80 + "\n")
    
    # Set permissions
    os.chmod(output_file, 0o644)
    
    # Print content
    with open(output_file, 'r', encoding='utf-8') as f:
        print(f.read())

if __name__ == "__main__":
    main()

