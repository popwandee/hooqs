<?php // callback.php
// กรณีต้องการตรวจสอบการแจ้ง error ให้เปิด 3 บรรทัดล่างนี้ให้ทำงาน กรณีไม่ ให้ comment ปิดไป
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__."/vendor/autoload.php";
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use \Statickidz\GoogleTranslate;
use LINE\LINEBot;
use LINE\LINEBot\HTTPClient;
use LINE\LINEBot\HTTPClient\CurlHTTPClient;
use LINE\LINEBot\Constant\Flex\ComponentLayout;
use LINE\LINEBot\Constant\Flex\ComponentIconSize;
use LINE\LINEBot\Constant\Flex\ComponentImageSize;
use LINE\LINEBot\Constant\Flex\ComponentImageAspectRatio;
use LINE\LINEBot\Constant\Flex\ComponentImageAspectMode;
use LINE\LINEBot\Constant\Flex\ComponentFontSize;
use LINE\LINEBot\Constant\Flex\ComponentFontWeight;
use LINE\LINEBot\Constant\Flex\ComponentMargin;
use LINE\LINEBot\Constant\Flex\ComponentSpacing;
use LINE\LINEBot\Constant\Flex\ComponentButtonStyle;
use LINE\LINEBot\Constant\Flex\ComponentButtonHeight;
use LINE\LINEBot\Constant\Flex\ComponentSpaceSize;
use LINE\LINEBot\Constant\Flex\ComponentGravity;
use LINE\LINEBot\MessageBuilder;
use LINE\LINEBot\MessageBuilder\RawMessageBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use LINE\LINEBot\MessageBuilder\Imagemap\BaseSizeBuilder;
use LINE\LINEBot\MessageBuilder\ImagemapMessageBuilder;
use LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use LINE\LINEBot\MessageBuilder\LocationMessageBuilder;
use LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use LINE\LINEBot\ImagemapActionBuilder;
use LINE\LINEBot\ImagemapActionBuilder\AreaBuilder;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapMessageActionBuilder ;
use LINE\LINEBot\ImagemapActionBuilder\ImagemapUriActionBuilder;
use LINE\LINEBot\TemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\DatetimePickerTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\MessageTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;
use LINE\LINEBot\TemplateActionBuilder\UriTemplateActionBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ConfirmTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ImageCarouselColumnTemplateBuilder;
use LINE\LINEBot\MessageBuilder\FlexMessageBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\BoxComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ButtonComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\IconComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\ImageComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\TextComponentBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\CarouselContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ContainerBuilder\BubbleContainerBuilder;
use LINE\LINEBot\MessageBuilder\Flex\ComponentBuilder\SpacerComponentBuilder;
use LINE\LINEBot\QuickReplyBuilder\ButtonBuilder\QuickReplyButtonBuilder;
use LINE\LINEBot\QuickReplyBuilder\QuickReplyMessageBuilder;
$logger = new Logger('LineBot');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));
define("MLAB_API_KEY", '6QxfLc4uRn3vWrlgzsWtzTXBW7CYVsQv');
define("LINE_MESSAGING_API_CHANNEL_SECRET", 'eb6cf532359c17403e5e20339b389466');
define("LINE_MESSAGING_API_CHANNEL_TOKEN", 'yf5kpt5rDBiNTVwoI/tkKWlCXvD2fJBq9dDKfqxcuu7qIwf+auxo5hs3wGJsj0Shq5UCfkhGf8gLrcB4PluHJ4ViBppUh5/6PllJ4xi7z+dMUTaNwLa3FXC+FwgVqSvbn7WGnUASUMtkgsh/9dhl9AdB04t89/1O/w1cDnyilFU=');
$bot = new \LINE\LINEBot(
    new \LINE\LINEBot\HTTPClient\CurlHTTPClient(LINE_MESSAGING_API_CHANNEL_TOKEN),
    ['channelSecret' => LINE_MESSAGING_API_CHANNEL_SECRET]
);
$signature = $_SERVER["HTTP_".\LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
try {
	$events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
	error_log('parseEventRequest failed. InvalidSignatureException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
	error_log('parseEventRequest failed. UnknownEventTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
	error_log('parseEventRequest failed. UnknownMessageTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
	error_log('parseEventRequest failed. InvalidEventRequestException => '.var_export($e, true));
}
foreach ($events as $event) {
	// Message Event
 if ($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage) {
  $rawText = $event->getText();$text = strtolower($rawText);$explodeText=explode(" ",$text);$textReplyMessage="";
	$log_note=$text;
	 $tz_object = new DateTimeZone('Asia/Bangkok');
         $datetime = new DateTime();
         $datetime->setTimezone($tz_object);
         $dateTimeNow = $datetime->format('Y\-m\-d\ H:i:s');
	$replyToken = $event->getReplyToken();	
        $multiMessage =     new MultiMessageBuilder;
	$replyData='No Data';
        $userId=$event->getUserId();
	$res = $bot->getProfile($userId);
         if ($res->isSucceeded()) {
              $profile = $res->getJSONDecodedBody();
              if(!is_null($profile['displayName'])){$displayName = $profile['displayName'];}else{$displayName ='';}
              if(!is_null($profile['statusMessage'])){$statusMessage = $profile['statusMessage'];}else{$statusMessage ='';}
              if(!is_null($profile['pictureUrl'])){$pictureUrl = $profile['pictureUrl'];}else{$pictureUrl ='';}
	      $textReplyMessage= "คุณ ".$displayName;
	      //$textMessage = new TextMessageBuilder($textReplyMessage);
	      //$multiMessage->add($textMessage);  
		 
		 if(($explodeText[0]=='#register') and (isset($explodeText[1]))){ // เก็บข้อมูลผู้สมัคร แต่ยังคงให้ status =0
			$text_parameter = str_replace("#register ","", $text); 
			$newUserData = json_encode(array('userName' => $text_parameter,'displayName' => $displayName,
					'userId'=> $userId,'statusMessage'=> $statusMessage,
					'pictureUrl'=>$pictureUrl,'status'=>0) );
                        $opts = array('http' => array( 'method' => "POST",
                        'header' => "Content-type: application/json",
                        'content' => $newUserData ) );
           
                        $url = 'https://api.mlab.com/api/1/databases/hooqline/collections/user_register?apiKey='.MLAB_API_KEY.'';
                        $context = stream_context_create($opts);
                        $returnValue = file_get_contents($url,false,$context);
			if($returnValue){
		              $textReplyMessage= "คุณ".$displayName." ได้ส่ง รหัสเครื่องให้นกฮูกแล้วนะคะ\n\n รอการอนุมัติสักครู่นะคะ เพื่อให้การลงทะเบียนสมบูรณ์ (ปกติจะใช้เวลาไม่นานถ้าไม่ลืมนะคะ คริคริ) หลังจากที่ท่านลงทะเบียนแล้วถึงจะสามารถตรวจสอบข้อมูลกับนกฮูกได้นะค่ะ";
			      $textReplyMessage= $textReplyMessage."\n\n เพื่อป้องกันการเข้ามาใช้งานโดยไม่ได้รับอนุญาต และปกป้องข้อมูลของหน่วย ซึ่งเป็นเรื่องที่สำคัญ \n\n ผู้ใช้จำเป็นต้องลงทะเบียน เมื่อท่านพิมพ์ #register และข้อมูลส่วนตัวของท่าน แสดงว่าท่านยินยอมให้นกฮูกเก็บรหัสของ LINE กับอุปกรณ์ที่ท่านใช้งาน เพื่อยืนยันตัวบุคคลก่อน";
			      $textReplyMessage= $textReplyMessage."\n\n พิมพ์ #help เพื่อสอบถามวิธีการตั้งคำถามให้นกฮูกช่วยตอบ";
                              $textReplyMessage= $textReplyMessage."\n\n รหัสของคุณคือ ".$userId."\n\n รหัสของคุณจะใช้ได้จนกว่าคุณจะสมัคร LINE ใหม่ หรือเปลี่ยนเครื่อง \n\n ";
			      $textMessage = new TextMessageBuilder($textReplyMessage);
			      $multiMessage->add($textMessage);		                           
			      $textReplyMessage= $userId;
                              $textMessage = new TextMessageBuilder($textReplyMessage);
			      $multiMessage->add($textMessage);
			      $replyData = $multiMessage;
			      $response = $bot->replyMessage($replyToken,$replyData);
			      $userId = NULL;
				 }else{
				$textReplyMessage= "คุณ".$displayName." ไม่สามารถลงทะเบียน ID ".$userId." ได้ค่ะ\n\n กรุณาลองใหม่อีกครั้งค่ะ \n\nหรือแจ้งผู้ดูแลระบบโดยตรงนะคะ";
                                $textMessage = new TextMessageBuilder($textReplyMessage);
			        $multiMessage->add($textMessage);
                                $replyData = $multiMessage;
				$userId = NULL;
			}
		 } // end #register
		 
		 /*---- prove user by update status from 0 to 1---*/
		 
		 /*---- prove user by update status from 0 to 1---*/
		if(($explodeText[0]=='#prove') and ($userId=='Ua300e9b08826b655e221d12b446d34e5')){ 
				$toProveUserId = str_replace("#prove ","", $rawText);  
			// get $_id
				$json = file_get_contents('https://api.mlab.com/api/1/databases/hooqline/collections/user_register?apiKey='.MLAB_API_KEY.'&q={"userId":"'.$toProveUserId.'"}');
                                  $data = json_decode($json);
                                  $isGet_id=sizeof($data);
                                 if($isGet_id >0){
                                    foreach($data as &$rec){
                                       $documentId= $rec->_id;
					    foreach($documentId as $key => $value){
						    if($key === '$oid'){
							    $updateId=$value;
					                   $textReplyMessage="อนุมัติ Id ".$rec->userId." แล้วค่ะ";
					                    }
					             } // end for each $key=>$value
					    }//end for each
			  $updateUserData = json_encode(array('$set' => array('status' => '1')));
			  $opts = array('http' => array( 'method' => "PUT",
                                          'header' => "Content-type: application/json",
                                          'content' => $updateUserData
                                           )
                                        );
           
                                  $url = 'https://api.mlab.com/api/1/databases/hooqline/collections/user_register/'.$updateId.'?apiKey='.MLAB_API_KEY;
                                  $context = stream_context_create($opts);
                                  $returnValue = file_get_contents($url,false,$context);
				 }else{// end isGet_id
					$textReplyMessage=$explodeText[1]." No User ID";
				 }// end isGet_id
				 $textMessage = new TextMessageBuilder($textReplyMessage);
			          $multiMessage->add($textMessage);
			          $replyData = $multiMessage;
			           $response = $bot->replyMessage($replyToken,$replyData);
			 } // end #prove
		 /*--------------------------*/
		 if($explodeText[0]=='#help'){
			  $textReplyMessage= "คุณ".$displayName."\n\n เพื่อป้องกันการเข้ามาใช้งานโดยไม่ได้รับอนุญาต และปกป้องข้อมูลของหน่วย ซึ่งเป็นเรื่องที่สำคัญ \n\n ผู้ใช้จำเป็นต้องลงทะเบียน เมื่อท่านพิมพ์ #register และข้อมูลส่วนตัวของท่าน แสดงว่าท่านยินยอมให้นกฮูกเก็บรหัสของ LINE กับอุปกรณ์ที่ท่านใช้งาน เพื่อยืนยันตัวบุคคลก่อน";
			 $textReplyMessage= $textReplyMessage."\nพิมพ์ #register ยศ ชื่อ นามสกุล ตำแหน่ง สังกัด หมายเลขโทรศัพท์ เพื่อลงทะเบียนขอใช้งานระบบ หลังจากนั้นรอผู้ดูแล อนุมัติ ท่านจะใช้งานได้ค่ะ ปกติก็ใช้เวลาไม่นานนะคะ ถ้าไม่ลืม คริคริ";
			 $textReplyMessage= $textReplyMessage."\n\n#help ";
			 $textReplyMessage= $textReplyMessage."\n พิมพ์ #help เพื่อสอบถามวิธีการตั้งคำถามให้นกฮูกช่วยตอบ";
			 $textReplyMessage= $textReplyMessage."\n\n พิมพ์ #c ทะเบียนรถ (เช่น #c กก12345ยะลา) เพื่อตรวจสอบทะเบียนรถ"; 
			 $textReplyMessage= $textReplyMessage."\n\n พิมพ์ #p หมายเลข ปชช. 13 หลัก (เช่น #p 1234567891234) เพื่อตรวจสอบประวัติบุคคลใน ทกร.";
			 $textReplyMessage= $textReplyMessage."\n\n#lisa คำถาม คำตอบ";
			 $textReplyMessage= $textReplyMessage."\n พิมพ์ #lisa เพื่อสอนความรู้ใหม่ให้นกฮูก เช่น #lisa ยะลา จังหวัดหนึ่งในประเทศไทย) ";
			 $textReplyMessage= $textReplyMessage."\n\n#lisa คำถาม ";
			 $textReplyMessage= $textReplyMessage."\n พิมพ์ #lisa ค เพื่อสอบถามข้อมูลจากนกฮูก  (เช่น #lisa ยะลา )";
			 $textReplyMessage= $textReplyMessage."\n\n พิมพ์ #tran รหัสประเทศต้นทาง ปลายทาง คำที่ต้องการแปล (เช่น #tran ms th hello แปลคำว่า hello จากมาเลเซียเป็นไทย) เพื่อแปลภาษา";
			 $textReplyMessage= $textReplyMessage."\n\n th ไทย ms มาเลเซีย id อินโดนีเซีย zh-CN จีน en อังกฤษ";			 
			 $textReplyMessage= $textReplyMessage."\n\n อาจจะยุ่งยากนิดนึงนะคะ แต่เพื่อป้องกันไม่ให้นกฮูกตอบเองโดยไม่ตั้งใจถาม จะเป็นการรบกวนพี่ๆ นะคะ นกฮูกเกรงจายยยยยยยย";
				 $textMessage = new TextMessageBuilder($textReplyMessage);
			          $multiMessage->add($textMessage);
			          $replyData = $multiMessage;
			          $response = $bot->replyMessage($replyToken,$replyData);
		 }// end of help
		 
              }else{ // end get displayName succeed
		 /*-----------------  register by no data --*/
		  if(($explodeText[0]=='#register') and (isset($explodeText[1]))){ // เก็บข้อมูลผู้สมัคร แต่ยังคงให้ status =0
			  
			                $text_parameter = str_replace("#register ","", $text); 
			               $displayName ='';
                                       $statusMessage ='';
                                       $pictureUrl ='';
			                $text_parameter = str_replace("#register ","", $text); 
					$newUserData = json_encode(array('userName' => $text_parameter,'displayName' => $displayName,
									 'userId'=> $userId,'statusMessage'=> $statusMessage,
									 'pictureUrl'=>$pictureUrl,'status'=>0) );
                                        $opts = array('http' => array( 'method' => "POST",
                                          'header' => "Content-type: application/json",
                                          'content' => $newUserData ) );
           
                                       $url = 'https://api.mlab.com/api/1/databases/hooqline/collections/user_register?apiKey='.MLAB_API_KEY.'';
                                       $context = stream_context_create($opts);
                                       $returnValue = file_get_contents($url,false,$context);
			               if($returnValue){
		                           $textReplyMessage= "คุณ".$displayName." ได้ส่ง รหัสเครื่องให้น้องนกฮูกแล้วนะคะ\n\n รอการอนุมัติสักครู่นะคะ เพื่อให้การลงทะเบียนสมบูรณ์ (ปกติจะใช้เวลาไม่นานถ้าไม่ลืมนะคะ คริคริ) หลังจากที่ท่านลงทะเบียนแล้วถึงจะสามารถตรวจสอบข้อมูลกับน้องนกฮูกได้นะค่ะ";
			                    $textReplyMessage= $textReplyMessage."\n\n เพื่อป้องกันการเข้ามาใช้งานโดยไม่ได้รับอนุญาต และปกป้องข้อมูลของหน่วย ซึ่งเป็นเรื่องที่สำคัญ \n\n ผู้ใช้จำเป็นต้องลงทะเบียน เมื่อท่านพิมพ์ #register และข้อมูลส่วนตัวของท่าน แสดงว่าท่านยินยอมให้น้องนกฮูกเก็บรหัสของ LINE กับอุปกรณ์ที่ท่านใช้งาน เพื่อยืนยันตัวบุคคลก่อน";
			                    $textReplyMessage= $textReplyMessage."\n\n พิมพ์ #help เพื่อสอบถามวิธีการตั้งคำถามให้น้องนกฮูกช่วยตอบ";
                                            $textReplyMessage= $textReplyMessage."\n\n รหัสของคุณคือ ".$userId."\n\n รหัสของคุณจะใช้ได้จนกว่าคุณจะสมัคร LINE ใหม่ หรือเปลี่ยนเครื่อง ";
			                   $textMessage = new TextMessageBuilder($textReplyMessage);
			                   $multiMessage->add($textMessage);		                           
					   $textReplyMessage= $userId;
                                           $textMessage = new TextMessageBuilder($textReplyMessage);
			                   $multiMessage->add($textMessage);
					   $replyData = $multiMessage;
			                   $response = $bot->replyMessage($replyToken,$replyData);
					   $userId = NULL;
				           }else{
					   $textReplyMessage= "คุณ".$displayName." ไม่สามารถลงทะเบียน ID ".$userId." ได้ค่ะ\n\n กรุณาลองใหม่อีกครั้งค่ะ \n\nหรือแจ้งผู้ดูแลระบบโดยตรงนะคะ";
                                           $textMessage = new TextMessageBuilder($textReplyMessage);
			                   $multiMessage->add($textMessage);
                                           $replyData = $multiMessage;
					   $userId = NULL;
				       }
		 } // can not get displayName and //end of #register by userId 
	 }// end can not get displayName
	if(!is_null($userId)){
	    $json = file_get_contents('https://api.mlab.com/api/1/databases/hooqline/collections/user_register?apiKey='.MLAB_API_KEY.'&q={"userId":"'.$userId.'"}');
            $data = json_decode($json);
            $isUserRegister=sizeof($data);
		if($isUserRegister <=0){
		           $notRegisterReplyMessage= "คุณ".$displayName." ยังไม่ได้ลงทะเบียน ID ".$userId." ไม่สามารถเข้าถึงฐานข้อมูลได้นะคะ\n กรุณาพิมพ์ #register ยศ ชื่อ นามสกุล ตำแหน่ง สังกัด หมายเลขโทรศัพท์ เพื่อลงทะเบียนค่ะ";
                          //$log_note = $log_note.$notRegisterReplyMessage;
	         }else{ // User registered
                    foreach($data as $rec){
			    $registerUserReplyMessage="From phone \nDisplayname ".$displayName."\n User Id ".$userId;
			    $userName=$rec->userName;
                           //$log_note = $log_note."From phone \nDisplayname ".$displayName."\n User Id ".$userId;
                           //$log_note= $log_note."\nFrom DB\nDisplayname ".$rec->displayName."\n Registered Id ".$rec->userId;
			     }//end for each
	if($rec->status==1){ // อนุมัติตัวบุคคลแล้ว
		switch ($explodeText[0]) { 
			case '#p':
				if (!is_null($explodeText[1])){
			          $json = file_get_contents('https://api.mlab.com/api/1/databases/hooqline/collections/people?apiKey='.MLAB_API_KEY.'&q={"nationid":"'.$explodeText[1].'"}');
                                  $data = json_decode($json);
                                  $isData=sizeof($data);
                                 if($isData >0){
                                    $count=1;
                                    foreach($data as $rec){
	                               $count++;
                                       $textReplyMessage= "\nหมายเลข ปชช. ".$rec->nationid."\nชื่อ".$rec->name."\nที่อยู่".$rec->address."\nหมายเหตุ".$rec->note;
                                       $textMessage = new TextMessageBuilder($textReplyMessage);
	                               $multiMessage->add($textMessage);
				       //$log_note= $log_note.$textReplyMessage;
	                              if (!is_null($rec->picUrl)){
	                               $picFullSize = "https://firebasestorage.googleapis.com/v0/b/carlicenseplate.appspot.com/o/$rec->picUrl";
	                               $imageMessage = new ImageMessageBuilder($picFullSize,$picFullSize);
	                               $multiMessage->add($imageMessage);
				      }else{ 
	                               $picFullSize = "https://firebasestorage.googleapis.com/v0/b/carlicenseplate.appspot.com/o/demo_person.png?alt=media&token=0e0da7f2-ecbd-4751-9a97-2fe9f52fe663";
	                               $imageMessage = new ImageMessageBuilder($picFullSize,$picFullSize);
	                               $multiMessage->add($imageMessage);
				      }
			               $replyData = $multiMessage;
                                    }//end for each

                                 }else{ //$isData <0  ไม่พบข้อมูลที่ค้นหา
                                   $textReplyMessage= "ไม่พบ ".$explodeText[1]."  ในฐานข้อมูลของหน่วย";
	                           $textMessage = new TextMessageBuilder($textReplyMessage);
	                           $multiMessage->add($textMessage);
			           $replyData = $multiMessage;
                                   } // end $isData>0
				}else{ // no $explodeText[1]
			          $textReplyMessage= "ให้ข้อมูลสำหรับการตรวจสอบบุคคลไม่ครบค่ะ";
			          $textMessage = new TextMessageBuilder($textReplyMessage);
			          $multiMessage->add($textMessage);
			          $replyData = $multiMessage;
		                }// end !is_null($explodeText[1])
				//$log_note=$log_note."\n User select #p ".$textReplyMessage;
			        break;
                                                                    					
			    case '#c':
				if (!is_null($explodeText[1])){
			          $json = file_get_contents('https://api.mlab.com/api/1/databases/hooqline/collections/register_south?apiKey='.MLAB_API_KEY.'&q={"license_plate":"'.$explodeText[1].'"}');
                                  $data = json_decode($json);
                                  $isData=sizeof($data);
                                 if($isData >0){
                                    $count=1;
                                    foreach($data as $rec){
	                               $count++;
                                       $textReplyMessage= "\n ทะเบียน ".$rec->license_plate."\nยี่ห้อ".$rec->brand."\nรุ่น".$rec->model."\nสี".$rec->color."\nผู้ครอบครอง ".$rec->user."\nประวัติ".$rec->note."\nหากข้อมูลรถไม่เป็นไปตามนี้ให้สงสัยว่าทะเบียนปลอม";
                                       $textMessage = new TextMessageBuilder($textReplyMessage);
	                               $multiMessage->add($textMessage);
				       //$log_note= $log_note.$textReplyMessage;
	                              if (!is_null($rec->picUrl)){
	                               $picFullSize = "https://firebasestorage.googleapis.com/v0/b/lisa-77436.appspot.com/o/$rec->picUrl";
	                               $imageMessage = new ImageMessageBuilder($picFullSize,$picFullSize);
	                               $multiMessage->add($imageMessage);
				      }else{
				       $picFullSize = "https://firebasestorage.googleapis.com/v0/b/lisa-77436.appspot.com/o/carsImage%2Fdemo_car.png?alt=media&token=e183745a-5fa0-41b7-89b4-d863c572adc3";
	                               $imageMessage = new ImageMessageBuilder($picFullSize,$picFullSize);
	                               $multiMessage->add($imageMessage);
				      }
			               $replyData = $multiMessage;
                                    }//end for each
                                 }else{ //$isData <0  ไม่พบข้อมูลที่ค้นหา
                                   $textReplyMessage= "ไม่พบ ".$explodeText[1]."  ในฐานข้อมูลของหน่วย";
	                           $textMessage = new TextMessageBuilder($textReplyMessage);
	                           $multiMessage->add($textMessage);
			           $replyData = $multiMessage;
                                   } // end $isData>0
				}else{ // no $explodeText[1]
			          $textReplyMessage= "ให้ข้อมูลสำหรับการตรวจสอบยานพาหนะไม่ครบค่ะ";
			          $textMessage = new TextMessageBuilder($textReplyMessage);
			          $multiMessage->add($textMessage);
			          $replyData = $multiMessage;
		                }// end !is_null($explodeText[1])
				//$log_note=$log_note."\n User select #p ".$textReplyMessage;
			        break;
			case '#update':
				$toProveUserId = str_replace("#update ","", $rawText);  
			// get $_id
				$json = file_get_contents('https://api.mlab.com/api/1/databases/hooqline/collections/people?apiKey='.MLAB_API_KEY.'&q={"nationid":"'.$explodeText[1].'"}');
                                  $data = json_decode($json);
                                  $isGet_id=sizeof($data);
                                 if($isGet_id >0){
                                    foreach($data as &$rec){
                                       $documentId= $rec->_id;
					    foreach($documentId as $key => $value){
						    if($key === '$oid'){
							    $updateId=$value;
					                    $textReplyMessage="Update Id ".$rec->nationid;
					                    }
					             } // end for each $key=>$value
					    }//end for each
			  $updateData = json_encode(array('$set' => array('$explodeText[1]' => '$explodeText[2]')));
			  $opts = array('http' => array( 'method' => "PUT",
                                          'header' => "Content-type: application/json",
                                          'content' => $updateData
                                           )
                                        );
           
                                  $url = 'https://api.mlab.com/api/1/databases/hooqline/collections/people/'.$updateId.'?apiKey='.MLAB_API_KEY;
                                  $context = stream_context_create($opts);
                                  $returnValue = file_get_contents($url,false,$context);
				 }else{// end isGet_id
					$textReplyMessage=$explodeText[1]." No national ID";
				 }// end isGet_id
				 $textMessage = new TextMessageBuilder($textReplyMessage);
			          $multiMessage->add($textMessage);
			          $replyData = $multiMessage;
			           $response = $bot->replyMessage($replyToken,$replyData);
				break;
		case '#lisa':
				if(!isset($explodeText[2])){ // just question, 
				$json = file_get_contents('https://api.mlab.com/api/1/databases/hooqline/collections/knowledge?apiKey='.MLAB_API_KEY.'&q={"question":"'.$explodeText[1].'"}');
                                $data = json_decode($json);
                                $isData=sizeof($data);
                                if($isData >0){
                                   foreach($data as $rec){
                                           $textReplyMessage= $textReplyMessage."\n".$explodeText[1]." คือ\n".$rec->answer."\n";
                                           }//end for each
				    $textMessage = new TextMessageBuilder($textReplyMessage);
		                    $multiMessage->add($textMessage);
		                    $replyData = $multiMessage;
                                    }
				}else{// no answer
                                //Post New Data
		                $indexCount=1;$answer='';
	                        foreach($explodeText as $rec){
		                       $indexCount++;
		                       if($indexCount>1){
		                           $answer= $answer." ".$explodeText[$indexCount];
		                          }
	                                }
                                $newData = json_encode(array('question' => $explodeText[1],'answer'=> $answer) );
                                $opts = array('http' => array( 'method' => "POST",
                                          'header' => "Content-type: application/json",
                                          'content' => $newData
                                           )
                                        );
                                $url = 'https://api.mlab.com/api/1/databases/hooqline/collections/knowledge?apiKey='.MLAB_API_KEY.'';
                                $context = stream_context_create($opts);
                                $returnValue = file_get_contents($url,false,$context);
                                       if($returnValue){
		                          $textReplyMessage= $textReplyMessage."\nขอบคุณที่สอนน้องนกฮูกค่ะ";
		                          $textReplyMessage= $textReplyMessage."\nน้องนกฮูกจำได้แล้วว่า ".$explodeText[1]." คือ ".$answer;
	                                      }else{ $textReplyMessage= $textReplyMessage."\nสอนน้องนกฮูกไม่สำเร็จ";
		                                     }
				    $textMessage = new TextMessageBuilder($textReplyMessage);
		                    $multiMessage->add($textMessage);
		                    $replyData = $multiMessage;
				}// end no answer, just question only
                                 break;
			   
			   case '#tran':
			        $text_parameter = str_replace("#tran ","", $text);  
                                if (!is_null($explodeText[1])){ $source =$explodeText[1];}else{$source ='en';}
                                if (!is_null($explodeText[2])){ $target =$explodeText[2];}else{$target ='th';}
                                $result=tranlateLang($source,$target,$text_parameter);
				$flexData = new ReplyTranslateMessage;
                                $replyData = $flexData->get($text_parameter,$result);
				//$log_note=$log_note."\n User select #tran ".$text_parameter.$result;
		                break;
			
			   default: 
				$replyData ="";
				break;
                        }//end switch 
			
			}// end check user status == 1
		   
	              }// end User Registered 
		
		//-- บันทึกการเข้าใช้งานระบบ ---//
		
              if(!is_null($displayName)){
		      $displayName =$displayName;
	      }elseif(isset($userName)){
		      $displayName =$userName;
		 }else{
		      $displayName = ' ';
	      }
              if(is_null($pictureUrl)){$pictureUrl ='';}
		   $newUserData = json_encode(array('displayName' => $displayName,'userId'=> $userId,'dateTime'=> $dateTimeNow,
						    'log_note'=>$log_note,'pictureUrl'=>$pictureUrl) );
                           $opts = array('http' => array( 'method' => "POST",
                                          'header' => "Content-type: application/json",
                                          'content' => $newUserData
                                           )
                                        );
           
            $url = 'https://api.mlab.com/api/1/databases/hooqline/collections/use_log?apiKey='.MLAB_API_KEY.'';
            $context = stream_context_create($opts);
            $returnValue = file_get_contents($url,false,$context);
		
	} // end of !is_null($userId)
	
	
	
            // ส่งกลับข้อมูล
	    // ส่วนส่งกลับข้อมูลให้ LINE
           $response = $bot->replyMessage($replyToken,$replyData);
           if ($response->isSucceeded()) { echo 'Succeeded!'; return;}
              // Failed ส่งข้อความไม่สำเร็จ
             $statusMessage = $response->getHTTPStatus() . ' ' . $response->getRawBody(); echo $statusMessage;
             $bot->replyText($replyToken, $statusMessage);   
	}//end if event is textMessage
}// end foreach event



function tranlateLang($source, $target, $text_parameter)
{
    $text = str_replace($source,"", $text_parameter);
    $text = str_replace($target,"", $text);  
    $trans = new GoogleTranslate();
    $result = $trans->translate($source, $target, $text);	    
    return $result;
}
class ReplyTranslateMessage
{
    /**
     * Create  flex message
     *
     * @return \LINE\LINEBot\MessageBuilder\FlexMessageBuilder
     */
    public static function get($question,$answer)
    {
        return FlexMessageBuilder::builder()
            ->setAltText('Lisa')
            ->setContents(
                BubbleContainerBuilder::builder()
                    ->setHero(self::createHeroBlock())
                    ->setBody(self::createBodyBlock($question,$answer))
                    ->setFooter(self::createFooterBlock())
            );
    }
    private static function createHeroBlock()
    {
	   
        return ImageComponentBuilder::builder()
            ->setUrl('https://www.hooq.info/wp-content/uploads/2019/02/Connect-with-precision.jpg')
            ->setSize(ComponentImageSize::FULL)
            ->setAspectRatio(ComponentImageAspectRatio::R20TO13)
            ->setAspectMode(ComponentImageAspectMode::FIT)
            ->setAction(new UriTemplateActionBuilder(null, 'https://www.hooq.info'));
    }
    private static function createBodyBlock($question,$answer)
    {
        $title = TextComponentBuilder::builder()
            ->setText($question)
            ->setWeight(ComponentFontWeight::BOLD)
	    ->setwrap(true)
            ->setSize(ComponentFontSize::SM);
        
        $textDetail = TextComponentBuilder::builder()
            ->setText($answer)
            ->setSize(ComponentFontSize::LG)
            ->setColor('#000000')
            ->setMargin(ComponentMargin::MD)
	    ->setwrap(true)
            ->setFlex(2);
        $review = BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            ->setMargin(ComponentMargin::LG)
            ->setSpacing(ComponentSpacing::SM)
            ->setContents([$title,$textDetail]);
        return BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            ->setContents([$review]);
    }
    private static function createFooterBlock()
    {
        
        $websiteButton = ButtonComponentBuilder::builder()
            ->setStyle(ComponentButtonStyle::LINK)
            ->setHeight(ComponentButtonHeight::SM)
            ->setFlex(0)
            ->setAction(new UriTemplateActionBuilder('เพิ่มเติม','https://www.hooq.info'));
        $spacer = new SpacerComponentBuilder(ComponentSpaceSize::SM);
        return BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            ->setSpacing(ComponentSpacing::SM)
            ->setFlex(0)
            ->setContents([$websiteButton, $spacer]);
    }
} 
class ReplyCarRegisterMessage
{
    /**
     * Create  flex message
     *
     * @return \LINE\LINEBot\MessageBuilder\FlexMessageBuilder
     */
    public static function get($question,$answer,$picUrl)
    {
        return FlexMessageBuilder::builder()
            ->setAltText('Lisa')
            ->setContents(
                BubbleContainerBuilder::builder()
                    ->setHero(self::createHeroBlock($picUrl))
                    ->setBody(self::createBodyBlock($question,$answer))
                    ->setFooter(self::createFooterBlock($picUrl))
            );
    }
    private static function createHeroBlock($picUrl)
    {
	   
        return ImageComponentBuilder::builder()
            ->setUrl($picUrl)
            ->setSize(ComponentImageSize::FULL)
            ->setAspectRatio(ComponentImageAspectRatio::R20TO13)
            ->setAspectMode(ComponentImageAspectMode::FIT)
            ->setAction(new UriTemplateActionBuilder(null, $picUrl));
    }
    private static function createBodyBlock($question,$answer)
    {
        $title = TextComponentBuilder::builder()
            ->setText($question)
            ->setWeight(ComponentFontWeight::BOLD)
	    ->setwrap(true)
            ->setSize(ComponentFontSize::SM);
        
        $textDetail = TextComponentBuilder::builder()
            ->setText($answer)
            ->setSize(ComponentFontSize::LG)
            ->setColor('#000000')
            ->setMargin(ComponentMargin::MD)
	    ->setwrap(true)
            ->setFlex(2);
        $review = BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            //->setLayout(ComponentLayout::BASELINE)
            ->setMargin(ComponentMargin::LG)
            //->setMargin(ComponentMargin::SM)
            ->setSpacing(ComponentSpacing::SM)
            ->setContents([$title,$textDetail]);
	
	    /*    
        $place = BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::BASELINE)
            ->setSpacing(ComponentSpacing::SM)
            ->setContents([
                TextComponentBuilder::builder()
                    ->setText('ที่อยู่')
                    ->setColor('#aaaaaa')
                    ->setSize(ComponentFontSize::SM)
                    ->setFlex(1),
                TextComponentBuilder::builder()
                    ->setText('Samsen, Bangkok')
                    ->setWrap(true)
                    ->setColor('#666666')
                    ->setSize(ComponentFontSize::SM)
                    ->setFlex(5)
            ]);
        $time = BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::BASELINE)
            ->setSpacing(ComponentSpacing::SM)
            ->setContents([
                TextComponentBuilder::builder()
                    ->setText('Time')
                    ->setColor('#aaaaaa')
                    ->setSize(ComponentFontSize::SM)
                    ->setFlex(1),
                TextComponentBuilder::builder()
                    ->setText('10:00 - 23:00')
                    ->setWrap(true)
                    ->setColor('#666666')
                    ->setSize(ComponentFontSize::SM)
                    ->setFlex(5)
            ]);
	    
        $info = BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            ->setMargin(ComponentMargin::LG)
            ->setSpacing(ComponentSpacing::SM)
            ->setContents([$place, $time]);*/
        return BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            //->setContents([$review, $info]);
            ->setContents([$review]);
    }
    private static function createFooterBlock($picUrl)
    {
        
        $websiteButton = ButtonComponentBuilder::builder()
            ->setStyle(ComponentButtonStyle::LINK)
            ->setHeight(ComponentButtonHeight::SM)
            ->setFlex(0)
            ->setAction(new UriTemplateActionBuilder('เพิ่มเติม','https://www.hooq.info'));
        $spacer = new SpacerComponentBuilder(ComponentSpaceSize::SM);
        return BoxComponentBuilder::builder()
            ->setLayout(ComponentLayout::VERTICAL)
            ->setSpacing(ComponentSpacing::SM)
            ->setFlex(0)
            ->setContents([$websiteButton, $spacer]);
    }
} 
