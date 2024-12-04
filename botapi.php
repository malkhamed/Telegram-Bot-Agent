<?php
function telegram($method, $datas = [])
{
    global $APIKEY;
    $url = "https://api.telegram.org/bot" . $APIKEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    if (curl_error($ch)) {
        var_dump(curl_error($ch));
    } else {
        return json_decode($res);
    }
}
function sendmessage($chat_id,$text,$keyboard,$parse_mode){
    telegram('sendmessage',[
        'chat_id' => $chat_id,
        'text' => $text,
        'disable_web_page_preview' => true,
        'reply_markup' => $keyboard,
        'parse_mode' => $parse_mode,
        
        ]);
}

function forwardMessage($chat_id,$message_id,$chat_id_user){
    telegram('forwardMessage',[
        'from_chat_id'=> $chat_id,
        'message_id'=> $message_id,
        'chat_id'=> $chat_id_user,
    ]);
}
function sendphoto($chat_id,$photoid,$caption,$parse_mode = "HTML"){
    telegram('sendphoto',[
        'chat_id' => $chat_id,
        'photo'=> $photoid,
        'caption'=> $caption,
        'parse_mode' => $parse_mode,
    ]);
}
function sendvideo($chat_id,$videoid,$caption){
    telegram('sendvideo',[
        'chat_id' => $chat_id,
        'video'=> $videoid,
        'caption'=> $caption,
    ]);
}
function Editmessagetext($chat_id, $message_id, $text, $keyboard){
    telegram('editmessagetext', [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'reply_markup' => $keyboard
    ]);
}
 function deletemessage($chat_id, $message_id){
  telegram('deletemessage', [
'chat_id' => $chat_id, 
'message_id' => $message_id,
]);
 }
#-----------------------------#
$update = json_decode(file_get_contents("php://input"), true);
$from_id = $update['message']['from']['id'] ?? $update['callback_query']['from']['id'] ?? 0;
$Chat_type = $update["message"]["chat"]["type"] ?? '';
$text = $update["message"]["text"] ?? '';
$text_callback = $update["callback_query"]["message"]["text"] ?? '';
$message_id = $update["message"]["message_id"] ?? $update["callback_query"]["message"]["message_id"] ?? 0;
$photo = $update["message"]["photo"] ?? 0;
$photoid = $photo ? end($photo)["file_id"] : '';
$caption = $update["message"]["caption"] ?? '';
$video = $update["message"]["video"] ?? 0;
$videoid = $video ? $video["file_id"] : 0;
$forward_from_id = $update["message"]["reply_to_message"]["forward_from"]["id"] ?? 0;
$datain = $update["callback_query"]["data"] ?? '';
$username = $update['message']['from']['username'] ?? $update['callback_query']['from']['username'] ?? 'NOT_USERNAME';
$user_phone =$update["message"]["contact"]["phone_number"] ?? 0;
$contact_id = $update["message"]["contact"]["user_id"] ?? 0;
$first_name = $update['message']['from']['first_name']  ?? '';
$callback_query_id = $update["callback_query"]["id"] ?? 0;

// Handle
// لطفا این کدها رو تغییر ندید
if ($data == "request_agent") {
    $stmt = $pdo->prepare("SELECT is_agent FROM user WHERE id = ?");
    $stmt->execute([$from_id]);
    $user = $stmt->fetch();

    if ($user && $user['is_agent']) {
        telegram('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "شما قبلاً به نماینده تبدیل شده‌اید!",
        ]);
    } else {
        telegram('sendMessage', [
            'chat_id' => $chat_id,
            'text' => "درخواست شما برای مدیریت ارسال شد. لطفاً منتظر بمانید.",
        ]);

        // Notify admin
        foreach ($admin_ids as $admin) {
            telegram('sendMessage', [
                'chat_id' => $admin,
                'text' => "کاربر @$username درخواست نمایندگی داده است.",
                'reply_markup' => json_encode([
                    'inline_keyboard' => [
                        [
                            ['text' => "✅ تأیید", 'callback_data' => "approve_agent_$from_id"],
                            ['text' => "❌ رد", 'callback_data' => "reject_agent_$from_id"],
                        ],
                    ],
                ]),
            ]);
        }
    }
}

// Handle agent
if (strpos($data, "approve_agent_") === 0) {
    $user_id = str_replace("approve_agent_", "", $data);
    $stmt = $pdo->prepare("UPDATE user SET is_agent = 1 WHERE id = ?");
    $stmt->execute([$user_id]);

    telegram('sendMessage', [
        'chat_id' => $chat_id,
        'text' => "لطفاً درصد تخفیف را وارد کنید:",
    ]);
    step("set_discount_$user_id", $chat_id);
}

if (strpos($data, "reject_agent_") === 0) {
    $user_id = str_replace("reject_agent_", "", $data);
    telegram('sendMessage', [
        'chat_id' => $user_id,
        'text' => "درخواست نمایندگی شما رد شد.",
    ]);
}
