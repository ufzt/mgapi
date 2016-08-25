<?php
error_reporting(E_ALL); 
ini_set('display_errors', '1'); 
date_default_timezone_set('Asia/Shanghai'); 
/*if (!defined("TOP_SDK_WORK_DIR"))
{
	define("TOP_SDK_WORK_DIR", "/tmp/");
}

if (!defined("TOP_SDK_DEV_MODE"))
{
	define("TOP_SDK_DEV_MODE", true);
}

if (!defined("TOP_AUTOLOADER_PATH"))
{
	define("TOP_AUTOLOADER_PATH", dirname(__FILE__));
}
include "top/TopClient.php";
include "top/ResultSet.php";
include "top/RequestCheckUtil.php";
include "top/TopLogger.php";
include "top/request/AlibabaAliqinFcSmsNumSendRequest.php";*/
include "TopSdk.php";
$c = new TopClient;
$c->appkey = '23334186';
$c->secretKey = '0cf93c8bf67330b98692f80dc0d67638';
$req = new AlibabaAliqinFcSmsNumSendRequest;
$req->setExtend("123456");
$req->setSmsType("normal");
$req->setSmsFreeSignName("大鱼测试");
$req->setSmsParam("{'code':'123456','product':'大油条'}");
$req->setRecNum("18217343620");
$req->setSmsTemplateCode("SMS_6746329");
$resp = $c->execute($req);
var_dump($resp);

    /*$httpdns = new HttpdnsGetRequest;
    $client = new ClusterTopClient("4272","0ebbcccfee18d7ad1aebc5b135ffa906");
    $client->gatewayUrl = "http://api.daily.taobao.net/router/rest";
    var_dump($client->execute($httpdns,"6100e23657fb0b2d0c78568e55a3031134be9a3a5d4b3a365753805"));*/
?>