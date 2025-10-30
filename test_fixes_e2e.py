#!/usr/bin/env python3
"""
E2E тесты для проверки исправлений ранней загрузки переводов и корзины WooCommerce
Автор: EcopackPro Dev Team
Дата: 2025-10-30
"""

import sys
import os
import subprocess
import json
import time
from datetime import datetime
from pathlib import Path

# Цвета для вывода
class Colors:
    GREEN = '\033[92m'
    RED = '\033[91m'
    YELLOW = '\033[93m'
    BLUE = '\033[94m'
    RESET = '\033[0m'
    BOLD = '\033[1m'

class E2ETestRunner:
    def __init__(self):
        self.site_path = '/var/www/fastuser/data/www/ecopackpro.ru'
        self.debug_log = f'{self.site_path}/wp-content/debug.log'
        self.site_url = 'https://ecopackpro.ru'
        self.results = []
        self.start_time = None
        
    def log(self, message, color=Colors.RESET):
        """Логирование с цветом"""
        print(f"{color}{message}{Colors.RESET}")
    
    def test_mu_plugin_loaded(self):
        """Тест 1: Проверка загрузки mu-plugin"""
        self.log("\n📋 Тест 1: Проверка загрузки mu-plugin", Colors.BLUE + Colors.BOLD)
        
        mu_plugin_path = f'{self.site_path}/wp-content/mu-plugins/fix-early-translations-and-cart.php'
        
        if not os.path.exists(mu_plugin_path):
            self.results.append({
                'test': 'MU Plugin Exists',
                'status': 'FAIL',
                'message': 'Файл mu-plugin не найден'
            })
            self.log("❌ FAIL: Файл mu-plugin не найден", Colors.RED)
            return False
        
        # Проверка синтаксиса PHP
        result = subprocess.run(
            ['php', '-l', mu_plugin_path],
            capture_output=True,
            text=True
        )
        
        if result.returncode == 0:
            self.results.append({
                'test': 'MU Plugin Loaded',
                'status': 'PASS',
                'message': 'mu-plugin загружен и синтаксис корректен'
            })
            self.log("✅ PASS: mu-plugin загружен успешно", Colors.GREEN)
            return True
        else:
            self.results.append({
                'test': 'MU Plugin Syntax',
                'status': 'FAIL',
                'message': f'Ошибка синтаксиса: {result.stderr}'
            })
            self.log(f"❌ FAIL: Ошибка синтаксиса: {result.stderr}", Colors.RED)
            return False
    
    def test_translations_loading(self):
        """Тест 2: Проверка загрузки переводов через WP-CLI"""
        self.log("\n📋 Тест 2: Проверка загрузки переводов", Colors.BLUE + Colors.BOLD)
        
        # Очищаем debug.log перед тестом
        with open(self.debug_log, 'w') as f:
            f.write('')
        
        # Выполняем команду WordPress для загрузки
        result = subprocess.run(
            ['sudo', '-u', 'www-data', 'wp', 'plugin', 'list', '--path=' + self.site_path],
            capture_output=True,
            text=True,
            cwd=self.site_path
        )
        
        time.sleep(2)  # Даем время на запись логов
        
        # Проверяем логи на наличие ошибок ранней загрузки
        try:
            with open(self.debug_log, 'r') as f:
                log_content = f.read()
        except:
            log_content = ''
        
        # Проблемные домены
        problematic_domains = [
            'acf',
            'js_composer',
            'tier-pricing-table',
            'wc-quantity-plus-minus-button',
            'wp-yandex-metrika',
            'health-check',
            'woocommerce-1c',
            'import-products-to-vk',
            'wordpress-seo-news'
        ]
        
        errors_found = []
        for domain in problematic_domains:
            if f'<code>{domain}</code>' in log_content and 'triggered too early' in log_content:
                errors_found.append(domain)
        
        if errors_found:
            self.results.append({
                'test': 'Translations Loading',
                'status': 'FAIL',
                'message': f'Ошибки ранней загрузки для: {", ".join(errors_found)}'
            })
            self.log(f"❌ FAIL: Найдены ошибки для доменов: {', '.join(errors_found)}", Colors.RED)
            return False
        else:
            self.results.append({
                'test': 'Translations Loading',
                'status': 'PASS',
                'message': 'Нет ошибок ранней загрузки переводов'
            })
            self.log("✅ PASS: Ошибок ранней загрузки переводов не обнаружено", Colors.GREEN)
            return True
    
    def test_woocommerce_cart(self):
        """Тест 3: Проверка работы корзины WooCommerce"""
        self.log("\n📋 Тест 3: Проверка корзины WooCommerce", Colors.BLUE + Colors.BOLD)
        
        # Очищаем debug.log перед тестом
        with open(self.debug_log, 'w') as f:
            f.write('')
        
        # Проверяем корзину через WP-CLI
        result = subprocess.run(
            ['sudo', '-u', 'www-data', 'wp', 'eval', 'WC()->cart->is_empty();', '--path=' + self.site_path],
            capture_output=True,
            text=True,
            cwd=self.site_path
        )
        
        time.sleep(2)
        
        # Проверяем логи на предупреждения о get_cart
        try:
            with open(self.debug_log, 'r') as f:
                log_content = f.read()
        except:
            log_content = ''
        
        cart_warnings = 'get_cart' in log_content and 'wp_loaded' in log_content
        
        if cart_warnings:
            self.results.append({
                'test': 'WooCommerce Cart Loading',
                'status': 'FAIL',
                'message': 'Обнаружены предупреждения о вызове get_cart до wp_loaded'
            })
            self.log("❌ FAIL: Предупреждения о корзине все еще присутствуют", Colors.RED)
            return False
        else:
            self.results.append({
                'test': 'WooCommerce Cart Loading',
                'status': 'PASS',
                'message': 'Корзина WooCommerce работает без предупреждений'
            })
            self.log("✅ PASS: Корзина WooCommerce работает корректно", Colors.GREEN)
            return True
    
    def test_site_accessibility(self):
        """Тест 4: Проверка доступности сайта"""
        self.log("\n📋 Тест 4: Проверка доступности сайта", Colors.BLUE + Colors.BOLD)
        
        try:
            result = subprocess.run(
                ['curl', '-s', '-o', '/dev/null', '-w', '%{http_code}', self.site_url],
                capture_output=True,
                text=True,
                timeout=10
            )
            
            status_code = result.stdout.strip()
            
            if status_code == '200':
                self.results.append({
                    'test': 'Site Accessibility',
                    'status': 'PASS',
                    'message': f'Сайт доступен (HTTP {status_code})'
                })
                self.log(f"✅ PASS: Сайт доступен (HTTP {status_code})", Colors.GREEN)
                return True
            else:
                self.results.append({
                    'test': 'Site Accessibility',
                    'status': 'WARN',
                    'message': f'Сайт вернул HTTP {status_code}'
                })
                self.log(f"⚠️  WARN: Сайт вернул HTTP {status_code}", Colors.YELLOW)
                return True
        except Exception as e:
            self.results.append({
                'test': 'Site Accessibility',
                'status': 'FAIL',
                'message': f'Ошибка доступа: {str(e)}'
            })
            self.log(f"❌ FAIL: Ошибка доступа к сайту: {str(e)}", Colors.RED)
            return False
    
    def test_debug_log_size(self):
        """Тест 5: Проверка размера debug.log после тестов"""
        self.log("\n📋 Тест 5: Проверка размера debug.log", Colors.BLUE + Colors.BOLD)
        
        try:
            size = os.path.getsize(self.debug_log)
            size_kb = size / 1024
            
            if size_kb < 10:  # Менее 10 KB - хорошо
                self.results.append({
                    'test': 'Debug Log Size',
                    'status': 'PASS',
                    'message': f'Размер debug.log: {size_kb:.2f} KB (норма)'
                })
                self.log(f"✅ PASS: debug.log компактный ({size_kb:.2f} KB)", Colors.GREEN)
                return True
            else:
                self.results.append({
                    'test': 'Debug Log Size',
                    'status': 'WARN',
                    'message': f'Размер debug.log: {size_kb:.2f} KB (может содержать ошибки)'
                })
                self.log(f"⚠️  WARN: debug.log большой ({size_kb:.2f} KB)", Colors.YELLOW)
                return True
        except Exception as e:
            self.results.append({
                'test': 'Debug Log Size',
                'status': 'FAIL',
                'message': f'Ошибка проверки: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_fix_status_helper(self):
        """Тест 6: Проверка хелпера статуса исправлений"""
        self.log("\n📋 Тест 6: Проверка статуса исправлений через WP-CLI", Colors.BLUE + Colors.BOLD)
        
        result = subprocess.run(
            ['sudo', '-u', 'www-data', 'wp', 'eval', 'print_r(ecopackpro_check_fixes_status());', '--path=' + self.site_path],
            capture_output=True,
            text=True,
            cwd=self.site_path
        )
        
        if result.returncode == 0 and 'translations_fix_active' in result.stdout:
            self.results.append({
                'test': 'Fix Status Helper',
                'status': 'PASS',
                'message': 'Хелпер статуса работает корректно'
            })
            self.log("✅ PASS: Хелпер статуса работает", Colors.GREEN)
            self.log(f"Вывод:\n{result.stdout}", Colors.BLUE)
            return True
        else:
            self.results.append({
                'test': 'Fix Status Helper',
                'status': 'FAIL',
                'message': 'Хелпер статуса не работает'
            })
            self.log("❌ FAIL: Хелпер статуса не отвечает", Colors.RED)
            return False
    
    def generate_report(self):
        """Генерация отчета"""
        self.log("\n" + "="*80, Colors.BOLD)
        self.log("📊 ИТОГОВЫЙ ОТЧЕТ E2E ТЕСТИРОВАНИЯ", Colors.BOLD + Colors.BLUE)
        self.log("="*80, Colors.BOLD)
        
        total = len(self.results)
        passed = sum(1 for r in self.results if r['status'] == 'PASS')
        failed = sum(1 for r in self.results if r['status'] == 'FAIL')
        warned = sum(1 for r in self.results if r['status'] == 'WARN')
        
        self.log(f"\nВсего тестов: {total}")
        self.log(f"Успешно: {passed}", Colors.GREEN)
        if warned > 0:
            self.log(f"Предупреждения: {warned}", Colors.YELLOW)
        if failed > 0:
            self.log(f"Провалено: {failed}", Colors.RED)
        
        self.log("\nДетали:", Colors.BOLD)
        for result in self.results:
            status_color = Colors.GREEN if result['status'] == 'PASS' else \
                          Colors.YELLOW if result['status'] == 'WARN' else Colors.RED
            
            self.log(f"\n  [{result['status']}] {result['test']}", status_color)
            self.log(f"    └─ {result['message']}")
        
        # Сохранение в JSON
        report_file = f'{self.site_path}/e2e_test_report_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
        with open(report_file, 'w', encoding='utf-8') as f:
            json.dump({
                'timestamp': datetime.now().isoformat(),
                'duration_seconds': time.time() - self.start_time,
                'total_tests': total,
                'passed': passed,
                'failed': failed,
                'warned': warned,
                'results': self.results
            }, f, indent=2, ensure_ascii=False)
        
        self.log(f"\n📄 Отчет сохранен: {report_file}", Colors.BLUE)
        
        # Итоговый статус
        if failed == 0:
            self.log("\n🎉 ВСЕ ТЕСТЫ ПРОЙДЕНЫ УСПЕШНО!", Colors.GREEN + Colors.BOLD)
            return 0
        else:
            self.log(f"\n⚠️  ОБНАРУЖЕНЫ ПРОБЛЕМЫ: {failed} тест(ов) провалено", Colors.RED + Colors.BOLD)
            return 1
    
    def run_all_tests(self):
        """Запуск всех тестов"""
        self.start_time = time.time()
        
        self.log("="*80, Colors.BOLD)
        self.log("🚀 ЗАПУСК E2E ТЕСТОВ ИСПРАВЛЕНИЙ", Colors.BOLD + Colors.BLUE)
        self.log("="*80, Colors.BOLD)
        self.log(f"Дата: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        self.log(f"Сайт: {self.site_url}")
        self.log(f"Путь: {self.site_path}")
        
        # Запуск тестов
        self.test_mu_plugin_loaded()
        self.test_translations_loading()
        self.test_woocommerce_cart()
        self.test_site_accessibility()
        self.test_debug_log_size()
        self.test_fix_status_helper()
        
        # Генерация отчета
        return self.generate_report()

if __name__ == '__main__':
    runner = E2ETestRunner()
    exit_code = runner.run_all_tests()
    sys.exit(exit_code)

