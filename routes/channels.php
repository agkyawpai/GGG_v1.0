<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('orders', function ($user) {
    return $user !== null;
});

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    return $user !== null;
});
