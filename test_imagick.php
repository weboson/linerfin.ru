<?php
echo "<pre>";
echo "Testing ImageMagick...\n\n";

// Проверка shell_exec
echo "shell_exec enabled: " . (function_exists('shell_exec') ? 'YES' : 'NO') . "\n";

// Проверка convert
$convertCheck = shell_exec('which convert 2>&1');
echo "Convert path: " . ($convertCheck ?: 'NOT FOUND') . "\n";

// Тестовая команда
$testCmd = 'convert -version 2>&1';
$result = shell_exec($testCmd);
echo "Convert test:\n" . ($result ?: 'NO OUTPUT') . "\n";

// Проверка прав
echo "\nCurrent user: " . shell_exec('whoami') . "\n";
echo "Web server user: " . (isset($_SERVER['USER']) ? $_SERVER['USER'] : 'unknown') . "\n";

// Проверка временной директории
echo "Temp dir: " . sys_get_temp_dir() . "\n";
echo "Temp dir writable: " . (is_writable(sys_get_temp_dir()) ? 'YES' : 'NO') . "\n";

// Тест создания файла
$testFile = '/tmp/test_imagick_' . time() . '.png';
$cmd = "convert -size 100x100 xc:red " . escapeshellarg($testFile) . " 2>&1";
$result = shell_exec($cmd);
echo "\nTest file creation:\n";
echo "Command: $cmd\n";
echo "Result: " . ($result ?: 'NO OUTPUT') . "\n";
echo "File exists: " . (file_exists($testFile) ? 'YES' : 'NO') . "\n";
if (file_exists($testFile)) {
    echo "File size: " . filesize($testFile) . " bytes\n";
    unlink($testFile);
}