<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$steveTxs = \Illuminate\Support\Facades\DB::connection('steve')->table('transaction')->orderByDesc('transaction_pk')->take(5)->get();
print_r($steveTxs->toArray());
