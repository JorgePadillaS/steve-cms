<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "📋 COLUMNAS DE wallet_transactions:\n";
echo json_encode(Schema::getColumnListing('wallet_transactions')) . "\n";
