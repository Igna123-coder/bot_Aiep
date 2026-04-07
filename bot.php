function procesarConIA($mensaje, $key) {
    // CAMBIO CLAVE: Usamos gemini-1.5-flash-latest que es la dirección más estable
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent?key=' . $key;
    
    $data = [
        "contents" => [[
            "parts" => [["text" => "Eres un asistente de la universidad AIEP. Responde breve. El mensaje es: " . $mensaje]]
        ]]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode != 200) {
        // Esto nos ayudará a saber si el error cambia (ej. de 404 a 400 o 403)
        return "Error técnico: Código $httpCode. Revisa que la API Key en GitHub sea igual a la de Google Studio.";
    }

    $result = json_decode($response, true);
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? "La IA no pudo responder.";
}
