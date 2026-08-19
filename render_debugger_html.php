<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::first();
if ($user) {
    auth()->login($user);
}

try {
    $page = new \App\Filament\Pages\LibelulaDebugger();
    $page->mount();
    
    echo "MOUNT DATA:\n";
    print_r($page->data);

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
