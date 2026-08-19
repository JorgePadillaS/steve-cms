<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$startValue = DB::connection('steve')->table('transaction')->where('transaction_pk', 44)->value('start_value');
echo "START_VALUE: " . ($startValue ?? 'NULL') . "\n";
