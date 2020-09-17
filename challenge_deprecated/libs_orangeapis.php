<?php

function chargeAmountUser($msisdn, $price, $cur, $token){

	include 'vars.php';
	$msisdn = str_replace("+","",$msisdn);
	
	$url = $urlChargeAmount;
	$url=str_replace('/200/','/tel%3A%2B'.$msisdn.'/',$url);
	date_default_timezone_set('UTC');
	$thisDate = date("mdHis");
	$data = array("endUserId"=>'tel: +'.$msisdn,"transactionOperationStatus"=>"Charged","chargingInformation"=>array("description"=>"AskPeople account recharge","amount"=>$price,"currency"=>$cur),"chargingMetaData"=>array("serviceID"=>"Demo payment","productID"=>"001"),"referenceCode"=>$msisdn.'-'.$thisDate,"clientCorrelator"=>'ASK-'.$msisdn);
	$headerAuth = 'Authorization: Bearer '.$token;
	$data_string = json_encode($data);
	$ch = curl_init();	//  Initiate curl
	curl_setopt($ch, CURLOPT_SSLVERSION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300); //timeout in seconds
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                              
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',$headerAuth));
	curl_setopt($ch, CURLOPT_HEADER, 0);  //TRUE to include the header in the output.
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
	curl_setopt($ch, CURLOPT_URL,$url);	// Set the url
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result=curl_exec($ch);	// Execute
	$parsed_json = json_decode($result, true);
	$httpstatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	$data_array[0]=$httpstatus;
	$data_array[1]=$result;

	curl_close($ch);	// Closing
	return $data_array;

}

function sendSMS($msisdn, $msg, $senderName, $callbackdata, $token){

	include 'vars.php';
	$msisdn = str_replace("+","",$msisdn);

	$url = $urlSendSMS;
	date_default_timezone_set('UTC');
	$thisDate = date("YmdH:is");
	$thisDateMsg = date("Y-m-dTH:i:s");
	$data = array("address"=>array('tel:+'.$msisdn),"message"=>$msg,"senderName"=>$senderName,"callbackData"=>$callbackdata);
	$headerAuth = 'Authorization: Bearer '.$token;
	$data_string = json_encode($data);  //json_encode($data);
	$ch = curl_init();	//  Initiate curl
	curl_setopt($ch, CURLOPT_SSLVERSION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 300); //timeout in seconds
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                              
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json',$headerAuth));
	curl_setopt($ch, CURLOPT_HEADER, 0);  //TRUE to include the header in the output.
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
	curl_setopt($ch, CURLOPT_URL,$url);	// Set the url
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$result=curl_exec($ch);	// Execute
	$parsed_json = json_decode($result, true);
	$httpstatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	
	$data_array[0]=$httpstatus;
	$data_array[1]=$result;
	
	curl_close($ch);	// Closing
	return $data_array;

}


?>