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
}

if ($input["userId"] == NULL) {
    $result["state"] = false;
    $result["message"]["userId"] = "userId is missing";
}
if ($input["phone"] == NULL && $input["email"] == NULL) {
    $result["state"] = false;
    $result["message"]["contacts"] = "phone or email is missing";
}
if ($input["amount"] == NULL) {
    $result["state"] = false;
    $result["message"]["amount"] = "amount is missing";
}
if ($input["currency"] == NULL) {
    $result["state"] = false;
    $result["message"]["currency"] = "currency is missing";
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
$send_data["amount"] = $input["amount"];
$send_data["currency"] = $input["currency"];
$send_data["description"] = $input["description"];
$send_data["order_id"] = $input["userId"]."-".mt_rand(1000000, 9999999);
$send_data["server_url"] = $url."/callback.php?action=".urlencode($input["action"]);
if ($input["phone"] != NULL) {
    $send_data["phone"] = $input["phone"];
}
if ($input["email"] != NULL) {
    $send_data["email"] = $input["email"];
}
$data = base64_encode(json_encode($send_data));
$signature = base64_encode(sha1($private_key.$data.$private_key, true));
$result = json_decode(send_urlencode("data=".$data."&signature=".$signature, "https://www.liqpay.ua/api/request"), true);
echo json_encode($result);









