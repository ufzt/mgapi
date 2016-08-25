<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;
use Common\Atom\Seed;
Vendor('cos-php-sdk.include');
use Qcloud_cos\Cosapi;

class COSUpload extends CommonBehavior{

  static function commit() {
    set_time_limit(600);

     # 检查必填参数
    $inquire_params = array('file_path', 'file_name', 'cos_folder');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    $bucket_name = C('cos.admin_bucket');
    // $file_path = dirname(__FILE__).DIRECTORY_SEPARATOR.'office.mp4';
    $file_path = $_REQUEST['file_path'];
    $bucket_path = '/'.$_REQUEST['cos_folder'].'/'
                   .Seed::rand(4).'_'.$_REQUEST['file_name'];
    #第一次请求
      $response = Cosapi::upload(
        $bucket_name,
        $file_path,
        $bucket_path
      );
    if (0 != $response['code']) {
      $result->res = false;
      $result->msg = $response['message'];
      return $result;
    }
    $result->data = $response['data'];
    #第二次请求
    if (isset($response['data']) && isset($response['data']['resource_path']) )
      self::update($bucket_name, $response['data']['resource_path']);
    return $result;
  }

  static function update($bucket, $path){
    $response = Cosapi::update(
      $bucket,
      $path,
      null,
      null,
      array('Content-Disposition'=>'inline')
    );
    # 写日志
    if (0 != $response['code']){
      $log = 'COS UPDATE '.$path." FAILED.\nRESULT => ".json_encode($response);
      self::mp_log(__METHOD__, $log);
    }
    return $response;
  }
}
