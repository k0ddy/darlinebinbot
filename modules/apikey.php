<?php

/*

///==[API Key for CC Checker Commands]==///

/apikey sk_live_xxx - Sets the SK Key
/myapikey - Returns your  API Key

*/


include __DIR__."/../config/config.php";
include __DIR__."/../config/variables.php";
include_once __DIR__."/../functions/bot.php";
include_once __DIR__."/../functions/db.php";
include_once __DIR__."/../functions/functions.php";


////////////====[API KEY]====////////////
if(strpos($message, "/apikey ") === 0 || strpos($message, "!apikey ") === 0){   
    $antispam = antispamCheck($userId);
    addUser($userId);
    
    if($antispam != False){
      bot('sendmessage',[
        'chat_id'=>$chat_id,
        'text'=>"[<u>ANTI SPAM</u>] Vuelve a intentar despues de <b>$antispam</b>s.",
        'parse_mode'=>'html',
        'reply_to_message_id'=> $message_id
      ]);
      return;

    }else{
        $messageidtoedit1 = bot('sendmessage',[
        'chat_id'=>$chat_id,
        'text'=>"<b>Espera un momento...</b>",
        'parse_mode'=>'html',
        'reply_to_message_id'=> $message_id]);

        $messageidtoedit = capture(json_encode($messageidtoedit1), '"message_id":', ',');
        $sk = substr($message, 7);
        
        if(preg_match_all("/sk_(test|live)_[A-Za-z0-9]+/", $sk, $matches)) {
            $sk = $matches[0][0];

            if(fetchAPIKey($userId) == $sk){
                bot('editMessageText',[
                    'chat_id'=>$chat_id,
                    'message_id'=>$messageidtoedit,
                    'text'=>"<b>¡Esta clave SK es la misma que su clave SK existente!</b>",
                    'parse_mode'=>'html',
                    'disable_web_page_preview'=>'true'
                    
                ]);
                return;
            }

            ###CHECKER PART###  
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/tokens');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "card[number]=5154620061414478&card[exp_month]=01&card[exp_year]=2023&card[cvc]=235");
            curl_setopt($ch, CURLOPT_USERPWD, $sk. ':' . '');
            $headers = array();
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);

            file_put_contents('as.txt',$result);
            if(strpos($result, 'error') == False || stripos($result, '"type": "card_error"')){
                updateAPIKey($userId,$sk);

                bot('editMessageText',[
                    'chat_id'=>$chat_id,
                    'message_id'=>$messageidtoedit,
                    'text'=>"<b>✅ ¡Agregó con éxito su clave API!
Ahora puede verificar las tarjetas usando /schk</b>",
                    'parse_mode'=>'html',
                    'disable_web_page_preview'=>'true'
                    
                ]);
                
                if($chattype != 'private'){
                bot('deleteMessage',[
                    'chat_id'=>$chat_id,
                    'message_id'=>$message_id]);
                }


            }elseif(stripos($result, 'error')){
                bot('editMessageText',[
                    'chat_id'=>$chat_id,
                    'message_id'=>$messageidtoedit,
                    'text'=>"<b>¡Esta llave SK está muerta! Dame una clave de Live SK</b>",
                    'parse_mode'=>'html',
                    'disable_web_page_preview'=>'true'
                    
                ]);
            }
        }else{
            bot('editMessageText',[
                'chat_id'=>$chat_id,
                'message_id'=>$messageidtoedit,
                'text'=>"<b>¡XDNT! ¡Maldita sea, proporciona una clave SK!</b>",
                'parse_mode'=>'html',
                'disable_web_page_preview'=>'true'
                
            ]);
        }
    }
}

////////////====[MY API KEY]====////////////
if(strpos($message, "/myapikey") === 0 || strpos($message, "!myapikey") === 0){   
    $antispam = antispamCheck($userId);
    addUser($userId);
    
    if($antispam != False){
      bot('sendmessage',[
        'chat_id'=>$chat_id,
        'text'=>"[<u>ANTI SPAM</u>] Vuelve a intentar despues de <b>$antispam</b>s.",
        'parse_mode'=>'html',
        'reply_to_message_id'=> $message_id
      ]);
      return;

    }else{
        $apikey = fetchAPIKey($userId);

        if($chattype != 'private'){
            $apikey = substr_replace($apikey, '',12).preg_replace("/(?!^).(?!$)/", "*", substr($apikey, 12));
            $secmessage = "<b>Use el comando en mensaje privado para obtener su clave SK completa.</b>";
        }
        
        bot('sendmessage',[
        'chat_id'=>$chat_id,
        'text'=>"<b>Your API Key:- <code>$apikey</code></b>

$secmessage        ",
        'parse_mode'=>'html',
        'reply_to_message_id'=> $message_id]);

        

    }
}


?>