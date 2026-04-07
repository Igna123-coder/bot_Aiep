function procesarConIA($mensaje, $key) {
    // URL actualizada a la versión estable "v1" y modelo "gemini-pro"
    $url = 'https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent?key=' . $key;
    
    $data = [
        "contents" => [[
            "parts" => [["text" => "Eres un asistente de la universidad AIEP. Responde de forma breve. El alumno dice: " . $mensaje]]
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
        // Esto nos dirá si el error cambia de 404 a otro código
        return "Error técnico: La IA respondió con código " . $httpCode;
    }

    $result = json_decode($response, true);
    return $result['candidates'][0]['content']['parts'][0]['text'] ?? "No pude generar una respuesta ahora.";
}
