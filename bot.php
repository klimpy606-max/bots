<?php
// بوت تليجرام سوبر العرب
// Telegram Bot - Super Arab

define('BOT_TOKEN', '8487403801:AAF17ii-25xlMHTlGqueO9kFwZHlnkGU8Kw');
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');

// معالجة الطلبات الواردة من تليجرام
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if (!empty($update['message'])) {
    $message = $update['message'];
    $chat_id = $message['chat']['id'];
    $text = $message['text'] ?? '';
    
    // التعامل مع أمر /start
    if ($text === '/start') {
        sendMessage($chat_id, 'مرحبا بك في بوت سوبر العرب 🇸🇦');
    }
    // يمكنك إضافة أوامر أخرى هنا
    else {
        sendMessage($chat_id, 'أهلا بك! استخدم /start للبدء');
    }
}

/**
 * إرسال رسالة إلى المستخدم
 * Send message to user
 */
function sendMessage($chat_id, $text) {
    $url = TELEGRAM_API_URL . 'sendMessage';
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content' => json_encode($data)
        ]
    ];
    
    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    
    return json_decode($response, true);
}

/**
 * إرسال رسالة Keep-Alive كل 10 دقائق
 * Send Keep-Alive message every 10 minutes
 * (للاستضافات المجانية - يحافظ على البوت نشيطاً)
 */
function sendKeepAlive() {
    if (file_exists('keep_alive.txt')) {
        $last_keep_alive = (int)file_get_contents('keep_alive.txt');
    } else {
        $last_keep_alive = 0;
    }
    
    $current_time = time();
    
    // إذا مرت 10 دقائق، أرسل رسالة
    if ($current_time - $last_keep_alive >= 600) { // 600 ثانية = 10 دقائق
        // قم بتحديث وقت آخر طلب
        file_put_contents('keep_alive.txt', $current_time);
        
        // يمكنك إضافة chat_id الخاص بك هنا للحصول على تنبيهات
        // Replace YOUR_CHAT_ID with your actual chat ID
        // sendMessage(YOUR_CHAT_ID, '🤖 البوت لا يزال نشطاً');
        
        return true;
    }
    
    return false;
}

// تشغيل Keep-Alive في كل طلب
sendKeepAlive();

?>
