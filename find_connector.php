<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$s = App\Models\Station::where('charge_box_id', 'EPSCD12026')->first();
$connector = DB::connection('steve')->table('connector')->where('charge_box_id', $s->charge_box_id)->first();

echo "CONNECTOR_PK: " . ($connector->connector_pk ?? 'NONE') . "\n";
