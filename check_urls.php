<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "🔗 WEBHOOK URL: " . route('api.webhooks.libelula') . "\n";
echo "🌍 APP_URL: " . env('APP_URL') . "\n";
