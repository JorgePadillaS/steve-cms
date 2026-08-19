<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- REMOTE AUDIT LOGS ---\n";
$logs = \Illuminate\Support\Facades\DB::table('remote_audit_logs')->orderByDesc('id')->take(10)->get();
foreach($logs as $l) {
    echo "ID: {$l->id} | User: {$l->username} | Action: {$l->action} | Station: {$l->charge_box_id} | Details: {$l->details} | Date: {$l->created_at}\n";
}
