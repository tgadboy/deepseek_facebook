<?php
header('Content-Type: application/json');

// Секрет приложения Facebook. Будет задан как переменная среды на Railway.
$app_secret = getenv('FB_APP_SECRET');

function parse_signed_request($signed_request, $secret) {
    list($encoded_sig, $payload) = explode('.', $signed_request, 2);
    $sig = base64_url_decode($encoded_sig);
    $data = json_decode(base64_url_decode($payload), true);
    $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
    if ($sig !== $expected_sig) {
        return null;
    }
    return $data;
}

function base64_url_decode($input) {
    return base64_decode(strtr($input, '-_', '+/'));
}

try {
    if (!isset($_POST['signed_request'])) {
        throw new Exception('Отсутствует signed_request в запросе.');
    }
    
    $signed_request = $_POST['signed_request'];
    $data = parse_signed_request($signed_request, $app_secret);
    
    if (!$data || !isset($data['user_id'])) {
        throw new Exception('Неверная подпись запроса или отсутствует user_id.');
    }
    
    $user_id = $data['user_id'];
    
    // !!! ВАЖНО: Здесь должна быть твоя логика удаления данных пользователя $user_id из твоей БД.
    // Например, если у тебя есть подключение к базе данных, здесь должны быть SQL-запросы на удаление.
    // Это самый важный шаг, который ты должен реализовать сам.
    
    // Генерируем URL для статуса и код подтверждения.
    // Используем домен, который предоставит Railway.
    $base_url = getenv('RAILWAY_STATIC_URL') ?: 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN');
    $status_url = $base_url . '/deletion-status'; // Этой страницы у тебя может не быть, но для формальности нужен любой URL.
    $confirmation_code = 'del_' . $user_id . '_' . time();
    
    $response = array(
        'url' => $status_url,
        'confirmation_code' => $confirmation_code
    );
    
    echo json_encode($response);

} catch (Exception $e) {
    // В случае ошибки логируем и возвращаем JSON, чтобы Facebook не счел callback нерабочим.
    error_log('Ошибка: ' . $e->getMessage());
    $response = array(
        'url' => 'https://github.com/твой-гитхаб', // Можешь указать ссылку на свой GitHub или любой другой URL.
        'confirmation_code' => 'error_' . time()
    );
    echo json_encode($response);
}
?>
