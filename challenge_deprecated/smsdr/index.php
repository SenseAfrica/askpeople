<?php

	include '../libs_global.php';
	include '../vars.php';

	// ## RESPONSE HEADER ##
	$response_header="HTTP/1.1 200 OK";
	
	// ## RESPONSE BODY ##	
	$response_body="OK";
	
	// ### REQUEST BODY ###
	$request_body = file_get_contents('php://input');
	$json_request= json_decode( $request_body, TRUE ); //convert JSON into array
	// echo $request_body ;
	//print_r($request_body);
	
	//print_r($json_request);
	if (isset($json_request['deliveryInfoNotification'])){
	
	//{ "inboundSMSMessageNotification": { "inboundSMSMessage": { "senderAddress": "tel:+26772333490", "destinationAddress": "+26716968", "message": "BEGIN Test יטא@ END", "dateTime": "2014-04-15T07:40:13Z" } } }
	//{ "deliveryInfoNotification": { "deliveryInfo": { "address": "tel:+99xxxxxxxxxx", "deliveryStatus": "DeliveredToTerminal" }, "callbackData": "" } }
		
		$address=$json_request['deliveryInfoNotification']['deliveryInfo']['address'];
		$address = str_replace('tel:','',$address);	
		$deliveryStatus = $json_request['deliveryInfoNotification']['deliveryInfo']['deliveryStatus'];
		$callbackData=(int)$json_request['deliveryInfoNotification']['callbackData'];
		
		if(!$callbackData) return;
		include_once('../../db.php');
		$res=mysqli_query($db_conn,'SELECT org, phone, msg, todo FROM pendingsms WHERE id = '.$callbackData);
		if(($line=mysqli_fetch_assoc($res))&&($deliveryStatus!='MessageWaiting')){
			file_put_contents('res.txt','ok');
			mysqli_query($db_conn,'DELETE FROM pendingsms WHERE id = '.$callbackData);
			if (in_array($deliveryStatus,array('DeliveredToTerminal','DeliveredToNetwork'))) {
				if (!is_null($line['todo'])) eval($line['todo']);
			} else {
				include_once('../../utils.php');
				sms($line['org'],$line['phone'],$line['msg']);
			}
		}else file_put_contents('res.txt','ko');
	}
?>