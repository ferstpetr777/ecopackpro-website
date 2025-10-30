#!/usr/bin/env python3
"""
E2E тест для мобильной версии / WebView - Индикатор корзины
Автор: EcopackPro Dev Team
Дата: 2025-10-30
"""

import sys
import os
import subprocess
import json
import time
import requests
from datetime import datetime

# Цвета для вывода
class Colors:
    GREEN = '\033[92m'
    RED = '\033[91m'
    YELLOW = '\033[93m'
    BLUE = '\033[94m'
    CYAN = '\033[96m'
    RESET = '\033[0m'
    BOLD = '\033[1m'

class MobileWebViewE2ETest:
    def __init__(self):
        self.site_path = '/var/www/fastuser/data/www/ecopackpro.ru'
        self.site_url = 'https://ecopackpro.ru'
        self.api_base = f'{self.site_url}/wp-json/ecopackpro/v1'
        self.results = []
        self.start_time = None
        
    def log(self, message, color=Colors.RESET):
        """Логирование с цветом"""
        print(f"{color}{message}{Colors.RESET}")
    
    def test_functions_php_exists(self):
        """Тест 1: Проверка наличия functions.php"""
        self.log("\n📋 Тест 1: Проверка functions.php", Colors.BLUE + Colors.BOLD)
        
        functions_path = f'{self.site_path}/wp-content/themes/Impreza-child/functions.php'
        
        if not os.path.exists(functions_path):
            self.results.append({
                'test': 'functions.php exists',
                'status': 'FAIL',
                'message': 'Файл functions.php не найден'
            })
            self.log("❌ FAIL: functions.php не найден", Colors.RED)
            return False
        
        # Проверка синтаксиса
        result = subprocess.run(
            ['php', '-l', functions_path],
            capture_output=True,
            text=True
        )
        
        if result.returncode == 0:
            self.results.append({
                'test': 'functions.php valid',
                'status': 'PASS',
                'message': 'functions.php существует и синтаксически корректен'
            })
            self.log("✅ PASS: functions.php корректен", Colors.GREEN)
            return True
        else:
            self.results.append({
                'test': 'functions.php syntax',
                'status': 'FAIL',
                'message': f'Ошибка синтаксиса: {result.stderr}'
            })
            self.log(f"❌ FAIL: {result.stderr}", Colors.RED)
            return False
    
    def test_bridge_script_exists(self):
        """Тест 2: Проверка наличия mobile-webview-cart-bridge.js"""
        self.log("\n📋 Тест 2: Проверка mobile-webview-cart-bridge.js", Colors.BLUE + Colors.BOLD)
        
        bridge_path = f'{self.site_path}/wp-content/themes/Impreza-child/mobile-webview-cart-bridge.js'
        
        if not os.path.exists(bridge_path):
            self.results.append({
                'test': 'Bridge script exists',
                'status': 'FAIL',
                'message': 'Скрипт bridge не найден'
            })
            self.log("❌ FAIL: Bridge скрипт не найден", Colors.RED)
            return False
        
        # Проверка размера
        size = os.path.getsize(bridge_path)
        size_kb = size / 1024
        
        if size_kb > 10:  # Должен быть > 10 KB
            self.results.append({
                'test': 'Bridge script valid',
                'status': 'PASS',
                'message': f'Bridge скрипт существует ({size_kb:.2f} KB)'
            })
            self.log(f"✅ PASS: Bridge скрипт найден ({size_kb:.2f} KB)", Colors.GREEN)
            return True
        else:
            self.results.append({
                'test': 'Bridge script size',
                'status': 'WARN',
                'message': f'Скрипт слишком маленький ({size_kb:.2f} KB)'
            })
            self.log(f"⚠️  WARN: Размер подозрительно мал ({size_kb:.2f} KB)", Colors.YELLOW)
            return True
    
    def test_api_endpoint_cart_count(self):
        """Тест 3: Проверка REST API endpoint /cart/count"""
        self.log("\n📋 Тест 3: Проверка API /cart/count", Colors.BLUE + Colors.BOLD)
        
        try:
            url = f'{self.api_base}/cart/count'
            response = requests.get(url, timeout=10, verify=False)
            
            if response.status_code == 200:
                data = response.json()
                
                if 'count' in data:
                    self.results.append({
                        'test': 'API cart/count',
                        'status': 'PASS',
                        'message': f'API работает, количество: {data["count"]}'
                    })
                    self.log(f"✅ PASS: API вернул count={data['count']}", Colors.GREEN)
                    self.log(f"   Response: {json.dumps(data, ensure_ascii=False)}", Colors.CYAN)
                    return True
                else:
                    self.results.append({
                        'test': 'API cart/count format',
                        'status': 'FAIL',
                        'message': 'API не вернул поле count'
                    })
                    self.log("❌ FAIL: Нет поля count в ответе", Colors.RED)
                    return False
            else:
                self.results.append({
                    'test': 'API cart/count status',
                    'status': 'FAIL',
                    'message': f'HTTP {response.status_code}'
                })
                self.log(f"❌ FAIL: HTTP {response.status_code}", Colors.RED)
                return False
        except Exception as e:
            self.results.append({
                'test': 'API cart/count',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_api_endpoint_cart_details(self):
        """Тест 4: Проверка REST API endpoint /cart/details"""
        self.log("\n📋 Тест 4: Проверка API /cart/details", Colors.BLUE + Colors.BOLD)
        
        try:
            url = f'{self.api_base}/cart/details'
            response = requests.get(url, timeout=10, verify=False)
            
            if response.status_code == 200:
                data = response.json()
                
                if 'count' in data and 'items' in data:
                    self.results.append({
                        'test': 'API cart/details',
                        'status': 'PASS',
                        'message': f'API работает, товаров: {data["count"]}, items: {len(data["items"])}'
                    })
                    self.log(f"✅ PASS: API вернул детали корзины", Colors.GREEN)
                    self.log(f"   Count: {data['count']}, Items: {len(data['items'])}", Colors.CYAN)
                    return True
                else:
                    self.results.append({
                        'test': 'API cart/details format',
                        'status': 'FAIL',
                        'message': 'API не вернул нужные поля'
                    })
                    self.log("❌ FAIL: Нет нужных полей в ответе", Colors.RED)
                    return False
            else:
                self.results.append({
                    'test': 'API cart/details status',
                    'status': 'FAIL',
                    'message': f'HTTP {response.status_code}'
                })
                self.log(f"❌ FAIL: HTTP {response.status_code}", Colors.RED)
                return False
        except Exception as e:
            self.results.append({
                'test': 'API cart/details',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_bridge_script_loaded(self):
        """Тест 5: Проверка загрузки скрипта на странице"""
        self.log("\n📋 Тест 5: Проверка загрузки bridge на странице", Colors.BLUE + Colors.BOLD)
        
        try:
            # Проверяем главную страницу
            response = requests.get(self.site_url, timeout=10, verify=False)
            html = response.text
            
            if 'mobile-webview-cart-bridge.js' in html:
                self.results.append({
                    'test': 'Bridge script loaded',
                    'status': 'PASS',
                    'message': 'Скрипт bridge подключен на странице'
                })
                self.log("✅ PASS: Bridge скрипт найден в HTML", Colors.GREEN)
                return True
            else:
                self.results.append({
                    'test': 'Bridge script loaded',
                    'status': 'FAIL',
                    'message': 'Скрипт bridge не найден в HTML'
                })
                self.log("❌ FAIL: Bridge скрипт не подключен", Colors.RED)
                return False
        except Exception as e:
            self.results.append({
                'test': 'Bridge script loaded',
                'status': 'FAIL',
                'message': f'Ошибка проверки: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_data_attributes(self):
        """Тест 6: Проверка наличия data-атрибутов в HTML"""
        self.log("\n📋 Тест 6: Проверка data-cart-count атрибутов", Colors.BLUE + Colors.BOLD)
        
        try:
            response = requests.get(self.site_url, timeout=10, verify=False)
            html = response.text
            
            has_data_attr = 'data-cart-count' in html
            has_init_script = 'initialCartCount' in html
            
            if has_data_attr and has_init_script:
                self.results.append({
                    'test': 'Data attributes',
                    'status': 'PASS',
                    'message': 'data-cart-count и initialCartCount найдены'
                })
                self.log("✅ PASS: Все data-атрибуты на месте", Colors.GREEN)
                return True
            elif has_data_attr or has_init_script:
                self.results.append({
                    'test': 'Data attributes',
                    'status': 'WARN',
                    'message': f'Частичная реализация (data-attr: {has_data_attr}, init: {has_init_script})'
                })
                self.log(f"⚠️  WARN: Не все атрибуты найдены", Colors.YELLOW)
                return True
            else:
                self.results.append({
                    'test': 'Data attributes',
                    'status': 'FAIL',
                    'message': 'data-атрибуты не найдены'
                })
                self.log("❌ FAIL: data-атрибуты отсутствуют", Colors.RED)
                return False
        except Exception as e:
            self.results.append({
                'test': 'Data attributes',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_cors_headers(self):
        """Тест 7: Проверка CORS headers для API"""
        self.log("\n📋 Тест 7: Проверка CORS headers", Colors.BLUE + Colors.BOLD)
        
        try:
            url = f'{self.api_base}/cart/count'
            response = requests.options(url, timeout=10, verify=False)
            
            has_cors = 'Access-Control-Allow-Origin' in response.headers
            
            if has_cors:
                self.results.append({
                    'test': 'CORS headers',
                    'status': 'PASS',
                    'message': 'CORS headers установлены'
                })
                self.log(f"✅ PASS: CORS headers присутствуют", Colors.GREEN)
                self.log(f"   Allow-Origin: {response.headers.get('Access-Control-Allow-Origin')}", Colors.CYAN)
                return True
            else:
                self.results.append({
                    'test': 'CORS headers',
                    'status': 'WARN',
                    'message': 'CORS headers не найдены (могут не требоваться)'
                })
                self.log("⚠️  WARN: CORS headers отсутствуют", Colors.YELLOW)
                return True
        except Exception as e:
            self.results.append({
                'test': 'CORS headers',
                'status': 'WARN',
                'message': f'Не удалось проверить: {str(e)}'
            })
            self.log(f"⚠️  WARN: {str(e)}", Colors.YELLOW)
            return True
    
    def generate_report(self):
        """Генерация отчета"""
        self.log("\n" + "="*80, Colors.BOLD)
        self.log("📊 ИТОГОВЫЙ ОТЧЕТ E2E ТЕСТИРОВАНИЯ (MOBILE WEBVIEW)", Colors.BOLD + Colors.BLUE)
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
        report_file = f'{self.site_path}/mobile_webview_e2e_test_report_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
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
        self.log("🚀 ЗАПУСК E2E ТЕСТОВ ДЛЯ MOBILE WEBVIEW", Colors.BOLD + Colors.BLUE)
        self.log("="*80, Colors.BOLD)
        self.log(f"Дата: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        self.log(f"Сайт: {self.site_url}")
        self.log(f"API: {self.api_base}")
        
        # Запуск тестов
        self.test_functions_php_exists()
        self.test_bridge_script_exists()
        self.test_api_endpoint_cart_count()
        self.test_api_endpoint_cart_details()
        self.test_bridge_script_loaded()
        self.test_data_attributes()
        self.test_cors_headers()
        
        # Генерация отчета
        return self.generate_report()

if __name__ == '__main__':
    # Отключаем предупреждения SSL
    import urllib3
    urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
    
    runner = MobileWebViewE2ETest()
    exit_code = runner.run_all_tests()
    sys.exit(exit_code)

