<?php
namespace Common\Behavior\Derivative;

use Common\Behavior\CommonBehavior;

class CheckAppVersionBehavior extends CommonBehavior{

  static function commit(){
    # 检查必填参数
    $inquire_params = array('current_version');
    $result = self::inquire($inquire_params);
    if (false === $result->res)
      return $result;

    # 默认不用更新 data => false
    $result->data = false;
    $current_version = self::get_current_version();

    if ($_REQUEST['current_version'] != $current_version){
      $result->data = true;
      $result->current_version = $current_version;
    }
    return $result;
  }

  static function get_current_version(){
    $current_version = C('app.version');
    # patch
      $channel = $_REQUEST['channel'];
      if (stripos($channel,'NDR') > 0 && C('app.ndr_ver') )
        $current_version = C('app.ndr_ver');
      else if (stripos($channel,'IOS') > 0 && C('app.ios_ver') )
        $current_version = C('app.ios_ver');
    return $current_version;
  }
}
