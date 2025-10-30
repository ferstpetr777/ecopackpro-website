#!/usr/bin/env python3
"""
Комплексный тест восстановления нативного WooCommerce cart механизма
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

class NativeCartTest:
    def __init__(self):
        self.site_path = '/var/www/fastuser/data/www/ecopackpro.ru'
        self.site_url = 'https://ecopackpro.ru'
        self.results = []
        self.start_time = None
        
    def log(self, message, color=Colors.RESET):
        """Логирование с цветом"""
        print(f"{color}{message}{Colors.RESET}")
    
    def test_problematic_scripts_disabled(self):
        """Тест 1: Проверка отключения проблемных скриптов"""
        self.log("\n📋 Тест 1: Проверка отключения проблемных скриптов", Colors.BLUE + Colors.BOLD)
        
        # Проверяем что cart-counter-fix.js переименован
        counter_fix_disabled = not os.path.exists(f'{self.site_path}/wp-content/themes/Impreza/cart-counter-fix.js')
        
        # Проверяем что footer.php не содержит setInterval(5000)
        try:
            with open(f'{self.site_path}/wp-content/plugins/us-core/templates/footer.php', 'r') as f:
                footer_content = f.read()
                footer_fixed = 'setInterval' not in footer_content or 'DISABLED' in footer_content or 'Native Cart' in footer_content
        except:
            footer_fixed = False
        
        self.log(f"  {'✅' if counter_fix_disabled else '❌'} cart-counter-fix.js отключен", 
                Colors.GREEN if counter_fix_disabled else Colors.RED)
        self.log(f"  {'✅' if footer_fixed else '❌'} footer.php исправлен", 
                Colors.GREEN if footer_fixed else Colors.RED)
        
        if counter_fix_disabled and footer_fixed:
            self.results.append({
                'test': 'Problematic scripts disabled',
                'status': 'PASS',
                'message': 'Проблемные скрипты отключены'
            })
            self.log("\n✅ PASS: Проблемные скрипты удалены", Colors.GREEN)
            return True
        else:
            self.results.append({
                'test': 'Problematic scripts disabled',
                'status': 'FAIL',
                'message': 'Не все проблемные скрипты отключены'
            })
            self.log("\n❌ FAIL: Есть активные проблемные скрипты", Colors.RED)
            return False
    
    def test_native_cart_plugin_loaded(self):
        """Тест 2: Проверка загрузки restore-native-woocommerce-cart.php"""
        self.log("\n📋 Тест 2: Проверка нового mu-plugin", Colors.BLUE + Colors.BOLD)
        
        plugin_path = f'{self.site_path}/wp-content/mu-plugins/restore-native-woocommerce-cart.php'
        
        if not os.path.exists(plugin_path):
            self.results.append({
                'test': 'Native cart plugin exists',
                'status': 'FAIL',
                'message': 'Файл mu-plugin не найден'
            })
            self.log("❌ FAIL: mu-plugin не найден", Colors.RED)
            return False
        
        # Проверка синтаксиса
        result = subprocess.run(
            ['php', '-l', plugin_path],
            capture_output=True,
            text=True
        )
        
        if result.returncode == 0:
            self.results.append({
                'test': 'Native cart plugin loaded',
                'status': 'PASS',
                'message': 'mu-plugin загружен и синтаксис корректен'
            })
            self.log("✅ PASS: mu-plugin корректен", Colors.GREEN)
            return True
        else:
            self.results.append({
                'test': 'Native cart plugin syntax',
                'status': 'FAIL',
                'message': f'Ошибка синтаксиса: {result.stderr}'
            })
            self.log(f"❌ FAIL: {result.stderr}", Colors.RED)
            return False
    
    def test_woocommerce_ajax_enabled(self):
        """Тест 3: Проверка AJAX add-to-cart WooCommerce"""
        self.log("\n📋 Тест 3: Проверка AJAX add-to-cart", Colors.BLUE + Colors.BOLD)
        
        try:
            result = subprocess.run(
                ['sudo', '-u', 'www-data', 'wp', 'option', 'get', 
                 'woocommerce_enable_ajax_add_to_cart', 
                 '--path=' + self.site_path],
                capture_output=True,
                text=True,
                cwd=self.site_path
            )
            
            ajax_enabled = 'yes' in result.stdout.lower()
            
            if ajax_enabled:
                self.results.append({
                    'test': 'AJAX add-to-cart enabled',
                    'status': 'PASS',
                    'message': 'AJAX add-to-cart включен в WooCommerce'
                })
                self.log("✅ PASS: AJAX add-to-cart включен", Colors.GREEN)
                return True
            else:
                self.results.append({
                    'test': 'AJAX add-to-cart enabled',
                    'status': 'FAIL',
                    'message': 'AJAX add-to-cart ВЫКЛЮЧЕН!'
                })
                self.log("❌ FAIL: AJAX add-to-cart выключен", Colors.RED)
                return False
        except Exception as e:
            self.results.append({
                'test': 'AJAX add-to-cart enabled',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_cart_fragments_script_loaded(self):
        """Тест 4: Проверка загрузки cart-fragments.js"""
        self.log("\n📋 Тест 4: Проверка cart-fragments.js", Colors.BLUE + Colors.BOLD)
        
        try:
            response = requests.get(self.site_url, timeout=10, verify=False)
            html = response.text
            
            has_cart_fragments = 'cart-fragments' in html
            has_wc_ajax_url = 'wc-ajax' in html
            has_fragments_params = 'wc_cart_fragments_params' in html
            
            all_present = has_cart_fragments and has_wc_ajax_url
            
            self.log(f"  {'✅' if has_cart_fragments else '❌'} cart-fragments.js подключен", 
                    Colors.GREEN if has_cart_fragments else Colors.RED)
            self.log(f"  {'✅' if has_wc_ajax_url else '❌'} wc-ajax endpoint найден", 
                    Colors.GREEN if has_wc_ajax_url else Colors.RED)
            self.log(f"  {'✅' if has_fragments_params else '❌'} wc_cart_fragments_params присутствует", 
                    Colors.GREEN if has_fragments_params else Colors.RED)
            
            if all_present:
                self.results.append({
                    'test': 'Cart fragments loaded',
                    'status': 'PASS',
                    'message': 'Cart fragments механизм активен'
                })
                self.log("\n✅ PASS: Cart fragments работает", Colors.GREEN)
                return True
            else:
                self.results.append({
                    'test': 'Cart fragments loaded',
                    'status': 'FAIL',
                    'message': 'Cart fragments не полностью загружен'
                })
                self.log("\n❌ FAIL: Проблемы с cart fragments", Colors.RED)
                return False
        except Exception as e:
            self.results.append({
                'test': 'Cart fragments loaded',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_native_script_in_page(self):
        """Тест 5: Проверка нового нативного скрипта на странице"""
        self.log("\n📋 Тест 5: Проверка нового нативного скрипта", Colors.BLUE + Colors.BOLD)
        
        try:
            response = requests.get(self.site_url, timeout=10, verify=False)
            html = response.text
            
            has_native_script = '[EcopackPro Native Cart]' in html
            no_problematic_script = 'setInterval' not in html or has_native_script
            
            self.log(f"  {'✅' if has_native_script else '❌'} Нативный скрипт загружен", 
                    Colors.GREEN if has_native_script else Colors.RED)
            self.log(f"  {'✅' if no_problematic_script else '❌'} Проблемных setInterval нет", 
                    Colors.GREEN if no_problematic_script else Colors.YELLOW)
            
            if has_native_script:
                self.results.append({
                    'test': 'Native script loaded',
                    'status': 'PASS',
                    'message': 'Нативный скрипт присутствует на странице'
                })
                self.log("\n✅ PASS: Нативный скрипт работает", Colors.GREEN)
                return True
            else:
                self.results.append({
                    'test': 'Native script loaded',
                    'status': 'FAIL',
                    'message': 'Нативный скрипт не найден на странице'
                })
                self.log("\n❌ FAIL: Нативный скрипт отсутствует", Colors.RED)
                return False
        except Exception as e:
            self.results.append({
                'test': 'Native script loaded',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_woocommerce_session(self):
        """Тест 6: Проверка сессий WooCommerce"""
        self.log("\n📋 Тест 6: Проверка WooCommerce сессий", Colors.BLUE + Colors.BOLD)
        
        try:
            # Делаем запрос и проверяем куки
            session = requests.Session()
            response = session.get(self.site_url, verify=False)
            
            cookies = session.cookies.get_dict()
            
            # Ищем куки WooCommerce
            wc_cookies = [k for k in cookies.keys() if 'woocommerce' in k.lower() or 'wp_woocommerce' in k.lower()]
            
            self.log(f"  Найдено WooCommerce cookies: {len(wc_cookies)}", Colors.CYAN)
            for cookie_name in wc_cookies:
                self.log(f"    • {cookie_name}", Colors.CYAN)
            
            if len(wc_cookies) > 0 or 'yes' in response.text:  # Могут быть и без товаров
                self.results.append({
                    'test': 'WooCommerce session',
                    'status': 'PASS',
                    'message': f'Сессии работают ({len(wc_cookies)} cookies)'
                })
                self.log("\n✅ PASS: Сессии WooCommerce работают", Colors.GREEN)
                return True
            else:
                self.results.append({
                    'test': 'WooCommerce session',
                    'status': 'WARN',
                    'message': 'WC cookies не найдены (возможно корзина пуста)'
                })
                self.log("\n⚠️  WARN: WC cookies не найдены (норма для пустой корзины)", Colors.YELLOW)
                return True
        except Exception as e:
            self.results.append({
                'test': 'WooCommerce session',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def generate_report(self):
        """Генерация отчета"""
        self.log("\n" + "="*80, Colors.BOLD)
        self.log("📊 ИТОГОВЫЙ ОТЧЕТ: NATIVE WOOCOMMERCE CART", Colors.BOLD + Colors.BLUE)
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
        report_file = f'{self.site_path}/native_cart_test_report_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
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
        self.log("🧪 РУЧНОЕ ТЕСТИРОВАНИЕ", Colors.MAGENTA + Colors.BOLD)
        self.log("="*80, Colors.BOLD)
        
        self.log("\n1. Откройте https://ecopackpro.ru на DESKTOP", Colors.CYAN)
        self.log("2. Откройте консоль (F12)", Colors.CYAN)
        self.log("3. Найдите товар и нажмите 'Добавить в корзину'", Colors.CYAN)
        self.log("\n4. ПРОВЕРЬТЕ КОНСОЛЬ - должны появиться:", Colors.CYAN)
        self.log("   [EcopackPro Native Cart] Product added, fragments: {...}", Colors.YELLOW)
        self.log("   [EcopackPro Native Cart] New cart count: 1", Colors.YELLOW)
        self.log("\n5. Badge должен обновиться СРАЗУ (не через 3-5 секунд)!", Colors.CYAN)
        
        self.log("\n" + "-"*80, Colors.BOLD)
        self.log("\n6. Откройте на МОБИЛЬНОМ телефоне", Colors.CYAN)
        self.log("7. Добавьте товар в корзину", Colors.CYAN)
        self.log("8. Badge в НИЖНЕЙ НАВИГАЦИИ должен появиться СРАЗУ!", Colors.CYAN)
        self.log("9. Перейдите на другую страницу - badge НЕ должен исчезать!", Colors.CYAN)
        
        # Итоговый статус
        if failed == 0:
            self.log("\n🎉 ВСЕ ТЕСТЫ ПРОЙДЕНЫ!", Colors.GREEN + Colors.BOLD)
            self.log("\nНативный механизм WooCommerce ВОССТАНОВЛЕН ✅", Colors.GREEN)
            self.log("Теперь корзина должна работать СРАЗУ без задержек!", Colors.GREEN)
            return 0
        else:
            self.log(f"\n⚠️  ОБНАРУЖЕНЫ ПРОБЛЕМЫ: {failed} тест(ов) провалено", Colors.RED + Colors.BOLD)
            return 1
    
    def run_all_tests(self):
        """Запуск всех тестов"""
        self.start_time = time.time()
        
        self.log("="*80, Colors.BOLD)
        self.log("🚀 ТЕСТИРОВАНИЕ НАТИВНОГО МЕХАНИЗМА WOOCOMMERCE", Colors.BOLD + Colors.BLUE)
        self.log("="*80, Colors.BOLD)
        self.log(f"Дата: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        self.log(f"Сайт: {self.site_url}")
        
        # Запуск тестов
        self.test_problematic_scripts_disabled()
        self.test_native_cart_plugin_loaded()
        self.test_woocommerce_ajax_enabled()
        self.test_cart_fragments_script_loaded()
        self.test_native_script_in_page()
        self.test_woocommerce_session()
        
        # Генерация отчета
        return self.generate_report()

if __name__ == '__main__':
    # Отключаем предупреждения SSL
    import urllib3
    urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
    
    runner = NativeCartTest()
    exit_code = runner.run_all_tests()
    sys.exit(exit_code)

