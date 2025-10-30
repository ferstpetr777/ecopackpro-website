#!/usr/bin/env python3
"""
Тест v3: ЗЕЛЁНЫЙ badge + синхронизация между страницами
"""

import sys
import requests
import json
import re
from datetime import datetime

class Colors:
    GREEN = '\033[92m'
    RED = '\033[91m'
    YELLOW = '\033[93m'
    BLUE = '\033[94m'
    CYAN = '\033[96m'
    RESET = '\033[0m'
    BOLD = '\033[1m'

class GreenBadgeTest:
    def __init__(self):
        self.site_url = 'https://ecopackpro.ru'
        self.results = []
        
    def log(self, message, color=Colors.RESET):
        print(f"{color}{message}{Colors.RESET}")
    
    def test_v3_loaded(self):
        """Тест 1: Проверка загрузки v3"""
        self.log("\n📋 Тест 1: Загрузка mobile-cart-badge-v3-green.js", Colors.BLUE + Colors.BOLD)
        
        try:
            response = requests.get(self.site_url, timeout=10, verify=False)
            html = response.text
            
            has_v3 = 'mobile-cart-badge-v3-green.js' in html or 'mobile-cart-badge-v3' in html
            
            self.log(f"  {'✅' if has_v3 else '❌'} v3.0 подключен в HTML", 
                    Colors.GREEN if has_v3 else Colors.RED)
            
            if has_v3:
                # Проверяем сам файл
                js_url = f'{self.site_url}/wp-content/mu-plugins/mobile-cart-badge-v3-green.js'
                js_response = requests.get(js_url, timeout=10, verify=False)
                
                if js_response.status_code == 200:
                    js = js_response.text
                    
                    has_v3_marker = 'Mobile Cart Badge v3.0' in js or 'v3 GREEN' in js
                    has_green_marker = 'ЗЕЛЁНЫЙ' in js or 'GREEN' in js
                    has_sync = 'localStorage' in js and 'ecopackpro_cart_count_sync' in js
                    
                    self.log(f"  {'✅' if has_v3_marker else '❌'} Версия 3.0 маркер", 
                            Colors.GREEN if has_v3_marker else Colors.RED)
                    self.log(f"  {'✅' if has_green_marker else '❌'} GREEN маркер", 
                            Colors.GREEN if has_green_marker else Colors.RED)
                    self.log(f"  {'✅' if has_sync else '❌'} Синхронизация localStorage", 
                            Colors.GREEN if has_sync else Colors.RED)
                    
                    if has_v3_marker and has_sync:
                        self.results.append({
                            'test': 'v3 loaded',
                            'status': 'PASS',
                            'message': 'v3.0 GREEN загружен с синхронизацией'
                        })
                        self.log("\n✅ PASS: v3.0 активен", Colors.GREEN)
                        return True
            
            self.results.append({
                'test': 'v3 loaded',
                'status': 'FAIL',
                'message': 'v3.0 не загружен'
            })
            self.log("\n❌ FAIL: v3.0 не найден", Colors.RED)
            return False
        except Exception as e:
            self.results.append({
                'test': 'v3 loaded',
                'status': 'FAIL',
                'message': str(e)
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_green_color_css(self):
        """Тест 2: Проверка зелёного цвета в CSS"""
        self.log("\n📋 Тест 2: ЗЕЛЁНЫЙ цвет badge (#00796B)", Colors.BLUE + Colors.BOLD)
        
        try:
            css_url = f'{self.site_url}/wp-content/mu-plugins/fix-all-cart-issues.css'
            response = requests.get(css_url, timeout=10, verify=False)
            
            if response.status_code == 200:
                css = response.text
                
                # Ищем зелёный цвет для mobile-cart-badge
                has_green_bg = '#00796B' in css
                has_green_comment = 'ЗЕЛЁНЫЙ' in css or 'GREEN' in css
                
                # Проверяем что красный цвет НЕ используется для mobile-cart-badge
                lines = css.split('\n')
                red_in_mobile_badge = False
                in_mobile_badge_block = False
                
                for line in lines:
                    if '.mobile-cart-badge' in line:
                        in_mobile_badge_block = True
                    if in_mobile_badge_block and '#ff4444' in line:
                        red_in_mobile_badge = True
                        break
                    if in_mobile_badge_block and '}' in line:
                        in_mobile_badge_block = False
                
                self.log(f"  {'✅' if has_green_bg else '❌'} Зелёный фон #00796B", 
                        Colors.GREEN if has_green_bg else Colors.RED)
                self.log(f"  {'✅' if has_green_comment else '❌'} Комментарий про зелёный", 
                        Colors.GREEN if has_green_comment else Colors.RED)
                self.log(f"  {'✅' if not red_in_mobile_badge else '❌'} Красный НЕ используется", 
                        Colors.GREEN if not red_in_mobile_badge else Colors.RED)
                
                if has_green_bg and not red_in_mobile_badge:
                    self.results.append({
                        'test': 'green color css',
                        'status': 'PASS',
                        'message': 'Badge имеет зелёный цвет'
                    })
                    self.log("\n✅ PASS: Цвет ЗЕЛЁНЫЙ", Colors.GREEN)
                    return True
                else:
                    self.results.append({
                        'test': 'green color css',
                        'status': 'FAIL',
                        'message': 'Цвет неправильный'
                    })
                    self.log("\n❌ FAIL: Цвет не зелёный", Colors.RED)
                    return False
        except Exception as e:
            self.results.append({
                'test': 'green color css',
                'status': 'FAIL',
                'message': str(e)
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_positioning_fixed(self):
        """Тест 3: Проверка позиционирования"""
        self.log("\n📋 Тест 3: Позиционирование (вместе с иконкой)", Colors.BLUE + Colors.BOLD)
        
        try:
            css_url = f'{self.site_url}/wp-content/mu-plugins/fix-all-cart-issues.css'
            response = requests.get(css_url, timeout=10, verify=False)
            
            if response.status_code == 200:
                css = response.text
                
                has_transform = 'transform: translate(50%, -50%)' in css
                has_relative_parent = 'position: relative' in css
                has_top_right_zero = 'top: 0px' in css and 'right: 0px' in css
                
                self.log(f"  {'✅' if has_transform else '❌'} Transform для углового позиционирования", 
                        Colors.GREEN if has_transform else Colors.RED)
                self.log(f"  {'✅' if has_relative_parent else '❌'} Relative на родителе", 
                        Colors.GREEN if has_relative_parent else Colors.RED)
                self.log(f"  {'✅' if has_top_right_zero else '❌'} Top/Right: 0px", 
                        Colors.GREEN if has_top_right_zero else Colors.RED)
                
                if has_transform and has_relative_parent:
                    self.results.append({
                        'test': 'positioning fixed',
                        'status': 'PASS',
                        'message': 'Позиционирование правильное'
                    })
                    self.log("\n✅ PASS: Badge в углу иконки", Colors.GREEN)
                    return True
                else:
                    self.results.append({
                        'test': 'positioning fixed',
                        'status': 'FAIL',
                        'message': 'Позиционирование неправильное'
                    })
                    self.log("\n❌ FAIL: Позиционирование проблемное", Colors.RED)
                    return False
        except Exception as e:
            self.results.append({
                'test': 'positioning fixed',
                'status': 'FAIL',
                'message': str(e)
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_sync_mechanism(self):
        """Тест 4: Проверка механизма синхронизации"""
        self.log("\n📋 Тест 4: Механизм синхронизации (localStorage)", Colors.BLUE + Colors.BOLD)
        
        try:
            js_url = f'{self.site_url}/wp-content/mu-plugins/mobile-cart-badge-v3-green.js'
            response = requests.get(js_url, timeout=10, verify=False)
            
            if response.status_code == 200:
                js = response.text
                
                has_storage_key = 'ecopackpro_cart_count_sync' in js
                has_set_item = 'setItem' in js
                has_get_item = 'getItem' in js
                has_timestamp = 'timestamp' in js
                has_storage_event = 'addEventListener' in js and 'storage' in js
                
                self.log(f"  {'✅' if has_storage_key else '❌'} Storage key определён", 
                        Colors.GREEN if has_storage_key else Colors.RED)
                self.log(f"  {'✅' if has_set_item else '❌'} setItem для записи", 
                        Colors.GREEN if has_set_item else Colors.RED)
                self.log(f"  {'✅' if has_get_item else '❌'} getItem для чтения", 
                        Colors.GREEN if has_get_item else Colors.RED)
                self.log(f"  {'✅' if has_timestamp else '❌'} Timestamp для актуальности", 
                        Colors.GREEN if has_timestamp else Colors.RED)
                self.log(f"  {'✅' if has_storage_event else '❌'} Storage event listener", 
                        Colors.GREEN if has_storage_event else Colors.RED)
                
                if has_storage_key and has_set_item and has_get_item:
                    self.results.append({
                        'test': 'sync mechanism',
                        'status': 'PASS',
                        'message': 'Синхронизация через localStorage работает'
                    })
                    self.log("\n✅ PASS: Синхронизация реализована", Colors.GREEN)
                    return True
                else:
                    self.results.append({
                        'test': 'sync mechanism',
                        'status': 'FAIL',
                        'message': 'Синхронизация неполная'
                    })
                    self.log("\n❌ FAIL: Синхронизация проблемная", Colors.RED)
                    return False
        except Exception as e:
            self.results.append({
                'test': 'sync mechanism',
                'status': 'FAIL',
                'message': str(e)
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def generate_report(self):
        """Генерация отчета"""
        self.log("\n" + "="*80, Colors.BOLD)
        self.log("📊 ОТЧЁТ: ЗЕЛЁНЫЙ BADGE + СИНХРОНИЗАЦИЯ", Colors.BOLD + Colors.BLUE)
        self.log("="*80, Colors.BOLD)
        
        total = len(self.results)
        passed = sum(1 for r in self.results if r['status'] == 'PASS')
        failed = sum(1 for r in self.results if r['status'] == 'FAIL')
        
        self.log(f"\nВсего тестов: {total}")
        self.log(f"Успешно: {passed}", Colors.GREEN)
        if failed > 0:
            self.log(f"Провалено: {failed}", Colors.RED)
        
        self.log("\nДетали:", Colors.BOLD)
        for result in self.results:
            status_color = Colors.GREEN if result['status'] == 'PASS' else Colors.RED
            self.log(f"\n  [{result['status']}] {result['test']}", status_color)
            self.log(f"    └─ {result['message']}")
        
        # Инструкции
        self.log("\n" + "="*80, Colors.BOLD)
        self.log("📱 ПРОВЕРКА НА МОБИЛЬНОМ ТЕЛЕФОНЕ", Colors.CYAN + Colors.BOLD)
        self.log("="*80, Colors.BOLD)
        
        self.log("\n1. Откройте https://ecopackpro.ru (главная)", Colors.CYAN)
        self.log("2. Смотрите на НИЖНЮЮ ПАНЕЛЬ", Colors.CYAN)
        self.log("3. ✅ Badge должен быть ЗЕЛЁНЫЙ (не красный!)", Colors.GREEN)
        self.log("4. ✅ Badge показывает 15 (не 3!)", Colors.GREEN)
        self.log("5. Перейдите в корзину", Colors.CYAN)
        self.log("6. ✅ Badge по-прежнему показывает 15", Colors.GREEN)
        self.log("7. ✅ Цвет остаётся ЗЕЛЁНЫЙ", Colors.GREEN)
        
        self.log("\n🐛 ОТЛАДКА В КОНСОЛИ:", Colors.YELLOW + Colors.BOLD)
        self.log("console.log(MobileCartBadge.currentCount);  // Должно быть: 15")
        self.log("console.log(localStorage.getItem('ecopackpro_cart_count_sync'));")
        
        if failed == 0:
            self.log("\n🎉 ВСЕ ТЕСТЫ ПРОЙДЕНЫ!", Colors.GREEN + Colors.BOLD)
            return 0
        else:
            self.log(f"\n⚠️  ПРОБЛЕМЫ: {failed} тест(ов)", Colors.RED + Colors.BOLD)
            return 1
    
    def run_all_tests(self):
        """Запуск всех тестов"""
        self.log("="*80, Colors.BOLD)
        self.log("🚀 ТЕСТ v3.0: ЗЕЛЁНЫЙ BADGE + СИНХРОНИЗАЦИЯ", Colors.BOLD + Colors.BLUE)
        self.log("="*80, Colors.BOLD)
        self.log(f"Дата: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        self.log(f"Сайт: {self.site_url}")
        
        self.test_v3_loaded()
        self.test_green_color_css()
        self.test_positioning_fixed()
        self.test_sync_mechanism()
        
        return self.generate_report()

if __name__ == '__main__':
    import urllib3
    urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
    
    runner = GreenBadgeTest()
    exit_code = runner.run_all_tests()
    sys.exit(exit_code)

