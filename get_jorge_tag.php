<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$u = App\Models\User::where('email', 'jorge.ps.bo@gmail.com')->first();
if ($u) {
    $tag = App\Models\RfidTag::where('user_id', $u->id)->first();
    echo "USER_TAG: " . ($tag->id_tag ?? 'NONE') . "\n";
} else {
    echo "USER NOT FOUND\n";
}
