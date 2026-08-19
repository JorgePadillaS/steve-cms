<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$connector = DB::connection('steve')->table('connector')->where('charge_box_id', 'EPSCBLP12')->first();
echo "CONNECTOR_PK: " . ($connector->connector_pk ?? 'NONE') . "\n";
