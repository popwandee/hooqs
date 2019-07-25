<?php

require_once __DIR__ . '/vendor/autoload.php';

/*
* 各キーを環境変数に設定
*
* Line Developers
* CHANNEL_SECRET = Channel Secret
* CHANNEL_ACCESS_TOKEN = Channel Access Token
*
* ぐるなび Web Service
* GNAVI_API_KEY
*/

// Line Message APIに接続
$input = file_get_contents('php://input');
$json = json_decode($input);
$event = $json--->events[0];
$http_client = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
$bot = new \LINE\LINEBot($http_client, ['channelSecret' => getenv('CHANNEL_SECRET')]);

// メッセージ識別子を取得
$event_type = $event->type;
$event_message_type = $event->message->type;

// メッセージの場合
if ('message' == $event_type) {

    // テキストメッセージの場合
    if ('text' == $event_message_type) {

        // メッセージの取得
        $text = $event->message->text;

        // メッセージを受け取ったらメッセージをそのまま返す
        $text_message_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text);
        $response = $bot->replyMessage($event->replyToken, $text_message_builder);
    }

    // 位置情報メッセージの場合
    else if ('location' == $event_message_type) {
        // 緯度経度情報の取得
        $latitude = $event->message->latitude;
        $longitude = $event->message->longitude;

        // ぐるなびWebサービス利用するためのURLの組み立て
        $url = buildGnaviUrl($latitude, $longitude);

        // ぐるなびAPI実行
        $json = file_get_contents($url);
        $results = resultsParse($json);

        // 店舗情報の取得内容に応じて処理
        if($results != null) {

            // いつも同じ結果にならないように配列をシャッフル
            shuffle($results);

            // カルーセル生成を5以下に制限
            if (count($results) > 5) {
                $max = 5;
            } else {
                $max = count($results);
            }

            // model Carousel
            $columns = [];
            for ($i = 0; $i < $max; $i++) {
                // // สร้างปุ่มเพื่อให้ภาพหมุน
                $action = new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder('Store details', $results[$i]['url']);

                // สร้างคอลัมน์สำหรับ carousel
                $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder($results[$i]['name'], $info, $results[$i]['image_url'], [$action]);
                $columns[] = $column;
            }

            // model Carousel จากอาร์เรย์ของคอลัมน์
            $carousel_template_builder = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder($columns);
            $template_message = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder('เก็บรายการข้อมูล (5 ราย)', $carousel_template_builder);
            $message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
            $message->add($template_message);
            $response = $bot->replyMessage($event->replyToken, $message);

        } else {
            // เมื่อไม่มีผลการค้นหา
            $text_message_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('ฉันขอโทษ NYA ไม่มีร้านราเมนอยู่ใกล้ ๆ . .');
            $response = $bot->replyMessage($event->replyToken, $text_message_builder);
        }
    }

    // テキスト、位置情報以外のメッセージの場合
    else {
        $text_message_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('スタンプを送ってくれたのかにゃ？でも反応できないにゃ。ごめんにゃ<img draggable="false" class="emoji" alt="🍜" src="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" id="exifviewer-img-3" exifid="-1690832363" oldsrc="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" scale="0">');
        $response = $bot->replyMessage($event->replyToken, $text_message_builder);
    }
}

// お友達追加時
else if ('follow' == $event_type) {
    // お友達追加時のメッセージ
    $text_message_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('お友達追加ありがとにゃ！よろしくにゃ<img draggable="false" class="emoji" alt="🍜" src="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" id="exifviewer-img-4" exifid="-1690832363" oldsrc="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" scale="0">');
    $response = $bot->replyMessage($event->replyToken, $text_message_builder);
}

// グループ追加時
else if ('join' == $event_type) {
    // グループ追加時のメッセージ
    $text_message_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('ようこそ！ラーメンについて話そうにゃ<img draggable="false" class="emoji" alt="🍜" src="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" id="exifviewer-img-5" exifid="-1690832363" oldsrc="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" scale="0">よろしくにゃ<img draggable="false" class="emoji" alt="🍜" src="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" id="exifviewer-img-6" exifid="-1690832363" oldsrc="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" scale="0">');
    $response = $bot->replyMessage($event->replyToken, $text_message_builder);
}

// その他のアクセス（ブラウザからのリクエストなど）
else {
    $text_message_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('不正なアクセス');
    $response = $bot->replyMessage($event->replyToken, $text_message_builder);

    echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
}

// ぐるなびAPI用のURLを生成
function buildGnaviUrl($latitude, $longitude) {

    // ぐるなびAPI設定
    $gnavi_uri = 'http://api.gnavi.co.jp/RestSearchAPI/20150630/';
    $gnavi_acckey = getenv('GNAVI_API_KEY');
    $gnavi_format = 'json';
    $gnavi_range = 3;
    $gnavi_category = 'RSFST08008'; // ラーメン

    // URL組み立て
    $url  = sprintf('%s%s%s%s%s%s%s%s%s%s%s%s%s', $gnavi_uri, '?format=', $gnavi_format, '&keyid=', $gnavi_acckey, '&latitude=', $latitude, '&longitude=', $longitude, '&range=', $gnavi_range, '&category_s=', $gnavi_category);

    return $url;
}

// ぐるなびAPIの結果をパース
function resultsParse($json) {
    $obj  = json_decode($json);

    // 連想配列初期化
    $results = [];

    $total_hit_count = $obj->{'total_hit_count'};

    if ($total_hit_count !== null) {
        $n = 0;
        foreach($obj->{'rest'} as $val) {

            // 店名
            if (checkString($val->{'name'})) {
                $results[$n]['name'] = $val->{'name'};
            }

            // 住所
            if (checkString($val->{'address'})) {
                $results[$n]['address'] = $val->{'address'};
            }

            // ぐるなびURL
            if (checkString($val->{'url'})) {
                $results[$n]['url'] = $val->{'url'};
            }

            // 店舗画像
            if (checkString($val->{'image_url'}->{'shop_image1'})) {
                $results[$n]['image_url'] = $val->{'image_url'}->{'shop_image1'};
            } else {
                $results[$n]['image_url'] = '※※※ 任意の画像URL ※※※';
            }

            // PR
            if (checkString($val->{'pr'})) {
                $results[$n]['pr'] = $val->{'pr'};
            } else {
                $results[$n]['pr'] = '';
            }

            $n++;
        }
    }
    return $results;
}

// 文字列であるかをチェック
function checkString($input) {
    if(isset($input) && is_string($input)) {
        return true;
    } else {
        return false;
    }
}
