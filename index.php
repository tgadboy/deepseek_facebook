<?php
// Простейший рабочий вариант
header('Content-Type: application/json');

$response = [
    'url' => 'https://github.com',
    'confirmation_code' => 'test_123'
];

echo json_encode($response);
?>
