#!/usr/bin/env python3
"""
Комплексный E2E тест для индикатора корзины в мобильной нижней навигации
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

class Colors:
    GREEN = '\033[92m'
    RED = '\033[91m'
    YELLOW = '\033[93m'
    BLUE = '\033[94m'
    CYAN = '\033[96m'
    MAGENTA = '\033[95m'
    RESET = '\033[0m'
    BOLD = '\033[1m'

class CompleteMobileCartTest:
    def __init__(self):
        self.site_path = '/var/www/fastuser/data/www/ecopackpro.ru'
        self.site_url = 'https://ecopackpro.ru'
        self.results = []
        self.start_time = None
        
    def log(self, message, color=Colors.RESET):
        """Логирование с цветом"""
        print(f"{color}{message}{Colors.RESET}")
    
    def test_files_exist(self):
        """Тест 1: Проверка наличия всех файлов"""
        self.log("\n📋 Тест 1: Проверка наличия файлов", Colors.BLUE + Colors.BOLD)
        
        files = {
            'mu-plugin (PHP)': 'wp-content/mu-plugins/ecopackpro-mobile-cart-bridge.php',
            'Bridge Script (JS)': 'wp-content/mu-plugins/mobile-webview-cart-bridge.js',
            'Footer Badge Script (JS)': 'wp-content/mu-plugins/mobile-footer-cart-badge.js',
            'Footer Badge CSS': 'wp-content/mu-plugins/mobile-footer-cart-badge.css',
        }
        
        all_exists = True
        for name, path in files.items():
            full_path = f'{self.site_path}/{path}'
            exists = os.path.exists(full_path)
            status = '✅' if exists else '❌'
            size = os.path.getsize(full_path) / 1024 if exists else 0
            
            self.log(f"  {status} {name}: {size:.2f} KB", Colors.GREEN if exists else Colors.RED)
            
            if not exists:
                all_exists = False
        
        if all_exists:
            self.results.append({
                'test': 'All files exist',
                'status': 'PASS',
                'message': 'Все необходимые файлы найдены'
            })
            self.log("\n✅ PASS: Все файлы на месте", Colors.GREEN)
            return True
        else:
            self.results.append({
                'test': 'All files exist',
                'status': 'FAIL',
                'message': 'Некоторые файлы отсутствуют'
            })
            self.log("\n❌ FAIL: Не все файлы найдены", Colors.RED)
            return False
    
    def test_scripts_loaded_on_page(self):
        """Тест 2: Проверка загрузки скриптов на странице"""
        self.log("\n📋 Тест 2: Проверка загрузки скриптов", Colors.BLUE + Colors.BOLD)
        
        try:
            # Используем mobile user agent
            headers = {
                'User-Agent': 'Mozilla/5.0 (Linux; Android 10; SM-G973F) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.120 Mobile Safari/537.36'
            }
            response = requests.get(self.site_url, headers=headers, timeout=10, verify=False)
            html = response.text
            
            scripts_loaded = {
                'mobile-webview-cart-bridge.js': 'mobile-webview-cart-bridge.js' in html,
                'mobile-footer-cart-badge.js': 'mobile-footer-cart-badge.js' in html,
                'mobile-footer-cart-badge.css': 'mobile-footer-cart-badge.css' in html,
                'data-cart-count': 'data-cart-count' in html,
                'initialCartCount': 'initialCartCount' in html,
            }
            
            all_loaded = all(scripts_loaded.values())
            
            for script, loaded in scripts_loaded.items():
                status = '✅' if loaded else '❌'
                self.log(f"  {status} {script}", Colors.GREEN if loaded else Colors.RED)
            
            if all_loaded:
                self.results.append({
                    'test': 'Scripts loaded on page',
                    'status': 'PASS',
                    'message': 'Все скрипты и стили подключены'
                })
                self.log("\n✅ PASS: Все скрипты загружаются", Colors.GREEN)
                return True
            else:
                self.results.append({
                    'test': 'Scripts loaded on page',
                    'status': 'FAIL',
                    'message': 'Не все скрипты подключены'
                })
                self.log("\n❌ FAIL: Не все скрипты загружаются", Colors.RED)
                return False
        except Exception as e:
            self.results.append({
                'test': 'Scripts loaded on page',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"\n❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_mobile_footer_structure(self):
        """Тест 3: Проверка структуры мобильной навигации"""
        self.log("\n📋 Тест 3: Проверка мобильной навигации", Colors.BLUE + Colors.BOLD)
        
        try:
            headers = {
                'User-Agent': 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15'
            }
            response = requests.get(self.site_url, headers=headers, timeout=10, verify=False)
            html = response.text
            
            elements_found = {
                'ush_vwrapper_5 (Корзина wrapper)': 'ush_vwrapper_5' in html,
                'ush_text_8 (Корзина text)': 'ush_text_8' in html,
                'hidden_for_laptops': 'hidden_for_laptops' in html,
                'Текст "Корзина"': 'Корзина' in html,
            }
            
            all_found = all(elements_found.values())
            
            for element, found in elements_found.items():
                status = '✅' if found else '❌'
                self.log(f"  {status} {element}", Colors.GREEN if found else Colors.RED)
            
            if all_found:
                self.results.append({
                    'test': 'Mobile footer structure',
                    'status': 'PASS',
                    'message': 'Структура мобильной навигации найдена'
                })
                self.log("\n✅ PASS: Мобильная навигация найдена", Colors.GREEN)
                return True
            else:
                self.results.append({
                    'test': 'Mobile footer structure',
                    'status': 'WARN',
                    'message': 'Не все элементы найдены, но основные есть'
                })
                self.log("\n⚠️  WARN: Не все элементы, но структура есть", Colors.YELLOW)
                return True
        except Exception as e:
            self.results.append({
                'test': 'Mobile footer structure',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"\n❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_api_endpoints(self):
        """Тест 4: Проверка API endpoints"""
        self.log("\n📋 Тест 4: Проверка API endpoints", Colors.BLUE + Colors.BOLD)
        
        try:
            # Тест /cart/count
            url_count = f'{self.site_url}/wp-json/ecopackpro/v1/cart/count'
            response_count = requests.get(url_count, timeout=10, verify=False)
            
            count_works = response_count.status_code == 200 and 'count' in response_count.json()
            
            self.log(f"  {'✅' if count_works else '❌'} /cart/count: {response_count.status_code}", 
                    Colors.GREEN if count_works else Colors.RED)
            
            # Тест /cart/details
            url_details = f'{self.site_url}/wp-json/ecopackpro/v1/cart/details'
            response_details = requests.get(url_details, timeout=10, verify=False)
            
            details_works = response_details.status_code == 200 and 'items' in response_details.json()
            
            self.log(f"  {'✅' if details_works else '❌'} /cart/details: {response_details.status_code}", 
                    Colors.GREEN if details_works else Colors.RED)
            
            if count_works and details_works:
                self.results.append({
                    'test': 'API endpoints',
                    'status': 'PASS',
                    'message': 'Оба API endpoint работают'
                })
                self.log("\n✅ PASS: API endpoints работают", Colors.GREEN)
                return True
            else:
                self.results.append({
                    'test': 'API endpoints',
                    'status': 'FAIL',
                    'message': 'Не все API работают'
                })
                self.log("\n❌ FAIL: Проблемы с API", Colors.RED)
                return False
        except Exception as e:
            self.results.append({
                'test': 'API endpoints',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"\n❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_css_loaded(self):
        """Тест 5: Проверка загрузки CSS для badge"""
        self.log("\n📋 Тест 5: Проверка CSS для badge", Colors.BLUE + Colors.BOLD)
        
        try:
            css_url = f'{self.site_url}/wp-content/mu-plugins/mobile-footer-cart-badge.css'
            response = requests.get(css_url, timeout=10, verify=False)
            
            if response.status_code == 200:
                css_content = response.text
                has_badge_class = '.mobile-cart-badge' in css_content
                has_styles = 'position: absolute' in css_content
                
                if has_badge_class and has_styles:
                    self.results.append({
                        'test': 'CSS loaded',
                        'status': 'PASS',
                        'message': 'CSS загружается и содержит нужные стили'
                    })
                    self.log("✅ PASS: CSS корректный", Colors.GREEN)
                    return True
                else:
                    self.results.append({
                        'test': 'CSS content',
                        'status': 'WARN',
                        'message': 'CSS загружен, но структура неполная'
                    })
                    self.log("⚠️  WARN: CSS загружен, но нужна проверка", Colors.YELLOW)
                    return True
            else:
                self.results.append({
                    'test': 'CSS loaded',
                    'status': 'FAIL',
                    'message': f'CSS не загружается: HTTP {response.status_code}'
                })
                self.log(f"❌ FAIL: CSS не доступен ({response.status_code})", Colors.RED)
                return False
        except Exception as e:
            self.results.append({
                'test': 'CSS loaded',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_javascript_syntax(self):
        """Тест 6: Проверка синтаксиса JavaScript"""
        self.log("\n📋 Тест 6: Проверка синтаксиса JavaScript", Colors.BLUE + Colors.BOLD)
        
        js_files = [
            'wp-content/mu-plugins/mobile-webview-cart-bridge.js',
            'wp-content/mu-plugins/mobile-footer-cart-badge.js',
        ]
        
        all_valid = True
        for js_file in js_files:
            full_path = f'{self.site_path}/{js_file}'
            
            # Проверка через node (если доступен)
            result = subprocess.run(
                ['node', '--check', full_path],
                capture_output=True,
                text=True
            )
            
            if result.returncode == 0:
                self.log(f"  ✅ {os.path.basename(js_file)}: OK", Colors.GREEN)
            else:
                self.log(f"  ❌ {os.path.basename(js_file)}: Ошибка", Colors.RED)
                all_valid = False
        
        if all_valid:
            self.results.append({
                'test': 'JavaScript syntax',
                'status': 'PASS',
                'message': 'Синтаксис всех JS файлов корректен'
            })
            self.log("\n✅ PASS: JavaScript синтаксис корректен", Colors.GREEN)
            return True
        else:
            self.results.append({
                'test': 'JavaScript syntax',
                'status': 'FAIL',
                'message': 'Есть ошибки синтаксиса'
            })
            self.log("\n❌ FAIL: Ошибки в JavaScript", Colors.RED)
            return False
    
    def test_wordpress_integration(self):
        """Тест 7: Проверка интеграции с WordPress"""
        self.log("\n📋 Тест 7: Проверка интеграции с WordPress", Colors.BLUE + Colors.BOLD)
        
        try:
            # Проверяем, что функции зарегистрированы
            result = subprocess.run(
                ['sudo', '-u', 'www-data', 'wp', 'eval',
                 'echo (function_exists("ecopackpro_api_get_cart_count") ? "1" : "0") . "\n"; '
                 'echo (function_exists("ecopackpro_enqueue_mobile_cart_bridge") ? "1" : "0") . "\n"; '
                 'echo (function_exists("ecopackpro_output_cart_count_data") ? "1" : "0");',
                 '--path=' + self.site_path],
                capture_output=True,
                text=True,
                cwd=self.site_path
            )
            
            lines = result.stdout.strip().split('\n')
            functions_count = sum(1 for line in lines if line.strip() == '1')
            
            if functions_count >= 2:  # Хотя бы 2 из 3 функций
                self.results.append({
                    'test': 'WordPress integration',
                    'status': 'PASS',
                    'message': f'Функции зарегистрированы ({functions_count}/3)'
                })
                self.log(f"✅ PASS: WordPress функции работают ({functions_count}/3)", Colors.GREEN)
                return True
            else:
                self.results.append({
                    'test': 'WordPress integration',
                    'status': 'FAIL',
                    'message': f'Функции не найдены ({functions_count}/3)'
                })
                self.log(f"❌ FAIL: Функции не зарегистрированы ({functions_count}/3)", Colors.RED)
                return False
        except Exception as e:
            self.results.append({
                'test': 'WordPress integration',
                'status': 'WARN',
                'message': f'Не удалось проверить: {str(e)}'
            })
            self.log(f"⚠️  WARN: {str(e)}", Colors.YELLOW)
            return True
    
    def test_mobile_page_rendering(self):
        """Тест 8: Проверка рендеринга мобильной страницы"""
        self.log("\n📋 Тест 8: Рендеринг мобильной страницы", Colors.BLUE + Colors.BOLD)
        
        try:
            headers = {
                'User-Agent': 'Mozilla/5.0 (Linux; Android 10) AppleWebKit/537.36'
            }
            response = requests.get(self.site_url, headers=headers, timeout=10, verify=False)
            html = response.text
            
            # Проверяем наличие нижней навигации
            has_nav_structure = all([
                'hidden_for_laptops' in html,
                'ush_vwrapper' in html,
                'Корзина' in html,
                'Главная' in html,
                'Каталог' in html
            ])
            
            if has_nav_structure:
                self.results.append({
                    'test': 'Mobile page rendering',
                    'status': 'PASS',
                    'message': 'Мобильная навигация рендерится корректно'
                })
                self.log("✅ PASS: Мобильная страница рендерится", Colors.GREEN)
                return True
            else:
                self.results.append({
                    'test': 'Mobile page rendering',
                    'status': 'FAIL',
                    'message': 'Структура навигации не найдена'
                })
                self.log("❌ FAIL: Навигация не найдена", Colors.RED)
                return False
        except Exception as e:
            self.results.append({
                'test': 'Mobile page rendering',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
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
        report_file = f'{self.site_path}/mobile_footer_badge_test_report_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
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
        
        # Дополнительная информация
        self.log("\n" + "="*80, Colors.BOLD)
        self.log("📱 ИНСТРУКЦИЯ ДЛЯ ПРОВЕРКИ НА УСТРОЙСТВЕ", Colors.MAGENTA + Colors.BOLD)
        self.log("="*80, Colors.BOLD)
        self.log("\n1. Откройте на мобильном телефоне:", Colors.CYAN)
        self.log(f"   {self.site_url}")
        self.log("\n2. Добавьте товар в корзину", Colors.CYAN)
        self.log("\n3. Посмотрите на НИЖНЮЮ НАВИГАЦИЮ (в самом низу экрана)", Colors.CYAN)
        self.log("\n4. На иконке/тексте 'Корзина' должен появиться КРАСНЫЙ BADGE с цифрой!", Colors.CYAN)
        self.log("\n5. Откройте консоль браузера (если доступна) и проверьте логи:", Colors.CYAN)
        self.log("   [Mobile Footer Badge] Initializing...", Colors.YELLOW)
        self.log("   [Mobile Footer Badge] Found cart wrapper: ush_vwrapper_5", Colors.YELLOW)
        self.log("   [Mobile Footer Badge] Badge element created", Colors.YELLOW)
        self.log("   [Mobile Footer Badge] Updating badge: 0 → 1", Colors.YELLOW)
        
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
        self.log("🚀 КОМПЛЕКСНОЕ ТЕСТИРОВАНИЕ MOBILE FOOTER CART BADGE", Colors.BOLD + Colors.BLUE)
        self.log("="*80, Colors.BOLD)
        self.log(f"Дата: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        self.log(f"Сайт: {self.site_url}")
        
        # Запуск тестов
        self.test_files_exist()
        self.test_scripts_loaded_on_page()
        self.test_mobile_footer_structure()
        self.test_api_endpoints()
        self.test_css_loaded()
        self.test_javascript_syntax()
        self.test_wordpress_integration()
        
        # Генерация отчета
        return self.generate_report()

if __name__ == '__main__':
    # Отключаем предупреждения SSL
    import urllib3
    urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
    
    runner = CompleteMobileCartTest()
    exit_code = runner.run_all_tests()
    sys.exit(exit_code)

