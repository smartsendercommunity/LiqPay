<?php

// v1   19.11.2021
// Powered by Smart Sender
// https://smartsender.com

ini_set('max_execution_time', '1700');
set_time_limit(1700);

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: application/json');
header('Content-Type: application/json; charset=utf-8');

http_response_code(200);

//--------------

$input = json_decode(file_get_contents('php://input'), true);
include ('config.php');

// Functions
{
function send_forward($inputJSON, $link){
	
$request = 'POST';	
		
$descriptor = curl_init($link);

 curl_setopt($descriptor, CURLOPT_POSTFIELDS, $inputJSON);
 curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
 curl_setopt($descriptor, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 
 curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $request);

    $itog = curl_exec($descriptor);
    curl_close($descriptor);

   		 return $itog;
		
}
function send_urlencode($param, $link){
	
$request = 'POST';	
		
$descriptor = curl_init($link);

 curl_setopt($descriptor, CURLOPT_POSTFIELDS, $param);
 curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
 curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $request);

    $itog = curl_exec($descriptor);
    curl_close($descriptor);

   		 return $itog;
		
}
function send_bearer($url, $token, $type = "GET", $param = []){
	
		
$descriptor = curl_init($url);

 curl_setopt($descriptor, CURLOPT_POSTFIELDS, json_encode($param));
 curl_setopt($descriptor, CURLOPT_RETURNTRANSFER, 1);
 curl_setopt($descriptor, CURLOPT_HTTPHEADER, array('User-Agent: M-Soft Integration', 'Content-Type: application/json', 'Authorization: Bearer '.$token)); 
 curl_setopt($descriptor, CURLOPT_CUSTOMREQUEST, $type);

    $itog = curl_exec($descriptor);
    curl_close($descriptor);

   		 return $itog;
		
}
}

if ($input["userId"] == NULL) {
    $result["state"] = false;
    $result["message"]["userId"] = "userId is missing";
}
if ($input["phone"] == NULL && $input["email"] == NULL) {
    $result["state"] = false;
    $result["message"]["contacts"] = "phone or email is missing";
}
if ($input["description"] == NULL) {
    $result["state"] = false;
    $result["message"]["description"] = "description is missing";
}
if ($input["action"] == NULL) {
    $result["state"] = false;
    $result["message"]["action"] = "action is missing";
}
if ($result["state"] === false) {
    http_response_code(422);
    echo json_encode($result);
    exit;
}

// Формирование данных
$send_data["version"] = 3;
$send_data["public_key"] = $public_key;
$send_data["action"] = "invoice_send";
$send_data["description"] = $input["description"];
$send_data["order_id"] = $input["userId"]."-".mt_rand(1000000, 9999999);
$send_data["server_url"] = $url."/callback.php?action=".urlencode($input["action"]);
if ($input["phone"] != NULL) {
    $send_data["phone"] = $input["phone"];
}
if ($input["email"] != NULL) {
    $send_data["email"] = $input["email"];
}

// Получение списка товаров в корзине пользователя
$cursor = json_decode(send_bearer("https://api.smartsender.com/v1/contacts/".$input["userId"]."/checkout?page=1&limitation=20", $ss_token), true);
if ($cursor["error"] != NULL && $cursor["error"] != 'undefined') {
    $result["status"] = "error";
    $result["message"][] = "Ошибка получения данных из SmartSender";
    if ($cursor["error"]["code"] == 404 || $cursor["error"]["code"] == 400) {
        $result["message"][] = "Пользователь не найден. Проверте правильность идентификатора пользователя и приналежность токена к текущему проекту.";
    } else if ($cursor["error"]["code"] == 403) {
        $result["message"][] = "Токен проекта SmartSender указан неправильно. Проверте правильность токена.";
    }
    echo json_encode($result);
    exit;
} else if (empty($cursor["collection"])) {
    $result["status"] = "error";
    $result["message"][] = "Корзина пользователя пустая. Для тестирования добавте товар в корзину.";
    echo json_encode($result);
    exit;
}
$pages = $cursor["cursor"]["pages"];
for ($i = 1; $i <= $pages; $i++) {
    $checkout = json_decode(send_bearer("https://api.smartsender.com/v1/contacts/".$input["userId"]."/checkout?page=".$i."&limitation=20", $ss_token), true);
	$essences = $checkout["collection"];
	$send_data["currency"] = $essences[0]["currency"];
	foreach ($essences as $product) {
	    $goods["amount"] = $product["price"];
	    $goods["count"] = $product["pivot"]["quantity"];
	    $goods["unit"] = " ";
	    $goods["name"] = $product["product"]["name"].': '.$product["name"];
	    $send_data["goods"][] = $goods;
	    unset($goods);
		$summ[] = $product["pivot"]["quantity"]*$product["cash"]["amount"];
    	}
    }
$send_data["amount"] = array_sum($summ);
$data = base64_encode(json_encode($send_data));
$signature = base64_encode(sha1($private_key.$data.$private_key, true));
$result = json_decode(send_urlencode("data=".$data."&signature=".$signature, "https://www.liqpay.ua/api/request"), true);
echo json_encode($result);









