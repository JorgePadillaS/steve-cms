<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$email = $argv[1] ?? 'sap-integration@evce.com.bo';
$tokenName = $argv[2] ?? 'custom-api-token';

$user = User::where('email', $email)->first();

if (!$user) {
    echo "Error: User with email '{$email}' not found.\n\n";
    echo "=== ACTIVE PRODUCTION USERS ===\n";
    try {
        $users = User::select('id', 'name', 'email')->get();
        foreach ($users as $u) {
            echo "ID: {$u->id} | Name: {$u->name} | Email: {$u->email}\n";
        }
    } catch (\Exception $e) {
        echo "Error fetching users: " . $e->getMessage() . "\n";
    }
    exit(1);
}

// Generate the token
$tokenResult = $user->createToken($tokenName);

echo "Token generated successfully for {$user->name} ({$user->email})!\n";
echo "--------------------------------------------------\n";
echo "PLAIN TEXT TOKEN:\n";
echo $tokenResult->plainTextToken . "\n";
echo "--------------------------------------------------\n";
echo "Please save this token. It will NOT be shown again.\n";
echo "To use it, add it to your request as an 'Authorization' header:\n";
echo "Authorization: Bearer <TOKEN>\n";
echo "Or as a query parameter (due to QueryTokenAuth middleware):\n";
echo "?token=<TOKEN>\n";
