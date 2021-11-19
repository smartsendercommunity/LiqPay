<?php

// v1   19.11.2021
// Powered by M-Soft
// https://t.me/mufik

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

// Верификация данных
$signature = $signature = base64_encode(sha1($private_key.$_POST["data"].$private_key, true));
if ($_POST["signature"] != $signature) {
    $result["state"] = false;
    $result["message"]["signature"] = "signature is failed";
    echo json_encode($result);
    exit;
}
$data = json_decode(base64_decode($_POST["data"]), true);
if ($data["status"] != "success") {
    $result["state"] = false;
    $result["message"]["status"] = "wait is status=success";
	echo json_encode($result);
    exit;
}

// Запуск триггера в Smart Sender
$userId = (explode("-", $data["order_id"]))[0];
$trigger["name"] = $_GET["action"];
json_decode(send_bearer("https://api.smartsender.com/v1/contacts/".$userId."/fire?name=".$rtigger, $ss_token, "POST", $trigger), true);
$result["state"] = true;

echo json_encode($data);











