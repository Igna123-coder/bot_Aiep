<?php
// 1. CONFIGURACIÓN DE TOKENS (No cambies los tokens, ya están correctos)
$token = '8740398851:AAETpsAsEvDFzwci8jyrgf0GcwLUhhb0agU';
$website = 'https://api.telegram.org/bot'.$token;
$apiKeyIA = 'AIzaSyB1Uies1S6VPK6MUUgbs1MpuoKdPT4MUiY'; // Tu llave de Gemini

// 2. CAPTURAR DATOS DE TELEGRAM
$input = file_get_contents('php://input');
$update = json_decode($input, TRUE);

// Si no hay mensaje (por ejemplo, al configurar el webhook), cerramos el script
if (!isset($update['message'])) {
    exit;
}

$chatId = $update['message']['chat']['id'];
$messageUsuario = $update['message']['text'] ?? '';

// 3. FUNCIÓN PARA PROCESAR CON INTELIGENCIA ARTIFICIAL (GEMINI)
function procesarConIA($mensaje, $key) {
    // Usamos la versión v1beta y el modelo gemini-1.5-flash que es el más compatible
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $key;
    
    $data = [
        "contents" => [[
            "parts" => [["text" => "Eres un asistente de la universidad AIEP en Chile. Responde de forma breve y simpática. El alumno te dice: " . $mensaje]]
        ]]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Evita errores de certificados en el servidor

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Si la API de Google falla, nos dirá el código exacto
    if ($httpCode != 200) {
        return "Error técnico: La IA respondió con código " . $httpCode . ". Revisa si la API Key está activa.";
    }

    $result = json_decode($response, true);
    
    // Extraemos la respuesta de la estructura JSON de Google
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? "No pude generar una respuesta en este momento.";
}

// 4. LÓGICA DE FLUJO DEL BOT
if ($messageUsuario == '/start') {
    $respuesta = "¡Hola Ignacio! Soy tu bot con IA de AIEP. Pregúntame lo que quieras sobre automatización.";
} else if (!empty($messageUsuario)) {
    // Si el usuario escribe algo, se lo mandamos a la IA
    $respuesta = procesarConIA($messageUsuario, $apiKeyIA);
} else {
    exit;
}

// 5. ENVIAR RESPUESTA A TELEGRAM
sendMessage($chatId, $respuesta);

function sendMessage($chatId, $response) {
    global $website;
    $url = $website.'/sendMessage?chat_id='.$chatId.'&text='.urlencode($response);
    file_get_contents($url);
}
?>
