<?php
// Payment Configuration
$paymentConfig = [
    'momo' => [
        'partner_code' => 'MOMO',
        'access_key' => 'F8BBA842ECF85',
        'secret_key' => 'K951B6PE1waDMi640xX08PD3vg6EkVlz',
        'endpoint' => 'https://test-payment.momo.vn/v2/gateway/api/create',
        'return_url' => 'http://localhost/administrator/elements_LQA/payment/return.php',
        'notify_url' => 'http://localhost/administrator/elements_LQA/payment/notify.php'
    ],
    'bank_transfer' => [
        'bank_code' => 'MB',
        'account_number' => '0123456789',
        'account_name' => 'NGUYEN VAN A'
    ]
];
?>
