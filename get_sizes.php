<?php
/**
 * Script to get directory sizes
 */

function formatSize($bytes) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    return round($bytes, 2) . ' ' . $units[$pow];
}

function getDirSize($directory) {
    $size = 0;
    try {
        $output = shell_exec("du -sb " . escapeshellarg($directory) . " 2>/dev/null");
        if ($output) {
            $size = intval(trim(explode("\t", $output)[0]));
        }
    } catch (Exception $e) {
        // Fallback to recursive calculation if shell_exec fails
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)) as $file) {
            $size += $file->getSize();
        }
    }
    return $size;
}

$rootPath = '/var/www/fastuser/data/www/ecopackpro.ru';

echo str_repeat("=", 80) . "\n";
echo "РАЗМЕРЫ ДИРЕКТОРИИ САЙТА ECOPACKPRO.RU\n";
echo str_repeat("=", 80) . "\n\n";

// Get total size
$totalSize = getDirSize($rootPath);
echo "📊 ОБЩИЙ РАЗМЕР ДИРЕКТОРИИ: " . formatSize($totalSize) . "\n\n";

echo str_repeat("=", 80) . "\n";
echo "ТАБЛИЦА РАЗМЕРОВ ПОДПАПОК:\n";
echo str_repeat("=", 80) . "\n\n";

printf("%-4s %-50s %-15s\n", "№", "ПАПКА", "РАЗМЕР");
echo str_repeat("-", 80) . "\n";

// Get subdirectories
$subdirs = [];
$items = scandir($rootPath);
foreach ($items as $item) {
    if ($item == '.' || $item == '..') continue;
    $fullPath = $rootPath . '/' . $item;
    if (is_dir($fullPath)) {
        $size = getDirSize($fullPath);
        $subdirs[] = [
            'name' => $item,
            'size' => $size,
            'formatted' => formatSize($size)
        ];
    }
}

// Sort by size descending
usort($subdirs, function($a, $b) {
    return $b['size'] - $a['size'];
});

// Print table
foreach ($subdirs as $idx => $dir) {
    printf("%-4d %-50s %-15s\n", $idx + 1, $dir['name'], $dir['formatted']);
}

echo str_repeat("-", 80) . "\n";
echo "Всего подпапок: " . count($subdirs) . "\n\n";

// Top 5
echo str_repeat("=", 80) . "\n";
echo "ТОП-5 САМЫХ БОЛЬШИХ ПАПОК:\n";
echo str_repeat("=", 80) . "\n";
foreach (array_slice($subdirs, 0, 5) as $idx => $dir) {
    printf("%d. %-50s %s\n", $idx + 1, $dir['name'], $dir['formatted']);
}

// Get wp-content subdirectories
echo "\n" . str_repeat("=", 80) . "\n";
echo "ДЕТАЛИЗАЦИЯ WP-CONTENT:\n";
echo str_repeat("=", 80) . "\n\n";

$wpContentPath = $rootPath . '/wp-content';
if (is_dir($wpContentPath)) {
    $wpContentDirs = [];
    $items = scandir($wpContentPath);
    foreach ($items as $item) {
        if ($item == '.' || $item == '..') continue;
        $fullPath = $wpContentPath . '/' . $item;
        if (is_dir($fullPath)) {
            $size = getDirSize($fullPath);
            $wpContentDirs[] = [
                'name' => $item,
                'size' => $size,
                'formatted' => formatSize($size)
            ];
        }
    }
    
    usort($wpContentDirs, function($a, $b) {
        return $b['size'] - $a['size'];
    });
    
    printf("%-4s %-50s %-15s\n", "№", "ПАПКА", "РАЗМЕР");
    echo str_repeat("-", 80) . "\n";
    
    foreach ($wpContentDirs as $idx => $dir) {
        printf("%-4d %-50s %-15s\n", $idx + 1, $dir['name'], $dir['formatted']);
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "Отчет создан: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 80) . "\n";
?>

