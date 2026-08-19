<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$billing = app(App\Services\BillingService::class);
$session = App\Models\ChargingSession::orderByDesc('id')->first();
if (!$session) {
    echo "No session found.\n";
    exit;
}

$pricing = $billing->calculateSessionCost($session, 1.0, now());
echo "Session #{$session->id} (User: {$session->user_id}, Tag: " . ($session->rfidTag?->tag_code ?? 'N/A') . ")\n";
echo "Subtotal: {$pricing['subtotal']}\n";
echo "Discount: {$pricing['discount_amount']}\n";
echo "Total: {$pricing['total']}\n";
echo "Session Fee: {$pricing['session_fee']}\n";
