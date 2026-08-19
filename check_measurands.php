<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$measurands = DB::connection('steve')
    ->table('connector_meter_value')
    ->where('value_timestamp', '>', now()->subHours(24))
    ->distinct()
    ->pluck('measurand')
    ->all();

print_r($measurands);
