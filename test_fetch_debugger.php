<?php
$html = file_get_contents("https://e2v.evbol.com/admin/login");
echo "LOGIN HTML LENGTH: " . strlen($html) . "\n";
file_put_contents("/tmp/login_page.html", $html);

preg_match_all('/<input[^>]+>/', $html, $inputs);
print_r($inputs[0] ?? []);
