<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- STEVE CHARGE BOXES ---\n";
$boxes = \Illuminate\Support\Facades\DB::connection('steve')->table('charge_box')->get();
foreach($boxes as $b) {
    print_r((array)$b);
}
