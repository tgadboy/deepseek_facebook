<?php
// Устанавливаем правильный заголовок для JSON
header('Content-Type: application/json');

// Получаем секретный ключ из переменных окружения Railway
$app_secret = getenv('FB_APP_SECRET');

// Проверяем, что запрос пришел методом POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Only POST requests are allowed']);
    exit;
}

// Проверяем, что передан signed_request
if (!isset($_POST['signed_request'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'signed_request parameter is missing']);
    exit;
}

// Проверяем, что секретный ключ установлен
if (empty($app_secret)) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'FB_APP_SECRET is not configured']);
    exit;
}

// Функция для разбора подписанного запроса
function parse_signed_request($signed_request, $secret) {
    list($encoded_sig, $payload) = explode('.', $signed_request, 2);
    
    // Декодируем данные
    $sig = base64_decode(strtr($encoded_sig, '-_', '+/'));
    $data = json_decode(base64_decode(strtr($payload, '-_', '+/')), true);
    
    // Проверяем подпись
    $expected_sig = hash_hmac('sha256', $payload, $secret, true);
    
    if ($sig !== $expected_sig) {
        return null;
    }
    
    return $data;
}

// Основная обработка запроса
try {
    $signed_request = $_POST['signed_request'];
    $data = parse_signed_request($signed_request, $app_secret);
    
    if (!$data) {
        throw new Exception('Invalid signature');
    }
    
    if (!isset($data['user_id'])) {
        throw new Exception('User ID not found in request');
    }
    
    $user_id = $data['user_id'];
    
    // ЗДЕСЬ ДОЛЖНЫ УДАЛЯТЬСЯ ДАННЫЕ ПОЛЬЗОВАТЕЛЯ
    // Но так как у тебя нет базы данных, просто логируем
    error_log("Facebook data deletion requested for user: " . $user_id);
    
    // Генерируем код подтверждения
    $confirmation_code = 'del_' . $user_id . '_' . time();
    
    // Формируем ответ
    $response = [
        'url' => 'https://github.com/tgadboy', // ЗАМЕНИ на свой GitHub!
        'confirmation_code' => $confirmation_code
    ];
    
    // Отправляем успешный ответ
    http_response_code(200);
    echo json_encode($response);
    
} catch (Exception $e) {
    // В случае ошибки все равно возвращаем валидный JSON
    $response = [
        'url' => 'https://github.com/tgadboy', // ЗАМЕНИ на свой GitHub!
        'confirmation_code' => 'error_' . time()
    ];
    
    http_response_code(200);
    echo json_encode($response);
}
?><?php
echo "Facebook Data Deletion Callback is running.";
