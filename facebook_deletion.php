<?php
// 1. Говорим Фейсбуку, что ответ будет в формате JSON
header('Content-Type: application/json');

// 2. Секретный ключ от твоего приложения. Его ты найдешь тут:
// https://developers.facebook.com/apps/ -> Выбери свое приложение -> Настройки -> Основные -> "Секрет приложения"
$app_secret = '33241493a33830480bd50c7759027834'; // <- ЗАМЕНИ ЭТУ СТРОКУ!

// 3. Функции для расшифровки запроса от Фейсбука (просто скопируй их, не вникая)
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

// 4. НАЧАЛО ГЛАВНОЙ ЧАСТИ
// Попробуем сделать то, что просит Фейсбук
try {
    // Ловим запрос от Фейсбука
    $signed_request = $_POST['signed_request'];
    // Расшифровываем его с помощью нашего секретного ключа
    $data = parse_signed_request($signed_request, $app_secret);
    // Достаем из запроса ID пользователя, который хочет удалить данные
    $user_id = $data['user_id'];

    // !!! САМОЕ ВАЖНОЕ МЕСТО !!!
    // ТВОЯ ЗАДАЧА — НАПИСАТЬ КОД, КОТОРЫЙ УДАЛИТ ДАННЫЕ ЭТОГО ПОЛЬЗОВАТЕЛЯ (user_id) ИЗ ТВОЕЙ БАЗЫ ДАННЫХ.
    // Например, если у тебя есть таблица `users`, это может выглядеть так:
    // include 'db_connect.php'; // Подключение к базе данных
    // $sql = "DELETE FROM users WHERE fb_user_id = '".$user_id."'";
    // $result = mysqli_query($conn, $sql);
    // ЭТО ПРИМЕР. Тебе нужно сделать что-то похожее для твоих таблиц.

    // 5. Готовим ответ для Фейсбука
    // Придумываем любой уникальный код для подтверждения (например, дата + ID)
    $confirmation_code = 'del_' . $user_id . '_' . time();
    // Указываем ссылку на страницу на твоем сайте, где написано про удаление данных
    $status_url = 'https://твой-сайт.ru/privacy'; // <- ЗАМЕНИ НА СВОЙ URL!

    // 6. Формируем ответ в строгом формате, как требует Фейсбук
    $response = array(
        'url' => $status_url,
        'confirmation_code' => $confirmation_code
    );

    // 7. Отправляем ответ
    echo json_encode($response);

} catch (Exception $e) {
    // Если что-то пошло не так (например, ошибка в коде выше), все равно отвечаем Фейсбуку
    $response = array(
        'url' => 'https://docs.google.com/document/d/1cR_svmm0CMBL5KCK-GF20y4vezHbE6guYZkqpl6r0jM/edit?tab=t.0', // <- И ЗДЕСЬ ЗАМЕНИ
        'confirmation_code' => 'error_' . time()
    );
    echo json_encode($response);
}
?>