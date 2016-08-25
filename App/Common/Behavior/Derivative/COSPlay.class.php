<?php
namespace Common\Behavior\Derivative;

Vendor('cos-php-sdk.include');
use Qcloud_cos\Auth;
use Qcloud_cos\Cosapi;
use Common\Atom\Result;

class COSPlay{

  static function commit(){
    $result = new Result(true);
    $result->data = self::sign(C('cos.user_bucket'));
    return $result;
  }

  static function sign($bucketName){
    $expired = time() + 60*15;
    return Auth::appSign($expired, $bucketName);
  }
}
