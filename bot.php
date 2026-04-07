<?php
// Mostrar errores para evitar silencios
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Tokens y Llaves
$token = '8740398851:AAETpsAsEvDFzwci8jyrgf0GcwLUhhb0agU';
$website = 'https://api.telegram.org/bot'.$token;
$apiKeyIA = 'AIzaSyB1Uies1S6VPK6MUUgbs1MpuoKdPT4MUiY';

// 2. Capturar mensaje
$input = file_get_contents('php://input');
$update = json_decode($input, TRUE);

// Si no hay texto, detener para no causar errores
if (!isset($update['message']['text'])) {
    exit;
}

$chatId = $update['message']['chat']['id'];
$messageUsuario = $update['message']['text'];

// 3. Función IA (Sin cURL, modo seguro)
function procesarConIA($mensaje, $key) {
    // CAMBIO APLICADO: Modelo gemini-1.0-pro
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.0-pro:generateContent?key=' . $key;
    
    $data = [
        "contents" => [[
            "parts" => [["text" => "Eres un asistente de AIEP Chile. Responde de forma breve y amable. El alumno dice: " . $mensaje]]
        ]]
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
            'ignore_errors' => true // CLAVE: Evita que el servidor se caiga si Google rechaza la llave
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ];

    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    // Si la conexión falla rotundamente
    if ($response === false) {
         return "Error crítico: El servidor de Render no puede salir a Internet.";
    }

    $result = json_decode($response, true);
    
    // Si Google nos manda un error por mala configuración
    if (isset($result['error'])) {
        return "Error de Google: " . $result['error']['message'];
    }

    // Respuesta exitosa
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? "La IA no supo qué responder.";
}

// 4. Lógica de Respuesta
if ($messageUsuario == '/start') {
    $respuesta = "¡Hola! Soy tu bot de automatización de AIEP. ¿En qué te ayudo?";
} else {
    $respuesta = procesarConIA($messageUsuario, $apiKeyIA);
}

sendMessage($chatId, $respuesta);

// 5. Función de Envío
function sendMessage($chatId, $response) {
    global $website;
    $url = $website.'/sendMessage?chat_id='.$chatId.'&text='.urlencode($response);
    file_get_contents($url);
}
?>
