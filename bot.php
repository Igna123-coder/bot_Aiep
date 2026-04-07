<?php
// 1. Configuración de Tokens
$token = '8740398851:AAETpsAsEvDFzwci8jyrgf0GcwLUhhb0agU';
$website = 'https://api.telegram.org/bot'.$token;
$apiKeyIA = 'AIzaSyB1Uies1S6VPK6MUUgbs1MpuoKdPT4MUiY'; // Tu llave de Gemini

// 2. Capturar mensaje de Telegram
$input = file_get_contents('php://input');
$update = json_decode($input, TRUE);
$chatId = $update['message']['chat']['id'];
$messageUsuario = $update['message']['text'] ?? '';

// 3. Función para procesar con IA (Gemini)
function procesarConIA($mensaje, $key) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $key;
    
    $data = [
        "contents" => [[
            "parts" => [["text" => "Eres un asistente de la universidad AIEP. Responde de forma breve y simpática. El alumno te dice: " . $mensaje]]
        ]]
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    $result = json_decode($response, true);

    return $result['candidates'][0]['content']['parts'][0]['text'] ?? "Lo siento, tuve un error al pensar.";
}

// 4. Lógica de envío
if ($messageUsuario == '/start') {
    $respuesta = "¡Hola! Soy tu nuevo bot con IA de AIEP. Pregúntame lo que quieras.";
} else {
    $respuesta = procesarConIA($messageUsuario, $apiKeyIA);
}

sendMessage($chatId, $respuesta);

function sendMessage($chatId, $response) {
    global $website;
    $url = $website.'/sendMessage?chat_id='.$chatId.'&text='.urlencode($response);
    file_get_contents($url);
}
?>
