<?php
// 1. Configuración de Tokens
$token = '8740398851:AAETpsAsEvDFzwci8jyrgf0GcwLUhhb0agU';
$website = 'https://api.telegram.org/bot'.$token;
$apiKeyIA = 'AIzaSyB1Uies1S6VPK6MUUgbs1MpuoKdPT4MUiY'; // Tu llave de Gemini

// 2. Capturar mensaje de Telegram
$input = file_get_contents('php://input');
$update = json_decode($input, TRUE);

// Validar que el mensaje exista para evitar errores
if (!isset($update['message'])) { exit; }

$chatId = $update['message']['chat']['id'];
$messageUsuario = $update['message']['text'] ?? '';

// 3. Función Robusta con cURL para Gemini
function procesarConIA($mensaje, $key) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $key;
    
    $data = [
        "contents" => [[
            "parts" => [["text" => "Eres un asistente de la universidad AIEP. Responde de forma breve y simpática. El alumno te dice: " . $mensaje]]
        ]]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Esto evita errores de certificado en el servidor

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        return "Error técnico: La IA respondió con código " . $httpCode;
    }

    $result = json_decode($response, true);
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? "No pude generar una respuesta ahora.";
}

// 4. Lógica de flujo
if ($messageUsuario == '/start') {
    $respuesta = "¡Hola! Soy tu bot con IA de AIEP. ¿En qué te puedo ayudar hoy?";
} else if (!empty($messageUsuario)) {
    $respuesta = procesarConIA($messageUsuario, $apiKeyIA);
} else {
    exit;
}

sendMessage($chatId, $respuesta);

// Función de envío a Telegram
function sendMessage($chatId, $response) {
    global $website;
    $url = $website.'/sendMessage?chat_id='.$chatId.'&text='.urlencode($response);
    file_get_contents($url);
}
?>
