<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tariff;
$t = Tariff::first();
echo "Name: " . $t->name . "\n";
echo "Discount Amount: " . $t->discount_fixed_amount . "\n";
echo "Apply to App: " . ($t->apply_discount_to_app ? 'YES' : 'NO') . "\n";
echo "Apply to Cards: " . ($t->apply_discount_to_cards ? 'YES' : 'NO') . "\n";
echo "Parking Fee Enabled: " . ($t->is_parking_fee_enabled ? 'YES' : 'NO') . "\n";
echo "Session Price: " . $t->price_session . "\n";
