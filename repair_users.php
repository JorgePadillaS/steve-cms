<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Wallet;
use App\Models\RfidTag;
use App\Models\Product;
use Illuminate\Support\Str;

echo "🛠️ REPARANDO USUARIOS SIN BILLETERA O TAG...\n";

$users = User::all();
$virtualProduct = Product::where('internal_code', 'VIRTUAL-TAG')->first();

foreach ($users as $user) {
    // Fix Wallet
    $wallet = Wallet::firstOrCreate(
        ['user_id' => $user->id],
        ['balance' => 0, 'currency' => 'BOB', 'is_postpaid' => false]
    );

    // Fix Virtual Tag
    $hasTag = RfidTag::where('user_id', $user->id)->exists();
    if (!$hasTag) {
        $tagCode = 'A' . strtoupper(Str::random(7));
        while (RfidTag::where('tag_code', $tagCode)->exists()) {
            $tagCode = 'A' . strtoupper(Str::random(7));
        }

        RfidTag::create([
            'tag_code' => $tagCode,
            'user_id' => $user->id,
            'product_id' => $virtualProduct?->id,
            'name' => 'Tag Virtual App',
            'is_active' => true,
            'is_virtual' => true,
        ]);
        echo " ✅ Tag Virtual creado para: {$user->email}\n";
    }
}

echo "🚀 REPARACIÓN COMPLETADA.\n";
