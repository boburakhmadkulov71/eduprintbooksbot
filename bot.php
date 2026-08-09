<?php
set_time_limit(0);
header('Content-Type: text/html; charset=utf-8');

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    echo "Bot faol holatda!";
    exit;
}

$token = "8595893115:AAEz5LkSBLdA1dsUBJSkP4Ysp-_Aw4URQpA";
$api_url = "https://api.telegram.org/bot" . $token . "/";
$admin_id = "8543483836";
$group_id = "-1004363062011";

if (isset($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $message_id = $message['message_id'];
    $user_id = $message['from']['id'];

    // 1. Guruhdagi kirdi-chiqdi xabarlarni o'chirish
    if (isset($message['new_chat_members']) || isset($message['left_chat_member'])) {
        file_get_contents($api_url . "deleteMessage?" . http_build_query([
            'chat_id' => $chat_id,
            'message_id' => $message_id
        ]));
    }

    // 2. Admin botga xabar/rasm yuborganida guruhga uzatish
    if ($chat_id > 0 && $user_id == $admin_id) {
        
        // Agar rasm yuborilgan bo'lsa (matni bo'lsa ham, bo'lmasa ham)
        if (isset($message['photo'])) {
            $photo = end($message['photo'])['file_id'];
            $caption = isset($message['caption']) ? $message['caption'] : '';
            
            file_get_contents($api_url . "sendPhoto?" . http_build_query([
                'chat_id' => $group_id,
                'photo' => $photo,
                'caption' => $caption,
                'parse_mode' => 'HTML'
            ]));
        } 
        // Faqat matn o'zi yuborilgan bo'lsa
        elseif (isset($message['text'])) {
            file_get_contents($api_url . "sendMessage?" . http_build_query([
                'chat_id' => $group_id,
                'text' => $message['text'],
                'parse_mode' => 'HTML'
            ]));
        }
    }
}
?>
