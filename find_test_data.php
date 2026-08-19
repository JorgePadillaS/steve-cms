<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::first();
$s = App\Models\Station::first();

echo "USER_EMAIL: " . ($u->email ?? 'NONE') . "\n";
echo "STATION_ID: " . ($s->charge_box_id ?? 'NONE') . "\n";
