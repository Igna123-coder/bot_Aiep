<?php

$token = '8740398851:AAETpsAsEvDFzwci8jyrgf0GcwLUhhb0agU';
$website = 'https://api.telegram.org/bot'.$token;


$input = file_get_contents('php://input');
$update = json_decode($input, TRUE);


$chatId = $update['message']['chat']['id'];
$message = $update['message']['text'];


switch($message) {
    case '/start':
        $response = "Me has iniciado";
        sendMessage($chatId, $response);
        break;
    case 'info':
        $response = "¡Hola!";
        sendMessage($chatId, $response);
        break;
    case 'que haces':
        $response = "aquí estudiando la semana 2 de automatización";
        sendMessage($chatId, $response);
        break;
    case 'como crees que te ira en la sumativa':
        $response = "súper bien, hice los ejercicios y vi los videos";
        sendMessage($chatId, $response);
        break;
    default:
        $response = "No te he entendido";
        sendMessage($chatId, $response); 
        break;
}


function sendMessage($chatId, $response) {
    global $website;
    $url = $website.'/sendMessage?chat_id='.$chatId.'&text='.urlencode($response);
    file_get_contents($url);
}
?>