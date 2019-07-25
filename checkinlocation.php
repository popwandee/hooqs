<?php
require_once __DIR__ . '/vendor/autoload.php';
/*
* ตั้งค่าแต่ละคีย์เป็นตัวแปรสภาพแวดล้อม
*
* Line Developers
* CHANNEL_SECRET = Channel Secret
* CHANNEL_ACCESS_TOKEN = Channel Access Token
*

*/
// Line Message APIに接続
$input = file_get_contents('php://input');
$json = json_decode($input);
$event = $json--->events[0];
$http_client = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
$bot = new \LINE\LINEBot($http_client, ['channelSecret' => getenv('CHANNEL_SECRET')]);
// รับตัวระบุข้อความ
$event_type = $event->type;
$event_message_type = $event->message->type;
// ในกรณีของข้อความ
if ('message' == $event_type) {
    // ในกรณีของข้อความ
    if ('text' == $event_message_type) {
        // รับข้อความ
        $text = $event->message->text;
        // หากคุณได้รับข้อความให้ส่งคืนข้อความตามที่เป็น
        $text_message_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($text);
        $response = $bot->replyMessage($event->replyToken, $text_message_builder);
    }
    // ในกรณีที่มีข้อความข้อมูลสถานที่
    else if ('location' == $event_message_type) {
        // การได้มาซึ่งข้อมูลละติจูดและลองจิจูด
        $latitude = $event->message->latitude;
        $longitude = $event->message->longitude;
        // การประกอบ URL เพื่อใช้ ตรวจสอบพิกัด
        $result = checkInLocation($latitude, $longitude);
       
        
        // ดำเนินการตามเนื้อหาที่ได้มาของข้อมูลพิกัด
        if($results != null) {
            
                /* // สร้างปุ่มเพื่อให้ภาพหมุน
                $action = new \LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder('Store details', $results[$i]['url']);
                 สร้างคอลัมน์สำหรับ carousel
                $column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder($results[$i]['name'], $info, $results[$i]['image_url'], [$action]);
                $columns[] = $column;
            }
            // model Carousel จากอาร์เรย์ของคอลัมน์
            $carousel_template_builder = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder($columns);
            $template_message = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder('เก็บรายการข้อมูล (5 ราย)', $carousel_template_builder);
            $message = new \LINE\LINEBot\MessageBuilder\MultiMessageBuilder();
            $message->add($template_message);
            $response = $bot->replyMessage($event->replyToken, $message);
            */
        } else {
            // เมื่อไม่มีผลการค้นหา
            $text_message_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('ฉันขอโทษ คุณไม่ได้อยู่ในพื้นที่ พัน.ขกท. . .');
            $response = $bot->replyMessage($event->replyToken, $text_message_builder);
        }
    }
    // สำหรับข้อความอื่นที่ไม่ใช่ข้อความและข้อมูลตำแหน่ง
    else {
        $text_message_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('ฉันไม่ทราบว่าคุณส่งอะไรมา <img draggable="false" class="emoji" alt="🍜" src="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" id="exifviewer-img-3" exifid="-1690832363" oldsrc="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" scale="0">');
        $response = $bot->replyMessage($event->replyToken, $text_message_builder);
    }
}
// เมื่อเพิ่มเพื่อน
else if ('follow' == $event_type) {
    // ข้อความเมื่อเพิ่มเพื่อน
    $text_message_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('ขอบคุณสำหรับการเพิ่มเพื่อนของคุณ! ยินดีที่ได้รู้จัก<img draggable="false" class="emoji" alt="🍜" src="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" id="exifviewer-img-4" exifid="-1690832363" oldsrc="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" scale="0">');
    $response = $bot->replyMessage($event->replyToken, $text_message_builder);
}
// เมื่อเพิ่มกลุ่ม
else if ('join' == $event_type) {
    // ข้อความเมื่อเพิ่มกลุ่ม
    $text_message_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('ยินดีต้อนรับ! <img draggable="false" class="emoji" alt="🍜" src="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" id="exifviewer-img-5" exifid="-1690832363" oldsrc="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" scale="0">よろしくにゃ<img draggable="false" class="emoji" alt="🍜" src="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" id="exifviewer-img-6" exifid="-1690832363" oldsrc="https://s.w.org/images/core/emoji/2.4/svg/1f35c.svg" scale="0">');
    $response = $bot->replyMessage($event->replyToken, $text_message_builder);
}
// การเข้าถึงอื่น ๆ (เช่นคำขอจากเบราว์เซอร์)
else {
    $text_message_builder = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder('การเข้าถึงที่ไม่ได้รับอนุญาต');
    $response = $bot->replyMessage($event->replyToken, $text_message_builder);
    echo $response->getHTTPStatus() . ' ' . $response->getRawBody();
}
// ตรวจสอบพิกัดว่าอยู่ใน พื้นที่ หรือไม่
function checkInLocation($latitude, $longitude) {
    // ตรวจสอบ Latitude
    $gnavi_uri = 'http://api.gnavi.co.jp/RestSearchAPI/20150630/';
    $gnavi_acckey = getenv('GNAVI_API_KEY');
    $gnavi_format = 'json';
    $gnavi_range = 3;
    $gnavi_category = 'RSFST08008'; // บะหมี่ราเมน
    // ชุดประกอบ URL
    $url  = sprintf('%s%s%s%s%s%s%s%s%s%s%s%s%s', $gnavi_uri, '?format=', $gnavi_format, '&keyid=', $gnavi_acckey, '&latitude=', $latitude, '&longitude=', $longitude, '&range=', $gnavi_range, '&category_s=', $gnavi_category);
    return $url;
}
// ติดตามผลของ Gurunavi API
function resultsParse($json) {
    $obj  = json_decode($json);
    //การเริ่มต้นอาร์เรย์ที่เกี่ยวข้อง
    $results = [];
    $total_hit_count = $obj->{'total_hit_count'};
    if ($total_hit_count !== null) {
        $n = 0;
        foreach($obj->{'rest'} as $val) {
            //ชื่อร้าน
            if (checkString($val->{'name'})) {
                $results[$n]['name'] = $val->{'name'};
            }
            //ที่อยู่
            if (checkString($val->{'address'})) {
                $results[$n]['address'] = $val->{'address'};
            }
            // URL ของ Gurunavi
            if (checkString($val->{'url'})) {
                $results[$n]['url'] = $val->{'url'};
            }
            // จัดเก็บภาพ
            if (checkString($val->{'image_url'}->{'shop_image1'})) {
                $results[$n]['image_url'] = $val->{'image_url'}->{'shop_image1'};
            } else {
                $results[$n]['image_url'] = '※※※ URL รูปภาพที่กำหนดเอง ※※※';
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
// ตรวจสอบว่ามันเป็นสตริง
function checkString($input) {
    if(isset($input) && is_string($input)) {
        return true;
    } else {
        return false;
    }
}
