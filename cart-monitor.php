<?php
/**
 * Мониторинг корзины в реальном времени
 * Отслеживание всех событий добавления в корзину
 */

// Загружаем WordPress
require_once('wp-load.php');

// Проверяем права доступа (только для админов)
if (!current_user_can('administrator')) {
    die('Доступ запрещен');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Мониторинг корзины - EcopackPro</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .log-section { margin: 20px 0; }
        .log-section h3 { background: #0073aa; color: white; padding: 10px; margin: 0; border-radius: 4px 4px 0 0; }
        .log-content { background: #1e1e1e; color: #00ff00; padding: 15px; border-radius: 0 0 4px 4px; font-family: 'Courier New', monospace; font-size: 12px; max-height: 400px; overflow-y: auto; }
        .status { padding: 10px; margin: 10px 0; border-radius: 4px; }
        .status.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .status.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .status.warning { background: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
        .controls { margin: 20px 0; }
        .btn { padding: 10px 20px; margin: 5px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-primary { background: #0073aa; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .auto-refresh { background: #17a2b8; color: white; }
        .timestamp { color: #888; font-size: 11px; }
        .ajax-test { background: #e9ecef; padding: 15px; margin: 10px 0; border-radius: 4px; }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>🔍 Мониторинг корзины EcopackPro</h1>
        
        <div class="status success">
            <strong>✅ Логирование активно!</strong> Все события добавления в корзину записываются в логи.
        </div>

        <div class="controls">
            <button class="btn btn-primary" onclick="refreshLogs()">🔄 Обновить логи</button>
            <button class="btn btn-success" onclick="clearLogs()">🗑️ Очистить логи</button>
            <button class="btn auto-refresh" onclick="toggleAutoRefresh()">⏱️ Автообновление</button>
            <button class="btn btn-primary" onclick="testAjax()">🧪 Тест AJAX</button>
        </div>

        <div class="ajax-test">
            <h4>Тестирование AJAX добавления в корзину:</h4>
            <button class="btn btn-success" onclick="testAddToCart(7143)">Добавить товар ID 7143</button>
            <button class="btn btn-success" onclick="testAddToCart(7062)">Добавить товар ID 7062</button>
            <div id="ajax-result" style="margin-top: 10px;"></div>
        </div>

        <div class="log-section">
            <h3>📝 Debug Log (последние 50 строк)</h3>
            <div class="log-content" id="debug-log">
                <?php
                $debug_file = 'wp-content/debug.log';
                if (file_exists($debug_file)) {
                    $lines = file($debug_file);
                    $last_lines = array_slice($lines, -50);
                    foreach ($last_lines as $line) {
                        echo htmlspecialchars($line) . '<br>';
                    }
                } else {
                    echo 'Debug log не найден или пуст.';
                }
                ?>
            </div>
        </div>

        <div class="log-section">
            <h3>🛒 WooCommerce Logs</h3>
            <div class="log-content" id="wc-logs">
                <?php
                $wc_logs = glob('wp-content/uploads/wc-logs/*.log');
                if ($wc_logs) {
                    $latest_log = max($wc_logs);
                    $lines = file($latest_log);
                    $last_lines = array_slice($lines, -30);
                    foreach ($last_lines as $line) {
                        echo htmlspecialchars($line) . '<br>';
                    }
                } else {
                    echo 'WooCommerce логи не найдены.';
                }
                ?>
            </div>
        </div>

        <div class="log-section">
            <h3>🌐 AJAX Endpoints</h3>
            <div class="status warning">
                <strong>Тестирование endpoints:</strong>
                <br>• <a href="/wp-admin/admin-ajax.php?action=test" target="_blank">admin-ajax.php</a>
                <br>• <a href="/wp-admin/admin-ajax.php?action=woocommerce_get_refreshed_fragments" target="_blank">get_refreshed_fragments</a>
            </div>
        </div>
    </div>

    <script>
        let autoRefreshInterval;
        let isAutoRefresh = false;

        function refreshLogs() {
            location.reload();
        }

        function clearLogs() {
            if (confirm('Очистить все логи?')) {
                fetch('?action=clear_logs', {method: 'POST'})
                    .then(() => refreshLogs());
            }
        }

        function toggleAutoRefresh() {
            if (isAutoRefresh) {
                clearInterval(autoRefreshInterval);
                isAutoRefresh = false;
                document.querySelector('.auto-refresh').textContent = '⏱️ Автообновление';
                document.querySelector('.auto-refresh').style.background = '#17a2b8';
            } else {
                autoRefreshInterval = setInterval(refreshLogs, 5000);
                isAutoRefresh = true;
                document.querySelector('.auto-refresh').textContent = '⏹️ Остановить';
                document.querySelector('.auto-refresh').style.background = '#dc3545';
            }
        }

        function testAjax() {
            document.getElementById('ajax-result').innerHTML = '<div style="color: blue;">Тестирую AJAX...</div>';
            
            fetch('/wp-admin/admin-ajax.php?action=woocommerce_get_refreshed_fragments', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: ''
            })
            .then(response => {
                if (response.ok) {
                    return response.text();
                } else {
                    throw new Error('HTTP ' + response.status);
                }
            })
            .then(data => {
                document.getElementById('ajax-result').innerHTML = '<div style="color: green;">✅ AJAX работает! Ответ получен.</div>';
                console.log('AJAX Response:', data);
            })
            .catch(error => {
                document.getElementById('ajax-result').innerHTML = '<div style="color: red;">❌ AJAX ошибка: ' + error.message + '</div>';
                console.error('AJAX Error:', error);
            });
        }

        function testAddToCart(productId) {
            document.getElementById('ajax-result').innerHTML = '<div style="color: blue;">Добавляю товар ID ' + productId + '...</div>';
            
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=woocommerce_add_to_cart&product_id=' + productId + '&quantity=1'
            })
            .then(response => {
                if (response.ok) {
                    return response.text();
                } else {
                    throw new Error('HTTP ' + response.status);
                }
            })
            .then(data => {
                document.getElementById('ajax-result').innerHTML = '<div style="color: green;">✅ Товар добавлен! Ответ: ' + data.substring(0, 100) + '...</div>';
                console.log('Add to Cart Response:', data);
                // Обновляем логи через 2 секунды
                setTimeout(refreshLogs, 2000);
            })
            .catch(error => {
                document.getElementById('ajax-result').innerHTML = '<div style="color: red;">❌ Ошибка добавления: ' + error.message + '</div>';
                console.error('Add to Cart Error:', error);
            });
        }

        // Автоматическое обновление каждые 10 секунд
        setTimeout(() => {
            if (!isAutoRefresh) {
                toggleAutoRefresh();
            }
        }, 10000);
    </script>
</body>
</html>

<?php
// Обработка AJAX запросов
if (isset($_POST['action']) && $_POST['action'] === 'clear_logs') {
    file_put_contents('wp-content/debug.log', '');
    file_put_contents('wp-content/uploads/wc-logs/debug-' . date('Y-m-d') . '.log', '');
    echo 'Логи очищены';
    exit;
}
?>



