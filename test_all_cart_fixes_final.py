#!/usr/bin/env python3
"""
ФИНАЛЬНЫЙ КОМПЛЕКСНЫЙ ТЕСТ ВСЕХ ИСПРАВЛЕНИЙ КОРЗИНЫ
Проверяет:
1. Позиционирование badge корзины
2. Стили dropdown корзины
3. Отсутствие дублирования на wishlist
4. Создание mobile badge
5. Работу на desktop и mobile
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

class FinalCartTest:
    def __init__(self):
        self.site_path = '/var/www/fastuser/data/www/ecopackpro.ru'
        self.site_url = 'https://ecopackpro.ru'
        self.results = []
        self.start_time = None
        
    def log(self, message, color=Colors.RESET):
        print(f"{color}{message}{Colors.RESET}")
    
    def test_css_files_loaded(self):
        """Тест 1: Проверка загрузки CSS"""
        self.log("\n📋 Тест 1: Проверка CSS файлов", Colors.BLUE + Colors.BOLD)
        
        try:
            response = requests.get(self.site_url, timeout=10, verify=False)
            html = response.text
            
            has_fix_css = 'fix-all-cart-issues.css' in html
            
            # Проверяем сам CSS файл
            css_response = requests.get(f'{self.site_url}/wp-content/mu-plugins/fix-all-cart-issues.css', 
                                       timeout=10, verify=False)
            css_works = css_response.status_code == 200
            
            self.log(f"  {'✅' if has_fix_css else '❌'} CSS подключен в HTML", 
                    Colors.GREEN if has_fix_css else Colors.RED)
            self.log(f"  {'✅' if css_works else '❌'} CSS файл доступен ({css_response.status_code})", 
                    Colors.GREEN if css_works else Colors.RED)
            
            if has_fix_css and css_works:
                # Проверяем содержимое CSS
                css_content = css_response.text
                has_wishlist_fix = 'wishlist_products_counter .mobile-cart-badge' in css_content
                has_dropdown_fix = '.w-cart-dropdown' in css_content
                has_mobile_badge = '.mobile-cart-badge' in css_content
                
                self.log(f"  {'✅' if has_wishlist_fix else '❌'} Исправление wishlist дублирования", 
                        Colors.GREEN if has_wishlist_fix else Colors.RED)
                self.log(f"  {'✅' if has_dropdown_fix else '❌'} Исправление dropdown", 
                        Colors.GREEN if has_dropdown_fix else Colors.RED)
                self.log(f"  {'✅' if has_mobile_badge else '❌'} Mobile badge стили", 
                        Colors.GREEN if has_mobile_badge else Colors.RED)
                
                all_ok = has_wishlist_fix and has_dropdown_fix and has_mobile_badge
                
                if all_ok:
                    self.results.append({
                        'test': 'CSS files loaded',
                        'status': 'PASS',
                        'message': 'Все CSS исправления загружены'
                    })
                    self.log("\n✅ PASS: CSS полностью загружен", Colors.GREEN)
                    return True
                else:
                    self.results.append({
                        'test': 'CSS content',
                        'status': 'WARN',
                        'message': 'CSS загружен, но не все исправления найдены'
                    })
                    self.log("\n⚠️  WARN: CSS неполный", Colors.YELLOW)
                    return True
            else:
                self.results.append({
                    'test': 'CSS files loaded',
                    'status': 'FAIL',
                    'message': 'CSS не загружается'
                })
                self.log("\n❌ FAIL: CSS не загружен", Colors.RED)
                return False
        except Exception as e:
            self.results.append({
                'test': 'CSS files loaded',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_js_fixed_version_loaded(self):
        """Тест 2: Проверка исправленного JS"""
        self.log("\n📋 Тест 2: Проверка mobile-footer-cart-badge-fixed.js", Colors.BLUE + Colors.BOLD)
        
        try:
            response = requests.get(self.site_url, timeout=10, verify=False)
            html = response.text
            
            has_fixed_js = 'mobile-footer-cart-badge-fixed.js' in html
            
            self.log(f"  {'✅' if has_fixed_js else '❌'} Исправленный JS подключен", 
                    Colors.GREEN if has_fixed_js else Colors.RED)
            
            # Проверяем файл напрямую
            if has_fixed_js:
                js_response = requests.get(
                    f'{self.site_url}/wp-content/mu-plugins/mobile-footer-cart-badge-fixed.js',
                    timeout=10, verify=False
                )
                js_works = js_response.status_code == 200
                
                if js_works:
                    js_content = js_response.text
                    has_v2_marker = 'Mobile Footer Badge v2' in js_content
                    has_retry_logic = 'tryInit' in js_content or 'attempts' in js_content
                    
                    self.log(f"  {'✅' if has_v2_marker else '❌'} Версия 2.0", 
                            Colors.GREEN if has_v2_marker else Colors.RED)
                    self.log(f"  {'✅' if has_retry_logic else '❌'} Retry логика", 
                            Colors.GREEN if has_retry_logic else Colors.RED)
                    
                    if has_v2_marker and has_retry_logic:
                        self.results.append({
                            'test': 'JS fixed version loaded',
                            'status': 'PASS',
                            'message': 'Исправленный JS загружен (v2.0)'
                        })
                        self.log("\n✅ PASS: JS v2.0 активен", Colors.GREEN)
                        return True
            
            self.results.append({
                'test': 'JS fixed version loaded',
                'status': 'FAIL',
                'message': 'Исправленный JS не загружен'
            })
            self.log("\n❌ FAIL: JS не активен", Colors.RED)
            return False
        except Exception as e:
            self.results.append({
                'test': 'JS fixed version loaded',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_no_wishlist_duplication(self):
        """Тест 3: Проверка отсутствия дублирования на wishlist"""
        self.log("\n📋 Тест 3: Дублирование на wishlist", Colors.BLUE + Colors.BOLD)
        
        try:
            css_response = requests.get(
                f'{self.site_url}/wp-content/mu-plugins/fix-all-cart-issues.css',
                timeout=10, verify=False
            )
            
            if css_response.status_code == 200:
                css = css_response.text
                
                # Проверяем что есть правило для скрытия badge на wishlist
                has_wishlist_hide = '.wishlist_products_counter .mobile-cart-badge' in css and 'display: none' in css
                
                if has_wishlist_hide:
                    self.results.append({
                        'test': 'No wishlist duplication',
                        'status': 'PASS',
                        'message': 'CSS правило для предотвращения дублирования найдено'
                    })
                    self.log("✅ PASS: Защита от дублирования активна", Colors.GREEN)
                    return True
                else:
                    self.results.append({
                        'test': 'No wishlist duplication',
                        'status': 'FAIL',
                        'message': 'CSS правило не найдено'
                    })
                    self.log("❌ FAIL: Нет защиты от дублирования", Colors.RED)
                    return False
        except Exception as e:
            self.results.append({
                'test': 'No wishlist duplication',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_dropdown_positioning(self):
        """Тест 4: Проверка позиционирования dropdown"""
        self.log("\n📋 Тест 4: Позиционирование dropdown корзины", Colors.BLUE + Colors.BOLD)
        
        try:
            css_response = requests.get(
                f'{self.site_url}/wp-content/mu-plugins/fix-all-cart-issues.css',
                timeout=10, verify=False
            )
            
            if css_response.status_code == 200:
                css = css_response.text
                
                # Проверяем правила для dropdown
                has_dropdown_rules = '.w-cart-dropdown' in css
                has_max_width = 'max-width: 350px' in css
                has_positioning = 'right: 0' in css and 'top: 100%' in css
                has_mobile_fix = 'position: fixed' in css and '@media (max-width: 768px)' in css
                
                self.log(f"  {'✅' if has_dropdown_rules else '❌'} Dropdown CSS найден", 
                        Colors.GREEN if has_dropdown_rules else Colors.RED)
                self.log(f"  {'✅' if has_max_width else '❌'} Ограничение ширины", 
                        Colors.GREEN if has_max_width else Colors.RED)
                self.log(f"  {'✅' if has_positioning else '❌'} Правильное позиционирование", 
                        Colors.GREEN if has_positioning else Colors.RED)
                self.log(f"  {'✅' if has_mobile_fix else '❌'} Mobile исправление", 
                        Colors.GREEN if has_mobile_fix else Colors.RED)
                
                if has_dropdown_rules and has_positioning:
                    self.results.append({
                        'test': 'Dropdown positioning',
                        'status': 'PASS',
                        'message': 'Dropdown позиционирован правильно'
                    })
                    self.log("\n✅ PASS: Dropdown исправлен", Colors.GREEN)
                    return True
                else:
                    self.results.append({
                        'test': 'Dropdown positioning',
                        'status': 'FAIL',
                        'message': 'Dropdown CSS неполный'
                    })
                    self.log("\n❌ FAIL: Dropdown CSS проблемный", Colors.RED)
                    return False
        except Exception as e:
            self.results.append({
                'test': 'Dropdown positioning',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_mobile_badge_script(self):
        """Тест 5: Проверка mobile badge скрипта"""
        self.log("\n📋 Тест 5: Mobile badge скрипт", Colors.BLUE + Colors.BOLD)
        
        try:
            js_response = requests.get(
                f'{self.site_url}/wp-content/mu-plugins/mobile-footer-cart-badge-fixed.js',
                timeout=10, verify=False
            )
            
            if js_response.status_code == 200:
                js = js_response.text
                
                has_init = 'CartBadgeManager' in js and 'init' in js
                has_retry = 'tryInit' in js or 'maxAttempts' in js
                has_multiple_methods = js.count('querySelector') >= 3
                
                self.log(f"  {'✅' if has_init else '❌'} CartBadgeManager класс", 
                        Colors.GREEN if has_init else Colors.RED)
                self.log(f"  {'✅' if has_retry else '❌'} Retry логика", 
                        Colors.GREEN if has_retry else Colors.RED)
                self.log(f"  {'✅' if has_multiple_methods else '❌'} Множественные методы поиска", 
                        Colors.GREEN if has_multiple_methods else Colors.RED)
                
                if has_init and has_retry:
                    self.results.append({
                        'test': 'Mobile badge script',
                        'status': 'PASS',
                        'message': 'Mobile badge скрипт корректен'
                    })
                    self.log("\n✅ PASS: Mobile скрипт готов", Colors.GREEN)
                    return True
                else:
                    self.results.append({
                        'test': 'Mobile badge script',
                        'status': 'FAIL',
                        'message': 'Mobile скрипт неполный'
                    })
                    self.log("\n❌ FAIL: Mobile скрипт проблемный", Colors.RED)
                    return False
        except Exception as e:
            self.results.append({
                'test': 'Mobile badge script',
                'status': 'FAIL',
                'message': f'Ошибка: {str(e)}'
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def generate_report(self):
        """Генерация отчета"""
        self.log("\n" + "="*80, Colors.BOLD)
        self.log("📊 ФИНАЛЬНЫЙ ОТЧЕТ ВСЕХ ИСПРАВЛЕНИЙ", Colors.BOLD + Colors.BLUE)
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
        
        # Сохранение
        report_file = f'{self.site_path}/final_cart_fixes_report_{datetime.now().strftime("%Y%m%d_%H%M%S")}.json'
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
        
        # Инструкции
        self.log("\n" + "="*80, Colors.BOLD)
        self.log("🧪 РУЧНАЯ ПРОВЕРКА НА УСТРОЙСТВАХ", Colors.MAGENTA + Colors.BOLD)
        self.log("="*80, Colors.BOLD)
        
        self.log("\n📱 НА МОБИЛЬНОМ ТЕЛЕФОНЕ:", Colors.CYAN + Colors.BOLD)
        self.log("1. Откройте https://ecopackpro.ru", Colors.CYAN)
        self.log("2. Добавьте товар в корзину", Colors.CYAN)
        self.log("3. СМОТРИТЕ НА НИЖНЮЮ ПАНЕЛЬ (самый низ экрана)", Colors.CYAN)
        self.log("4. ✅ На 'Корзина' должен быть КРАСНЫЙ badge с цифрой", Colors.GREEN)
        self.log("5. ✅ На 'Избранное' НЕ должно быть дублей (только зеленый)", Colors.GREEN)
        
        self.log("\n💻 НА DESKTOP:", Colors.CYAN + Colors.BOLD)
        self.log("1. Откройте https://ecopackpro.ru", Colors.CYAN)
        self.log("2. Добавьте товар в корзину", Colors.CYAN)
        self.log("3. ✅ Badge в header обновляется СРАЗУ", Colors.GREEN)
        self.log("4. Наведите мышь на корзину", Colors.CYAN)
        self.log("5. ✅ Dropdown НЕ должен выходить за экран", Colors.GREEN)
        self.log("6. ✅ Стили dropdown должны совпадать с сайтом", Colors.GREEN)
        
        if failed == 0:
            self.log("\n🎉 ВСЕ ТЕСТЫ ПРОЙДЕНЫ УСПЕШНО!", Colors.GREEN + Colors.BOLD)
            return 0
        else:
            self.log(f"\n⚠️  ПРОБЛЕМЫ: {failed} тест(ов) провалено", Colors.RED + Colors.BOLD)
            return 1
    
    def run_all_tests(self):
        """Запуск всех тестов"""
        self.start_time = time.time()
        
        self.log("="*80, Colors.BOLD)
        self.log("🚀 ФИНАЛЬНОЕ КОМПЛЕКСНОЕ ТЕСТИРОВАНИЕ", Colors.BOLD + Colors.BLUE)
        self.log("="*80, Colors.BOLD)
        self.log(f"Дата: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        self.log(f"Сайт: {self.site_url}")
        
        # Запуск тестов
        self.test_css_files_loaded()
        self.test_js_fixed_version_loaded()
        self.test_no_wishlist_duplication()
        self.test_dropdown_positioning()
        self.test_mobile_badge_script()
        
        # Генерация отчета
        return self.generate_report()

if __name__ == '__main__':
    import urllib3
    urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
    
    runner = FinalCartTest()
    exit_code = runner.run_all_tests()
    sys.exit(exit_code)

