<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- CMS STATIONS ---\n";
$stations = \App\Models\Station::get();
foreach($stations as $s) {
    echo "ID: {$s->id} | Name: {$s->name} | CB ID: {$s->charge_box_id} | Status: {$s->status}\n";
}
