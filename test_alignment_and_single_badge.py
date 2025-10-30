#!/usr/bin/env python3
"""
Тест: Выравнивание иконки корзины + только ОДИН ЗЕЛЁНЫЙ badge
"""

import sys
import requests
from datetime import datetime

class Colors:
    GREEN = '\033[92m'
    RED = '\033[91m'
    YELLOW = '\033[93m'
    BLUE = '\033[94m'
    CYAN = '\033[96m'
    RESET = '\033[0m'
    BOLD = '\033[1m'

class AlignmentBadgeTest:
    def __init__(self):
        self.site_url = 'https://ecopackpro.ru'
        self.results = []
        
    def log(self, message, color=Colors.RESET):
        print(f"{color}{message}{Colors.RESET}")
    
    def test_vertical_align_css(self):
        """Тест 1: Проверка vertical-align в CSS"""
        self.log("\n📋 Тест 1: Выравнивание иконки корзины", Colors.BLUE + Colors.BOLD)
        
        try:
            css_url = f'{self.site_url}/wp-content/mu-plugins/fix-all-cart-issues.css'
            response = requests.get(css_url, timeout=10, verify=False)
            
            if response.status_code == 200:
                css = response.text
                
                has_vertical_align = 'vertical-align: middle' in css
                has_align_items = 'align-items: center' in css
                has_flex = 'display: flex' in css
                
                self.log(f"  {'✅' if has_vertical_align else '❌'} vertical-align: middle", 
                        Colors.GREEN if has_vertical_align else Colors.RED)
                self.log(f"  {'✅' if has_align_items else '❌'} align-items: center", 
                        Colors.GREEN if has_align_items else Colors.RED)
                self.log(f"  {'✅' if has_flex else '❌'} display: flex (для навигации)", 
                        Colors.GREEN if has_flex else Colors.RED)
                
                if has_vertical_align and has_align_items:
                    self.results.append({
                        'test': 'vertical align css',
                        'status': 'PASS',
                        'message': 'Выравнивание реализовано'
                    })
                    self.log("\n✅ PASS: Выравнивание в CSS", Colors.GREEN)
                    return True
                else:
                    self.results.append({
                        'test': 'vertical align css',
                        'status': 'FAIL',
                        'message': 'Выравнивание неполное'
                    })
                    self.log("\n❌ FAIL: Выравнивание проблемное", Colors.RED)
                    return False
        except Exception as e:
            self.results.append({
                'test': 'vertical align css',
                'status': 'FAIL',
                'message': str(e)
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_red_badge_hidden(self):
        """Тест 2: Проверка скрытия красного badge"""
        self.log("\n📋 Тест 2: Скрытие КРАСНОГО badge", Colors.BLUE + Colors.BOLD)
        
        try:
            css_url = f'{self.site_url}/wp-content/mu-plugins/fix-all-cart-issues.css'
            response = requests.get(css_url, timeout=10, verify=False)
            
            if response.status_code == 200:
                css = response.text
                
                # Ищем правила скрытия .w-cart-quantity в mobile footer
                has_hide_rule = '.l-subheader.at_bottom .w-cart-quantity' in css
                has_display_none = 'display: none' in css
                
                # Проверяем комментарий
                has_comment = 'УБИРАЕМ КРАСНЫЙ' in css or 'Скрываем КРАСНЫЙ badge' in css
                
                self.log(f"  {'✅' if has_hide_rule else '❌'} Правило для .w-cart-quantity", 
                        Colors.GREEN if has_hide_rule else Colors.RED)
                self.log(f"  {'✅' if has_display_none else '❌'} display: none присутствует", 
                        Colors.GREEN if has_display_none else Colors.RED)
                self.log(f"  {'✅' if has_comment else '❌'} Комментарий про красный badge", 
                        Colors.GREEN if has_comment else Colors.RED)
                
                if has_hide_rule:
                    self.results.append({
                        'test': 'red badge hidden',
                        'status': 'PASS',
                        'message': 'Красный badge скрыт в CSS'
                    })
                    self.log("\n✅ PASS: Красный badge скрыт", Colors.GREEN)
                    return True
                else:
                    self.results.append({
                        'test': 'red badge hidden',
                        'status': 'FAIL',
                        'message': 'Правило скрытия не найдено'
                    })
                    self.log("\n❌ FAIL: Правило не найдено", Colors.RED)
                    return False
        except Exception as e:
            self.results.append({
                'test': 'red badge hidden',
                'status': 'FAIL',
                'message': str(e)
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_green_badge_visible(self):
        """Тест 3: Проверка что зелёный badge НЕ скрыт"""
        self.log("\n📋 Тест 3: ЗЕЛЁНЫЙ badge остаётся видимым", Colors.BLUE + Colors.BOLD)
        
        try:
            css_url = f'{self.site_url}/wp-content/mu-plugins/fix-all-cart-issues.css'
            response = requests.get(css_url, timeout=10, verify=False)
            
            if response.status_code == 200:
                css = response.text
                
                # Проверяем что .mobile-cart-badge НЕ скрыт в корзине
                lines = css.split('\n')
                mobile_badge_hidden = False
                
                for i, line in enumerate(lines):
                    if '.mobile-cart-badge' in line and 'display: none' in line:
                        # Проверяем что это НЕ для wishlist
                        if 'wishlist' not in line.lower():
                            mobile_badge_hidden = True
                            break
                
                has_green_color = '#00796B' in css
                
                self.log(f"  {'✅' if not mobile_badge_hidden else '❌'} mobile-cart-badge НЕ скрыт", 
                        Colors.GREEN if not mobile_badge_hidden else Colors.RED)
                self.log(f"  {'✅' if has_green_color else '❌'} Зелёный цвет #00796B", 
                        Colors.GREEN if has_green_color else Colors.RED)
                
                if not mobile_badge_hidden and has_green_color:
                    self.results.append({
                        'test': 'green badge visible',
                        'status': 'PASS',
                        'message': 'Зелёный badge видимый'
                    })
                    self.log("\n✅ PASS: Зелёный badge активен", Colors.GREEN)
                    return True
                else:
                    self.results.append({
                        'test': 'green badge visible',
                        'status': 'FAIL',
                        'message': 'Зелёный badge может быть скрыт'
                    })
                    self.log("\n❌ FAIL: Проблема с зелёным badge", Colors.RED)
                    return False
        except Exception as e:
            self.results.append({
                'test': 'green badge visible',
                'status': 'FAIL',
                'message': str(e)
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def test_flexbox_alignment(self):
        """Тест 4: Проверка flexbox для навигации"""
        self.log("\n📋 Тест 4: Flexbox выравнивание навигации", Colors.BLUE + Colors.BOLD)
        
        try:
            css_url = f'{self.site_url}/wp-content/mu-plugins/fix-all-cart-issues.css'
            response = requests.get(css_url, timeout=10, verify=False)
            
            if response.status_code == 200:
                css = response.text
                
                has_flex = 'display: flex' in css and '.l-subheader.at_bottom' in css
                has_align_items = 'align-items: center' in css
                has_justify = 'justify-content: space-around' in css
                
                self.log(f"  {'✅' if has_flex else '❌'} display: flex для нижней панели", 
                        Colors.GREEN if has_flex else Colors.RED)
                self.log(f"  {'✅' if has_align_items else '❌'} align-items: center", 
                        Colors.GREEN if has_align_items else Colors.RED)
                self.log(f"  {'✅' if has_justify else '❌'} justify-content: space-around", 
                        Colors.GREEN if has_justify else Colors.RED)
                
                if has_flex and has_align_items:
                    self.results.append({
                        'test': 'flexbox alignment',
                        'status': 'PASS',
                        'message': 'Flexbox для выравнивания настроен'
                    })
                    self.log("\n✅ PASS: Flexbox активен", Colors.GREEN)
                    return True
                else:
                    self.results.append({
                        'test': 'flexbox alignment',
                        'status': 'FAIL',
                        'message': 'Flexbox не настроен'
                    })
                    self.log("\n❌ FAIL: Flexbox проблема", Colors.RED)
                    return False
        except Exception as e:
            self.results.append({
                'test': 'flexbox alignment',
                'status': 'FAIL',
                'message': str(e)
            })
            self.log(f"❌ FAIL: {str(e)}", Colors.RED)
            return False
    
    def generate_report(self):
        """Генерация отчета"""
        self.log("\n" + "="*80, Colors.BOLD)
        self.log("📊 ОТЧЁТ: ВЫРАВНИВАНИЕ + ОДИН BADGE", Colors.BOLD + Colors.BLUE)
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
        self.log("📱 ПРОВЕРКА НА МОБИЛЬНОМ", Colors.CYAN + Colors.BOLD)
        self.log("="*80, Colors.BOLD)
        
        self.log("\n1. Откройте https://ecopackpro.ru/cart (корзина)", Colors.CYAN)
        self.log("2. Смотрите на НИЖНЮЮ ПАНЕЛЬ", Colors.CYAN)
        self.log("\n✅ ПРОВЕРЬТЕ:", Colors.GREEN + Colors.BOLD)
        self.log("   • Иконка 'Корзина' НА ОДНОЙ ЛИНИИ с другими иконками")
        self.log("   • Виден ТОЛЬКО ОДИН badge (ЗЕЛЁНЫЙ)")
        self.log("   • Красного badge НЕТ")
        self.log("\n❌ НЕ ДОЛЖНО БЫТЬ:", Colors.RED + Colors.BOLD)
        self.log("   • Иконка корзины выпрыгнула вверх")
        self.log("   • Два badge (зелёный + красный)")
        
        self.log("\n" + "="*80, Colors.BOLD)
        self.log("🎯 ОЖИДАЕМЫЙ РЕЗУЛЬТАТ", Colors.YELLOW + Colors.BOLD)
        self.log("="*80, Colors.BOLD)
        
        self.log("\nНИЖНЯЯ ПАНЕЛЬ:")
        self.log("┌────────┬────────┬──────────┬────────┬────────┐")
        self.log("│ Главная│ Каталог│Избранное │ Кабинет│ Корзина│")
        self.log("│   🏠   │   🏪   │    ♥️⓪   │   👤   │  🛒⓵  │  ← ВСЕ НА ОДНОЙ ЛИНИИ!")
        self.log("└────────┴────────┴──────────┴────────┴────────┘")
        self.log("                                          ↑")
        self.log("                             Только ЗЕЛЁНЫЙ badge (15)")
        self.log("                             Красного НЕТ!")
        
        if failed == 0:
            self.log("\n🎉 ВСЕ ТЕСТЫ ПРОЙДЕНЫ!", Colors.GREEN + Colors.BOLD)
            return 0
        else:
            self.log(f"\n⚠️  ПРОБЛЕМЫ: {failed} тест(ов)", Colors.RED + Colors.BOLD)
            return 1
    
    def run_all_tests(self):
        """Запуск всех тестов"""
        self.log("="*80, Colors.BOLD)
        self.log("🚀 ТЕСТ: ВЫРАВНИВАНИЕ + ОДИН BADGE", Colors.BOLD + Colors.BLUE)
        self.log("="*80, Colors.BOLD)
        self.log(f"Дата: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}")
        self.log(f"Сайт: {self.site_url}")
        
        self.test_vertical_align_css()
        self.test_red_badge_hidden()
        self.test_green_badge_visible()
        self.test_flexbox_alignment()
        
        return self.generate_report()

if __name__ == '__main__':
    import urllib3
    urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
    
    runner = AlignmentBadgeTest()
    exit_code = runner.run_all_tests()
    sys.exit(exit_code)

