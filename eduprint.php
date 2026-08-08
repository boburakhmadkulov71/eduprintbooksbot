<?php
$content = file_get_contents("php://input");
$update = json_decode($content, true);

// @BotFather taqdim etgan bot tokeningiz
$token = "8595893115:AAEz5LkSBLdA1dsUBJSkP4Ysp-_Aw4URQpA";
$api_url = "https://api.telegram.org/bot" . $token . "/";

// Bot faqat sizning buyruqlaringizni bajarishi uchun o'zingizning Telegram ID ingizni kiriting
// (Shaxsiy ID ingizni @userinfobot orqali bilib olishingiz mumkin)
$admin_id = "8543483836"; 

// Xabaringiz yuborilishi kerak bo'lgan guruhning ID si
// (Guruh ID si odatda -100 bilan boshlanadi, masalan: -1001234567890)
$group_id = "-1004363062011"; 

if (isset($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $message_id = $message['message_id'];
    $user_id = $message['from']['id'];

    // 1. "Guruhga qo'shildi" va "Guruhni tark etdi" xabarlarini o'chirish
    if (isset($message['new_chat_members']) || isset($message['left_chat_member'])) {
        file_get_contents($api_url . "deleteMessage?chat_id=" . $chat_id . "&message_id=" . $message_id);
    }

    // 2. Siz botning shaxsiyiga (PM) xabar yuborganingizda uni guruhga bot nomidan joylash
    if ($chat_id > 0 && $user_id == $admin_id) { // Agar xabar shaxsiy chatdan va admin tomonidan kelgan bo'lsa
        
        // Agar matnli xabar bo'lsa
        if (isset($message['text'])) {
            $text = $message['text'];
            file_get_contents($api_url . "sendMessage?" . http_build_query([
                'chat_id' => $group_id,
                'text' => $text
            ]));
        }
        // Agar rasm bo'lsa
        elseif (isset($message['photo'])) {
            $photo = end($message['photo'])['file_id'];
            $caption = isset($message['caption']) ? $message['caption'] : '';
            file_get_contents($api_url . "sendPhoto?" . http_build_query([
                'chat_id' => $group_id,
                'photo' => $photo,
                'caption' => $caption
            ]));
        }
        // Agar video bo'lsa
        elseif (isset($message['video'])) {
            $video = $message['video']['file_id'];
            $caption = isset($message['caption']) ? $message['caption'] : '';
            file_get_contents($api_url . "sendVideo?" . http_build_query([
                'chat_id' => $group_id,
                'video' => $video,
                'caption' => $caption
            ]));
        }
        // Agar fayl (document) bo'lsa
        elseif (isset($message['document'])) {
            $document = $message['document']['file_id'];
            $caption = isset($message['caption']) ? $message['caption'] : '';
            file_get_contents($api_url . "sendDocument?" . http_build_query([
                'chat_id' => $group_id,
                'document' => $document,
                'caption' => $caption
            ]));
        }
    }
}
?>
