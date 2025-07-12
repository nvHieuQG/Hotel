<?php

require_once 'vendor/autoload.php';

use App\Http\Middleware\CheckBookingAccess;

try {
    $middleware = new CheckBookingAccess();
    echo "Middleware CheckBookingAccess instantiated successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} 