<?php

// php example.php  (with SMM_API_URL and SMM_API_KEY set in your environment)

require __DIR__.'/src/SmmApiException.php';
require __DIR__.'/src/SmmApi.php';

use EvaPanel\SmmApi\SmmApi;
use EvaPanel\SmmApi\SmmApiException;

$api = new SmmApi((string) getenv('SMM_API_URL'), (string) getenv('SMM_API_KEY'));

try {
    $balance = $api->balance();
    echo "Balance: {$balance['balance']} {$balance['currency']}\n";

    $services = $api->services();
    echo count($services)." services\n";
    foreach (array_slice($services, 0, 5) as $s) {
        echo "  #{$s['service']} {$s['name']}: {$s['rate']} per 1,000 (min {$s['min']}, max {$s['max']})\n";
    }

    // Place an order (uncomment and fill in):
    // $order = $api->order(['service' => 1, 'link' => 'https://instagram.com/p/xyz', 'quantity' => 1000]);
    // print_r($api->status($order['order']));
} catch (SmmApiException $e) {
    echo 'API error: '.$e->getMessage()."\n";
}
