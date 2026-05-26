<?php
echo "Laravel Vendor Check:\n";

$vendorAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    echo "✅ vendor/autoload.php exists\n";
    
    // Check Laravel version
    $appFile = __DIR__ . '/vendor/laravel/framework/src/Illuminate/Foundation/Application.php';
    if (file_exists($appFile)) {
        $content = file_get_contents($appFile);
        preg_match('/VERSION\s*=\s*\'([^\']+)\'/', $content, $matches);
        $version = $matches[1] ?? 'unknown';
        echo "✅ Laravel version: " . $version . "\n";
    }
    
    require $vendorAutoload;
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo "✅ App bootsrapped successfully\n";
    echo "✅ DB Connection: " . config('database.default') . "\n";
} else {
    echo "❌ vendor/autoload.php NOT FOUND\n";
    echo "   Run: composer install\n";
}