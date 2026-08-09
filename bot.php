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

    // 2. Admin botga xabar yuborganida guruhga uzatish
    if ($chat_id > 0 && $user_id == $admin_id) {

        // A) Media Group (Albom/Bir nechta rasm) kelganda
        if (isset($message['media_group_id'])) {
            $media_group_id = $message['media_group_id'];
            $file_name = "media_" . $media_group_id . ".json";

            // Mavjud rasmlar ro'yxatini yuklaymiz yoki yangi ochamiz
            $data = file_exists($file_name) ? json_decode(file_get_contents($file_name), true) : [];

            $photo = end($message['photo'])['file_id'];
            $caption = isset($message['caption']) ? $message['caption'] : '';

            $item = [
                'type' => 'photo',
                'media' => $photo
            ];
            if (!empty($caption)) {
                $item['caption'] = $caption;
                $item['parse_mode'] = 'HTML';
            }

            $data[] = $item;
            file_put_contents($file_name, json_encode($data));

            // Barcha rasmlar kelib bo'lishini 2 sekund kutamiz
            sleep(2);

            // Agar fayldagi rasmlar to'plami hali guruhga yuborilmagan bo'lsa
            if (file_exists($file_name)) {
                $final_data = json_decode(file_get_contents($file_name), true);
                unlink($file_name); // Vaqtinchalik faylni o'chiramiz

                // Telegram'ga bir vaqtning o'zida albom qilib yuboramiz
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $api_url . "sendMediaGroup");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                    'chat_id' => $group_id,
                    'media' => json_encode($final_data)
                ]));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch);
                curl_close($ch);
            }
        } 
        // B) Bitta rasm kelganda
        elseif (isset($message['photo'])) {
            $photo = end($message['photo'])['file_id'];
            $caption = isset($message['caption']) ? $message['caption'] : '';

            file_get_contents($api_url . "sendPhoto?" . http_build_query([
                'chat_id' => $group_id,
                'photo' => $photo,
                'caption' => $caption,
                'parse_mode' => 'HTML'
            ]));
        } 
        // C) Faqat matn kelganda
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
