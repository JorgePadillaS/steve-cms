<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "--- CMS SESSIONS ---\n";
$sessions = \App\Models\ChargingSession::orderByDesc('id')->take(5)->get(['id', 'status', 'transaction_id', 'user_id', 'created_at']);
foreach($sessions as $s) {
    echo "ID: {$s->id} | Status: {$s->status} | Tx: {$s->transaction_id} | User: {$s->user_id} | Date: {$s->created_at}\n";
}

echo "\n--- STEVE TRANSACTIONS ---\n";
$steveTxs = \Illuminate\Support\Facades\DB::connection('steve')->table('transaction')->orderByDesc('transaction_pk')->take(5)->get();
foreach($steveTxs as $t) {
    echo "PK: {$t->transaction_pk} | Tag: {$t->id_tag} | Start: {$t->start_timestamp} | Stop: {$t->stop_timestamp}\n";
}

echo "\n--- STEVE AUTH CACHE ---\n";
$auth = \Illuminate\Support\Facades\DB::connection('steve')->table('id_tag')->orderByDesc('expiry_date')->take(5)->get();
foreach($auth as $a) {
    echo "Tag: {$a->id_tag} | Expiry: {$a->expiry_date} | Blocked: {$a->blocked}\n";
}
