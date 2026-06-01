<?php
echo "SAPI: " . PHP_SAPI . "\n";
echo "php_ini: " . php_ini_loaded_file() . "\n";
echo "ext_dir: " . ini_get('extension_dir') . "\n";
echo "scan_dir: " . php_ini_scanned_files() . "\n";
echo "PHP_INI_SCAN_DIR env: " . (getenv('PHP_INI_SCAN_DIR') ?: 'not set') . "\n";
echo "pdo_mysql loaded: " . (extension_loaded('pdo_mysql') ? 'YES' : 'NO') . "\n";
echo "PDO drivers: " . implode(', ', PDO::getAvailableDrivers()) . "\n";

// Try to manually check if the DLL exists and load it
$extDir = ini_get('extension_dir');
$dllPath = $extDir . DIRECTORY_SEPARATOR . "php_pdo_mysql.dll";
echo "DLL exists at $dllPath: " . (file_exists($dllPath) ? 'YES' : 'NO') . "\n";

echo "All loaded extensions: " . implode(', ', get_loaded_extensions()) . "\n";
